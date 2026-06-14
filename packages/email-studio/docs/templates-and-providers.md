# Templates And Providers

Email Studio separates template registration, message creation, provider delivery, and provider adapter normalization. Package code should call Actions and registries rather than creating message rows by hand.

## Register a Template

Use `EmailTemplateRegistry::registerDefinition()` from a service provider when another package needs a static fallback template available to editors. Static definitions should point at package-owned Blade views or literal bodies; database template rows are customizations, not the source of truth.

```php
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplateVariableData;
use Capell\EmailStudio\Support\EmailTemplateRegistry;

$this->app->afterResolving(EmailTemplateRegistry::class, static function (EmailTemplateRegistry $registry): void {
    $registry->registerDefinition(new EmailTemplateDefinitionData(
        key: 'access-approved',
        packageName: 'capell-app/access-gate',
        name: 'Access approved',
        description: 'Sent when an access request is approved.',
        variables: [
            new EmailTemplateVariableData(name: 'name', label: 'Recipient name', sampleValue: 'Sam Editor'),
            new EmailTemplateVariableData(name: 'claim_url', label: 'Claim URL', sampleValue: 'https://example.test/access/claim/token'),
            new EmailTemplateVariableData(name: 'config.app.name', label: 'App name', required: false),
        ],
        defaultLocale: 'en',
        subject: 'Your access request was approved',
        previewText: 'Claim access to {{ config.app.name }}.',
        htmlView: 'capell-access-gate::emails.access-approved',
        text: "Hi {{ name }},\n\nClaim access: {{ claim_url }}",
        defaultThemeKey: 'default',
    ));
});
```

The registry persists registration metadata through `RegisterEmailTemplateAction`. Keep the key stable; editors may already have variants attached to it. The legacy `register()` method is metadata-only compatibility. It makes templates discoverable but does not provide a static render fallback.

Resolution order is:

1. Active database variant for the requested site scope and locale.
2. Active global database variant for the locale.
3. Registered static definition for the locale, then its default locale.
4. Existing Email Studio rendering exception when no renderable template exists.

Variables are explicit. Declare flat names such as `name` or dot-path names such as `customer.name`; Email Studio renders only values supplied in the variables array or Data payload. Config variables must be declared as `config.*` and allow-listed in Email Studio settings, with secrets excluded by default.

## Send an Email

Use `SendEmailAction` with `SendEmailData`. It resolves the profile, approved template, matching variant, suppression status, recipients, queue, and rendered body.

```php
use Capell\EmailStudio\Actions\SendEmailAction;
use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\SendEmailData;
use Spatie\LaravelData\DataCollection;

$message = SendEmailAction::run(new SendEmailData(
    templateKey: 'access-approved',
    to: EmailAddressData::collect([
        new EmailAddressData(email: 'sam@example.test', name: 'Sam Editor'),
    ], DataCollection::class),
    cc: EmailAddressData::collect([], DataCollection::class),
    bcc: EmailAddressData::collect([], DataCollection::class),
    siteId: 1,
    siteScopeKey: 'global',
    emailProfileId: null,
    variables: [
        'name' => 'Sam Editor',
        'claim_url' => 'https://example.test/access/claim/token',
    ],
    headers: EmailHeaderData::collect([], DataCollection::class),
    triggeredByType: null,
    triggeredById: null,
));
```

If the constructor shape changes, update this example with the data class.

## Register a Provider Adapter

Provider adapters implement `EmailProviderAdapter`. They normalize outbound delivery immediately, and expose webhook payload plus inbound reply normalizers for later ingestion slices. The current package does not ship webhook or reply ingestion routes/actions.

```php
use Capell\EmailStudio\Contracts\EmailProviderAdapter;
use Capell\EmailStudio\Data\InboundEmailReplyData;
use Capell\EmailStudio\Data\ProviderSendResultData;
use Capell\EmailStudio\Data\ProviderWebhookEventData;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Support\EmailProviderRegistry;

final class DemoEmailProviderAdapter implements EmailProviderAdapter
{
    public function send(EmailMessage $message): ProviderSendResultData
    {
        return new ProviderSendResultData(successful: true);
    }

    public function normalizeWebhookPayload(array $payload, array $headers = []): ProviderWebhookEventData
    {
        return new ProviderWebhookEventData(
            provider: 'demo',
            eventType: (string) ($payload['event'] ?? 'delivered'),
            providerMessageId: isset($payload['message_id']) ? (string) $payload['message_id'] : null,
            recipientEmail: isset($payload['email']) ? (string) $payload['email'] : null,
            payload: $payload,
        );
    }

    public function normalizeInboundReply(array $payload, array $headers = []): InboundEmailReplyData
    {
        return new InboundEmailReplyData(
            provider: 'demo',
            providerMessageId: isset($payload['message_id']) ? (string) $payload['message_id'] : null,
            fromEmail: (string) ($payload['from_email'] ?? ''),
            fromName: isset($payload['from_name']) ? (string) $payload['from_name'] : null,
            subject: isset($payload['subject']) ? (string) $payload['subject'] : null,
            textBody: isset($payload['text']) ? (string) $payload['text'] : null,
            htmlBody: isset($payload['html']) ? (string) $payload['html'] : null,
            payload: $payload,
        );
    }
}

$this->app->afterResolving(EmailProviderRegistry::class, static function (EmailProviderRegistry $registry): void {
    $registry->register(EmailProviderType::Fake, new DemoEmailProviderAdapter);
});
```

Use a new `EmailProviderType` case before registering a real provider. The `Fake` case is for local/test behavior.

## Config Keys

| Key                                             | Use                                                                                                                            |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `capell-email-studio.default_provider`          | Provider used when an email profile does not override it.                                                                      |
| `capell-email-studio.queue`                     | Queue used by `SendEmailJob`. Can be set with `CAPELL_EMAIL_STUDIO_QUEUE`.                                                     |
| `capell-email-studio.track_opens`               | Legacy top-level flag retained for compatibility; MailTracker open tracking is configured through `mail_tracker.inject_pixel`. |
| `capell-email-studio.track_clicks`              | Legacy top-level flag retained for compatibility; MailTracker click tracking is configured through `mail_tracker.track_links`. |
| `capell-email-studio.body_retention_days`       | How long rendered message bodies should be retained.                                                                           |
| `capell-email-studio.webhook_tolerance_seconds` | Reserved tolerance window for planned provider webhook validation.                                                             |
| `capell-email-studio.public_route_prefix`       | Public prefix for provider webhook routes. Can be set with `CAPELL_EMAIL_STUDIO_PUBLIC_PREFIX`.                                |
| `capell-email-studio.tracking_token_ttl_days`   | Lifetime of tracking tokens.                                                                                                   |
| `capell-email-studio.webhook_rate_limit`        | Reserved rate limiter name for planned webhooks.                                                                               |
| `capell-email-studio.tracking_rate_limit`       | Reserved rate limiter name for planned tracking routes.                                                                        |

Table-name keys are part of install and migration behavior. Document them in migration notes rather than setup prose unless a host app needs custom table names.

## Verification

```bash
vendor/bin/pest packages/email-studio/tests --configuration=phpunit.xml
```
