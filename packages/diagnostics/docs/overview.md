# Diagnostics

<!-- prettier-ignore-start -->

## What This Plugin Adds

Diagnostics is an **Available**, **Schema-owning** Capell package in the **Capell Operations** product group. It ships as `capell-app/diagnostics` and extends these surfaces: admin, console.

A single operations cockpit for Capell that surfaces system health, queue activity, config drift, permission audits, and per-package install health - with a guarded command palette for trusted capell:* operations.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/diagnostics`
- Namespace: `Capell\Diagnostics`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A single operations cockpit for Capell that surfaces system health, queue activity, config drift, permission audits, and per-package install health - with a guarded command palette for trusted capell:* operations.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Developer tools dashboard (admin, required).
- Health widgets on the admin dashboard (admin, required).
- Command palette page (admin, required).
- System health page (admin, required).
- Permission audit page (admin, required).
- Queue Operations page (admin, required).

## Technical Shape

- Service providers: `Capell\Diagnostics\Providers\DiagnosticsServiceProvider`, `Capell\Diagnostics\Providers\AdminServiceProvider`.
- Config files: `packages/diagnostics/config/capell-diagnostics.php`.
- Migrations: `packages/diagnostics/database/migrations/2026_05_10_190846_01_create_command_palette_runs_table.php`, `packages/diagnostics/database/migrations/2026_05_29_000001_create_queue_monitors_table.php`, `packages/diagnostics/database/migrations/2026_06_07_000001_create_diagnostics_health_snapshots_table.php`.
- Models: `CommandPaletteRun`, `DiagnosticsHealthSnapshot`, `FailedJob`, `PendingQueueJob`, `QueueMonitor`.
- Filament classes: `CommandPalettePage`, `DiagnosticsPage`, `PermissionAuditPage`, `QueueHealthPage`, `SystemHealthPage`, `PermissionAuditTable`, `QueueHealthTable`, `AlertsFilamentWidget`, `CacheHealthFilamentWidget`, `ConfigDriftFilamentWidget`, `ContentGraphHealthFilamentWidget`, `ContentHealthFilamentWidget`, `and 7 more`.
- Actions: `DiscoverCommandPaletteCommandsAction`, `ExecuteCommandPaletteCommandAction`, `RedactCommandPaletteOutputAction`, `ValidateCommandPaletteParametersAction`, `BuildCacheHealthAction`, `BuildConfigDriftAction`, `BuildContentGraphHealthAction`, `BuildMigrationsHealthAction`, `BuildPackagesInstalledAction`, `BuildRegistryHealthAction`, `BuildSetupHealthAction`, `BuildTailwindBuildStatusAction`, `and 17 more`.
- Data objects: `CommandPaletteCommandData`, `CommandPaletteParameterData`, `CommandPaletteResultData`, `CacheHealthData`, `ConfigDriftData`, `ConfigDriftEntryData`, `ContentGraphHealthData`, `ContentHealthData`, `ContentHealthIssueData`, `MigrationsHealthData`, `PackageInfoData`, `PackagesInstalledData`, `and 14 more`.
- Command signatures: `capell:diagnostics:health`.
- Console command classes: `RunDiagnosticsHealthCommand`.
- Manifest contributions: `admin-page: Capell\Diagnostics\Manifest\CommandPalettePageContribution`, `admin-page: Capell\Diagnostics\Manifest\DiagnosticsPageContribution`, `admin-page: Capell\Diagnostics\Manifest\PermissionAuditPageContribution`, `admin-page: Capell\Diagnostics\Manifest\QueueHealthPageContribution`, `admin-page: Capell\Diagnostics\Manifest\SystemHealthPageContribution`, `dashboard-widget: Capell\Diagnostics\Manifest\SiteHealthWidgetContribution`, `dashboard-widget: Capell\Diagnostics\Manifest\SystemHealthWidgetsContribution`.
- Health checks: `Capell\Diagnostics\Health\DiagnosticsHealthCheck`.
- Blade views: `packages/diagnostics/resources/views/filament/pages/command-palette.blade.php`, `packages/diagnostics/resources/views/filament/pages/diagnostics.blade.php`, `packages/diagnostics/resources/views/widgets/cache-health.blade.php`, `packages/diagnostics/resources/views/widgets/config-drift.blade.php`, `packages/diagnostics/resources/views/widgets/content-graph-health.blade.php`, `packages/diagnostics/resources/views/widgets/content-health.blade.php`, `packages/diagnostics/resources/views/widgets/migrations-health.blade.php`, `packages/diagnostics/resources/views/widgets/packages-installed.blade.php`, `packages/diagnostics/resources/views/widgets/registry-health.blade.php`, `packages/diagnostics/resources/views/widgets/setup-health.blade.php`, `packages/diagnostics/resources/views/widgets/site-health.blade.php`, `packages/diagnostics/resources/views/widgets/tailwind-build-status.blade.php`.
- Cache tags: `diagnostics`.

## Data Model

- Required tables: `command_palette_runs`, `queue_monitors`, `diagnostics_health_snapshots`.
- Models: `CommandPaletteRun`, `DiagnosticsHealthSnapshot`, `FailedJob`, `PendingQueueJob`, `QueueMonitor`.
- Migration files: `2026_05_10_190846_01_create_command_palette_runs_table.php`, `2026_05_29_000001_create_queue_monitors_table.php`, `2026_06_07_000001_create_diagnostics_health_snapshots_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `accessDiagnostics`, `viewDiagnostics`, `View:PermissionAuditPage`, `View:CommandPalettePage`, `View:QueueHealthPage`, `Manage:QueueHealthPage`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `diagnostics`.
- Commands: `capell:diagnostics:health`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |

## Quick Start

1. Install the package: `composer require capell-app/diagnostics`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Diagnostics appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Html Cache](../../html-cache/README.md).
- Focused tests: `vendor/bin/pest packages/diagnostics/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
