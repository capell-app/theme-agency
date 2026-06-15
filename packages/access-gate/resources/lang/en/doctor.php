<?php

declare(strict_types=1);

return [
    'announcement_hook' => [
        'missing' => 'The Access Gate announcement hook is not registered as cache-safe.',
        'missing_remediation' => 'Ensure the Access Gate package is installed and the frontend render hook registrar is available before package boot.',
        'ok' => 'Announcement hook registration is cache-safe.',
        'skipped' => 'Announcement hook cache-safety checks were skipped because the frontend hook registry is unavailable or Access Gate is not installed.',
    ],
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
    'customer_portal' => [
        'missing' => 'The Access Gate Customer Portal self-service provider is not registered.',
        'missing_remediation' => 'Ensure Customer Portal is booted before Access Gate registers installed package integrations.',
        'ok' => 'Customer Portal gated resource provider is registered.',
        'skipped' => 'Customer Portal provider checks were skipped because Customer Portal is unavailable or Access Gate is not installed.',
    ],
    'failed' => 'Access Gate doctor found :count blocking issue(s).',
    'middleware' => [
        'alias_missing' => 'The access-gate middleware alias is not registered.',
        'ok' => 'Middleware alias and order look safe.',
        'page_cache_before_gate' => 'Page cache middleware is configured before access-gate; protected pages can be bypassed.',
        'route_level_required' => 'Page cache middleware is in the web group. Ensure protected routes apply access-gate before cache reads.',
    ],
    'none' => 'none',
    'passed' => 'Access Gate doctor checks passed.',
    'payment_fulfillment' => [
        'missing' => 'The Access Gate Payments fulfillment handler is not registered.',
        'missing_remediation' => 'Ensure Payments is available and Access Gate registers the gated-access fulfillment handler during package boot.',
        'ok' => 'Payments gated-access fulfillment handler is registered.',
        'skipped' => 'Payments fulfillment checks were skipped because Payments is unavailable or Access Gate is not installed.',
    ],
    'registration_configuration' => [
        'invalid' => 'Registration configuration contains invalid access methods (:methods) or fields (:fields).',
        'invalid_remediation' => 'Configure only class strings that implement the AccessRequestMethod or RegistrationField contracts.',
        'ok' => 'Registration configuration looks safe. Methods: :methods. Fields: :fields.',
    ],
    'route_throttles' => [
        'missing' => 'Access Gate route throttles are missing or not applied: :limiters.',
        'missing_remediation' => 'Keep the access-gate-request and access-gate-logout rate limiters registered and applied to the public request/logout routes.',
        'ok' => 'Public request and logout route throttles are registered.',
    ],
    'site_scoped_areas' => [
        'missing_site_config' => 'Site-scoped access areas are missing explicit config for at least one site: :areas.',
        'missing_site_config_remediation' => 'Add an all-sites area row or create a matching site-scoped area for each site that can use the protected route key.',
        'no_sites' => 'No sites exist; site-scoped area coverage was skipped.',
        'not_enabled' => 'Site-scoped access areas are not enabled.',
        'ok' => 'Site-scoped access area coverage looks safe.',
        'sites_missing' => 'Sites table is unavailable; site-scoped area coverage was skipped.',
    ],
];
