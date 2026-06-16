<?php

declare(strict_types=1);

use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Health\SocialFeedsHealthCheck;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Tests\TestCase;
use Carbon\CarbonImmutable;

uses(TestCase::class);

it('passes stale sync diagnostics when connected feeds are fresh', function (): void {
    config()->set('capell-social-feeds.stale_sync_minutes', 60);

    createHealthCheckSocialFeedConnection([
        'last_synced_at' => CarbonImmutable::now()->subMinutes(15),
    ]);

    $result = (new SocialFeedsHealthCheck)->staleSyncCheck();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toContain('within 60 minutes');
});

it('fails stale sync diagnostics for connected feeds that never synced or are stale', function (): void {
    config()->set('capell-social-feeds.stale_sync_minutes', 60);

    createHealthCheckSocialFeedConnection([
        'name' => 'Fresh feed',
        'last_synced_at' => CarbonImmutable::now()->subMinutes(15),
    ]);
    createHealthCheckSocialFeedConnection([
        'name' => 'Stale feed',
        'last_synced_at' => CarbonImmutable::now()->subMinutes(90),
    ]);
    createHealthCheckSocialFeedConnection([
        'name' => 'Never synced feed',
        'last_synced_at' => null,
    ]);
    createHealthCheckSocialFeedConnection([
        'name' => 'Disconnected feed',
        'status' => SocialFeedConnectionStatus::Disconnected,
        'last_synced_at' => null,
    ]);

    $result = (new SocialFeedsHealthCheck)->staleSyncCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('2 connected Social Feed connection(s)')
        ->and($result->remediation)->toContain('capell:social-feeds:sync --all');
});

/**
 * @param  array<string, mixed>  $overrides
 */
function createHealthCheckSocialFeedConnection(array $overrides = []): SocialFeedConnection
{
    return SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'News',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.com/feed.xml'],
        ...$overrides,
    ]);
}
