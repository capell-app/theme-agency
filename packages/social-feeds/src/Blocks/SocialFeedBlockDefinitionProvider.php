<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Blocks;

use Capell\BlockLibrary\Contracts\BlockDefinitionProvider;
use Capell\BlockLibrary\Data\BlockDefinitionData;
use Capell\BlockLibrary\Data\BlockSettingDefinitionData;
use Capell\BlockLibrary\Data\BlockVariantData;
use Capell\BlockLibrary\Data\BlockVariantKey;

final class SocialFeedBlockDefinitionProvider implements BlockDefinitionProvider
{
    /**
     * @return iterable<BlockDefinitionData>
     */
    public function definitions(): iterable
    {
        yield new BlockDefinitionData(
            key: 'social-feed',
            label: 'capell-social-feeds::package.blocks.social_feed.label',
            description: 'capell-social-feeds::package.blocks.social_feed.description',
            category: 'media',
            view: 'capell-social-feeds::blocks.social-feed',
            defaults: [
                'layout' => 'carousel',
                'limit' => 12,
                'page_size' => 6,
                'columns' => 3,
                'show_caption' => true,
                'show_author' => true,
                'show_date' => true,
                'show_media' => true,
                'autoplay' => false,
                'transition_ms' => 450,
                'aspect_ratio' => 'square',
                'media_alt_strategy' => 'auto',
                'empty_state' => 'hidden',
            ],
            renderer: SocialFeedBlockRenderer::class,
            safeForPublicOutput: true,
            sourcePackage: 'social-feeds',
            variants: [
                new BlockVariantData(BlockVariantKey::from('list'), 'capell-social-feeds::package.blocks.social_feed.variants.list', defaultSettings: ['layout' => 'list']),
                new BlockVariantData(BlockVariantKey::from('slideshow'), 'capell-social-feeds::package.blocks.social_feed.variants.slideshow', defaultSettings: ['layout' => 'slideshow']),
                new BlockVariantData(BlockVariantKey::from('carousel'), 'capell-social-feeds::package.blocks.social_feed.variants.carousel', defaultSettings: ['layout' => 'carousel']),
                new BlockVariantData(BlockVariantKey::from('paginated'), 'capell-social-feeds::package.blocks.social_feed.variants.paginated', defaultSettings: ['layout' => 'paginated']),
            ],
            defaultVariant: BlockVariantKey::from('carousel'),
            settings: $this->settings(),
            defaultSettings: [
                'layout' => 'carousel',
                'limit' => 12,
                'page_size' => 6,
                'columns' => 3,
            ],
        );
    }

    /**
     * @return array<int, BlockSettingDefinitionData>
     */
    private function settings(): array
    {
        return [
            new BlockSettingDefinitionData('connection_id', 'capell-social-feeds::package.blocks.social_feed.settings.connection_id', 'number', group: 'source', order: 10),
            new BlockSettingDefinitionData('provider', 'capell-social-feeds::package.blocks.social_feed.settings.provider', 'select', options: [
                'rss' => 'capell-social-feeds::package.providers.rss',
                'tiktok' => 'capell-social-feeds::package.providers.tiktok',
                'youtube' => 'capell-social-feeds::package.providers.youtube',
                'bluesky' => 'capell-social-feeds::package.providers.bluesky',
                'instagram' => 'capell-social-feeds::package.providers.instagram',
                'facebook' => 'capell-social-feeds::package.providers.facebook',
                'linkedin' => 'capell-social-feeds::package.providers.linkedin',
                'x' => 'capell-social-feeds::package.providers.x',
            ], group: 'source', order: 20),
            new BlockSettingDefinitionData('layout', 'capell-social-feeds::package.blocks.social_feed.settings.layout', 'select', 'carousel', options: [
                'list' => 'capell-social-feeds::package.blocks.social_feed.variants.list',
                'slideshow' => 'capell-social-feeds::package.blocks.social_feed.variants.slideshow',
                'carousel' => 'capell-social-feeds::package.blocks.social_feed.variants.carousel',
                'paginated' => 'capell-social-feeds::package.blocks.social_feed.variants.paginated',
            ], group: 'layout', order: 30),
            new BlockSettingDefinitionData('limit', 'capell-social-feeds::package.blocks.social_feed.settings.limit', 'number', 12, group: 'layout', order: 40),
            new BlockSettingDefinitionData('page_size', 'capell-social-feeds::package.blocks.social_feed.settings.page_size', 'number', 6, group: 'layout', order: 50),
            new BlockSettingDefinitionData('columns', 'capell-social-feeds::package.blocks.social_feed.settings.columns', 'number', 3, group: 'layout', order: 60),
            new BlockSettingDefinitionData('show_caption', 'capell-social-feeds::package.blocks.social_feed.settings.show_caption', 'boolean', true, group: 'display', order: 70),
            new BlockSettingDefinitionData('show_author', 'capell-social-feeds::package.blocks.social_feed.settings.show_author', 'boolean', true, group: 'display', order: 80),
            new BlockSettingDefinitionData('show_date', 'capell-social-feeds::package.blocks.social_feed.settings.show_date', 'boolean', true, group: 'display', order: 90),
            new BlockSettingDefinitionData('show_media', 'capell-social-feeds::package.blocks.social_feed.settings.show_media', 'boolean', true, group: 'display', order: 100),
            new BlockSettingDefinitionData('autoplay', 'capell-social-feeds::package.blocks.social_feed.settings.autoplay', 'boolean', false, group: 'motion', order: 110),
            new BlockSettingDefinitionData('transition_ms', 'capell-social-feeds::package.blocks.social_feed.settings.transition_ms', 'number', 450, group: 'motion', order: 120),
            new BlockSettingDefinitionData('aspect_ratio', 'capell-social-feeds::package.blocks.social_feed.settings.aspect_ratio', 'select', 'square', options: [
                'square' => 'capell-social-feeds::package.blocks.social_feed.aspect_ratios.square',
                'landscape' => 'capell-social-feeds::package.blocks.social_feed.aspect_ratios.landscape',
                'portrait' => 'capell-social-feeds::package.blocks.social_feed.aspect_ratios.portrait',
                'natural' => 'capell-social-feeds::package.blocks.social_feed.aspect_ratios.natural',
            ], group: 'media', order: 130),
            new BlockSettingDefinitionData('media_alt_strategy', 'capell-social-feeds::package.blocks.social_feed.settings.media_alt_strategy', 'select', 'auto', options: [
                'auto' => 'capell-social-feeds::package.blocks.social_feed.media_alt_strategies.auto',
                'caption' => 'capell-social-feeds::package.blocks.social_feed.media_alt_strategies.caption',
                'decorative' => 'capell-social-feeds::package.blocks.social_feed.media_alt_strategies.decorative',
            ], group: 'media', order: 135),
            new BlockSettingDefinitionData('empty_state', 'capell-social-feeds::package.blocks.social_feed.settings.empty_state', 'select', 'hidden', options: [
                'hidden' => 'capell-social-feeds::package.blocks.social_feed.empty_states.hidden',
                'message' => 'capell-social-feeds::package.blocks.social_feed.empty_states.message',
            ], group: 'display', order: 140),
        ];
    }
}
