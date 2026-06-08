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
        'device_presets' => [
            'fullscreen' => [
                'icon' => 'heroicon-o-computer-desktop',
                'width' => '100%',
                'height' => '100%',
                'canRotatePreset' => false,
            ],
            'tablet' => [
                'icon' => 'heroicon-o-device-tablet',
                'rotateIcon' => true,
                'width' => '1024px',
                'height' => '768px',
                'canRotatePreset' => true,
            ],
            'mobile' => [
                'icon' => 'heroicon-o-device-phone-mobile',
                'width' => '390px',
                'height' => '844px',
                'canRotatePreset' => true,
            ],
        ],
        'initial_device_preset' => env('CAPELL_FILAMENT_PEEK_INITIAL_DEVICE_PRESET', 'fullscreen'),
        'allow_iframe_overflow' => false,
        'allow_iframe_pointer_events' => false,
    ],
];
