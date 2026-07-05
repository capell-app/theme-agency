# Theme Open Studio

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Open Studio is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-open-studio` and extends these surfaces: frontend.

Put the work centre-stage: rich case studies with credits, process notes, media stacks, and a hire-me CTA. Built for studios and creators who win clients by showing, not telling.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-open-studio`
- Namespace: `Capell\ThemeStudio\OpenStudio`
- Theme key: `open-studio`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Put the work centre-stage: rich case studies with credits, process notes, media stacks, and a hire-me CTA.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Open Studio Homepage (frontend, optional).
- Open Studio Homepage - Tablet (frontend, optional).
- Open Studio Homepage - Mobile (frontend, optional).
- Open Studio Landing page (frontend, optional).
- Open Studio Landing page - Tablet (frontend, optional).
- Open Studio Landing page - Mobile (frontend, optional).
- Open Studio List page (frontend, optional).
- Open Studio List page - Tablet (frontend, optional).
- Open Studio List page - Mobile (frontend, optional).
- Open Studio Search results (frontend, optional).
- Open Studio Search results - Tablet (frontend, optional).
- Open Studio Search results - Mobile (frontend, optional).
- Open Studio Contact form (frontend, optional).
- Open Studio Contact form - Tablet (frontend, optional).
- Open Studio Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\OpenStudio\OpenStudioThemeServiceProvider`.
- Actions: `InstallOpenStudioThemeDemoAction`.
- Command signatures: `capell:theme-open-studio-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\OpenStudio\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\OpenStudio\Health\ThemeOpenStudioHealthCheck`.
- Blade views: `packages/theme-open-studio/resources/views/livewire/page/page.blade.php`, `packages/theme-open-studio/resources/views/page.blade.php`, `packages/theme-open-studio/resources/views/sections/content-listing.blade.php`, `packages/theme-open-studio/resources/views/sections/creator-hero.blade.php`, `packages/theme-open-studio/resources/views/sections/credits-tools.blade.php`, `packages/theme-open-studio/resources/views/sections/cta.blade.php`, `packages/theme-open-studio/resources/views/sections/discipline-filters.blade.php`, `packages/theme-open-studio/resources/views/sections/footer.blade.php`, `packages/theme-open-studio/resources/views/sections/hero--split.blade.php`, `packages/theme-open-studio/resources/views/sections/hero.blade.php`, `packages/theme-open-studio/resources/views/sections/navigation.blade.php`, `packages/theme-open-studio/resources/views/sections/newsletter.blade.php`, `and 4 more`.
- Cache tags: `theme-open-studio`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-open-studio`.
- Commands: `capell:theme-open-studio-demo`.

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

1. Install the package: `composer require capell-app/theme-open-studio`.
2. Run the required setup: `php artisan capell:theme-open-studio-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-open-studio/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
