# Theme Night Shift

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Night Shift is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-night-shift` and extends these surfaces: frontend.

Near-black surfaces, refined borders, and product-UI shells for SaaS that works after dark. Roadmaps, changelogs, and security proof - all glowing quietly.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-night-shift`
- Namespace: `Capell\ThemeStudio\NightShift`
- Theme key: `night-shift`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Near-black surfaces, refined borders, and product-UI shells for SaaS that works after dark.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Night Shift Homepage (frontend, optional).
- Night Shift Homepage - Tablet (frontend, optional).
- Night Shift Homepage - Mobile (frontend, optional).
- Night Shift Landing page (frontend, optional).
- Night Shift Landing page - Tablet (frontend, optional).
- Night Shift Landing page - Mobile (frontend, optional).
- Night Shift List page (frontend, optional).
- Night Shift List page - Tablet (frontend, optional).
- Night Shift List page - Mobile (frontend, optional).
- Night Shift Search results (frontend, optional).
- Night Shift Search results - Tablet (frontend, optional).
- Night Shift Search results - Mobile (frontend, optional).
- Night Shift Contact form (frontend, optional).
- Night Shift Contact form - Tablet (frontend, optional).
- Night Shift Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\NightShift\NightShiftThemeServiceProvider`.
- Actions: `InstallNightShiftThemeDemoAction`.
- Command signatures: `capell:theme-night-shift-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\NightShift\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\NightShift\Health\ThemeNightShiftHealthCheck`.
- Blade views: `packages/theme-night-shift/resources/views/livewire/page/page.blade.php`, `packages/theme-night-shift/resources/views/page.blade.php`, `packages/theme-night-shift/resources/views/sections/agents-automation.blade.php`, `packages/theme-night-shift/resources/views/sections/changelog-integrations.blade.php`, `packages/theme-night-shift/resources/views/sections/content-listing.blade.php`, `packages/theme-night-shift/resources/views/sections/cta.blade.php`, `packages/theme-night-shift/resources/views/sections/footer.blade.php`, `packages/theme-night-shift/resources/views/sections/hero.blade.php`, `packages/theme-night-shift/resources/views/sections/navigation.blade.php`, `packages/theme-night-shift/resources/views/sections/newsletter.blade.php`, `packages/theme-night-shift/resources/views/sections/planning-roadmap.blade.php`, `packages/theme-night-shift/resources/views/sections/proof.blade.php`, `and 3 more`.
- Cache tags: `theme-night-shift`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-night-shift`.
- Commands: `capell:theme-night-shift-demo`.

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

1. Install the package: `composer require capell-app/theme-night-shift`.
2. Run the required setup: `php artisan capell:theme-night-shift-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-night-shift/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
