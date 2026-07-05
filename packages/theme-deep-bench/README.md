# Theme Deep Bench

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Deep Bench is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-deep-bench` and extends these surfaces: frontend.

A who's-who of designers and developers: browsable portfolio listings plus career resources. The roster people check before they hire.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-deep-bench`
- Namespace: `Capell\ThemeStudio\DeepBench`
- Theme key: `deep-bench`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A who's-who of designers and developers: browsable portfolio listings plus career resources.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Deep Bench Homepage (frontend, optional).
- Deep Bench Homepage - Tablet (frontend, optional).
- Deep Bench Homepage - Mobile (frontend, optional).
- Deep Bench Landing page (frontend, optional).
- Deep Bench Landing page - Tablet (frontend, optional).
- Deep Bench Landing page - Mobile (frontend, optional).
- Deep Bench List page (frontend, optional).
- Deep Bench List page - Tablet (frontend, optional).
- Deep Bench List page - Mobile (frontend, optional).
- Deep Bench Search results (frontend, optional).
- Deep Bench Search results - Tablet (frontend, optional).
- Deep Bench Search results - Mobile (frontend, optional).
- Deep Bench Contact form (frontend, optional).
- Deep Bench Contact form - Tablet (frontend, optional).
- Deep Bench Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\DeepBench\DeepBenchThemeServiceProvider`.
- Actions: `InstallDeepBenchThemeDemoAction`.
- Command signatures: `capell:theme-deep-bench-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\DeepBench\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\DeepBench\Health\ThemeDeepBenchHealthCheck`.
- Blade views: `packages/theme-deep-bench/resources/views/livewire/page/page.blade.php`, `packages/theme-deep-bench/resources/views/page.blade.php`, `packages/theme-deep-bench/resources/views/sections/content-listing.blade.php`, `packages/theme-deep-bench/resources/views/sections/cta.blade.php`, `packages/theme-deep-bench/resources/views/sections/curated-lists.blade.php`, `packages/theme-deep-bench/resources/views/sections/directory-hero.blade.php`, `packages/theme-deep-bench/resources/views/sections/footer.blade.php`, `packages/theme-deep-bench/resources/views/sections/hero.blade.php`, `packages/theme-deep-bench/resources/views/sections/navigation.blade.php`, `packages/theme-deep-bench/resources/views/sections/newsletter.blade.php`, `packages/theme-deep-bench/resources/views/sections/portfolio-grid.blade.php`, `packages/theme-deep-bench/resources/views/sections/profile-detail.blade.php`, `and 3 more`.
- Cache tags: `theme-deep-bench`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-deep-bench`.
- Commands: `capell:theme-deep-bench-demo`.

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

1. Install the package: `composer require capell-app/theme-deep-bench`.
2. Run the required setup: `php artisan capell:theme-deep-bench-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-deep-bench/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
