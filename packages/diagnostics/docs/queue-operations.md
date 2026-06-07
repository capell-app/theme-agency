# Queue Operations

Queue Operations is the Capell Diagnostics wrapper around [`croustibat/filament-jobs-monitor`](https://github.com/ultraviolettes/filament-jobs-monitor). Capell keeps the upstream package as the low-level telemetry collector and owns the admin page, queries, actions, permissions, retention defaults, and screenshots.

Use this page when you are changing queue monitoring, failed-job retry flows, pending database queue cleanup, or the Diagnostics queue screenshots.

## Upstream Credit

The queue history table is powered by [`croustibat/filament-jobs-monitor`](https://packagist.org/packages/croustibat/filament-jobs-monitor), maintained at [`ultraviolettes/filament-jobs-monitor`](https://github.com/ultraviolettes/filament-jobs-monitor). That package listens to Laravel queue events and writes `queue_monitors` rows. Diagnostics does not fork that work; it wraps it with Capell-specific configuration and a safer admin surface.

Keep the upstream package credited in:

- [../README.md](../README.md), under Built With and Screens And Workflow.
- [credits-and-acknowledgements.md](credits-and-acknowledgements.md), under Open-source Packages And Authors.
- [overview.md](overview.md), under Queue Operations.
- [screenshots.json](screenshots.json), in Queue Operations capture notes.
- [../capell.json](../capell.json), in marketplace screenshot captions and package metadata.

## Runtime Shape

- `DiagnosticsServiceProvider` binds `Croustibat\FilamentJobsMonitor\Models\QueueMonitor` to `Capell\Diagnostics\Models\QueueMonitor`.
- The upstream Filament navigation is disabled. Capell exposes `QueueHealthPage` as the Diagnostics-owned Queue Operations surface.
- The guarded migration creates an upstream-compatible `queue_monitors` table when the host app has not already published the upstream migration.
- `config/capell-diagnostics.php` controls retention days, monitored queues, Capell queue config paths, pending jobs, retry, deletion, and pruning.
- `BuildQueueOperationsStatsAction` aggregates totals, failures, running jobs, pending jobs, average runtime, and daily trends without the upstream widget's repeated per-day count loop.
- Retry, bulk retry, pending deletion, and pruning are handled by typed Diagnostics Actions instead of Filament closures owning domain logic.

## Admin Page

`QueueHealthPage` keeps the existing Capell navigation label `capell-admin::navigation.queue_health` and now renders:

- History tab from `queue_monitors`.
- Failed jobs tab from Laravel's `failed_jobs` table.
- Pending jobs tab only when `queue.default` is `database` and the configured jobs table exists.
- Filters for status, queue, date range, and job class.
- Safer actions for retrying failed jobs, deleting pending database queue rows, and pruning old monitor rows.

Access is restricted to super admins and users that already have Diagnostics access or view permissions. This is an operations-only surface, not a client-admin feature.

## Configuration

The Diagnostics config is the Capell source of truth:

```php
'queue_monitor' => [
    'retention_days' => 14,
    'queues' => ['default'],
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
```

`DiscoverQueueMonitorQueuesAction` merges the explicit queue list, configured Capell package queue paths, and the active Laravel queue connection's queue name. Keep that Action as the only queue discovery boundary so screenshots, stats, upstream config, and pending queries agree.

## Screenshots

The screenshot contract lives in [screenshots.json](screenshots.json). Queue Operations screenshots should use seeded synthetic operational data so the page is useful in the Capell app and marketplace without exposing real job names, customer payloads, or exception details.

Generated runner screenshots are stored under:

- `packages/diagnostics/docs/assets/screenshots/queue-health-page.png`
- `packages/diagnostics/docs/assets/screenshots/queue-health-page-dark.png`
- `packages/diagnostics/docs/screenshots/queue-health-page.png`
- `packages/diagnostics/docs/screenshots/queue-health-page-dark.png`
- `packages/diagnostics/docs/screenshots/diagnostics-dashboard.png`
- `packages/diagnostics/docs/screenshots/system-health-page.png`
- `packages/diagnostics/docs/screenshots/permission-audit-page.png`
- `packages/diagnostics/docs/screenshots/command-palette-page.png`
- `packages/diagnostics/docs/screenshots/health-widgets-on-the-admin-dashboard.png`

The local fixture source is [assets/screenshots/diagnostics-dummy-screens.html](assets/screenshots/diagnostics-dummy-screens.html). It is documentation-only. It exists to keep package docs and marketplace screenshots deterministic when a real host app does not have representative queue history.

## Verification

Run the package test slice after queue operation changes:

```bash
vendor/bin/pest packages/diagnostics/tests --configuration=phpunit.xml
```

Useful focused tests:

- `packages/diagnostics/tests/Feature/Actions/DashboardReports/QueueOperationsActionsTest.php`
- `packages/diagnostics/tests/Feature/Filament/Pages/QueueHealthPageTest.php`

The Filament tests cover access, pending tab visibility, Unix timestamp filtering for database queue jobs, and failed-tab-only bulk retry visibility.
