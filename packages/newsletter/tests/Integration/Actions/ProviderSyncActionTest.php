<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\RequeueDueProviderSyncAttemptsAction;
use Capell\Newsletter\Actions\SyncSubscriberToProviderAction;
use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\ProviderType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Enums\SyncStatus;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Models\ProviderSubscriber;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Models\SyncAttempt;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('syncs durable attempts through a provider adapter', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'sync@example.com',
    ]);
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);
    $audience = ProviderAudience::query()->create([
        'provider_connection_id' => $connection->getKey(),
        'name' => 'Default',
        'remote_id' => 'fake-audience',
        'is_default' => true,
        'sync_subscribed_only' => true,
    ]);

    $syncAttempt = SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'provider_audience_id' => $audience->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::Pending,
        'attempts' => 0,
    ]);

    SyncSubscriberToProviderAction::run($syncAttempt);

    expect($syncAttempt->refresh()->sync_status)->toBe(SyncStatus::Succeeded)
        ->and(ProviderSubscriber::query()->where('subscriber_id', $subscriber->getKey())->exists())->toBeTrue();
});

it('does not execute a provider sync attempt more than once', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'duplicate-sync@example.com',
    ]);
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);
    $audience = ProviderAudience::query()->create([
        'provider_connection_id' => $connection->getKey(),
        'name' => 'Default',
        'remote_id' => 'fake-audience',
        'is_default' => true,
        'sync_subscribed_only' => true,
    ]);
    $syncAttempt = SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'provider_audience_id' => $audience->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::Pending,
        'attempts' => 0,
    ]);

    SyncSubscriberToProviderAction::run($syncAttempt);
    SyncSubscriberToProviderAction::run($syncAttempt);

    expect($syncAttempt->refresh()->sync_status)->toBe(SyncStatus::Succeeded)
        ->and($syncAttempt->attempts)->toBe(1)
        ->and(ProviderSubscriber::query()->where('subscriber_id', $subscriber->getKey())->count())->toBe(1);
});

it('normalizes provider webhooks into local subscriber state', function (): void {
    $site = $this->createNewsletterSite();
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);

    $this->postJson(route('capell-newsletter.provider-webhook', ['providerConnection' => $connection]), [
        'email' => 'webhook@example.com',
        'status' => SubscriberStatus::Unsubscribed->value,
        'event_type' => 'unsubscribe',
    ])->assertOk();

    expect(Subscriber::query()->forEmail($site->getKey(), 'webhook@example.com')->first()?->status)
        ->toBe(SubscriberStatus::Unsubscribed);
});

it('blocks fake provider webhook writes in production unless explicitly enabled', function (): void {
    $site = $this->createNewsletterSite();
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);

    app()->detectEnvironment(static fn (): string => 'production');

    try {
        $this->postJson(route('capell-newsletter.provider-webhook', ['providerConnection' => $connection]), [
            'email' => 'blocked-webhook@example.com',
            'status' => SubscriberStatus::Unsubscribed->value,
            'event_type' => 'unsubscribe',
        ])->assertForbidden();

        expect(Subscriber::query()->forEmail($site->getKey(), 'blocked-webhook@example.com')->exists())
            ->toBeFalse();

        config()->set('capell-newsletter.providers.allow_fake_provider', true);

        $this->postJson(route('capell-newsletter.provider-webhook', ['providerConnection' => $connection]), [
            'email' => 'allowed-webhook@example.com',
            'status' => SubscriberStatus::Unsubscribed->value,
            'event_type' => 'unsubscribe',
        ])->assertOk();

        expect(Subscriber::query()->forEmail($site->getKey(), 'allowed-webhook@example.com')->exists())
            ->toBeTrue();
    } finally {
        app()->detectEnvironment(static fn (): string => 'testing');
    }
});

it('blocks fake provider sync attempts in production unless explicitly enabled', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'blocked-sync@example.com',
    ]);
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);
    $audience = ProviderAudience::query()->create([
        'provider_connection_id' => $connection->getKey(),
        'name' => 'Default',
        'remote_id' => 'fake-audience',
        'is_default' => true,
        'sync_subscribed_only' => true,
    ]);
    $syncAttempt = SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'provider_audience_id' => $audience->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::Pending,
        'attempts' => 0,
    ]);

    app()->detectEnvironment(static fn (): string => 'production');

    try {
        SyncSubscriberToProviderAction::run($syncAttempt);

        expect($syncAttempt->refresh()->sync_status)->toBe(SyncStatus::RetryScheduled)
            ->and($syncAttempt->error_message)->toBe('The fake newsletter provider is disabled for this environment.')
            ->and(ProviderSubscriber::query()->where('subscriber_id', $subscriber->getKey())->exists())->toBeFalse();
    } finally {
        app()->detectEnvironment(static fn (): string => 'testing');
    }
});

it('acknowledges duplicate provider webhook retries without re-recording consent', function (): void {
    if (! Schema::hasTable('newsletter_processed_webhook_events')) {
        Schema::create('newsletter_processed_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_connection_id')
                ->constrained('newsletter_provider_connections', 'id', 'newsletter_processed_webhooks_connection_fk')
                ->cascadeOnDelete();
            $table->string('remote_event_id');
            $table->string('event_type');
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();
            $table->unique(
                ['provider_connection_id', 'remote_event_id', 'event_type'],
                'newsletter_processed_webhook_events_uniq',
            );
        });
    }

    $site = $this->createNewsletterSite();
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);

    $payload = [
        'email' => 'webhook-retry@example.com',
        'status' => SubscriberStatus::Unsubscribed->value,
        'event_type' => 'unsubscribe',
        'remoteId' => 'provider-event-1',
    ];

    $this->postJson(route('capell-newsletter.provider-webhook', ['providerConnection' => $connection]), $payload)
        ->assertOk();

    $subscriber = Subscriber::query()->forEmail($site->getKey(), 'webhook-retry@example.com')->first();
    $consentEventsCount = $subscriber?->consentEvents()->count();
    expect(DB::table('newsletter_processed_webhook_events')->count())->toBe(1);

    $this->postJson(route('capell-newsletter.provider-webhook', ['providerConnection' => $connection]), $payload)
        ->assertOk();

    expect($subscriber)->not->toBeNull()
        ->and($subscriber?->refresh()->consentEvents()->count())->toBe($consentEventsCount);
});

it('requeues due provider sync attempts without touching future attempts', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create(['site_id' => $site->getKey()]);
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);

    $oldestDueAttempt = SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::RetryScheduled,
        'payload_hash' => 'oldest',
        'attempts' => 1,
        'next_retry_at' => now()->subMinutes(10),
    ]);
    $newerDueAttempt = SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::RetryScheduled,
        'payload_hash' => 'newer',
        'attempts' => 1,
        'next_retry_at' => now()->subMinute(),
    ]);
    $futureAttempt = SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::RetryScheduled,
        'payload_hash' => 'future',
        'attempts' => 1,
        'next_retry_at' => now()->addMinute(),
    ]);

    $count = RequeueDueProviderSyncAttemptsAction::run(limit: 1, dispatchJobs: false);

    expect($count)->toBe(1)
        ->and($oldestDueAttempt->refresh()->sync_status)->toBe(SyncStatus::Pending)
        ->and($oldestDueAttempt->next_retry_at)->toBeNull()
        ->and($newerDueAttempt->refresh()->sync_status)->toBe(SyncStatus::RetryScheduled)
        ->and($futureAttempt->refresh()->sync_status)->toBe(SyncStatus::RetryScheduled);
});

it('does not requeue an attempt already claimed by another retry runner', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create(['site_id' => $site->getKey()]);
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Fake',
        'provider' => ProviderType::Fake,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);

    $dueAttempt = SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::RetryScheduled,
        'payload_hash' => 'claimed',
        'attempts' => 1,
        'next_retry_at' => now()->subMinute(),
    ]);

    $dispatcher = SyncAttempt::getEventDispatcher();

    try {
        SyncAttempt::retrieved(function (SyncAttempt $syncAttempt) use ($dueAttempt): void {
            if ((int) $syncAttempt->getKey() !== (int) $dueAttempt->getKey()) {
                return;
            }

            SyncAttempt::query()
                ->whereKey($syncAttempt->getKey())
                ->update([
                    'sync_status' => SyncStatus::Pending,
                    'next_retry_at' => null,
                ]);
        });

        expect(RequeueDueProviderSyncAttemptsAction::run(dispatchJobs: false))->toBe(0)
            ->and($dueAttempt->refresh()->sync_status)->toBe(SyncStatus::Pending)
            ->and($dueAttempt->next_retry_at)->toBeNull();
    } finally {
        SyncAttempt::setEventDispatcher($dispatcher);
    }
});
