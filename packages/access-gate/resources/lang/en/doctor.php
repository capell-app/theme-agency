<?php

declare(strict_types=1);

return [
    'claim_hosts' => [
        'app_host_not_listed' => 'APP_URL host is not listed in claim_url_hosts for: :areas.',
        'app_url_missing' => 'APP_URL does not contain a host; claim host checks were skipped.',
        'ok' => 'Claim host settings look safe.',
    ],
    'cookies' => [
        'invalid_same_site' => 'Browser token same_site must be lax, strict, or none.',
        'none_requires_secure' => 'Browser token same_site=none requires secure=true.',
        'ok' => 'Browser token cookie settings look safe.',
        'production_secure' => 'Production should set ACCESS_GATE_COOKIE_SECURE=true.',
    ],
    'database' => [
        'missing_tables' => 'Access Gate tables are missing: :tables.',
        'ok' => 'Database connection is reachable: :connection.',
        'unreachable' => 'Database connection is not reachable: :connection.',
    ],
    'failed' => 'Access Gate doctor found :count blocking issue(s).',
    'middleware' => [
        'alias_missing' => 'The access-gate middleware alias is not registered.',
        'ok' => 'Middleware alias and order look safe.',
        'page_cache_before_gate' => 'Page cache middleware is configured before access-gate; protected pages can be bypassed.',
        'route_level_required' => 'Page cache middleware is in the web group. Ensure protected routes apply access-gate before cache reads.',
    ],
    'passed' => 'Access Gate doctor checks passed.',
    'site_scoped_areas' => [
        'missing_site_config' => 'Site-scoped access areas are missing explicit config for at least one site: :areas.',
        'missing_site_config_remediation' => 'Add an all-sites area row or create a matching site-scoped area for each site that can use the protected route key.',
        'no_sites' => 'No sites exist; site-scoped area coverage was skipped.',
        'not_enabled' => 'Site-scoped access areas are not enabled.',
        'ok' => 'Site-scoped access area coverage looks safe.',
        'sites_missing' => 'Sites table is unavailable; site-scoped area coverage was skipped.',
    ],
];
