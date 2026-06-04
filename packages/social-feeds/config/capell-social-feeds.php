<?php

declare(strict_types=1);

return [
    'enabled' => env('CAPELL_SOCIAL_FEEDS_ENABLED', true),
    'http_timeout' => (int) env('CAPELL_SOCIAL_FEEDS_HTTP_TIMEOUT', 10),
    'http_connect_timeout' => (int) env('CAPELL_SOCIAL_FEEDS_HTTP_CONNECT_TIMEOUT', 3),
    'default_limit' => (int) env('CAPELL_SOCIAL_FEEDS_DEFAULT_LIMIT', 12),
    'max_limit' => (int) env('CAPELL_SOCIAL_FEEDS_MAX_LIMIT', 48),
    'retention_items' => (int) env('CAPELL_SOCIAL_FEEDS_RETENTION_ITEMS', 200),
];
