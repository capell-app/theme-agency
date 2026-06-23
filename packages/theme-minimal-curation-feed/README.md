# Theme Minimal Curation Feed

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Minimal Curation Feed is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-minimal-curation-feed` and extends these surfaces: frontend.

Minimal Curation Feed extends the default Capell frontend with a calm daily curation direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for white pages, system sans typography, hairline borders, tiny labels, loose masonry columns, screenshot cards, maker/source/rating/platform metadata, live update indicators, category tabs, search, newsletter signup, best-of lists, latest feeds, apps, websites, and icons.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-minimal-curation-feed`
- Namespace: `Capell\ThemeStudio\MinimalCurationFeed`
- Theme key: `minimal-curation-feed`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A minimal Capell theme for daily inspiration feeds with screenshots, app and website references, icon views, tiny labels, category tabs, search, and newsletter signup.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Minimal Curation Feed Homepage (frontend, optional).
- Minimal Curation Feed Landing page (frontend, optional).
- Minimal Curation Feed Latest feed (frontend, optional).
- Minimal Curation Feed Search results (frontend, optional).
- Minimal Curation Feed Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\MinimalCurationFeed\MinimalCurationFeedThemeServiceProvider`.
- Actions: `InstallMinimalCurationFeedThemeDemoAction`.
- Command signatures: `capell:theme-minimal-curation-feed-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\MinimalCurationFeed\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\MinimalCurationFeed\Health\ThemeMinimalCurationFeedHealthCheck`.
- Blade views: `packages/theme-minimal-curation-feed/resources/views/livewire/page/page.blade.php`, `packages/theme-minimal-curation-feed/resources/views/page.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/app-website-icons.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/best-of-views.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/category-tabs.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/content-listing.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/cta.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/curation-feed.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/feed-hero.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/footer.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/hero.blade.php`, `packages/theme-minimal-curation-feed/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-minimal-curation-feed`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-minimal-curation-feed`.
- Commands: `capell:theme-minimal-curation-feed-demo`.

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

1. Install the package: `composer require capell-app/theme-minimal-curation-feed`.
2. Run the required setup: `php artisan capell:theme-minimal-curation-feed-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-minimal-curation-feed/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
