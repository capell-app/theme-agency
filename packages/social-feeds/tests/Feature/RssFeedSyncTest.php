<?php

declare(strict_types=1);

use Capell\SocialFeeds\Actions\SyncSocialFeedConnectionAction;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Tests\TestCase;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

it('syncs rss feed items into the cached social feed table', function (): void {
    Http::fake([
        'https://example.test/feed.xml' => Http::response(<<<'XML'
            <?xml version="1.0" encoding="UTF-8" ?>
            <rss version="2.0">
                <channel>
                    <title>Example Updates</title>
                    <item>
                        <title>First post</title>
                        <link>https://example.test/posts/first</link>
                        <guid>first-post</guid>
                        <description>First post summary</description>
                        <pubDate>Tue, 02 Jun 2026 10:00:00 GMT</pubDate>
                    </item>
                    <item>
                        <title>Second post</title>
                        <link>https://example.test/posts/second</link>
                        <guid>second-post</guid>
                        <description>Second post summary</description>
                        <pubDate>Tue, 02 Jun 2026 11:00:00 GMT</pubDate>
                    </item>
                </channel>
            </rss>
            XML, 200, ['Content-Type' => 'application/rss+xml']),
    ]);

    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Example Updates',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.test/feed.xml'],
    ]);

    $synced = SyncSocialFeedConnectionAction::run($connection, 10);

    expect($synced)->toBe(2)
        ->and($connection->refresh()->last_sync_error)->toBeNull()
        ->and(SocialFeedItem::query()->where('external_id', 'first-post')->exists())->toBeTrue()
        ->and(SocialFeedItem::query()->where('external_id', 'second-post')->exists())->toBeTrue();
});

it('keeps existing cached items when a provider request fails', function (): void {
    Http::fake([
        'https://example.test/feed.xml' => Http::response('Unavailable', 503),
    ]);

    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Example Updates',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.test/feed.xml'],
    ]);

    SocialFeedItem::query()->create([
        'connection_id' => $connection->getKey(),
        'provider' => 'rss',
        'external_id' => 'existing',
        'type' => 'link',
        'text' => 'Existing cached post',
        'permalink' => 'https://example.test/posts/existing',
        'published_at' => now(),
    ]);

    $synced = SyncSocialFeedConnectionAction::run($connection, 10);

    expect($synced)->toBe(0)
        ->and($connection->refresh()->status)->toBe(SocialFeedConnectionStatus::Connected)
        ->and(SocialFeedItem::query()->where('external_id', 'existing')->exists())->toBeTrue();
});

it('syncs configured social providers through feed url credentials', function (): void {
    Http::fake([
        'https://youtube.test/channel.xml' => Http::response(<<<'XML'
            <?xml version="1.0" encoding="UTF-8" ?>
            <feed xmlns="http://www.w3.org/2005/Atom">
                <entry>
                    <id>video-1</id>
                    <title>Launch walkthrough</title>
                    <link href="https://youtube.test/watch/video-1" />
                    <published>2026-06-03T09:00:00Z</published>
                    <author><name>Capell TV</name></author>
                </entry>
            </feed>
            XML, 200, ['Content-Type' => 'application/atom+xml']),
    ]);

    $connection = SocialFeedConnection::query()->create([
        'provider' => 'youtube',
        'name' => 'Capell TV',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://youtube.test/channel.xml'],
    ]);

    $synced = SyncSocialFeedConnectionAction::run($connection, 10);

    expect($synced)->toBe(1)
        ->and($connection->refresh()->last_sync_error)->toBeNull()
        ->and(SocialFeedItem::query()->where('provider', 'youtube')->where('external_id', 'video-1')->exists())->toBeTrue();
});

it('marks configured social providers as errored when feed url is missing', function (): void {
    $connection = SocialFeedConnection::query()->create([
        'provider' => 'bluesky',
        'name' => 'Capell Social',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => [],
    ]);

    $synced = SyncSocialFeedConnectionAction::run($connection, 10);

    expect($synced)->toBe(0)
        ->and($connection->refresh()->status)->toBe(SocialFeedConnectionStatus::Error)
        ->and($connection->last_sync_error)->toContain('requires a feed_url credential');
});
