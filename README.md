# Theme Agency

<!-- prettier-ignore-start -->

## What This Extension Adds

Theme Agency is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-agency` and extends these surfaces: frontend.

Awarded portfolios and design-education picks with a best-seat-in-the-house presentation. Includes the playful curated "Hand Picked" preset for lighter, taste-led indexes.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-agency`
- Namespace: `Capell\ThemeAgency`
- Theme key: `agency`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Awarded portfolios and design-education picks with a best-seat-in-the-house presentation.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Agency Homepage (frontend, optional).
- Agency Homepage - Tablet (frontend, optional).
- Agency Homepage - Mobile (frontend, optional).
- Agency Landing page (frontend, optional).
- Agency Landing page - Tablet (frontend, optional).
- Agency Landing page - Mobile (frontend, optional).
- Agency List page (frontend, optional).
- Agency List page - Tablet (frontend, optional).
- Agency List page - Mobile (frontend, optional).
- Agency Search results (frontend, optional).
- Agency Search results - Tablet (frontend, optional).
- Agency Search results - Mobile (frontend, optional).
- Agency Contact form (frontend, optional).
- Agency Contact form - Tablet (frontend, optional).
- Agency Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeAgency\AgencyThemeServiceProvider`.
- Actions: `InstallAgencyThemeDemoAction`.
- Command signatures: `capell:theme-agency-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeAgency\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeAgency\Health\ThemeAgencyHealthCheck`.
- Blade views: `packages/theme-agency/resources/views/livewire/page/page.blade.php`, `packages/theme-agency/resources/views/page.blade.php`, `packages/theme-agency/resources/views/sections/awarded-profiles.blade.php`, `packages/theme-agency/resources/views/sections/content-listing.blade.php`, `packages/theme-agency/resources/views/sections/creator-directory.blade.php`, `packages/theme-agency/resources/views/sections/cta.blade.php`, `packages/theme-agency/resources/views/sections/education-upsell.blade.php`, `packages/theme-agency/resources/views/sections/featured-portfolios.blade.php`, `packages/theme-agency/resources/views/sections/filter-taxonomies.blade.php`, `packages/theme-agency/resources/views/sections/footer.blade.php`, `packages/theme-agency/resources/views/sections/hero.blade.php`, `packages/theme-agency/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-agency`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-agency`.
- Commands: `capell:theme-agency-demo`.

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

1. Install the package: `composer require capell-app/theme-agency`.
2. Run the required setup: `php artisan capell:theme-agency-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-agency/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
