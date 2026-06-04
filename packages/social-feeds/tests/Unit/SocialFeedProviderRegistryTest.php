<?php

declare(strict_types=1);

use Capell\SocialFeeds\Contracts\SocialFeedProviderProvider;
use Capell\SocialFeeds\Providers\Drivers\RssFeedProvider;
use Capell\SocialFeeds\Support\SocialFeedProviderRegistry;
use Capell\SocialFeeds\Tests\Fixtures\FixtureSocialFeedProvider;
use Capell\SocialFeeds\Tests\Fixtures\FixtureSocialFeedProviderProvider;
use Capell\SocialFeeds\Tests\TestCase;

uses(TestCase::class);

it('registers the built-in premium provider set', function (): void {
    $registry = resolve(SocialFeedProviderRegistry::class);

    expect(array_keys($registry->all()))->toBe([
        'rss',
        'tiktok',
        'youtube',
        'bluesky',
        'instagram',
        'facebook',
        'linkedin',
        'x',
    ]);
});

it('rejects duplicate providers', function (): void {
    $registry = new SocialFeedProviderRegistry;

    $registry->register(new RssFeedProvider);
    $registry->register(new RssFeedProvider);
})->throws(InvalidArgumentException::class, 'Social feed provider [rss] is already registered.');

it('allows consuming packages to register providers through the tag seam', function (): void {
    app()->tag([FixtureSocialFeedProviderProvider::class], SocialFeedProviderProvider::TAG);

    $registry = new SocialFeedProviderRegistry;

    foreach (app()->tagged(SocialFeedProviderProvider::TAG) as $provider) {
        if ($provider instanceof SocialFeedProviderProvider) {
            $provider->registerProviders($registry);
        }
    }

    expect($registry->get('fixture'))->toBeInstanceOf(FixtureSocialFeedProvider::class);
});
