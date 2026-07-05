# Theme Field Guide

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Field Guide is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-field-guide` and extends these surfaces: frontend.

A reference library you can actually navigate: multi-taxonomy filters, dense grids, thousands of entries kept findable. Inspiration at catalogue scale.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-field-guide`
- Namespace: `Capell\ThemeStudio\FieldGuide`
- Theme key: `field-guide`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A reference library you can actually navigate: multi-taxonomy filters, dense grids, and thousands of entries kept findable at catalogue scale.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Field Guide Homepage (frontend, optional).
- Field Guide Homepage — Tablet (frontend, optional).
- Field Guide Homepage — Mobile (frontend, optional).
- Field Guide Landing page (frontend, optional).
- Field Guide Landing page — Tablet (frontend, optional).
- Field Guide Landing page — Mobile (frontend, optional).
- Field Guide Filter archive (frontend, optional).
- Field Guide Filter archive — Tablet (frontend, optional).
- Field Guide Filter archive — Mobile (frontend, optional).
- Field Guide Search results (frontend, optional).
- Field Guide Search results — Tablet (frontend, optional).
- Field Guide Search results — Mobile (frontend, optional).
- Field Guide Contact form (frontend, optional).
- Field Guide Contact form — Tablet (frontend, optional).
- Field Guide Contact form — Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\FieldGuide\FieldGuideThemeServiceProvider`.
- Actions: `InstallFieldGuideThemeDemoAction`.
- Command signatures: `capell:theme-field-guide-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\FieldGuide\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\FieldGuide\Health\ThemeFieldGuideHealthCheck`.
- Blade views: `packages/theme-field-guide/resources/views/livewire/page/page.blade.php`, `packages/theme-field-guide/resources/views/page.blade.php`, `packages/theme-field-guide/resources/views/sections/blog-mission.blade.php`, `packages/theme-field-guide/resources/views/sections/content-listing.blade.php`, `packages/theme-field-guide/resources/views/sections/cta.blade.php`, `packages/theme-field-guide/resources/views/sections/editor-picks.blade.php`, `packages/theme-field-guide/resources/views/sections/faq-archives.blade.php`, `packages/theme-field-guide/resources/views/sections/filter-hero.blade.php`, `packages/theme-field-guide/resources/views/sections/footer.blade.php`, `packages/theme-field-guide/resources/views/sections/hero.blade.php`, `packages/theme-field-guide/resources/views/sections/latest-designs.blade.php`, `packages/theme-field-guide/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-field-guide`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-field-guide`.
- Commands: `capell:theme-field-guide-demo`.

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

1. Install the package: `composer require capell-app/theme-field-guide`.
2. Run the required setup: `php artisan capell:theme-field-guide-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-field-guide/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
