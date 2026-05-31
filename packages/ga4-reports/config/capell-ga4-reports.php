<?php

declare(strict_types=1);

return [
    'enabled' => false,
    'property_id' => null,
    'credentials_path' => null,
    'http_timeout' => env('CAPELL_GA4_REPORTS_HTTP_TIMEOUT', 20),
    'sync_lock_seconds' => env('CAPELL_GA4_REPORTS_SYNC_LOCK_SECONDS', 3600),
    'sync_overlap_minutes' => env('CAPELL_GA4_REPORTS_SYNC_OVERLAP_MINUTES', 120),
    'sync_days' => 30,
    'route_slug' => 'ga4-reports',
    'tables' => [
        'sync_runs' => 'ga4_reports_sync_runs',
        'daily_metrics' => 'ga4_reports_daily_metrics',
        'page_metrics' => 'ga4_reports_page_metrics',
    ],
];
