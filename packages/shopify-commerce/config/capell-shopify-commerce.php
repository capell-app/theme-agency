<?php

declare(strict_types=1);

return [
    'enabled' => env('CAPELL_SHOPIFY_COMMERCE_ENABLED', true),
    'client_id' => env('SHOPIFY_APP_CLIENT_ID'),
    'client_secret' => env('SHOPIFY_APP_CLIENT_SECRET'),
    'default_api_version' => '2026-04',
    'default_scopes' => ['read_products', 'read_customers'],
    'http_timeout' => env('CAPELL_SHOPIFY_COMMERCE_HTTP_TIMEOUT', 15),
    'bulk_sync_poll_delay_seconds' => env('CAPELL_SHOPIFY_COMMERCE_BULK_SYNC_POLL_DELAY_SECONDS', 15),
    'scheduled_sync_enabled' => env('CAPELL_SHOPIFY_COMMERCE_SCHEDULED_SYNC_ENABLED', true),
    'customer_sync_page_size' => env('CAPELL_SHOPIFY_COMMERCE_CUSTOMER_SYNC_PAGE_SIZE', 100),
    'customer_sync_max_pages' => env('CAPELL_SHOPIFY_COMMERCE_CUSTOMER_SYNC_MAX_PAGES', 100),
    'health_max_catalog_sync_age_hours' => env('CAPELL_SHOPIFY_COMMERCE_HEALTH_MAX_CATALOG_SYNC_AGE_HOURS', 24),
    'state_ttl_seconds' => 600,
    'default_currency' => 'USD',
];
