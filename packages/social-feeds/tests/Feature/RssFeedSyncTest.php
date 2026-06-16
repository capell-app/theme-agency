<?php

declare(strict_types=1);

use Capell\SocialFeeds\Actions\SyncSocialFeedConnectionAction;
use Capell\SocialFeeds\Actions\UpsertSocialFeedItemsAction;
use Capell\SocialFeeds\Contracts\SocialFeedHostResolver;
use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Enums\SocialFeedItemType;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Tests\Fixtures\StaticSocialFeedHostResolver;
use Capell\SocialFeeds\Tests\TestCase;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

beforeEach(function (): void {
    app()->instance(SocialFeedHostResolver::class, new StaticSocialFeedHostResolver([
        'example.test' => ['93.184.216.34'],
        'youtube.test' => ['93.184.216.34'],
    ]));
});

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

it('does not follow rss feed redirects to unchecked targets', function (): void {
    /** @var array<string, mixed>|null $requestOptions */
    $requestOptions = null;

    Http::fake(function (Request $request, array $options) use (&$requestOptions): PromiseInterface {
        $requestOptions = $options;

        return Http::response('', 302, ['Location' => 'http://169.254.169.254/latest/meta-data']);
    });

    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Example Updates',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.test/feed.xml'],
    ]);

    $synced = SyncSocialFeedConnectionAction::run($connection, 10);

    expect($synced)->toBe(0)
        ->and($connection->refresh()->status)->toBe(SocialFeedConnectionStatus::Connected)
        ->and(data_get($requestOptions, 'allow_redirects'))->toBeFalse()
        ->and(data_get($requestOptions, 'curl.' . CURLOPT_RESOLVE))->toBe(['example.test:443:93.184.216.34']);

    Http::assertSentCount(1);
});

it('strips unsafe rss item links and media urls before caching feed items', function (): void {
    Http::fake([
        'https://example.test/feed.xml' => Http::response(<<<'XML'
            <?xml version="1.0" encoding="UTF-8" ?>
            <rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
                <channel>
                    <title>Example Updates</title>
                    <item>
                        <title>Unsafe post</title>
                        <link>javascript:alert(1)</link>
                        <guid>unsafe-post</guid>
                        <description>Unsafe post summary</description>
                        <enclosure url="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==" type="image/png" />
                        <media:content url="file:///etc/passwd" />
                        <pubDate>Tue, 02 Jun 2026 10:00:00 GMT</pubDate>
                    </item>
                    <item>
                        <title>Safe post</title>
                        <link>https://example.test/posts/safe</link>
                        <guid>safe-post</guid>
                        <description>Safe post summary</description>
                        <enclosure url="https://example.test/images/safe.jpg" type="image/jpeg" />
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

    $unsafeItem = SocialFeedItem::query()->where('external_id', 'unsafe-post')->firstOrFail();
    $safeItem = SocialFeedItem::query()->where('external_id', 'safe-post')->firstOrFail();

    expect($synced)->toBe(2)
        ->and($unsafeItem->permalink)->toBeNull()
        ->and($unsafeItem->media_url)->toBeNull()
        ->and($unsafeItem->thumbnail_url)->toBeNull()
        ->and($safeItem->permalink)->toBe('https://example.test/posts/safe')
        ->and($safeItem->media_url)->toBe('https://example.test/images/safe.jpg')
        ->and($safeItem->thumbnail_url)->toBe('https://example.test/images/safe.jpg');
});

it('blocks rss feed urls that target private hosts', function (): void {
    Http::fake();

    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Private Updates',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'http://127.0.0.1/feed.xml'],
    ]);

    $synced = SyncSocialFeedConnectionAction::run($connection, 10);

    expect($synced)->toBe(0)
        ->and($connection->refresh()->status)->toBe(SocialFeedConnectionStatus::Error)
        ->and($connection->last_sync_error)->toContain('host is not allowed');

    Http::assertNothingSent();
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

it('prunes cached feed items beyond the configured per-connection retention limit', function (): void {
    config()->set('capell-social-feeds.retention_items', 2);

    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Example Updates',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.test/feed.xml'],
    ]);

    $synced = UpsertSocialFeedItemsAction::run($connection, [
        socialFeedPost('old-post', 'Old post', 3),
        socialFeedPost('middle-post', 'Middle post', 2),
        socialFeedPost('new-post', 'New post', 1),
    ]);

    expect($synced)->toBe(3)
        ->and(SocialFeedItem::query()->where('connection_id', $connection->getKey())->count())->toBe(2)
        ->and(SocialFeedItem::query()->where('external_id', 'old-post')->exists())->toBeFalse()
        ->and(SocialFeedItem::query()->where('external_id', 'middle-post')->exists())->toBeTrue()
        ->and(SocialFeedItem::query()->where('external_id', 'new-post')->exists())->toBeTrue();
});

function socialFeedPost(string $externalId, string $text, int $daysAgo): SocialFeedPostData
{
    return new SocialFeedPostData(
        externalId: $externalId,
        type: SocialFeedItemType::Link,
        text: $text,
        permalink: "https://example.test/posts/{$externalId}",
        mediaUrl: null,
        thumbnailUrl: null,
        authorName: 'Example Author',
        authorAvatarUrl: null,
        publishedAt: now()->subDays($daysAgo)->toImmutable(),
        raw: [],
    );
}
