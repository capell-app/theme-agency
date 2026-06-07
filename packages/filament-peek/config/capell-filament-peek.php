<?php

declare(strict_types=1);

return [
    'enabled' => true,

    'preview' => [
        'cache_store' => env('CAPELL_FILAMENT_PEEK_CACHE_STORE'),
        'ttl_minutes' => (int) env('CAPELL_FILAMENT_PEEK_TTL_MINUTES', 15),
        'max_payload_kb' => (int) env('CAPELL_FILAMENT_PEEK_MAX_PAYLOAD_KB', 512),
        'route_prefix' => 'capell-filament-peek',
        'middleware' => ['web', 'signed'],
    ],
];
