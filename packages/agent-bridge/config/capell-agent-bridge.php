<?php

declare(strict_types=1);

return [
    'routes' => [
        // Set any route to null to stop this package registering it in the host app.
        'home' => null,
        'knowledge' => null,
        'site' => 'agent-bridge/capell',
    ],

    'site_auth_guard' => env('CAPELL_AGENT_BRIDGE_AUTH_GUARD', 'web'),

    'token_prefix' => env('CAPELL_AGENT_BRIDGE_TOKEN_PREFIX', 'cagent-bridge_'),

    'confirmation_ttl_minutes' => env('CAPELL_AGENT_BRIDGE_CONFIRMATION_TTL_MINUTES', 10),

    'rate_limit_per_minute' => env('CAPELL_AGENT_BRIDGE_RATE_LIMIT_PER_MINUTE', 60),

    'rate_limit_enabled' => env('CAPELL_AGENT_BRIDGE_RATE_LIMIT_ENABLED', true),

    'last_used_throttle_minutes' => env('CAPELL_AGENT_BRIDGE_LAST_USED_THROTTLE_MINUTES', 5),

    'accept_legacy_token_hashes' => env('CAPELL_AGENT_BRIDGE_ACCEPT_LEGACY_TOKEN_HASHES', true),

    'inspect_app_runtime' => env('CAPELL_AGENT_BRIDGE_INSPECT_APP_RUNTIME', false),

    'audit_retention_days' => env('CAPELL_AGENT_BRIDGE_AUDIT_RETENTION_DAYS', 90),

    'enable_user_resource_bridge' => true,

    'public_docs_paths' => [
        base_path('README.md'),
        base_path('docs'),
        base_path('packages/*/README.md'),
        base_path('packages/*/docs'),
    ],
];
