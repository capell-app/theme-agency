# Filament Peek

<!-- prettier-ignore-start -->

## What This Plugin Adds

Filament Peek is an **Available**, **No schema impact** Capell package in the **Capell Foundation** product group. It ships as `capell-app/filament-peek` and extends these surfaces: admin, frontend.

Preview unsaved page and Layout Builder edits exactly as they'll render on your live theme - through a private, expiring, signed link, with nothing written to your content until you save.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/filament-peek`
- Namespace: `Capell\FilamentPeek`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Preview unsaved page and Layout Builder edits exactly as they'll render on your live theme - through a private, expiring, signed link, with nothing written to your content until you save.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Page edit preview actions (admin, required).

## Technical Shape

- Service providers: `Capell\FilamentPeek\Providers\FilamentPeekServiceProvider`.
- Config files: `packages/filament-peek/config/capell-filament-peek.php`.
- Filament classes: `PeekPagePreviewAction`, `FilamentPeekPanelExtender`, `PagePeekPreviewActionExtender`.
- Route files: `packages/filament-peek/routes/web.php`.
- Actions: `CreatePagePreviewSnapshotAction`, `FindPagePreviewSnapshotAction`, `RegisterLayoutBuilderPreviewWidgetsAction`, `RenderPagePreviewSnapshotAction`, `StoreLayoutBuilderPreviewStateAction`.
- Data objects: `LayoutBuilderPreviewStateData`, `PagePreviewSnapshotData`.
- Manifest contributions: `route: Capell\FilamentPeek\Manifest\FilamentPeekRoutesContribution`.
- Health checks: `Capell\FilamentPeek\Health\FilamentPeekHealthCheck`.
- Blade views: `packages/filament-peek/resources/views/preview-error.blade.php`, `packages/filament-peek/resources/views/preview-ribbon.blade.php`.
- Cache tags: `filament-peek-preview`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

Docs gap: document extension points here if the package delegates persistence to a host package.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `filament-peek-preview`.
- Commands: none declared.

## Common Pitfalls

- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/filament-peek`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Open the related Capell admin surface and verify Filament Peek appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Layout Builder](../layout-builder/README.md), [Publishing Studio](../publishing-studio/README.md).
- Focused tests: `vendor/bin/pest packages/filament-peek/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
