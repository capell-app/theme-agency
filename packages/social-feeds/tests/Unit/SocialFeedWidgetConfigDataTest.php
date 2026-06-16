<?php

declare(strict_types=1);

use Capell\SocialFeeds\Data\SocialFeedWidgetConfigData;
use Capell\SocialFeeds\Enums\SocialFeedLayout;
use Capell\SocialFeeds\Tests\TestCase;

uses(TestCase::class);

it('normalises editor state into bounded widget config', function (): void {
    config()->set('capell-social-feeds.max_limit', 24);

    $config = SocialFeedWidgetConfigData::fromState([
        'connection_id' => '9',
        'provider' => 'youtube',
        'layout' => 'paginated',
        'limit' => 500,
        'page_size' => 50,
        'columns' => 99,
        'show_caption' => false,
        'autoplay' => true,
        'transition_ms' => 9999,
        'aspect_ratio' => 'landscape',
        'media_alt_strategy' => 'caption',
        'empty_state' => 'message',
    ]);

    expect($config->connectionId)->toBe(9)
        ->and($config->provider)->toBe('youtube')
        ->and($config->layout)->toBe(SocialFeedLayout::Paginated)
        ->and($config->limit)->toBe(24)
        ->and($config->pageSize)->toBe(24)
        ->and($config->columns)->toBe(6)
        ->and($config->showCaption)->toBeFalse()
        ->and($config->autoplay)->toBeTrue()
        ->and($config->transitionMs)->toBe(5000)
        ->and($config->aspectRatio)->toBe('landscape')
        ->and($config->mediaAltStrategy)->toBe('caption')
        ->and($config->emptyState)->toBe('message');
});
