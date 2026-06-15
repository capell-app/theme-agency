<?php

declare(strict_types=1);

use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\SocialFeedConnectionResource;
use Capell\SocialFeeds\Filament\Resources\SocialFeedItems\SocialFeedItemResource;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Tests\TestCase;

uses(TestCase::class);

it('exposes social feed connection and cached item resources when installed', function (): void {
    expect(SocialFeedConnectionResource::shouldRegisterNavigation())->toBeTrue()
        ->and(SocialFeedItemResource::shouldRegisterNavigation())->toBeTrue()
        ->and(SocialFeedConnectionResource::getModel())->toBe(SocialFeedConnection::class)
        ->and(SocialFeedItemResource::getModel())->toBe(SocialFeedItem::class);
});
