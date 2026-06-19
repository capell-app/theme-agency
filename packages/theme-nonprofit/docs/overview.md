# Theme Nonprofit

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Nonprofit is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-nonprofit` and extends these surfaces: frontend.

Impact-led civic and charity theme for campaigns, donations, volunteering, and community stories.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-nonprofit`
- Namespace: `Capell\ThemeStudio\Nonprofit`
- Theme key: `nonprofit`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium charity, NGO, and civic theme that moves visitors from your mission to a clear support action - donate, volunteer, or follow - with impact proof, live campaign progress, and transparent annual-report sections built in.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Theme admin list showing Nonprofit (admin, required).
- Frontend page rendered with Nonprofit theme (frontend, required).
- Nonprofit homepage (frontend, required).
- Campaigns and appeals (frontend, required).
- Impact evidence (frontend, required).
- Volunteer and donate (frontend, required).
- Community events (frontend, required).
- Supporter and beneficiary stories (frontend, required).
- Signed admin preview route (admin, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider`.
- Actions: `InstallNonprofitThemeDemoAction`.
- Command signatures: `capell:theme-nonprofit-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\Nonprofit\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\Nonprofit\Health\ThemeNonprofitHealthCheck`.
- Blade views: `packages/theme-nonprofit/resources/views/page.blade.php`, `packages/theme-nonprofit/resources/views/sections/annual-report-proof.blade.php`, `packages/theme-nonprofit/resources/views/sections/campaigns.blade.php`, `packages/theme-nonprofit/resources/views/sections/contact.blade.php`, `packages/theme-nonprofit/resources/views/sections/content-listing.blade.php`, `packages/theme-nonprofit/resources/views/sections/cta.blade.php`, `packages/theme-nonprofit/resources/views/sections/donation-impact.blade.php`, `packages/theme-nonprofit/resources/views/sections/events.blade.php`, `packages/theme-nonprofit/resources/views/sections/features.blade.php`, `packages/theme-nonprofit/resources/views/sections/footer.blade.php`, `packages/theme-nonprofit/resources/views/sections/hero.blade.php`, `packages/theme-nonprofit/resources/views/sections/impact.blade.php`, `and 6 more`.
- Cache tags: `theme-nonprofit`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-nonprofit`.
- Commands: `capell:theme-nonprofit-demo`.

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

1. Install the package: `composer require capell-app/theme-nonprofit`.
2. Run the required setup: `php artisan capell:theme-nonprofit-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Layout Builder](../../layout-builder/README.md), [Frontend Authoring](../../frontend-authoring/README.md), [Publishing Studio](../../publishing-studio/README.md), [Seo Suite](../../seo-suite/README.md), [Blog](../../blog/README.md).
- Focused tests: `vendor/bin/pest packages/theme-nonprofit/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
