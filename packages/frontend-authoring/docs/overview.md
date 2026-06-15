# Frontend Authoring

<!-- prettier-ignore-start -->

## What This Plugin Adds

Frontend Authoring is an **Available**, **No schema impact** Capell package in the **Capell Foundation** product group. It ships as `capell-app/frontend-authoring` and extends these surfaces: frontend, console.

In-page (frontend) editing for Capell: an admin-only beacon adds inline edit controls to live pages without leaking any editor markup or signed URLs into public or cached output.

After install, the package affects public rendering, public routes, or frontend runtime behaviour.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/frontend-authoring`
- Namespace: `Capell\FrontendAuthoring`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, Laravel routes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Edit page titles, descriptions and content straight from the live site - admins get inline edit controls after the page loads, while visitors and caches only ever see ordinary HTML.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Frontend page with authoring enabled for admins (frontend, required).
- Capell app frontend authoring working on the public homepage (frontend, required).
- Capell app frontend authoring working on mobile (frontend, required).
- Beacon network request (admin, required).
- Frontend authoring enabled/disabled configuration proof (admin, required).

## Technical Shape

- Service providers: `Capell\FrontendAuthoring\Providers\FrontendAuthoringServiceProvider`.
- Config files: `packages/frontend-authoring/config/capell-frontend-authoring.php`.
- Livewire components: `EditRegionField`.
- Route files: `packages/frontend-authoring/routes/web.php`.
- Actions: `AuthorizeEditableRegionAction`, `BuildAuthoringBannerContextAction`, `BuildBeaconResponseAction`, `BuildEditableRegionManifestAction`, `ClearAffectedCachedUrlsAction`, `CollectAffectedCachedUrlsAction`, `UpdateEditableRegionAction`, `ValidateEditableRegionPayloadAction`.
- Data objects: `EditableRegionData`, `EditableRegionPayloadData`.
- Health checks: `Capell\FrontendAuthoring\Health\FrontendAuthoringHealthCheck`.
- Blade views: `packages/frontend-authoring/resources/views/authoring/bootstrap-script.blade.php`, `packages/frontend-authoring/resources/views/components/page-data.blade.php`, `packages/frontend-authoring/resources/views/editor/filament-shell-assets.blade.php`, `packages/frontend-authoring/resources/views/editor/region.blade.php`, `packages/frontend-authoring/resources/views/livewire/edit-region-field.blade.php`.
- Cache tags: `frontend-authoring`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

Docs gap: document extension points here if the package delegates persistence to a host package.

## Install Impact

- Admin navigation: no admin surface declared.
- Permissions: `frontend-authoring.edit`.
- Public routes: `capell-frontend.beacon` (`POST /beacon`, same-origin admin beacon, throttled and CSRF middleware disabled) and `capell-frontend.authoring.edit` (`GET /authoring/regions/{payload}`, authenticated signed editor iframe).
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `frontend-authoring`.
- Commands: none declared.

## Common Pitfalls

- Keep route metadata aligned with `routes/web.php`: the beacon stays throttled and same-origin gated, and the editor route stays authenticated, signed, and payload-validated.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/frontend-authoring`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Html Cache](../../html-cache/README.md).
- Focused tests: `vendor/bin/pest packages/frontend-authoring/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
