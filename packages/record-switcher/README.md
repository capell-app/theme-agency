# Record Switcher

<!-- prettier-ignore-start -->

## What This Plugin Adds

Record Switcher is an **Available**, **No schema impact** Capell package in the **Capell Foundation** product group. It ships as `capell-app/record-switcher` and extends these surfaces: admin.

Record Switcher adds a compact searchable switcher to Filament edit-page headings so editors can jump to nearby editable records without returning to table views.

After install, eligible edit pages render the first-party Livewire heading switcher and admin assets. Resources can opt out by defining `public static function recordSwitcherEnabled(): bool` and returning `false`.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/record-switcher`
- Namespace: `Capell\RecordSwitcher`
- Theme key: not applicable

## Why It Matters

**For developers:** The package uses Capell Admin's heading extension point, Livewire, and first-party assets instead of app-level Filament resource overrides.

**For teams:** Jump between editable Capell records directly from the page heading with fast suggestions and Tab-to-accept keyboard flow.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Record Switcher admin heading suggestions (admin, required).

## Technical Shape

- Service providers: `Capell\RecordSwitcher\Providers\RecordSwitcherServiceProvider`.
- Filament classes: `RecordSwitcherHeadingExtender`.
- Livewire components: `RecordSwitcher`.
- Manifest contributions: `asset: Capell\RecordSwitcher\Manifest\RecordSwitcherAssetsContribution`, `health-check: Capell\RecordSwitcher\Manifest\RecordSwitcherHealthContribution`.
- Health checks: `Capell\RecordSwitcher\Health\RecordSwitcherHealthCheck`.
- Blade views: `packages/record-switcher/resources/views/components/record-switcher.blade.php`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

Record Switcher reads existing resource queries only. It does not own or mutate content records.

## Install Impact

- Admin navigation: no menu entry; edit-page headings are extended through `EditRecordHeadingExtender`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: none declared.

## Common Pitfalls

- Verify the package is installed before expecting its provider, views, or extension contributions to run.
- If a custom resource has an unusual heading or sensitive record workflow, add `recordSwitcherEnabled(): bool` to that resource and return `false`.
- Empty suggestions usually mean the resource query, global-search attributes, or current user's resource access rules leave no sibling records to show.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Heading switcher is absent on one resource | Resource opted out or does not render a standard Filament edit page heading | Check `recordSwitcherEnabled()` and the page class | Remove the opt-out or leave the resource on its custom heading |
| Suggestions are empty | Resource query, search attributes, or access scope filters out records | Check `getEloquentQuery()`, `getGloballySearchableAttributes()`, and current user access | Adjust the resource query/search attributes or create another editable record |

## Quick Start

1. Install the package: `composer require capell-app/record-switcher`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Open the related Capell admin surface and verify Record Switcher appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/record-switcher/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
