<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\Newsletter\Actions\RedactNewsletterProviderErrorMessageAction;
use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\ProviderType;
use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Models\Subscriber;

it('redacts newsletter provider credentials and subscriber email from persisted errors', function (): void {
    $site = Site::factory()->create();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'subscriber@example.test',
    ]);
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Provider',
        'provider' => ProviderType::Mailchimp,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'mailchimp-secret-key'],
        'oauth_tokens' => ['access_token' => 'oauth-secret-token'],
        'webhook_secret' => 'newsletter-webhook-secret',
        'is_enabled' => true,
    ]);

    $message = 'Authorization: Bearer oauth-secret-token failed for subscriber@example.test with api_key=mailchimp-secret-key and webhook_secret=newsletter-webhook-secret';

    $redacted = RedactNewsletterProviderErrorMessageAction::run($message, $connection, $subscriber);

    expect($redacted)->not->toContain('oauth-secret-token')
        ->and($redacted)->not->toContain('subscriber@example.test')
        ->and($redacted)->not->toContain('mailchimp-secret-key')
        ->and($redacted)->not->toContain('newsletter-webhook-secret')
        ->and($redacted)->toContain('[redacted]');
});
