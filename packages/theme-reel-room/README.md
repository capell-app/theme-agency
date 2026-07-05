# Theme Reel Room

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Reel Room is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-reel-room` and extends these surfaces: frontend.

A digital-awards archive with motion previews, jury scores, and sharp media frames on muted grey. Every winner, every credit, every frame accounted for.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-reel-room`
- Namespace: `Capell\ThemeStudio\ReelRoom`
- Theme key: `reel-room`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A digital-awards archive with motion previews, jury scores, and sharp media frames on muted grey.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Reel Room Homepage (frontend, optional).
- Reel Room Homepage — Tablet (frontend, optional).
- Reel Room Homepage — Mobile (frontend, optional).
- Reel Room Landing page (frontend, optional).
- Reel Room Landing page — Tablet (frontend, optional).
- Reel Room Landing page — Mobile (frontend, optional).
- Reel Room Winner archive (frontend, optional).
- Reel Room Winner archive — Tablet (frontend, optional).
- Reel Room Winner archive — Mobile (frontend, optional).
- Reel Room Search results (frontend, optional).
- Reel Room Search results — Tablet (frontend, optional).
- Reel Room Search results — Mobile (frontend, optional).
- Reel Room Contact form (frontend, optional).
- Reel Room Contact form — Tablet (frontend, optional).
- Reel Room Contact form — Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\ReelRoom\ReelRoomThemeServiceProvider`.
- Actions: `InstallReelRoomThemeDemoAction`.
- Command signatures: `capell:theme-reel-room-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\ReelRoom\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\ReelRoom\Health\ThemeReelRoomHealthCheck`.
- Blade views: `packages/theme-reel-room/resources/views/livewire/page/page.blade.php`, `packages/theme-reel-room/resources/views/page.blade.php`, `packages/theme-reel-room/resources/views/sections/archive-hero.blade.php`, `packages/theme-reel-room/resources/views/sections/content-listing.blade.php`, `packages/theme-reel-room/resources/views/sections/cta.blade.php`, `packages/theme-reel-room/resources/views/sections/date-filter-rail.blade.php`, `packages/theme-reel-room/resources/views/sections/featured-project.blade.php`, `packages/theme-reel-room/resources/views/sections/footer.blade.php`, `packages/theme-reel-room/resources/views/sections/hero.blade.php`, `packages/theme-reel-room/resources/views/sections/jury-score-explainer.blade.php`, `packages/theme-reel-room/resources/views/sections/media-credits.blade.php`, `packages/theme-reel-room/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-reel-room`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-reel-room`.
- Commands: `capell:theme-reel-room-demo`.

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

1. Install the package: `composer require capell-app/theme-reel-room`.
2. Run the required setup: `php artisan capell:theme-reel-room-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-reel-room/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
