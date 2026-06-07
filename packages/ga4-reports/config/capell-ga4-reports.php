<?php

declare(strict_types=1);

return [
    'enabled' => false,
    'property_id' => null,
    'credentials_path' => null,
    'http_timeout' => env('CAPELL_GA4_REPORTS_HTTP_TIMEOUT', 20),
    'http_retry_times' => env('CAPELL_GA4_REPORTS_HTTP_RETRY_TIMES', 3),
    'http_retry_delay_ms' => env('CAPELL_GA4_REPORTS_HTTP_RETRY_DELAY_MS', 250),
    'http_retry_max_delay_ms' => env('CAPELL_GA4_REPORTS_HTTP_RETRY_MAX_DELAY_MS', 5000),
    'token_cache_store' => env('CAPELL_GA4_REPORTS_TOKEN_CACHE_STORE'),
    'sync_lock_seconds' => env('CAPELL_GA4_REPORTS_SYNC_LOCK_SECONDS', 3600),
    'sync_overlap_minutes' => env('CAPELL_GA4_REPORTS_SYNC_OVERLAP_MINUTES', 120),
    'sync_cron' => env('CAPELL_GA4_REPORTS_SYNC_CRON', '0 2 * * *'),
    'sync_days' => 30,
    'dashboard_cache_ttl_seconds' => env('CAPELL_GA4_REPORTS_DASHBOARD_CACHE_TTL_SECONDS', 300),
    'route_slug' => 'ga4-reports',
    'health' => [
        'max_successful_sync_age_hours' => env('CAPELL_GA4_REPORTS_HEALTH_MAX_SUCCESSFUL_SYNC_AGE_HOURS', 48),
    ],
    'tables' => [
        'sync_runs' => 'ga4_reports_sync_runs',
        'daily_metrics' => 'ga4_reports_daily_metrics',
        'page_metrics' => 'ga4_reports_page_metrics',
    ],
];
