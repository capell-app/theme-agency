<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\InboundEmailReplyData;
use Capell\EmailStudio\Data\ProviderSendResultData;
use Capell\EmailStudio\Data\ProviderWebhookEventData;
use Capell\EmailStudio\Data\RenderedEmailData;
use Capell\EmailStudio\Data\SendEmailData;
use Capell\EmailStudio\Enums\EmailEventType;
use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Enums\EmailTemplateStatus;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Capell\EmailStudio\Enums\SuppressionReason;
use Capell\EmailStudio\Exceptions\EmailTemplateRenderingException;
use Capell\EmailStudio\Health\EmailStudioHealthCheck;
use Capell\EmailStudio\Models\EmailEvent;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateRegistration;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Models\EmailTrackingToken;
use Capell\EmailStudio\Support\EmailAddressNormalizer;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Capell\EmailStudio\Support\EmailVariableRenderer;
use Capell\EmailStudio\Support\Providers\FakeEmailProviderAdapter;
use Capell\EmailStudio\Support\Providers\PostmarkEmailProviderAdapter;
use Capell\EmailStudio\Support\Providers\SmtpEmailProviderAdapter;
use Capell\EmailStudio\Tests\Fixtures\CapturingEmailStudioMailer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\DataCollection;
use Symfony\Component\Mime\Email as SymfonyEmail;

it('keeps email studio send input and provider payloads as typed data', function (): void {
    $to = EmailAddressData::collect([new EmailAddressData('ben@example.com', 'Ben')], DataCollection::class);
    $headers = EmailHeaderData::collect([new EmailHeaderData('X-Test', 'yes')], DataCollection::class);

    $send = new SendEmailData(
        templateKey: 'welcome',
        to: $to,
        cc: EmailAddressData::collect([], DataCollection::class),
        bcc: EmailAddressData::collect([], DataCollection::class),
        siteId: 1,
        siteScopeKey: 'main',
        emailProfileId: 5,
        variables: ['name' => 'Ben'],
        headers: $headers,
        triggeredByType: 'user',
        triggeredById: 10,
        queue: false,
        locale: 'en',
    );
    $context = new EmailContextData(['name' => 'Ben'], preview: true, metadata: ['source' => 'test']);
    $reply = new InboundEmailReplyData('postmark', 'msg-1', 'reply@example.com', payload: ['raw' => true]);
    $providerResult = new ProviderSendResultData(true, ['msg-1'], []);
    $webhook = new ProviderWebhookEventData('postmark', 'delivered', 'msg-1', 'ben@example.com', 'event-1');
    $rendered = new RenderedEmailData('Subject', 'Preview', '<p>Hi</p>', 'Hi', ['X-Test' => 'yes']);

    expect($send->to)->toHaveCount(1)
        ->and($send->variables)->toBe(['name' => 'Ben'])
        ->and($context->preview)->toBeTrue()
        ->and($reply->providerMessageId)->toBe('msg-1')
        ->and($providerResult->successful)->toBeTrue()
        ->and($webhook->idempotencyKey)->toBe('event-1')
        ->and($rendered->headers)->toBe(['X-Test' => 'yes']);
});

it('normalizes email addresses and renders declared template variables safely', function (): void {
    $normalizer = new EmailAddressNormalizer;
    $renderer = new EmailVariableRenderer;

    expect($normalizer->normalize(' BEN@Example.COM '))->toBe('ben@example.com')
        ->and($normalizer->hash(' BEN@Example.COM '))->toBe(hash('sha256', 'ben@example.com'))
        ->and($renderer->renderHtml('Hello {{ name }}', ['name' => '<Ben>'], ['name'], false))
        ->toBe('Hello &lt;Ben&gt;')
        ->and($renderer->renderText('Active: {{ active }}', ['active' => true], ['active'], false))
        ->toBe('Active: 1')
        ->and($renderer->renderEscapedText(null, [], [], false))->toBeNull()
        ->and($renderer->renderHtml('Hello {{ missing }}', [], ['missing'], true))
        ->toBe('Hello {{ missing }}');
});

it('rejects missing non-preview template variables', function (): void {
    (new EmailVariableRenderer)->renderText('Hello {{ name }}', [], ['name'], false);
})->throws(EmailTemplateRenderingException::class);

it('exposes labels for email studio state enums', function (): void {
    expect(EmailEventType::Delivered->getLabel())->toBeString()
        ->and(EmailMessageStatus::PartiallyFailed->getLabel())->toBeString()
        ->and(EmailProviderType::Postmark->getLabel())->toBeString()
        ->and(EmailRecipientStatus::Suppressed->getLabel())->toBeString()
        ->and(EmailTemplateStatus::Approved->getLabel())->toBeString()
        ->and(EmailVariantStatus::Retired->getLabel())->toBeString()
        ->and(SuppressionReason::Complaint->getLabel())->toBeString();
});

it('normalizes provider webhooks and inbound replies without leaking transport details', function (): void {
    $smtp = new SmtpEmailProviderAdapter;
    $fake = new FakeEmailProviderAdapter;

    $smtpWebhook = $smtp->normalizeWebhookPayload([
        'event' => 'delivered',
        'message_id' => 'smtp-message',
        'recipient' => 'ben@example.com',
        'id' => 'smtp-event',
    ]);
    $fakeReply = $fake->normalizeInboundReply([
        'message_id' => 'fake-message',
        'from_email' => 'reply@example.com',
        'from_name' => 'Reply Sender',
        'subject' => 'Re: Hello',
        'text' => 'Plain reply',
        'html' => '<p>Reply</p>',
    ]);

    expect($smtpWebhook->provider)->toBe('smtp')
        ->and($smtpWebhook->eventType)->toBe('delivered')
        ->and($smtpWebhook->providerMessageId)->toBe('smtp-message')
        ->and($smtpWebhook->idempotencyKey)->toBe('smtp-event')
        ->and($fakeReply->provider)->toBe('fake')
        ->and($fakeReply->fromEmail)->toBe('reply@example.com')
        ->and($fakeReply->htmlBody)->toBe('<p>Reply</p>');
});

it('delivers queued recipients through the smtp provider and maps provider message ids', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('attachments/report.txt', 'Report body');

    $profile = EmailProfile::factory()->create([
        'from_email' => 'sender@example.com',
        'from_name' => 'Sender',
        'reply_to_email' => 'reply@example.com',
        'reply_to_name' => 'Reply',
        'provider_settings' => ['mailer' => 'smtp-coverage'],
    ]);
    $message = EmailMessage::factory()
        ->for($profile, 'profile')
        ->create([
            'subject' => 'Workflow update',
            'rendered_html' => '<p>Hello team</p>',
            'rendered_text' => 'Hello team',
            'attachments' => [
                [
                    'disk' => 'local',
                    'path' => 'attachments/report.txt',
                    'name' => 'report.txt',
                    'mime' => 'text/plain',
                ],
            ],
        ]);
    $to = EmailRecipient::factory()->for($message, 'message')->create([
        'type' => 'to',
        'email' => 'to@example.com',
        'name' => 'To Recipient',
        'status' => EmailRecipientStatus::Queued,
    ]);
    $cc = EmailRecipient::factory()->for($message, 'message')->create([
        'type' => 'cc',
        'email' => 'cc@example.com',
        'name' => 'CC Recipient',
        'status' => EmailRecipientStatus::Queued,
    ]);
    $bcc = EmailRecipient::factory()->for($message, 'message')->create([
        'type' => 'bcc',
        'email' => 'bcc@example.com',
        'name' => 'BCC Recipient',
        'status' => EmailRecipientStatus::Queued,
    ]);
    EmailRecipient::factory()->for($message, 'message')->create([
        'type' => 'to',
        'email' => 'sent@example.com',
        'status' => EmailRecipientStatus::Sent,
    ]);

    $mailer = new CapturingEmailStudioMailer;
    Mail::shouldReceive('mailer')
        ->once()
        ->with('smtp-coverage')
        ->andReturn($mailer);

    $result = (new SmtpEmailProviderAdapter)->send($message);

    expect($result->successful)->toBeTrue()
        ->and($result->recipientProviderMessageIds)->toBe([
            $to->getKey() => 'smtp-provider-message-id',
            $cc->getKey() => 'smtp-provider-message-id',
            $bcc->getKey() => 'smtp-provider-message-id',
        ])
        ->and($mailer->message)->toBeInstanceOf(SymfonyEmail::class)
        ->and($mailer->message?->getSubject())->toBe('Workflow update')
        ->and($mailer->message?->getFrom()[0]->getAddress())->toBe('sender@example.com')
        ->and($mailer->message?->getReplyTo()[0]->getAddress())->toBe('reply@example.com')
        ->and($mailer->message?->getTo()[0]->getAddress())->toBe('to@example.com')
        ->and($mailer->message?->getCc()[0]->getAddress())->toBe('cc@example.com')
        ->and($mailer->message?->getBcc()[0]->getAddress())->toBe('bcc@example.com')
        ->and($mailer->message?->getHtmlBody())->toBe('<p>Hello team</p>')
        ->and($mailer->message?->getTextBody())->toBe('Hello team')
        ->and($mailer->message?->getAttachments())->toHaveCount(1)
        ->and($mailer->message?->getAttachments()[0]->getPreparedHeaders()->getHeaderParameter('Content-Disposition', 'filename'))->toBe('report.txt');
});

it('registers email provider adapters and exposes package health metadata', function (): void {
    $registry = new EmailProviderRegistry;
    $adapter = new PostmarkEmailProviderAdapter;

    $returned = $registry->register(EmailProviderType::Postmark, $adapter);

    expect($returned)->toBe($registry)
        ->and($registry->adapter(EmailProviderType::Postmark))->toBe($adapter)
        ->and($registry->supportedProviders())->toBe(['postmark'])
        ->and(EmailStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('persists registered email templates through the registry', function (): void {
    Site::factory()->create(['id' => 5]);

    $registry = new EmailTemplateRegistry;

    $registrations = $registry
        ->register(
            key: 'welcome',
            name: 'Welcome',
            variables: ['name', 'email'],
            description: 'Welcome email',
            packageName: 'capell-app/test',
            siteId: 5,
            siteScopeKey: 'primary',
        )
        ->persist();

    expect($registrations)->toHaveCount(1)
        ->and($registrations[0])->toBeInstanceOf(EmailTemplateRegistration::class)
        ->and($registrations[0]->getAttribute('template_key'))->toBe('welcome')
        ->and($registrations[0]->getAttribute('variables'))->toBe(['name', 'email'])
        ->and($registrations[0]->getAttribute('site_scope_key'))->toBe('primary')
        ->and($registrations[0]->getAttribute('is_static_renderable'))->toBeFalse();
});

it('does not expose metadata-only registrations as static renderable definitions', function (): void {
    $registry = (new EmailTemplateRegistry)
        ->register(
            key: 'metadata-only',
            name: 'Metadata only',
            variables: ['name'],
        );

    expect($registry->findDefinition('metadata-only'))->toBeNull()
        ->and($registry->definitions())->toBe([]);
});

it('skips email template persistence before migrations create the registration table', function (): void {
    Schema::shouldReceive('hasTable')
        ->once()
        ->with((new EmailTemplateRegistration)->getTable())
        ->andReturnFalse();

    $registrations = (new EmailTemplateRegistry)
        ->register(
            key: 'welcome',
            name: 'Welcome',
            variables: ['name'],
        )
        ->persist();

    expect($registrations)->toBe([]);
});

it('skips email template persistence until the registration table has authoring columns', function (): void {
    $registrationTable = (new EmailTemplateRegistration)->getTable();

    Schema::table($registrationTable, function (Blueprint $table) use ($registrationTable): void {
        if (Schema::hasColumn($registrationTable, 'is_static_renderable')) {
            $table->dropColumn('is_static_renderable');
        }

        if (Schema::hasColumn($registrationTable, 'default_locale')) {
            $table->dropColumn('default_locale');
        }
    });

    $registrations = (new EmailTemplateRegistry)
        ->register(
            key: 'welcome',
            name: 'Welcome',
            variables: ['name'],
        )
        ->persist();

    expect($registrations)->toBe([])
        ->and(EmailTemplateRegistration::query()->where('template_key', 'welcome')->exists())->toBeFalse();
});

it('casts email studio model state and links event tracking records', function (): void {
    $profile = EmailProfile::factory()->create([
        'provider_settings' => ['mailer' => 'smtp'],
        'track_opens' => true,
    ]);
    $template = EmailTemplate::factory()->create([
        'variables' => ['name'],
    ]);
    $variant = EmailTemplateVariant::factory()
        ->for($template, 'template')
        ->for($profile, 'profile')
        ->create([
            'status' => EmailVariantStatus::Active,
        ]);
    $message = EmailMessage::factory()
        ->for($profile, 'profile')
        ->for($template, 'template')
        ->for($variant, 'templateVariant')
        ->create();
    $recipient = EmailRecipient::factory()
        ->for($message, 'message')
        ->create();
    $event = EmailEvent::factory()
        ->for($profile, 'profile')
        ->for($message, 'message')
        ->for($recipient, 'recipient')
        ->create([
            'type' => EmailEventType::Opened,
            'provider_payload' => ['id' => 'event-1'],
        ]);
    $token = EmailTrackingToken::factory()
        ->for($recipient, 'recipient')
        ->create([
            'destination_url' => 'https://example.test',
        ]);

    expect($profile->refresh()->provider)->toBe(EmailProviderType::Smtp)
        ->and($profile->provider_settings)->toBe(['mailer' => 'smtp'])
        ->and($template->refresh()->variables)->toBe(['name'])
        ->and($variant->refresh()->status)->toBe(EmailVariantStatus::Active)
        ->and($event->refresh()->type)->toBe(EmailEventType::Opened)
        ->and($event->provider_payload)->toBe(['id' => 'event-1'])
        ->and($event->message->is($message))->toBeTrue()
        ->and($token->refresh()->recipient->is($recipient))->toBeTrue();
});
