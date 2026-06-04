<?php

declare(strict_types=1);

use Capell\BlockLibrary\Support\BlockRegistry;
use Capell\SocialFeeds\Blocks\SocialFeedBlockRenderer;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Tests\TestCase;

uses(TestCase::class);

it('registers a safe premium social feed block definition', function (): void {
    $definition = resolve(BlockRegistry::class)->getOrFail('social-feed');

    expect($definition->safeForPublicOutput)->toBeTrue()
        ->and($definition->variantKeys())->toBe(['list', 'slideshow', 'carousel', 'paginated'])
        ->and($definition->settings)->toHaveCount(14);
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
