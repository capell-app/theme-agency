# Theme Front Row

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Front Row is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-front-row` and extends these surfaces: frontend.

Awarded portfolios and design-education picks with a best-seat-in-the-house presentation. Includes the playful curated "Hand Picked" preset for lighter, taste-led indexes.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-front-row`
- Namespace: `Capell\ThemeStudio\FrontRow`
- Theme key: `front-row`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Awarded portfolios and design-education picks with a best-seat-in-the-house presentation.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Front Row Homepage (frontend, optional).
- Front Row Homepage - Tablet (frontend, optional).
- Front Row Homepage - Mobile (frontend, optional).
- Front Row Landing page (frontend, optional).
- Front Row Landing page - Tablet (frontend, optional).
- Front Row Landing page - Mobile (frontend, optional).
- Front Row List page (frontend, optional).
- Front Row List page - Tablet (frontend, optional).
- Front Row List page - Mobile (frontend, optional).
- Front Row Search results (frontend, optional).
- Front Row Search results - Tablet (frontend, optional).
- Front Row Search results - Mobile (frontend, optional).
- Front Row Contact form (frontend, optional).
- Front Row Contact form - Tablet (frontend, optional).
- Front Row Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\FrontRow\FrontRowThemeServiceProvider`.
- Actions: `InstallFrontRowThemeDemoAction`.
- Command signatures: `capell:theme-front-row-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\FrontRow\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\FrontRow\Health\ThemeFrontRowHealthCheck`.
- Blade views: `packages/theme-front-row/resources/views/livewire/page/page.blade.php`, `packages/theme-front-row/resources/views/page.blade.php`, `packages/theme-front-row/resources/views/sections/awarded-profiles.blade.php`, `packages/theme-front-row/resources/views/sections/content-listing.blade.php`, `packages/theme-front-row/resources/views/sections/creator-directory.blade.php`, `packages/theme-front-row/resources/views/sections/cta.blade.php`, `packages/theme-front-row/resources/views/sections/education-upsell.blade.php`, `packages/theme-front-row/resources/views/sections/featured-portfolios.blade.php`, `packages/theme-front-row/resources/views/sections/filter-taxonomies.blade.php`, `packages/theme-front-row/resources/views/sections/footer.blade.php`, `packages/theme-front-row/resources/views/sections/hero.blade.php`, `packages/theme-front-row/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-front-row`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-front-row`.
- Commands: `capell:theme-front-row-demo`.

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

1. Install the package: `composer require capell-app/theme-front-row`.
2. Run the required setup: `php artisan capell:theme-front-row-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-front-row/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
