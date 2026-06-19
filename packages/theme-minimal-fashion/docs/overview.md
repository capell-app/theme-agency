# Theme Minimal Fashion

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Minimal Fashion is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-minimal-fashion` and extends these surfaces: frontend.

Minimal Fashion extends the default Capell frontend with a restrained fashion retail direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for seasonal collections, sparse category paths, lookbooks, product care, materials, store assistance, and newsletter conversion.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-minimal-fashion`
- Namespace: `Capell\ThemeStudio\MinimalFashion`
- Theme key: `minimal-fashion`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for restrained fashion retail, seasonal collections, lookbooks, product care, and quiet conversion.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Minimal Fashion Homepage (frontend, optional).
- Minimal Fashion Landing page (frontend, optional).
- Minimal Fashion List page (frontend, optional).
- Minimal Fashion Search results (frontend, optional).
- Minimal Fashion Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\MinimalFashion\MinimalFashionThemeServiceProvider`.
- Actions: `InstallMinimalFashionThemeDemoAction`.
- Command signatures: `capell:theme-minimal-fashion-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\MinimalFashion\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\MinimalFashion\Health\ThemeMinimalFashionHealthCheck`.
- Blade views: `packages/theme-minimal-fashion/resources/views/livewire/page/page.blade.php`, `packages/theme-minimal-fashion/resources/views/page.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/category-paths.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/content-listing.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/cta.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/features.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/footer.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/hero.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/lookbook-feature.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/material-notes.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/navigation.blade.php`, `packages/theme-minimal-fashion/resources/views/sections/newsletter.blade.php`, `and 3 more`.
- Cache tags: `theme-minimal-fashion`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-minimal-fashion`.
- Commands: `capell:theme-minimal-fashion-demo`.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/theme-minimal-fashion`.
2. Run the required setup: `php artisan capell:theme-minimal-fashion-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../../blog/README.md), [Form Builder](../../form-builder/README.md), [Newsletter](../../newsletter/README.md), [Shopify Commerce](../../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-minimal-fashion/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
