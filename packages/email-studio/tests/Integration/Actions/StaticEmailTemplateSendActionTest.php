<?php

declare(strict_types=1);

use Capell\EmailStudio\Actions\SendEmailAction;
use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailAttachmentData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplateVariableData;
use Capell\EmailStudio\Data\SendEmailData;
use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Jobs\SendEmailJob;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Illuminate\Support\Facades\Queue;
use Spatie\LaravelData\DataCollection;

it('falls back to registered static definitions when no database variant exists', function (): void {
    Queue::fake();

    EmailProfile::factory()->create([
        'provider' => EmailProviderType::Fake,
        'is_default' => true,
    ]);

    resolve(EmailTemplateRegistry::class)->registerDefinition(new EmailTemplateDefinitionData(
        key: 'comments.verify-email',
        packageName: 'capell-app/comments',
        name: 'Comment email verification',
        variables: [
            new EmailTemplateVariableData('author.name'),
            new EmailTemplateVariableData('verification_url'),
        ],
        subject: 'Verify your comment, {{ author.name }}',
        html: '<p>Hello {{ author.name }}</p><a href="{{ verification_url }}">Verify</a>',
        text: 'Hello {{ author.name }}: {{ verification_url }}',
        cc: [
            ['email' => 'audit@example.com', 'name' => 'Audit'],
        ],
    ));

    $message = SendEmailAction::run(new SendEmailData(
        templateKey: 'comments.verify-email',
        to: new DataCollection(EmailAddressData::class, [new EmailAddressData('reader@example.com', 'Reader')]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: null,
        siteScopeKey: 'global',
        emailProfileId: null,
        variables: [
            'author' => ['name' => '<Reader>'],
            'verification_url' => 'https://example.test/comments/verify',
        ],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
        locale: 'en',
        attachments: new DataCollection(EmailAttachmentData::class, [
            new EmailAttachmentData('local', 'receipts/comment.txt', 'comment.txt', 'text/plain'),
        ]),
    ));

    expect($message)->toBeInstanceOf(EmailMessage::class)
        ->and($message->status)->toBe(EmailMessageStatus::Queued)
        ->and($message->email_template_id)->toBeNull()
        ->and($message->email_template_variant_id)->toBeNull()
        ->and($message->subject)->toBe('Verify your comment, <Reader>')
        ->and($message->rendered_html)->toContain('<p>Hello &lt;Reader&gt;</p>')
        ->and($message->attachments)->toBe([
            [
                'disk' => 'local',
                'path' => 'receipts/comment.txt',
                'name' => 'comment.txt',
                'mime' => 'text/plain',
            ],
        ]);

    expect(EmailRecipient::query()->where('email_message_id', $message->getKey())->orderBy('id')->pluck('type', 'email')->all())
        ->toBe([
            'reader@example.com' => 'to',
            'audit@example.com' => 'cc',
        ]);

    Queue::assertPushed(SendEmailJob::class, fn (SendEmailJob $job): bool => $job->emailMessageId === $message->getKey());
});

it('prefers active database variants over registered static definitions', function (): void {
    Queue::fake();

    EmailProfile::factory()->create([
        'provider' => EmailProviderType::Fake,
        'is_default' => true,
    ]);

    resolve(EmailTemplateRegistry::class)->registerDefinition(new EmailTemplateDefinitionData(
        key: 'comments.verify-email',
        packageName: 'capell-app/comments',
        name: 'Comment email verification',
        variables: [new EmailTemplateVariableData('author.name')],
        subject: 'Static {{ author.name }}',
        html: '<p>Static {{ author.name }}</p>',
        text: 'Static {{ author.name }}',
    ));

    $template = EmailTemplate::factory()->create([
        'key' => 'comments.verify-email',
        'variables' => ['author.name'],
    ]);

    EmailTemplateVariant::factory()->for($template, 'template')->create([
        'locale' => 'en',
        'subject' => 'Database {{ author.name }}',
        'html_body' => '<p>Database {{ author.name }}</p>',
        'text_body' => 'Database {{ author.name }}',
    ]);

    $message = SendEmailAction::run(new SendEmailData(
        templateKey: 'comments.verify-email',
        to: new DataCollection(EmailAddressData::class, [new EmailAddressData('reader@example.com')]),
        cc: new DataCollection(EmailAddressData::class, []),
        bcc: new DataCollection(EmailAddressData::class, []),
        siteId: null,
        siteScopeKey: 'global',
        emailProfileId: null,
        variables: ['author' => ['name' => 'Reader']],
        headers: new DataCollection(EmailHeaderData::class, []),
        triggeredByType: null,
        triggeredById: null,
        queue: true,
        locale: 'en',
    ));

    expect($message->email_template_id)->toBe($template->getKey())
        ->and($message->email_template_variant_id)->not->toBeNull()
        ->and($message->subject)->toBe('Database Reader')
        ->and($message->rendered_text)->toBe('Database Reader');
});
