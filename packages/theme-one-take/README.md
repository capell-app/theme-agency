# Theme One Take

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme One Take is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-one-take` and extends these surfaces: frontend.

Everything in one continuous take - showcase grid, story, CTA on a single confident page - for galleries and templates that don't need a second click.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-one-take`
- Namespace: `Capell\ThemeStudio\OneTake`
- Theme key: `one-take`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A friendly Capell theme for one-page website galleries, landing page showcases, template directories, startup inspiration sites, and practical marketing resources.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- One Take Homepage (frontend, optional).
- One Take Homepage - Tablet (frontend, optional).
- One Take Homepage - Mobile (frontend, optional).
- One Take Landing page (frontend, optional).
- One Take Landing page - Tablet (frontend, optional).
- One Take Landing page - Mobile (frontend, optional).
- One Take List page (frontend, optional).
- One Take List page - Tablet (frontend, optional).
- One Take List page - Mobile (frontend, optional).
- One Take Search results (frontend, optional).
- One Take Search results - Tablet (frontend, optional).
- One Take Search results - Mobile (frontend, optional).
- One Take Contact form (frontend, optional).
- One Take Contact form - Tablet (frontend, optional).
- One Take Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\OneTake\OneTakeThemeServiceProvider`.
- Actions: `InstallOneTakeThemeDemoAction`.
- Command signatures: `capell:theme-one-take-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\OneTake\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\OneTake\Health\ThemeOneTakeHealthCheck`.
- Blade views: `packages/theme-one-take/resources/views/livewire/page/page.blade.php`, `packages/theme-one-take/resources/views/page.blade.php`, `packages/theme-one-take/resources/views/sections/build-resources.blade.php`, `packages/theme-one-take/resources/views/sections/category-tabs.blade.php`, `packages/theme-one-take/resources/views/sections/content-listing.blade.php`, `packages/theme-one-take/resources/views/sections/cta.blade.php`, `packages/theme-one-take/resources/views/sections/footer.blade.php`, `packages/theme-one-take/resources/views/sections/hero.blade.php`, `packages/theme-one-take/resources/views/sections/navigation.blade.php`, `packages/theme-one-take/resources/views/sections/newsletter.blade.php`, `packages/theme-one-take/resources/views/sections/one-page-grid.blade.php`, `packages/theme-one-take/resources/views/sections/proof.blade.php`, `and 3 more`.
- Cache tags: `theme-one-take`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-one-take`.
- Commands: `capell:theme-one-take-demo`.

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

1. Install the package: `composer require capell-app/theme-one-take`.
2. Run the required setup: `php artisan capell:theme-one-take-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-one-take/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
