<?php

declare(strict_types=1);

return [
    'redirects' => [
        'allowed_status_codes' => [301, 302, 307, 308, 410],
        'absolute_target_allowed_hosts' => [
            //
        ],
        'allow_app_url_host' => true,
        'max_chain_depth' => 10,
        'regex' => [
            'max_pattern_length' => 512,
            'max_rules_checked' => 100,
        ],
        'create_prefix_redirect_on_parent_move' => true,
    ],

    'hit_recording' => [
        'defer' => true,
        'retention_days' => 365,
    ],

    'not_found' => [
        'capture_middleware_enabled' => true,
        'ignored_path_prefixes' => [
            '/admin',
            '/livewire',
            '/_debugbar',
            '/_clockwork',
            '/storage',
        ],
    ],

    'canonical' => [
        'enabled' => true,
        'scheme' => null,
        'host' => null,
        'lowercase_path' => true,
        'trailing_slash' => 'remove',
        'strip_query_keys' => [
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'gclid',
            'fbclid',
        ],
    ],
];
