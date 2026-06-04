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
                'empty_state' => 'Empty state',
            ],
            'variants' => [
                'list' => 'Plain list',
                'slideshow' => 'Slideshow',
                'carousel' => 'Carousel',
                'paginated' => 'Paginated',
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
    'empty' => 'No social posts are available yet.',
];
