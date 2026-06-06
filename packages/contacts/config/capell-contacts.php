<?php

declare(strict_types=1);

return [
    'tables' => [
        'contacts' => 'contacts',
        'organisations' => 'contact_organisations',
        'organisation_memberships' => 'contact_organisation_memberships',
        'leads' => 'contact_leads',
        'activities' => 'contact_activities',
    ],
    'hash_secret' => null,
    'overview_stats_cache_ttl_seconds' => 300,
];
