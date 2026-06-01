# Diagnostics

Diagnostics adds operational diagnostics for cache, configuration drift, migrations, packages, registries, queues, permissions, setup health, and Tailwind build status.

## At A Glance

- Package: `capell-app/diagnostics`
- Namespace: `Capell\Diagnostics\`
- Surfaces: Filament admin, database
- Service providers: `packages/diagnostics/src/Providers/AdminServiceProvider.php`, `packages/diagnostics/src/Providers/DiagnosticsServiceProvider.php`
- Capell dependencies: `capell-app/admin`, `capell-app/core`, `capell-app/html-cache`
- Third-party dependencies: `croustibat/filament-jobs-monitor`, `lorisleiva/laravel-actions`, `spatie/laravel-data`

## Why It Helps Your Capell Workflow

- Gives developers and operators one admin surface for checking cache, config drift, migrations, packages, queues, permissions, setup health, and Tailwind status.
- Shortens support loops because package health checks can be found from Capell instead of by reading logs first.
- Provides extension points for command-palette style diagnostic actions with explicit ability and risk metadata.

## Best Used With

- [Dashboard Reports](../dashboard-reports/README.md)
- [Login Audit](../login-audit/README.md)
- [Deployments](../deployments/README.md)

## What It Adds

Diagnostics adds operational diagnostics for cache, configuration drift, migrations, packages, registries, queues, permissions, setup health, and Tailwind build status.

- Command palette admin page.
- System health admin pages.
- Developer tools dashboard page.
- Permission audit report.
- Queue Operations report backed by [`croustibat/filament-jobs-monitor`](https://github.com/ultraviolettes/filament-jobs-monitor) telemetry.
- Health widgets for cache, content, migrations, registry, setup, packages, and Tailwind.
- Secure command palette discovery, execution, feedback, and audit logging for developer tools, system health, queue health, and trusted `capell:*` Artisan operations.

## Why It Matters

**For developers:** Keeps diagnostics in actions and data objects so admin pages can show health information without hard-coded checks in the UI.

**For teams:** Helps operators and agencies see setup problems before they become publishing or deployment issues.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Admin](https://github.com/capell-app/admin)
- [Capell Core](https://github.com/capell-app/core)

**Open-source packages used here**

- [Laravel Actions](https://github.com/lorisleiva/laravel-actions) - single-purpose action classes that keep package workflows out of controllers and Filament resources.
- [Filament Jobs Monitor](https://github.com/ultraviolettes/filament-jobs-monitor) by Croustibat / Ultraviolettes - Laravel queue-event telemetry for the `queue_monitors` history that Capell wraps as Queue Operations.
- [Spatie Laravel Data](https://github.com/spatie/laravel-data) - typed data objects for package boundaries, form state, settings, and structured results.

**Linked package previews**

[![Laravel Actions GitHub preview](https://opengraph.githubassets.com/capell-readme/lorisleiva/laravel-actions)](https://github.com/lorisleiva/laravel-actions)

[![Filament Jobs Monitor GitHub preview](https://opengraph.githubassets.com/capell-readme/ultraviolettes/filament-jobs-monitor)](https://github.com/ultraviolettes/filament-jobs-monitor)

[![Spatie Laravel Data GitHub preview](https://opengraph.githubassets.com/capell-readme/spatie/laravel-data)](https://github.com/spatie/laravel-data)

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment.

- Developer tools dashboard.
- Command palette page.
- System health page.
- Permission audit page.
- Queue Operations page with dummy queue-monitor history, failed jobs, and pending jobs.
- Health widgets on the admin dashboard.

Current dummy-data screenshots are committed under `packages/diagnostics/docs/screenshots`. The fixture source is `packages/diagnostics/docs/assets/screenshots/diagnostics-dummy-screens.html`.

## Technical Shape

- DiagnosticsServiceProvider and AdminServiceProvider register admin pages and widgets.
- DiagnosticsServiceProvider configures [`croustibat/filament-jobs-monitor`](https://packagist.org/packages/croustibat/filament-jobs-monitor) as the telemetry dependency, disables its upstream navigation, and routes queue UX through Capell Diagnostics.
- AdminServiceProvider registers palette command providers through the `capell.diagnostics.command-palette-provider` container tag.
- Command palette actions discover providers dynamically, authorize commands, validate parameters, execute navigation or Artisan commands, and record audit runs.
- Actions build each health report.
- Data objects describe report rows and dashboard state.
- QueueMonitor, FailedJob, and PendingQueueJob models support Queue Operations reporting.
- CommandPaletteRun model records command palette execution history.

## Code Map

| Area      | Path                                 | Purpose                                                             |
| --------- | ------------------------------------ | ------------------------------------------------------------------- |
| Actions   | `packages/diagnostics/src/Actions`   | Domain operations. Test these directly where possible.              |
| Data      | `packages/diagnostics/src/Data`      | Structured payloads, form state, view models, and integration data. |
| Enums     | `packages/diagnostics/src/Enums`     | Persisted states and Filament option values.                        |
| Models    | `packages/diagnostics/src/Models`    | Eloquent records owned by the package.                              |
| Filament  | `packages/diagnostics/src/Filament`  | Admin resources, pages, widgets, and settings UI.                   |
| Providers | `packages/diagnostics/src/Providers` | Registration, extension hooks, routes, migrations, and resources.   |
| Resources | `packages/diagnostics/resources`     | Views, translations, assets, and package resources.                 |
| Database  | `packages/diagnostics/database`      | Migrations, seeders, and settings migrations.                       |
| Tests     | `packages/diagnostics/tests`         | Package-level Pest coverage.                                        |

## Admin Surface

- Pages: `CommandPalettePage`, `DiagnosticsPage`, `PermissionAuditPage`, `PermissionAuditTable`, `QueueHealthPage`, `QueueHealthTable`, `SystemHealthPage`.
- Widgets: `AlertsWidgetAbstract`, `CacheHealthWidgetAbstract`, `ConfigDriftWidgetAbstract`, `ContentGraphHealthWidgetAbstract`, `ContentHealthWidgetAbstract`, `MigrationsHealthWidgetAbstract`, `PackagesInstalledWidgetAbstract`, `RegistryHealthWidgetAbstract`, `SetupHealthWidgetAbstract`, `SiteHealthWidgetAbstract`, `TailwindBuildStatusWidgetAbstract`.

## Data And Persistence

- This package owns the `command_palette_runs` table for command palette audit history.
- This package ships a guarded `queue_monitors` migration compatible with `croustibat/filament-jobs-monitor` so Capell installs do not depend on manual vendor publishing.
- It reads existing Laravel and Capell state such as config, migrations, failed jobs, permissions, packages, registries, and Tailwind outputs.

- Models: `CommandPaletteRun`, `FailedJob`, `PendingQueueJob`, `QueueMonitor`.
- Migrations: `2026_05_10_190846_01_create_command_palette_runs_table.php`, `2026_05_29_000001_create_queue_monitors_table.php`.
- Data objects live in `src/Data/`; use them for payloads, form state, and view models.

## Extension Points

- Contracts: `CommandPaletteProvider`.
- Register Capell extension points, routes, migrations, settings, render hooks, and resources from service providers.

## Install Impact

- Adds admin pages for developer diagnostics.
- Adds dashboard widgets.
- Adds the `command_palette_runs` audit table.
- Adds the `queue_monitors` table when the host app has not already installed the upstream `croustibat/filament-jobs-monitor` table.
- No public routes are registered by this package.

## Install And Setup

- Install with `composer require capell-app/diagnostics` in the host Capell application.
- Run migrations through the host application package install flow.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Admin And Access

- DiagnosticsPage (packages/diagnostics/src/Filament/Pages/DiagnosticsPage.php, slug `diagnostics`)
- CommandPalettePage (packages/diagnostics/src/Filament/Pages/CommandPalettePage.php, slug `diagnostics/command-palette`)
- PermissionAuditPage (packages/diagnostics/src/Filament/Pages/PermissionAuditPage.php, slug `dashboard-dashboard_reports/permission-audit`)
- QueueHealthPage / Queue Operations (packages/diagnostics/src/Filament/Pages/QueueHealthPage.php, slug `dashboard-dashboard_reports/queue-health`)
- SystemHealthPage (packages/diagnostics/src/Filament/Pages/SystemHealthPage.php, slug `system-health`)

- Gate: CacheHealthWidgetAbstract: `admin`, `super_admin`
- Gate: ConfigDriftWidgetAbstract: `super_admin`
- Gate: ContentHealthWidgetAbstract: `editor`, `admin`, `super_admin`
- Gate: DiagnosticsPage: Gate `accessDiagnostics`, `viewDiagnostics`
- Gate: MigrationsHealthWidgetAbstract: `super_admin`
- Gate: PackagesInstalledWidgetAbstract: `super_admin`
- Gate: QueueHealthPage: Gate `accessDiagnostics`, `viewDiagnostics`
- Gate: RegistryHealthWidgetAbstract: `super_admin`
- Gate: SetupHealthWidgetAbstract: settings-gated only
- Gate: SiteHealthWidgetAbstract: settings-gated only
- Gate: TailwindBuildStatusWidgetAbstract: `super_admin`

## Common Pitfalls

- Some checks depend on host-app conventions and may need configuration.
- Queue Operations depends on `croustibat/filament-jobs-monitor` queue telemetry for history, Laravel failed job data for retries, and the database queue driver for pending-job rows.
- Permission audit output is only useful when permissions are registered.

## Docs

- [docs index](docs/README.md)
- [command-palette.md](docs/command-palette.md)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)
- [queue-operations.md](docs/queue-operations.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/diagnostics/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Put behaviour changes in `src/Actions/`; UI classes, commands, and controllers should call actions instead of owning domain logic.
- Use package `Data` classes at boundaries instead of passing anonymous arrays between layers.
- Use backed enums for persisted values and enum labels for Filament options.
