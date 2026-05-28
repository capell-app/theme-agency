<?php

declare(strict_types=1);

return [
    'route_prefix' => 'actions',
    'api_route_prefix' => 'api/public-actions',
    'queue' => 'default',
    'webhook_timeout_seconds' => 10,
    'allow_insecure_webhook_urls' => false,
    'allow_private_webhook_urls' => false,
    'submit_rate_limit' => 'public-actions-submit',
    'api_rate_limit' => 'public-actions-api',
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
