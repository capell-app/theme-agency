<?php

declare(strict_types=1);

use Capell\EmailStudio\Enums\EmailEventType;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Models\EmailEvent;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Models\EmailSuppression;
use Illuminate\Support\Facades\Route;

it('throttles provider webhook events', function (): void {
    $route = Route::getRoutes()->getByName('capell-email-studio.provider-events');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('throttle:capell-email-studio-provider-events');
});

it('records provider webhooks and updates matching recipients', function (): void {
    $token = 'provider-token';
    $profile = EmailProfile::factory()->create([
        'provider' => EmailProviderType::Fake,
        'webhook_endpoint_token_hash' => hash('sha256', $token),
    ]);
    $message = EmailMessage::factory()->for($profile, 'profile')->create();
    $recipient = EmailRecipient::factory()->for($message, 'message')->create([
        'email' => 'buyer@example.test',
        'normalized_email' => 'buyer@example.test',
        'email_hash' => hash('sha256', 'buyer@example.test'),
        'provider_message_id' => 'provider-message-1',
        'status' => EmailRecipientStatus::Sent,
    ]);

    $this->postJson(route('capell-email-studio.provider-events', ['token' => $token]), [
        'id' => 'provider-event-1',
        'event' => 'delivered',
        'message_id' => 'provider-message-1',
        'recipient' => 'buyer@example.test',
    ])->assertOk();

    $event = EmailEvent::query()->firstOrFail();
    $recipient->refresh();

    expect($event->email_profile_id)->toBe($profile->getKey())
        ->and($event->email_message_id)->toBe($message->getKey())
        ->and($event->email_recipient_id)->toBe($recipient->getKey())
        ->and($event->type)->toBe(EmailEventType::Delivered)
        ->and($recipient->status)->toBe(EmailRecipientStatus::Delivered)
        ->and($recipient->delivered_at)->not->toBeNull();
});

it('keeps provider webhook event ingestion idempotent per profile', function (): void {
    $token = 'idempotent-provider-token';
    $profile = EmailProfile::factory()->create([
        'provider' => EmailProviderType::Fake,
        'webhook_endpoint_token_hash' => hash('sha256', $token),
    ]);
    $message = EmailMessage::factory()->for($profile, 'profile')->create();
    EmailRecipient::factory()->for($message, 'message')->create([
        'provider_message_id' => 'provider-message-2',
    ]);
    $payload = [
        'id' => 'provider-event-2',
        'event' => 'bounce',
        'message_id' => 'provider-message-2',
    ];

    $this->postJson(route('capell-email-studio.provider-events', ['token' => $token]), $payload)->assertOk();
    $this->postJson(route('capell-email-studio.provider-events', ['token' => $token]), $payload)->assertOk();

    expect(EmailEvent::query()->where('email_profile_id', $profile->getKey())->count())->toBe(1);
});

it('suppresses recipients automatically after provider bounce events', function (): void {
    $token = 'bounce-provider-token';
    $profile = EmailProfile::factory()->create([
        'provider' => EmailProviderType::Fake,
        'webhook_endpoint_token_hash' => hash('sha256', $token),
    ]);
    $message = EmailMessage::factory()->for($profile, 'profile')->create([
        'site_scope_key' => 'global',
    ]);
    $recipient = EmailRecipient::factory()->for($message, 'message')->create([
        'email' => 'bounce@example.test',
        'normalized_email' => 'bounce@example.test',
        'email_hash' => hash('sha256', 'bounce@example.test'),
        'provider_message_id' => 'provider-message-3',
        'site_scope_key' => 'global',
    ]);

    $this->postJson(route('capell-email-studio.provider-events', ['token' => $token]), [
        'id' => 'provider-event-bounce',
        'event' => 'bounce',
        'message_id' => 'provider-message-3',
    ])->assertOk();

    $recipient->refresh();

    expect($recipient->status)->toBe(EmailRecipientStatus::Bounced)
        ->and(EmailSuppression::query()->where('email_hash', hash('sha256', 'bounce@example.test'))->exists())->toBeTrue();
});

it('rejects provider webhooks with unknown endpoint tokens', function (): void {
    $this->postJson(route('capell-email-studio.provider-events', ['token' => 'missing-token']), [
        'id' => 'provider-event-3',
        'event' => 'delivered',
    ])->assertNotFound();
});

it('requires webhook signatures when the profile has a webhook secret', function (): void {
    $token = 'signed-provider-token';
    EmailProfile::factory()->create([
        'provider' => EmailProviderType::Fake,
        'webhook_endpoint_token_hash' => hash('sha256', $token),
        'provider_settings' => ['webhook_secret' => 'provider-secret'],
    ]);
    $payload = [
        'id' => 'provider-event-4',
        'event' => 'delivered',
    ];
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $signature = hash_hmac('sha256', $body, 'provider-secret');

    $this->postJson(route('capell-email-studio.provider-events', ['token' => $token]), $payload)
        ->assertUnauthorized();

    $this->postJson(route('capell-email-studio.provider-events', ['token' => $token]), $payload, [
        'X-Capell-Email-Studio-Signature' => $signature,
    ])->assertOk();
});
