# Theme Liquid Glass

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Liquid Glass is an **Available**, **No schema impact** Capell theme in the **Capell Foundation** product group. It ships as `capell-app/theme-liquid-glass` and extends these surfaces: frontend.

Theme Liquid Glass gives Capell sites a free modern glass interface with translucent panels, crisp typography, warm accent actions, and polished standard sections. Three presets cover bright product launches, editorial glass pages, and darker graphite surfaces while staying fully driven by Theme Studio tokens. It extends the default frontend runtime, keeps public output cache-safe and editor-free, and adds a polished alternative to the Foundation and Corporate free lanes.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/theme-liquid-glass`
- Namespace: `Capell\ThemeStudio\LiquidGlass`
- Theme key: `liquid-glass`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A free glass interface theme for modern Capell sites with translucent panels, sharp content rhythm, and three token-driven presets.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Liquid Glass homepage (frontend, optional).
- Liquid Glass landing page (frontend, optional).
- Liquid Glass listing page (frontend, optional).
- Liquid Glass search results (frontend, optional).
- Liquid Glass contact page (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider`.
- Actions: `InstallLiquidGlassThemeDemoAction`.
- Command signatures: `capell:theme-liquid-glass-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\LiquidGlass\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\LiquidGlass\Health\ThemeLiquidGlassHealthCheck`.
- Blade views: `packages/theme-liquid-glass/resources/views/livewire/page/page.blade.php`, `packages/theme-liquid-glass/resources/views/page.blade.php`, `packages/theme-liquid-glass/resources/views/sections/content-listing.blade.php`, `packages/theme-liquid-glass/resources/views/sections/cta.blade.php`, `packages/theme-liquid-glass/resources/views/sections/features.blade.php`, `packages/theme-liquid-glass/resources/views/sections/footer.blade.php`, `packages/theme-liquid-glass/resources/views/sections/hero.blade.php`, `packages/theme-liquid-glass/resources/views/sections/navigation.blade.php`, `packages/theme-liquid-glass/resources/views/sections/proof.blade.php`.
- Cache tags: `theme-liquid-glass`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-liquid-glass`.
- Commands: `capell:theme-liquid-glass-demo`.

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

1. Install the package: `composer require capell-app/theme-liquid-glass`.
2. Run the required setup: `php artisan capell:theme-liquid-glass-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- Focused tests: `vendor/bin/pest packages/theme-liquid-glass/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
