# Theme Quiet Web Gallery

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Quiet Web Gallery is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-quiet-web-gallery` and extends these surfaces: frontend.

Quiet Web Gallery extends the default Capell frontend with a calm web-design gallery direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for light grey pages, modest typography, practical browse panels, understated image grids, latest showcase entries, sponsor space, category archives, random picks, best-of lists, simple posts, and newsletter paths.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-quiet-web-gallery`
- Namespace: `Capell\ThemeStudio\QuietWebGallery`
- Theme key: `quiet-web-gallery`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A quiet Capell theme for calm web-design galleries with practical browse panels, understated image grids, latest entries, sponsor space, category archives, random picks, best-of lists, and simple posts.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Quiet Web Gallery Homepage (frontend, optional).
- Quiet Web Gallery Landing page (frontend, optional).
- Quiet Web Gallery Category archive (frontend, optional).
- Quiet Web Gallery Search results (frontend, optional).
- Quiet Web Gallery Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\QuietWebGallery\QuietWebGalleryThemeServiceProvider`.
- Actions: `InstallQuietWebGalleryThemeDemoAction`.
- Command signatures: `capell:theme-quiet-web-gallery-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\QuietWebGallery\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\QuietWebGallery\Health\ThemeQuietWebGalleryHealthCheck`.
- Blade views: `packages/theme-quiet-web-gallery/resources/views/livewire/page/page.blade.php`, `packages/theme-quiet-web-gallery/resources/views/page.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/style-type-categories.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/content-listing.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/cta.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/latest-showcase.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/footer.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/hero.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/sponsor-space.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/random-best-of.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/navigation.blade.php`, `packages/theme-quiet-web-gallery/resources/views/sections/newsletter.blade.php`, `and 3 more`.
- Cache tags: `theme-quiet-web-gallery`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-quiet-web-gallery`.
- Commands: `capell:theme-quiet-web-gallery-demo`.

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

1. Install the package: `composer require capell-app/theme-quiet-web-gallery`.
2. Run the required setup: `php artisan capell:theme-quiet-web-gallery-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-quiet-web-gallery/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
