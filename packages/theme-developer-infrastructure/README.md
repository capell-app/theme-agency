# Theme Developer Infrastructure

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Developer Infrastructure is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-developer-infrastructure` and extends these surfaces: frontend.

Developer Infrastructure extends the default Capell frontend with a developer infrastructure product direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for platform pillars, command blocks, deploy timelines, docs and changelog modules, integrations, architecture diagrams, enterprise CTAs, mono labels, sharp dividers, and technical feature lists.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-developer-infrastructure`
- Namespace: `Capell\ThemeStudio\DeveloperInfrastructure`
- Theme key: `developer-infrastructure`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A minimal Capell theme for developer platforms, infrastructure products, API products, deployment tools, technical SaaS, and enterprise developer workflows.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Developer Infrastructure Homepage (frontend, optional).
- Developer Infrastructure Landing page (frontend, optional).
- Developer Infrastructure List page (frontend, optional).
- Developer Infrastructure Search results (frontend, optional).
- Developer Infrastructure Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\DeveloperInfrastructure\DeveloperInfrastructureThemeServiceProvider`.
- Actions: `InstallDeveloperInfrastructureThemeDemoAction`.
- Command signatures: `capell:theme-developer-infrastructure-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\DeveloperInfrastructure\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\DeveloperInfrastructure\Health\ThemeDeveloperInfrastructureHealthCheck`.
- Blade views: `packages/theme-developer-infrastructure/resources/views/livewire/page/page.blade.php`, `packages/theme-developer-infrastructure/resources/views/page.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/architecture-enterprise.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/command-blocks.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/content-listing.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/cta.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/deploy-timeline.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/docs-changelog.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/footer.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/hero.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/integrations.blade.php`, `packages/theme-developer-infrastructure/resources/views/sections/navigation.blade.php`, `and 3 more`.
- Cache tags: `theme-developer-infrastructure`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-developer-infrastructure`.
- Commands: `capell:theme-developer-infrastructure-demo`.

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

1. Install the package: `composer require capell-app/theme-developer-infrastructure`.
2. Run the required setup: `php artisan capell:theme-developer-infrastructure-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-developer-infrastructure/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
