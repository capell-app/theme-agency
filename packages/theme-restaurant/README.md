# Theme Restaurant

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Restaurant is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-restaurant` and extends these surfaces: frontend, console.

Theme Restaurant gives hospitality teams a premium venue website built around menu discovery, reservation intent, private dining, events, opening hours, and location confidence. It extends the built-in default frontend theme, keeps restaurant copy and menu items in portable Capell content, and lets the package own the visual rhythm: editorial menu panels, service windows, private-room selling, event previews, and map-adjacent venue proof. It pairs with Bookings or Form Builder for reservation capture, Events for ticketed or seasonal occasions, and Blog for reviews or dining guides while rendering safe static fallbacks when those packages are absent.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-restaurant`
- Namespace: `Capell\ThemeStudio\Restaurant`
- Theme key: `restaurant`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium hospitality theme for menu-led restaurants, bars, private dining venues, and event-led dining businesses.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Restaurant homepage (frontend, optional).
- Menu highlights (frontend, optional).
- Reservation panel (frontend, optional).
- Private dining (frontend, optional).
- Restaurant events (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\Restaurant\RestaurantThemeServiceProvider`.
- Actions: `InstallRestaurantThemeDemoAction`.
- Command signatures: `capell:theme-restaurant-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\Restaurant\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\Restaurant\Health\ThemeRestaurantHealthCheck`.
- Blade views: `packages/theme-restaurant/resources/views/page.blade.php`, `packages/theme-restaurant/resources/views/sections/chef-story.blade.php`, `packages/theme-restaurant/resources/views/sections/content-listing.blade.php`, `packages/theme-restaurant/resources/views/sections/cta.blade.php`, `packages/theme-restaurant/resources/views/sections/events-calendar.blade.php`, `packages/theme-restaurant/resources/views/sections/features.blade.php`, `packages/theme-restaurant/resources/views/sections/footer.blade.php`, `packages/theme-restaurant/resources/views/sections/hero.blade.php`, `packages/theme-restaurant/resources/views/sections/location-guide.blade.php`, `packages/theme-restaurant/resources/views/sections/menu-highlights.blade.php`, `packages/theme-restaurant/resources/views/sections/navigation.blade.php`, `packages/theme-restaurant/resources/views/sections/opening-hours.blade.php`, `and 3 more`.
- Cache tags: `theme-restaurant`.

## Theme Inheritance Contract

Product group:
**Capell Themes**

- Product group: `Capell Themes`
- Manifest extends: `default`
- Runtime extends: `default`
- Restaurant runtime inheritance uses `extends: default` and requires `capell-app/frontend` for the built-in default fallback.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-restaurant`.
- Commands: `capell:theme-restaurant-demo`.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/theme-restaurant`.
2. Run the required setup: `php artisan capell:theme-restaurant-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Bookings](../bookings/README.md), [Events](../events/README.md), [Form Builder](../form-builder/README.md), [Seo Suite](../seo-suite/README.md).
- Focused tests: `vendor/bin/pest packages/theme-restaurant/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
