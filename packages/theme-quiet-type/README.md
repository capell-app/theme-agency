# Theme Quiet Type

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Quiet Type is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-quiet-type` and extends these surfaces: frontend.

Print-voice serif typography, generous measure, nothing shouting. For essayists and journals where the words are the design.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-quiet-type`
- Namespace: `Capell\ThemeStudio\QuietType`
- Theme key: `quiet-type`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Print-voice serif typography, generous measure, nothing shouting - for essayists and journals where the words are the design.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Quiet Type Homepage (frontend, optional).
- Quiet Type Homepage - Tablet (frontend, optional).
- Quiet Type Homepage - Mobile (frontend, optional).
- Quiet Type Directory (frontend, optional).
- Quiet Type Directory - Tablet (frontend, optional).
- Quiet Type Directory - Mobile (frontend, optional).
- Quiet Type Detail Article (frontend, optional).
- Quiet Type Detail Article - Tablet (frontend, optional).
- Quiet Type Detail Article - Mobile (frontend, optional).
- Quiet Type Contact (frontend, optional).
- Quiet Type Contact - Tablet (frontend, optional).
- Quiet Type Contact - Mobile (frontend, optional).
- Quiet Type Empty State (frontend, optional).
- Quiet Type Empty State - Tablet (frontend, optional).
- Quiet Type Empty State - Mobile (frontend, optional).
- Quiet Type Page Not Found (frontend, optional).
- Quiet Type Page Not Found - Tablet (frontend, optional).
- Quiet Type Page Not Found - Mobile (frontend, optional).
- Quiet Type Call To Action (frontend, optional).
- Quiet Type Call To Action - Tablet (frontend, optional).
- Quiet Type Call To Action - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\QuietType\QuietTypeThemeServiceProvider`.
- Actions: `InstallQuietTypeThemeDemoAction`.
- Command signatures: `capell:theme-quiet-type-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\QuietType\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\QuietType\Health\ThemeQuietTypeHealthCheck`.
- Blade views: `packages/theme-quiet-type/resources/views/livewire/page/page.blade.php`, `packages/theme-quiet-type/resources/views/page.blade.php`, `packages/theme-quiet-type/resources/views/sections/author-profiles.blade.php`, `packages/theme-quiet-type/resources/views/sections/content-listing.blade.php`, `packages/theme-quiet-type/resources/views/sections/cta.blade.php`, `packages/theme-quiet-type/resources/views/sections/editorial-statement.blade.php`, `packages/theme-quiet-type/resources/views/sections/essay-index.blade.php`, `packages/theme-quiet-type/resources/views/sections/features.blade.php`, `packages/theme-quiet-type/resources/views/sections/hero.blade.php`, `packages/theme-quiet-type/resources/views/sections/issue-archive.blade.php`, `packages/theme-quiet-type/resources/views/sections/proof.blade.php`, `packages/theme-quiet-type/resources/views/sections/subscription-panel.blade.php`.
- Cache tags: `theme-quiet-type`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-quiet-type`.
- Commands: `capell:theme-quiet-type-demo`.

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

1. Install the package: `composer require capell-app/theme-quiet-type`.
2. Run the required setup: `php artisan capell:theme-quiet-type-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/theme-quiet-type/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
