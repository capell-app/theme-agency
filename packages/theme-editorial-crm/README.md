# Theme Editorial CRM

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Editorial CRM is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-editorial-crm` and extends these surfaces: frontend.

Editorial CRM extends the default Capell frontend with a editorial CRM product direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for product dashboards, data models, workflow automation, collaboration, integrations, reporting, customer stories, neutral product fields, crisp interface modules, and flexible dashboards.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-editorial-crm`
- Namespace: `Capell\ThemeStudio\EditorialCrm`
- Theme key: `editorial-crm`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A polished Capell theme for CRM, revenue operations, customer data, automation, collaboration, integrations, reporting, and modern SaaS product storytelling.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Editorial CRM Homepage (frontend, optional).
- Editorial CRM Landing page (frontend, optional).
- Editorial CRM List page (frontend, optional).
- Editorial CRM Search results (frontend, optional).
- Editorial CRM Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\EditorialCrm\EditorialCrmThemeServiceProvider`.
- Actions: `InstallEditorialCrmThemeDemoAction`.
- Command signatures: `capell:theme-editorial-crm-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\EditorialCrm\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\EditorialCrm\Health\ThemeEditorialCrmHealthCheck`.
- Blade views: `packages/theme-editorial-crm/resources/views/livewire/page/page.blade.php`, `packages/theme-editorial-crm/resources/views/page.blade.php`, `packages/theme-editorial-crm/resources/views/sections/data-model.blade.php`, `packages/theme-editorial-crm/resources/views/sections/content-listing.blade.php`, `packages/theme-editorial-crm/resources/views/sections/cta.blade.php`, `packages/theme-editorial-crm/resources/views/sections/workflow-automation.blade.php`, `packages/theme-editorial-crm/resources/views/sections/footer.blade.php`, `packages/theme-editorial-crm/resources/views/sections/hero.blade.php`, `packages/theme-editorial-crm/resources/views/sections/collaboration.blade.php`, `packages/theme-editorial-crm/resources/views/sections/integrations-reporting.blade.php`, `packages/theme-editorial-crm/resources/views/sections/navigation.blade.php`, `packages/theme-editorial-crm/resources/views/sections/newsletter.blade.php`, `and 3 more`.
- Cache tags: `theme-editorial-crm`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-editorial-crm`.
- Commands: `capell:theme-editorial-crm-demo`.

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

1. Install the package: `composer require capell-app/theme-editorial-crm`.
2. Run the required setup: `php artisan capell:theme-editorial-crm-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-editorial-crm/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
