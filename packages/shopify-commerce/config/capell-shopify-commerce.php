<?php

declare(strict_types=1);

return [
    'enabled' => env('CAPELL_SHOPIFY_COMMERCE_ENABLED', true),
    'client_id' => env('SHOPIFY_APP_CLIENT_ID'),
    'client_secret' => env('SHOPIFY_APP_CLIENT_SECRET'),
    'default_api_version' => '2026-04',
    'default_scopes' => ['read_products'],
    'state_ttl_seconds' => 600,
    'default_currency' => 'USD',
];
