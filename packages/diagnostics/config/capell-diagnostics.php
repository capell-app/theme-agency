<?php

declare(strict_types=1);

return [
    'expensive_scan_cache_ttl_seconds' => 300,

    'health_checks' => [
        'local_packages_path' => null,
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
    ],
];
