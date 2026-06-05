<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\EmailStudio\Actions\DeliverEmailMessageAction;
use Capell\EmailStudio\Actions\SendEmailAction;
use Capell\EmailStudio\Actions\SuppressEmailAddressAction;
use Capell\EmailStudio\Contracts\EmailProviderAdapter;
use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\InboundEmailReplyData;
use Capell\EmailStudio\Data\ProviderSendResultData;
use Capell\EmailStudio\Data\ProviderWebhookEventData;
use Capell\EmailStudio\Data\SendEmailData;
use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Enums\SuppressionReason;
use Capell\EmailStudio\Exceptions\RetryableEmailDeliveryException;
use Capell\EmailStudio\Jobs\SendEmailJob;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Illuminate\Support\Facades\Queue;
use Spatie\LaravelData\DataCollection;

it('delivers queued recipients and rechecks suppressions before provider handoff', function (): void {
    Queue::fake();
    createEmailStudioSendFixtures();

    $message = SendEmailAction::run(new SendEmailData(
        templateKey: 'forms.confirmation',
        to: new DataCollection(EmailAddressData::class, [
            new EmailAddressData('first@example.com'),
            new EmailAddressData('second@example.com'),
        ]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: 12,
        siteScopeKey: 'site:12',
        emailProfileId: null,
        variables: ['name' => 'Ben'],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
    ));

    $deliveredMessage = DeliverEmailMessageAction::run($message);

    expect($deliveredMessage->status)->toBe(EmailMessageStatus::Sent)
        ->and($deliveredMessage->sent_at)->not->toBeNull();

    $recipients = EmailRecipient::query()->where('email_message_id', $message->getKey())->orderBy('email')->get();
    $firstRecipient = $recipients->get(0);
    $secondRecipient = $recipients->get(1);

    throw_if(! $firstRecipient instanceof EmailRecipient || ! $secondRecipient instanceof EmailRecipient, RuntimeException::class, 'Expected two email recipients to be available after delivery.');

    expect($recipients)->toHaveCount(2)
        ->and($recipients->pluck('status')->all())->toBe([
            EmailRecipientStatus::Sent,
            EmailRecipientStatus::Sent,
        ])
        ->and($recipients->pluck('provider_message_id')->all())->toBe([
            'fake-' . $message->id . '-' . $firstRecipient->id,
            'fake-' . $message->id . '-' . $secondRecipient->id,
        ]);

    $suppressionMessage = SendEmailAction::run(new SendEmailData(
        templateKey: 'forms.confirmation',
        to: new DataCollection(EmailAddressData::class, [
            new EmailAddressData('allowed@example.com'),
            new EmailAddressData('blocked@example.com'),
        ]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: 12,
        siteScopeKey: 'site:12',
        emailProfileId: null,
        variables: ['name' => 'Ben'],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
    ));

    SuppressEmailAddressAction::run(
        email: 'blocked@example.com',
        reason: SuppressionReason::Manual,
        siteId: 12,
        siteScopeKey: 'site:12',
        source: 'test',
    );

    $suppressedDeliveryMessage = DeliverEmailMessageAction::run($suppressionMessage);

    $allowedRecipient = EmailRecipient::query()->where('email', 'allowed@example.com')->sole();
    $blockedRecipient = EmailRecipient::query()->where('email', 'blocked@example.com')->sole();

    expect($suppressedDeliveryMessage->status)->toBe(EmailMessageStatus::PartiallyFailed)
        ->and($allowedRecipient->status)->toBe(EmailRecipientStatus::Sent)
        ->and($allowedRecipient->provider_message_id)->toBe('fake-' . $suppressionMessage->id . '-' . $allowedRecipient->id)
        ->and($blockedRecipient->status)->toBe(EmailRecipientStatus::Suppressed)
        ->and($blockedRecipient->provider_message_id)->toBeNull()
        ->and($blockedRecipient->suppressed_at)->not->toBeNull();

    resolve(EmailProviderRegistry::class)->register(EmailProviderType::Fake, new class implements EmailProviderAdapter
    {
        public function send(EmailMessage $message): ProviderSendResultData
        {
            return new ProviderSendResultData(
                successful: false,
                failureReason: 'Provider rejected the message.',
            );
        }

        public function normalizeWebhookPayload(array $payload, array $headers = []): ProviderWebhookEventData
        {
            return new ProviderWebhookEventData(provider: 'fake', eventType: 'failed', payload: $payload);
        }

        public function normalizeInboundReply(array $payload, array $headers = []): InboundEmailReplyData
        {
            return new InboundEmailReplyData(provider: 'fake', providerMessageId: null, fromEmail: 'sender@example.com', payload: $payload);
        }
    });

    $providerFailureMessage = SendEmailAction::run(new SendEmailData(
        templateKey: 'forms.confirmation',
        to: new DataCollection(EmailAddressData::class, [new EmailAddressData('failure@example.com')]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: 12,
        siteScopeKey: 'site:12',
        emailProfileId: null,
        variables: ['name' => 'Ben'],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
    ));

    $failedDeliveryMessage = DeliverEmailMessageAction::run($providerFailureMessage);
    $failedRecipient = EmailRecipient::query()->where('email', 'failure@example.com')->sole();

    expect($failedDeliveryMessage->status)->toBe(EmailMessageStatus::Failed)
        ->and($failedDeliveryMessage->failed_at)->not->toBeNull()
        ->and($failedDeliveryMessage->failure_reason)->toBe('Provider rejected the message.')
        ->and($failedRecipient->status)->toBe(EmailRecipientStatus::Failed)
        ->and($failedRecipient->sent_at)->toBeNull()
        ->and($failedRecipient->provider_message_id)->toBeNull()
        ->and($failedRecipient->failure_reason)->toBe('Provider rejected the message.');

    resolve(EmailProviderRegistry::class)->register(EmailProviderType::Fake, new class implements EmailProviderAdapter
    {
        public function send(EmailMessage $message): ProviderSendResultData
        {
            throw new RuntimeException('Transport exploded.');
        }

        public function normalizeWebhookPayload(array $payload, array $headers = []): ProviderWebhookEventData
        {
            return new ProviderWebhookEventData(provider: 'fake', eventType: 'failed', payload: $payload);
        }

        public function normalizeInboundReply(array $payload, array $headers = []): InboundEmailReplyData
        {
            return new InboundEmailReplyData(provider: 'fake', providerMessageId: null, fromEmail: 'sender@example.com', payload: $payload);
        }
    });

    $exceptionFailureMessage = SendEmailAction::run(new SendEmailData(
        templateKey: 'forms.confirmation',
        to: new DataCollection(EmailAddressData::class, [new EmailAddressData('exception@example.com')]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: 12,
        siteScopeKey: 'site:12',
        emailProfileId: null,
        variables: ['name' => 'Ben'],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
    ));

    expect(fn (): EmailMessage => DeliverEmailMessageAction::run($exceptionFailureMessage))
        ->toThrow(RetryableEmailDeliveryException::class, 'Transport exploded.');

    $retryableMessage = $exceptionFailureMessage->fresh(['recipients']);
    $exceptionRecipient = EmailRecipient::query()->where('email', 'exception@example.com')->sole();

    throw_if(! $retryableMessage instanceof EmailMessage, RuntimeException::class, 'Expected retryable email message to exist.');

    expect($retryableMessage->status)->toBe(EmailMessageStatus::Queued)
        ->and($retryableMessage->failure_reason)->toBe('Transport exploded.')
        ->and($exceptionRecipient->status)->toBe(EmailRecipientStatus::Queued)
        ->and($exceptionRecipient->failure_reason)->toBeNull();

    (new SendEmailJob($exceptionFailureMessage->id))->failed(new RuntimeException('Transport exploded.'));

    $terminalFailureMessage = $exceptionFailureMessage->fresh(['recipients']);
    $terminalFailureRecipient = EmailRecipient::query()->where('email', 'exception@example.com')->sole();

    throw_if(! $terminalFailureMessage instanceof EmailMessage, RuntimeException::class, 'Expected terminal failure email message to exist.');

    expect($terminalFailureMessage->status)->toBe(EmailMessageStatus::Failed)
        ->and($terminalFailureMessage->failed_at)->not->toBeNull()
        ->and($terminalFailureMessage->failure_reason)->toBe('Transport exploded.')
        ->and($terminalFailureRecipient->status)->toBe(EmailRecipientStatus::Failed)
        ->and($terminalFailureRecipient->failure_reason)->toBe('Transport exploded.');

    $immediateFailureMessage = SendEmailAction::run(new SendEmailData(
        templateKey: 'forms.confirmation',
        to: new DataCollection(EmailAddressData::class, [new EmailAddressData('immediate-exception@example.com')]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: 12,
        siteScopeKey: 'site:12',
        emailProfileId: null,
        variables: ['name' => 'Ben'],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: false,
    ));
    $immediateFailureRecipient = EmailRecipient::query()->where('email', 'immediate-exception@example.com')->sole();

    expect($immediateFailureMessage->status)->toBe(EmailMessageStatus::Failed)
        ->and($immediateFailureMessage->failure_reason)->toBe('Transport exploded.')
        ->and($immediateFailureRecipient->status)->toBe(EmailRecipientStatus::Failed)
        ->and($immediateFailureRecipient->failure_reason)->toBe('Transport exploded.');
});

it('does not send a message that another worker recently claimed', function (): void {
    Queue::fake();
    createEmailStudioSendFixtures();

    $message = SendEmailAction::run(new SendEmailData(
        templateKey: 'forms.confirmation',
        to: new DataCollection(EmailAddressData::class, [new EmailAddressData('claimed@example.com')]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: 12,
        siteScopeKey: 'site:12',
        emailProfileId: null,
        variables: ['name' => 'Ben'],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
    ));

    $message->forceFill(['status' => EmailMessageStatus::Sending])->save();

    resolve(EmailProviderRegistry::class)->register(EmailProviderType::Fake, new class implements EmailProviderAdapter
    {
        public function send(EmailMessage $message): ProviderSendResultData
        {
            throw new RuntimeException('Provider should not be called for claimed messages.');
        }

        public function normalizeWebhookPayload(array $payload, array $headers = []): ProviderWebhookEventData
        {
            return new ProviderWebhookEventData(provider: 'fake', eventType: 'ignored', payload: $payload);
        }

        public function normalizeInboundReply(array $payload, array $headers = []): InboundEmailReplyData
        {
            return new InboundEmailReplyData(provider: 'fake', providerMessageId: null, fromEmail: 'sender@example.com', payload: $payload);
        }
    });

    $deliveredMessage = DeliverEmailMessageAction::run($message);

    expect($deliveredMessage->status)->toBe(EmailMessageStatus::Sending)
        ->and(EmailRecipient::query()->where('email_message_id', $message->getKey())->sole()->status)
        ->toBe(EmailRecipientStatus::Queued);
});

it('reclaims stale sending messages after a worker crash', function (): void {
    Queue::fake();
    createEmailStudioSendFixtures();
    config(['capell-email-studio.sending_lock_ttl_seconds' => 300]);

    $message = SendEmailAction::run(new SendEmailData(
        templateKey: 'forms.confirmation',
        to: new DataCollection(EmailAddressData::class, [new EmailAddressData('stale@example.com')]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: 12,
        siteScopeKey: 'site:12',
        emailProfileId: null,
        variables: ['name' => 'Ben'],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
    ));

    $message->forceFill([
        'status' => EmailMessageStatus::Sending,
        'updated_at' => now()->subMinutes(10),
    ])->save();

    $deliveredMessage = DeliverEmailMessageAction::run($message);

    expect($deliveredMessage->status)->toBe(EmailMessageStatus::Sent)
        ->and(EmailRecipient::query()->where('email_message_id', $message->getKey())->sole()->status)
        ->toBe(EmailRecipientStatus::Sent);
});

function createEmailStudioSendFixtures(): void
{
    Site::factory()->create(['id' => 12]);

    EmailProfile::factory()->create([
        'site_id' => 12,
        'site_scope_key' => 'site:12',
        'provider' => EmailProviderType::Fake,
        'is_default' => true,
    ]);

    $template = EmailTemplate::factory()->create([
        'site_id' => 12,
        'site_scope_key' => 'site:12',
        'key' => 'forms.confirmation',
        'variables' => ['name'],
    ]);

    EmailTemplateVariant::factory()->for($template, 'template')->create([
        'site_id' => 12,
        'site_scope_key' => 'site:12',
        'locale' => 'en',
        'subject' => 'Hello {{ name }}',
        'html_body' => '<p>Hello {{ name }}</p>',
        'text_body' => 'Hello {{ name }}',
    ]);
}
