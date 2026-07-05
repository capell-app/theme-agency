# Theme Off Grid

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Off Grid is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-off-grid` and extends these surfaces: frontend.

Monospace type, stark links, visible focus rings, deliberately unpolished. A zine-grade archive for culture that lives outside the mainstream and distrusts gloss.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-off-grid`
- Namespace: `Capell\ThemeStudio\OffGrid`
- Theme key: `off-grid`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Monospace type, stark links, visible focus rings, deliberately unpolished.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Off Grid Homepage (frontend, optional).
- Off Grid Homepage - Tablet (frontend, optional).
- Off Grid Homepage - Mobile (frontend, optional).
- Off Grid Landing page (frontend, optional).
- Off Grid Landing page - Tablet (frontend, optional).
- Off Grid Landing page - Mobile (frontend, optional).
- Off Grid Archive (frontend, optional).
- Off Grid Archive - Tablet (frontend, optional).
- Off Grid Archive - Mobile (frontend, optional).
- Off Grid Search results (frontend, optional).
- Off Grid Search results - Tablet (frontend, optional).
- Off Grid Search results - Mobile (frontend, optional).
- Off Grid Contact form (frontend, optional).
- Off Grid Contact form - Tablet (frontend, optional).
- Off Grid Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\OffGrid\OffGridThemeServiceProvider`.
- Actions: `InstallOffGridThemeDemoAction`.
- Command signatures: `capell:theme-off-grid-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\OffGrid\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\OffGrid\Health\ThemeOffGridHealthCheck`.
- Blade views: `packages/theme-off-grid/resources/views/livewire/page/page.blade.php`, `packages/theme-off-grid/resources/views/page.blade.php`, `packages/theme-off-grid/resources/views/sections/archive-dates.blade.php`, `packages/theme-off-grid/resources/views/sections/archive-wall.blade.php`, `packages/theme-off-grid/resources/views/sections/content-listing.blade.php`, `packages/theme-off-grid/resources/views/sections/cta.blade.php`, `packages/theme-off-grid/resources/views/sections/footer.blade.php`, `packages/theme-off-grid/resources/views/sections/hero.blade.php`, `packages/theme-off-grid/resources/views/sections/irregular-index.blade.php`, `packages/theme-off-grid/resources/views/sections/navigation.blade.php`, `packages/theme-off-grid/resources/views/sections/newsletter.blade.php`, `packages/theme-off-grid/resources/views/sections/proof.blade.php`, `and 3 more`.
- Cache tags: `theme-off-grid`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-off-grid`.
- Commands: `capell:theme-off-grid-demo`.

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

1. Install the package: `composer require capell-app/theme-off-grid`.
2. Run the required setup: `php artisan capell:theme-off-grid-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-off-grid/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
