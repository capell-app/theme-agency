<?php

declare(strict_types=1);

use Capell\EmailStudio\Enums\EmailEventType;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Models\EmailEvent;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;

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
