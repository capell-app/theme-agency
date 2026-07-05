# Theme Launch Pad

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Launch Pad is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-launch-pad` and extends these surfaces: frontend.

A showcase gallery for landing pages and templates - rounded screenshot cards, votes, prices, and pro upsells on warm near-white. Where SaaS goes window-shopping.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-launch-pad`
- Namespace: `Capell\ThemeStudio\LaunchPad`
- Theme key: `launch-pad`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A showcase gallery for landing pages and templates - rounded screenshot cards, votes, prices, and pro upsells on warm near-white.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Launch Pad Homepage (frontend, optional).
- Launch Pad Homepage - Tablet (frontend, optional).
- Launch Pad Homepage - Mobile (frontend, optional).
- Launch Pad Landing page (frontend, optional).
- Launch Pad Landing page - Tablet (frontend, optional).
- Launch Pad Landing page - Mobile (frontend, optional).
- Launch Pad Archive page (frontend, optional).
- Launch Pad Archive page - Tablet (frontend, optional).
- Launch Pad Archive page - Mobile (frontend, optional).
- Launch Pad Search results (frontend, optional).
- Launch Pad Search results - Tablet (frontend, optional).
- Launch Pad Search results - Mobile (frontend, optional).
- Launch Pad Contact form (frontend, optional).
- Launch Pad Contact form - Tablet (frontend, optional).
- Launch Pad Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\LaunchPad\LaunchPadThemeServiceProvider`.
- Actions: `InstallLaunchPadThemeDemoAction`.
- Command signatures: `capell:theme-launch-pad-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\LaunchPad\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\LaunchPad\Health\ThemeLaunchPadHealthCheck`.
- Blade views: `packages/theme-launch-pad/resources/views/livewire/page/page.blade.php`, `packages/theme-launch-pad/resources/views/page.blade.php`, `packages/theme-launch-pad/resources/views/sections/category-navigation.blade.php`, `packages/theme-launch-pad/resources/views/sections/content-listing.blade.php`, `packages/theme-launch-pad/resources/views/sections/cta.blade.php`, `packages/theme-launch-pad/resources/views/sections/footer.blade.php`, `packages/theme-launch-pad/resources/views/sections/gallery-system.blade.php`, `packages/theme-launch-pad/resources/views/sections/hero.blade.php`, `packages/theme-launch-pad/resources/views/sections/navigation.blade.php`, `packages/theme-launch-pad/resources/views/sections/newsletter.blade.php`, `packages/theme-launch-pad/resources/views/sections/paid-templates.blade.php`, `packages/theme-launch-pad/resources/views/sections/partner-blocks.blade.php`, `and 3 more`.
- Cache tags: `theme-launch-pad`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-launch-pad`.
- Commands: `capell:theme-launch-pad-demo`.

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

1. Install the package: `composer require capell-app/theme-launch-pad`.
2. Run the required setup: `php artisan capell:theme-launch-pad-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-launch-pad/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
