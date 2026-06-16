# Theme Inertia Bookings

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Inertia Bookings is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-inertia-bookings` and extends these surfaces: frontend.

Premium Inertia booking-business theme for services, clinics, consultants, classes, and appointments.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-inertia-bookings`
- Namespace: `Capell\ThemeStudio\InertiaBookings`
- Theme key: `inertia-bookings`

## Why It Matters

**For developers:** The package gives developers package-owned service providers instead of pushing this behaviour into core or application code.

**For teams:** Premium Inertia booking-business theme for services, clinics, consultants, classes, and appointments.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Inertia bookings homepage (frontend, required).
- Inertia public booking request (frontend, required).
- Inertia bookings service sections (frontend, required).
- Inertia bookings locations and FAQ (frontend, required).
- Inertia bookings mobile request flow (frontend, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\InertiaBookings\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\InertiaBookings\Health\ThemeInertiaBookingsHealthCheck`.
- Cache tags: `theme-inertia-bookings`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-inertia-bookings`.
- Commands: none declared.
- Required runtime path: install `capell-app/inertia`, `capell-app/bookings`, the configured generic Inertia adapter, and the matching `theme-inertia-bookings-vue` or `theme-inertia-bookings-react` component pack.
- Health checks: verify the selected adapter package and the matching booking request component pack expose the required `Capell/Page` and `Capell/Bookings/Request` components.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Health fails for adapter components | `CAPELL_INERTIA_ADAPTER` is set to an adapter whose theme component pack is not installed or whose component map is incomplete | Check the configured adapter and matching `theme-inertia-bookings-*` package health | Install the matching Vue or React component pack and rebuild frontend assets |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/theme-inertia-bookings`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Inertia](../inertia/README.md), [Bookings](../bookings/README.md), [Inertia Vue Adapter](../inertia-vue-adapter/README.md), [Inertia React Adapter](../inertia-react-adapter/README.md), [Layout Builder](../layout-builder/README.md).
- Focused tests: `vendor/bin/pest packages/theme-inertia-bookings/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
