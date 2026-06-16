<?php

declare(strict_types=1);

return [
    'expensive_scan_cache_ttl_seconds' => 300,

    'health_checks' => [
        'local_packages_path' => null,
    ],

    'infrastructure' => [
        'warning_cache_drivers' => ['array', 'null'],
        'warning_queue_drivers' => ['sync', 'null'],
        'warning_mail_transports' => ['array', 'log'],
    ],

    'queue_monitor' => [
        'retention_days' => 14,
        'queues' => [
            'default',
        ],
        'queue_config_paths' => [
            'capell-email-studio.queue',
            'capell-newsletter.sync.queue',
            'capell-public-actions.queue',
            'migration-assistant.queue.name',
        ],
        'pending_jobs_enabled' => true,
        'retry_enabled' => true,
        'delete_pending_enabled' => true,
        'prune_enabled' => true,
        'trend_days' => 7,
        'stale_pending_seconds' => 300,
    ],
];
