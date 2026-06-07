<?php

declare(strict_types=1);

return [
    'hash_secret' => env('CAPELL_PRIVACY_CENTER_HASH_SECRET'),

    'tables' => [
        'consent_policies' => 'privacy_consent_policies',
        'consent_records' => 'privacy_consent_records',
        'policy_acceptances' => 'privacy_policy_acceptances',
        'retention_rules' => 'privacy_retention_rules',
        'privacy_requests' => 'privacy_requests',
    ],

    'privacy_request_due_days' => 30,

    'overview_stats_cache_ttl_seconds' => 300,
];
