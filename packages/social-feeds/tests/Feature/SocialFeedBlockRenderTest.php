<?php

declare(strict_types=1);

use Capell\BlockLibrary\Support\BlockRegistry;
use Capell\SocialFeeds\Actions\FetchSocialFeedRenderDataAction;
use Capell\SocialFeeds\Blocks\SocialFeedBlockRenderer;
use Capell\SocialFeeds\Data\SocialFeedRenderData;
use Capell\SocialFeeds\Data\SocialFeedRenderItemData;
use Capell\SocialFeeds\Data\SocialFeedWidgetConfigData;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Tests\TestCase;
use Illuminate\Support\Facades\DB;

uses(TestCase::class);

it('registers a safe premium social feed block definition', function (): void {
    $definition = resolve(BlockRegistry::class)->getOrFail('social-feed');

    expect($definition->safeForPublicOutput)->toBeTrue()
        ->and($definition->variantKeys())->toBe(['list', 'slideshow', 'carousel', 'paginated'])
        ->and($definition->settings)->toHaveCount(15);
});

it('renders cached items without leaking private connection details or raw html', function (): void {
    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Private Feed Name',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.test/feed.xml', 'api_key' => 'secret-token'],
    ]);

    SocialFeedItem::query()->create([
        'connection_id' => $connection->getKey(),
        'provider' => 'rss',
        'external_id' => 'public-post',
        'type' => 'link',
        'text' => '<script>alert("x")</script> Launch update',
        'permalink' => 'https://example.test/posts/public-post',
        'author_name' => 'Example Author',
        'published_at' => now(),
    ]);

    $definition = resolve(BlockRegistry::class)->getOrFail('social-feed');
    $html = (new SocialFeedBlockRenderer)->render($definition, [
        'connection_id' => $connection->getKey(),
        'layout' => 'list',
        'limit' => 3,
    ])->toHtml();

    expect($html)->toContain('capell-social-feed--list')
        ->and($html)->toContain('Launch update')
        ->and($html)->toContain('Example Author')
        ->and($html)->not->toContain('secret-token')
        ->and($html)->not->toContain('connection_id')
        ->and($html)->not->toContain('<script>alert("x")</script>');
});

it('fetches render data within a bounded query budget', function (): void {
    $connection = SocialFeedConnection::query()->create([
        'provider' => 'rss',
        'name' => 'Campaign Feed',
        'status' => SocialFeedConnectionStatus::Connected,
        'credentials' => ['feed_url' => 'https://example.test/feed.xml'],
    ]);

    foreach (range(1, 5) as $postNumber) {
        SocialFeedItem::query()->create([
            'connection_id' => $connection->getKey(),
            'provider' => 'rss',
            'external_id' => "budget-post-{$postNumber}",
            'type' => 'link',
            'text' => "Budget post {$postNumber}",
            'permalink' => "https://example.test/posts/budget-{$postNumber}",
            'author_name' => 'Example Author',
            'published_at' => now()->subMinutes($postNumber),
        ]);
    }

    $queryCount = 0;
    DB::listen(static function () use (&$queryCount): void {
        $queryCount++;
    });

    $renderData = FetchSocialFeedRenderDataAction::run(SocialFeedWidgetConfigData::fromState([
        'connection_id' => $connection->getKey(),
        'layout' => 'carousel',
        'limit' => 3,
    ]));
    throw_unless($renderData instanceof SocialFeedRenderData, RuntimeException::class, 'Expected social feed render data.');
    $items = $renderData->items;

    expect($queryCount)->toBeLessThanOrEqual(2)
        ->and($items)->toHaveCount(3);
});

it('renders prepared social feed data in Blade without database queries', function (): void {
    $renderData = new SocialFeedRenderData(
        config: SocialFeedWidgetConfigData::fromState([
            'layout' => 'list',
            'limit' => 1,
        ]),
        items: [
            new SocialFeedRenderItemData(
                provider: 'rss',
                type: 'image',
                text: 'Prepared post',
                permalink: 'https://example.test/posts/prepared',
                mediaUrl: 'https://example.test/images/prepared.jpg',
                thumbnailUrl: null,
                authorName: 'Prepared Author',
                authorAvatarUrl: null,
                publishedAt: now()->toImmutable(),
            ),
        ],
    );

    $queryCount = 0;
    DB::listen(static function () use (&$queryCount): void {
        $queryCount++;
    });

    $html = view('capell-social-feeds::blocks.social-feed', ['feed' => $renderData])->render();

    expect($queryCount)->toBe(0)
        ->and($html)->toContain('Prepared post')
        ->and($html)->toContain('Prepared Author');
});

it('derives social feed media alt text when captions are hidden', function (): void {
    $renderData = new SocialFeedRenderData(
        config: SocialFeedWidgetConfigData::fromState([
            'layout' => 'list',
            'limit' => 1,
            'show_caption' => false,
        ]),
        items: [
            new SocialFeedRenderItemData(
                provider: 'rss',
                type: 'image',
                text: 'Image-focused launch',
                permalink: 'https://example.test/posts/image-focused',
                mediaUrl: 'https://example.test/images/image-focused.jpg',
                thumbnailUrl: null,
                authorName: 'Example Author',
                authorAvatarUrl: null,
                publishedAt: now()->toImmutable(),
            ),
        ],
    );

    $html = view('capell-social-feeds::blocks.social-feed', ['feed' => $renderData])->render();

    expect($html)->toContain('alt="Image-focused launch"')
        ->and($html)->not->toContain('capell-social-feed__caption');
});

it('keeps social feed media decorative when configured', function (): void {
    $renderData = new SocialFeedRenderData(
        config: SocialFeedWidgetConfigData::fromState([
            'layout' => 'carousel',
            'limit' => 1,
            'media_alt_strategy' => 'decorative',
        ]),
        items: [
            new SocialFeedRenderItemData(
                provider: 'rss',
                type: 'image',
                text: 'Decorative repeat',
                permalink: 'https://example.test/posts/decorative-repeat',
                mediaUrl: 'https://example.test/images/decorative-repeat.jpg',
                thumbnailUrl: null,
                authorName: 'Example Author',
                authorAvatarUrl: null,
                publishedAt: now()->toImmutable(),
            ),
        ],
    );

    $html = view('capell-social-feeds::blocks.social-feed', ['feed' => $renderData])->render();

    expect($html)->toContain('alt=""');
});
