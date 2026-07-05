# Theme First Light

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme First Light is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-first-light` and extends these surfaces: frontend.

A calm daily feed of screenshots and finds: hairline borders, tiny labels, loose masonry, zero noise. Curation that respects the reader's morning.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-first-light`
- Namespace: `Capell\ThemeStudio\FirstLight`
- Theme key: `first-light`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A calm daily feed of screenshots and finds: hairline borders, tiny labels, loose masonry, zero noise.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- First Light Homepage (frontend, optional).
- First Light Homepage - Tablet (frontend, optional).
- First Light Homepage - Mobile (frontend, optional).
- First Light Landing page (frontend, optional).
- First Light Landing page - Tablet (frontend, optional).
- First Light Landing page - Mobile (frontend, optional).
- First Light Latest feed (frontend, optional).
- First Light Latest feed - Tablet (frontend, optional).
- First Light Latest feed - Mobile (frontend, optional).
- First Light Search results (frontend, optional).
- First Light Search results - Tablet (frontend, optional).
- First Light Search results - Mobile (frontend, optional).
- First Light Contact form (frontend, optional).
- First Light Contact form - Tablet (frontend, optional).
- First Light Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\FirstLight\FirstLightThemeServiceProvider`.
- Actions: `InstallFirstLightThemeDemoAction`.
- Command signatures: `capell:theme-first-light-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\FirstLight\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\FirstLight\Health\ThemeFirstLightHealthCheck`.
- Blade views: `packages/theme-first-light/resources/views/livewire/page/page.blade.php`, `packages/theme-first-light/resources/views/page.blade.php`, `packages/theme-first-light/resources/views/sections/app-website-icons.blade.php`, `packages/theme-first-light/resources/views/sections/best-of-views.blade.php`, `packages/theme-first-light/resources/views/sections/category-tabs.blade.php`, `packages/theme-first-light/resources/views/sections/content-listing.blade.php`, `packages/theme-first-light/resources/views/sections/cta.blade.php`, `packages/theme-first-light/resources/views/sections/curation-feed.blade.php`, `packages/theme-first-light/resources/views/sections/feed-hero.blade.php`, `packages/theme-first-light/resources/views/sections/footer.blade.php`, `packages/theme-first-light/resources/views/sections/hero.blade.php`, `packages/theme-first-light/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-first-light`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-first-light`.
- Commands: `capell:theme-first-light-demo`.

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

1. Install the package: `composer require capell-app/theme-first-light`.
2. Run the required setup: `php artisan capell:theme-first-light-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-first-light/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
