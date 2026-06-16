<?php

declare(strict_types=1);

return [
    'route_prefix' => 'actions',
    'api_route_prefix' => 'api/public-actions',
    'queue' => 'default',
    'dispatch_retry_seconds' => 60,
    'dispatch_backoff_seconds' => [60, 300, 900],
    'dispatch_retry_jitter_seconds' => 15,
    'submission_retention_days' => 365,
    'webhook_timeout_seconds' => 10,
    'allow_insecure_webhook_urls' => false,
    'allow_private_webhook_urls' => false,
    'allow_schemaless_payloads' => false,
    'submit_rate_limit' => 'public-actions-submit',
    'submit_rate_limit_per_minute' => 12,
    'api_rate_limit' => 'public-actions-api',
    'api_rate_limit_per_minute' => 120,
    'action_rate_limits' => [],
    'integration_token_rate_limits' => [
        'providers' => [],
        'tokens' => [],
    ],
    'form_builder' => [
        'mappings' => [],
    ],
    'spam_protection' => [
        'enabled' => ['honeypot'],
        'honeypot' => [
            'fields' => ['_hp'],
        ],
        'turnstile' => [
            'secret' => null,
        ],
        'hcaptcha' => [
            'secret' => null,
        ],
        'recaptcha' => [
            'secret' => null,
            'minimum_score' => 0.5,
            'action' => null,
        ],
    ],
    'tables' => [
        'actions' => 'public_actions',
        'destinations' => 'public_action_destinations',
        'submissions' => 'public_action_submissions',
        'dispatch_attempts' => 'public_action_dispatch_attempts',
        'integration_tokens' => 'public_action_integration_tokens',
    ],
    'adapters' => [
        'presets' => [
            'generic' => [
                'adapter' => 'http_webhook',
                'method' => 'POST',
                'expects_json' => true,
            ],
            'zapier' => [
                'adapter' => 'http_webhook',
                'method' => 'POST',
                'expects_json' => true,
            ],
            'pipedream' => [
                'adapter' => 'http_webhook',
                'method' => 'POST',
                'expects_json' => true,
            ],
            'n8n' => [
                'adapter' => 'http_webhook',
                'method' => 'POST',
                'expects_json' => true,
            ],
            'make' => [
                'adapter' => 'http_webhook',
                'method' => 'POST',
                'expects_json' => true,
            ],
        ],
    ],
];
