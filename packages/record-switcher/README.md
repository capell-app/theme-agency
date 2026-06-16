# Record Switcher

<!-- prettier-ignore-start -->

## What This Plugin Adds

Record Switcher is an **Available**, **No schema impact** Capell package in the **Capell Foundation** product group. It ships as `capell-app/record-switcher` and extends these surfaces: admin.

Fast Filament edit-page record switching for Capell admins, with searchable suggestions, sibling and same-site prioritization, recent-record ordering, and keyboard selection.

After install, edit-page headings can show a compact switcher that lets admins move to nearby records without returning to a table view.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/record-switcher`
- Namespace: `Capell\RecordSwitcher`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers a package-owned heading extender, Livewire component, and admin assets instead of pushing this behaviour into core or application code.

**For teams:** Jump between editable Capell records directly from the page heading with fast suggestions, nearby page ordering, and Tab-to-accept keyboard flow.

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

## Install Impact

- Admin navigation: replaces supported edit-page headings through the Capell Admin heading extender.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: none declared.

## Common Pitfalls

- Verify the package is installed before expecting its provider, views, Livewire component, or heading extension contribution to run.
- Custom Filament resources can opt out with `recordSwitcherEnabled(): false` when a heading workflow is sensitive or unusual.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Switcher is missing from edit headings | Provider or heading extender is not loaded, or the resource opted out | Confirm `capell.json`, provider registration, and `recordSwitcherEnabled()` on the resource | Reinstall the package, refresh Composer autoload, clear host caches, or remove the opt-out |
| Suggestions are empty | The resource query has no other editable records or no searchable attributes | Check the resource query, record count, and globally searchable attributes | Add editable records or define searchable attributes on the resource |

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
