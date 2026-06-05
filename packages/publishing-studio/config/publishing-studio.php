<?php

declare(strict_types=1);

use Capell\PublishingStudio\Checks\AccessibilityCheck;
use Capell\PublishingStudio\Checks\BrokenLinkCheck;
use Capell\PublishingStudio\Checks\MissingAltTextCheck;
use Capell\PublishingStudio\Checks\SeoMetaCheck;

return [
    /*
    |--------------------------------------------------------------------------
    | Publish readiness checks
    |--------------------------------------------------------------------------
    |
    | Checks run before a workspace is published. These defaults keep a fresh
    | package install from silently passing publish readiness without doing any
    | validation.
    |
    | @var list<class-string<\Capell\PublishingStudio\Checks\PublishCheck>>
    */
    'publish_checks' => [
        AccessibilityCheck::class,
        BrokenLinkCheck::class,
        MissingAltTextCheck::class,
        SeoMetaCheck::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Release windows
    |--------------------------------------------------------------------------
    |
    | Release windows are disabled by default so existing publish flows are not
    | restricted until an install opts in. When enabled, the default window is
    | weekday office hours in UTC.
    |
    | @var array{enabled: bool, timezone: string, bypass_permission: string, windows: list<array{days: list<string>, start: string, end: string}>}
    */
    'release_windows' => [
        'enabled' => false,
        'timezone' => 'UTC',
        'bypass_permission' => 'publish_outside_release_window',
        'windows' => [
            [
                'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                'start' => '09:00',
                'end' => '17:00',
            ],
        ],
    ],

    'scheduled_publish_enabled' => true,

    'prune_schedule_enabled' => false,

    'prune_schedule_cron' => '15 3 * * *',

    'preview' => [
        'home_route' => 'capell-frontend.home',
    ],
];
