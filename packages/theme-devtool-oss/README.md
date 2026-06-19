# Theme Devtool OSS

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Devtool OSS is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-devtool-oss` and extends these surfaces: frontend.

Devtool OSS extends the default Capell frontend with a devtool oss visual direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for open-source developer tools, self-host + cloud products.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-devtool-oss`
- Namespace: `Capell\ThemeStudio\DevtoolOss`
- Theme key: `devtool-oss`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for open-source developer tools, self-host + cloud products.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Devtool OSS Homepage (frontend, required).
- Devtool OSS Directory (frontend, required).
- Devtool OSS Detail (frontend, required).
- Devtool OSS Contact (frontend, required).
- Devtool OSS Empty State (frontend, required).
- Devtool OSS 404 State (frontend, required).
- Devtool OSS Conversion CTA (frontend, required).
- Devtool OSS Install (frontend, required).
- Devtool OSS Community (frontend, required).
- Devtool OSS Cloud vs Self-host (frontend, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\DevtoolOss\DevtoolOssThemeServiceProvider`.
- Actions: `InstallDevtoolOssThemeDemoAction`.
- Command signatures: `capell:theme-devtool-oss-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\DevtoolOss\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\DevtoolOss\Health\ThemeDevtoolOssHealthCheck`.
- Blade views: `packages/theme-devtool-oss/resources/views/livewire/page/page.blade.php`, `packages/theme-devtool-oss/resources/views/page.blade.php`, `packages/theme-devtool-oss/resources/views/sections/changelog.blade.php`, `packages/theme-devtool-oss/resources/views/sections/content-listing.blade.php`, `packages/theme-devtool-oss/resources/views/sections/contributors.blade.php`, `packages/theme-devtool-oss/resources/views/sections/cta.blade.php`, `packages/theme-devtool-oss/resources/views/sections/features.blade.php`, `packages/theme-devtool-oss/resources/views/sections/footer.blade.php`, `packages/theme-devtool-oss/resources/views/sections/github-proof.blade.php`, `packages/theme-devtool-oss/resources/views/sections/hero.blade.php`, `packages/theme-devtool-oss/resources/views/sections/install-hero.blade.php`, `packages/theme-devtool-oss/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-devtool-oss`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-devtool-oss`.
- Commands: `capell:theme-devtool-oss-demo`.

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

1. Install the package: `composer require capell-app/theme-devtool-oss`.
2. Run the required setup: `php artisan capell:theme-devtool-oss-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/theme-devtool-oss/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
