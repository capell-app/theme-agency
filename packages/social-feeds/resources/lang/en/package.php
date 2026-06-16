<?php

declare(strict_types=1);

return [
    'blocks' => [
        'social_feed' => [
            'label' => 'Social feed',
            'description' => 'Render cached social posts using list, slideshow, carousel, or paginated layouts.',
            'settings' => [
                'connection_id' => 'Connection',
                'provider' => 'Provider',
                'layout' => 'Layout',
                'limit' => 'Item limit',
                'page_size' => 'Page size',
                'columns' => 'Columns',
                'show_caption' => 'Show captions',
                'show_author' => 'Show author',
                'show_date' => 'Show date',
                'show_media' => 'Show media',
                'autoplay' => 'Autoplay',
                'transition_ms' => 'Transition speed',
                'aspect_ratio' => 'Media ratio',
                'media_alt_strategy' => 'Media alt text',
                'empty_state' => 'Empty state',
            ],
            'variants' => [
                'list' => 'Plain list',
                'slideshow' => 'Slideshow',
                'carousel' => 'Carousel',
                'paginated' => 'Paginated',
            ],
            'aspect_ratios' => [
                'square' => 'Square',
                'landscape' => 'Landscape',
                'portrait' => 'Portrait',
                'natural' => 'Natural',
            ],
            'empty_states' => [
                'hidden' => 'Hidden',
                'message' => 'Message',
            ],
            'media_alt_strategies' => [
                'auto' => 'Auto',
                'caption' => 'Use caption text',
                'decorative' => 'Decorative',
            ],
        ],
    ],
    'providers' => [
        'rss' => 'RSS / Atom',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'bluesky' => 'Bluesky',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'x' => 'X',
    ],
    'resources' => [
        'connections' => 'Social feed connections',
        'items' => 'Cached social feed items',
    ],
    'fields' => [
        'name' => 'Name',
        'provider' => 'Provider',
        'status' => 'Status',
        'site_id' => 'Site ID',
        'feed_url' => 'Feed URL',
        'handle' => 'Handle',
        'api_key' => 'API key',
        'meta' => 'Metadata',
        'sync_status' => 'Sync status',
        'items' => 'Items',
        'last_synced_at' => 'Last synced',
        'last_sync_error' => 'Last sync error',
        'connection' => 'Connection',
        'type' => 'Type',
        'text' => 'Text',
        'author' => 'Author',
        'published_at' => 'Published',
        'permalink' => 'Permalink',
    ],
    'statuses' => [
        'connected' => 'Connected',
        'disconnected' => 'Disconnected',
        'error' => 'Error',
        'pending' => 'Pending',
    ],
    'actions' => [
        'sync_now' => 'Sync now',
    ],
    'notifications' => [
        'connection_synced' => '{0} No new social feed items were synced.|{1} Synced one social feed item.|[2,*] Synced :count social feed items.',
    ],
    'commands' => [
        'sync' => [
            'description' => 'Sync connected Social Feeds into the cached item table.',
            'completed' => '{0} No social feed items were synced.|{1} Synced one social feed item.|[2,*] Synced :count social feed items.',
        ],
    ],
    'empty' => 'No social posts are available yet.',
];
