# Theme Inertia Bookings React

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Inertia Bookings React is an **Available**, **No schema impact** Capell plugin in the **Capell Themes** product group. It ships as `capell-app/theme-inertia-bookings-react` and extends these surfaces: frontend.

React component pack for Theme Inertia Bookings.

After install, the package affects public rendering, public routes, or frontend runtime behaviour.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-inertia-bookings-react`
- Namespace: `Capell\ThemeStudio\InertiaBookingsReact`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers instead of pushing this behaviour into core or application code.

**For teams:** React components for Theme Inertia Bookings.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- React booking component pack (frontend, required).
- React themed booking services (frontend, required).
- React booking slot loading state (frontend, required).
- React booking validation state (frontend, required).
- React booking mobile layout (frontend, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\InertiaBookingsReact\Providers\InertiaBookingsReactServiceProvider`.
- Manifest contributions: `frontend-component: Capell\ThemeStudio\InertiaBookingsReact\Manifest\InertiaBookingsReactComponentContribution`.
- Health checks: `Capell\ThemeStudio\InertiaBookingsReact\Health\InertiaBookingsReactHealthCheck`.
- Cache tags: `theme-inertia-bookings-react`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

Docs gap: document extension points here if the package delegates persistence to a host package.

## Install Impact

- Admin navigation: no admin surface declared.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-inertia-bookings-react`.
- Commands: none declared.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/theme-inertia-bookings-react`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Theme Inertia Bookings](../../theme-inertia-bookings/README.md), [Inertia React Adapter](../../inertia-react-adapter/README.md).
- Focused tests: `vendor/bin/pest packages/theme-inertia-bookings-react/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
