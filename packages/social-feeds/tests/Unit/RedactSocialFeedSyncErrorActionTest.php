<?php

declare(strict_types=1);

use Capell\SocialFeeds\Actions\RedactSocialFeedSyncErrorAction;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Tests\TestCase;

uses(TestCase::class);

it('redacts social feed credentials from persisted sync errors', function (): void {
    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Private Feed',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => [
            'feed_url' => 'https://feed.example.test/rss?token=feed-secret-token',
            'api_key' => 'feed-api-key',
        ],
    ]);

    $redacted = RedactSocialFeedSyncErrorAction::run(
        new RuntimeException('Failed https://feed.example.test/rss?token=feed-secret-token with Authorization: Bearer feed-api-key'),
        $connection,
    );

    expect($redacted)->not->toContain('feed-secret-token')
        ->and($redacted)->not->toContain('feed-api-key')
        ->and($redacted)->toContain('[redacted]');
});
