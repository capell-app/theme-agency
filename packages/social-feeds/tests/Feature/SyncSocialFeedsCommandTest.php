<?php

declare(strict_types=1);

use Capell\SocialFeeds\Contracts\SocialFeedHostResolver;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Tests\Fixtures\StaticSocialFeedHostResolver;
use Capell\SocialFeeds\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

it('syncs connected social feed connections from the console command', function (): void {
    app()->instance(SocialFeedHostResolver::class, new StaticSocialFeedHostResolver([
        'example.test' => ['93.184.216.34'],
    ]));

    Http::fake([
        'https://example.test/feed.xml' => Http::response(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
                <channel>
                    <item>
                        <guid>command-post</guid>
                        <title>Command synced post</title>
                        <link>https://example.test/posts/command</link>
                        <pubDate>Wed, 04 Jun 2026 12:00:00 GMT</pubDate>
                    </item>
                </channel>
            </rss>
            XML, 200, ['Content-Type' => 'application/rss+xml']),
    ]);

    SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'News',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.test/feed.xml'],
    ]);

    $exitCode = Artisan::call('capell:social-feeds:sync', ['--all' => true, '--limit' => 5]);

    expect($exitCode)->toBe(0)
        ->and(SocialFeedItem::query()->where('external_id', 'command-post')->exists())->toBeTrue();
});
