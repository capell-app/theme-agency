# Record Switcher

<!-- prettier-ignore-start -->

## What This Plugin Adds

Record Switcher is an **Available**, **No schema impact** Capell package in the **Capell Foundation** product group. It ships as `capell-app/record-switcher` and extends these surfaces: admin.

Fast Filament edit-page record switching for Capell admins, with searchable suggestions and keyboard selection.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/record-switcher`
- Namespace: `Capell\RecordSwitcher`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Jump between editable Capell records directly from the page heading with fast suggestions and Tab-to-accept keyboard flow.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

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

Docs gap: document extension points here if the package delegates persistence to a host package.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: none declared.

## Common Pitfalls

- Verify the package is installed before expecting its provider, views, or extension contributions to run.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |

## Quick Start

1. Install the package: `composer require capell-app/record-switcher`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Open the related Capell admin surface and verify Record Switcher appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/record-switcher/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
