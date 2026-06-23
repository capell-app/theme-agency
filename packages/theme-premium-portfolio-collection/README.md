# Theme Premium Portfolio Collection

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Premium Portfolio Collection is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-premium-portfolio-collection` and extends these surfaces: frontend.

Premium Portfolio Collection extends the default Capell frontend with an awards-style portfolio directory direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for featured portfolios, newest entries, winner labels, taxonomy filters, creator profiles, educational resources, and newsletters.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-premium-portfolio-collection`
- Namespace: `Capell\ThemeStudio\PremiumPortfolioCollection`
- Theme key: `premium-portfolio-collection`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Laravel routes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for portfolio directories, award-style collections, creator profiles, filters, galleries, and portfolio education.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Premium Portfolio Collection Homepage (frontend, optional).
- Premium Portfolio Collection Hero base desktop (frontend, optional).
- Premium Portfolio Collection Hero base mobile (frontend, optional).
- Premium Portfolio Collection Hero looping video desktop (frontend, optional).
- Premium Portfolio Collection Hero looping video mobile (frontend, optional).
- Premium Portfolio Collection Hero looping GIF desktop (frontend, optional).
- Premium Portfolio Collection Hero image-only desktop (frontend, optional).
- Premium Portfolio Collection Landing page (frontend, optional).
- Premium Portfolio Collection List page (frontend, optional).
- Premium Portfolio Collection Search results (frontend, optional).
- Premium Portfolio Collection Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\PremiumPortfolioCollection\PremiumPortfolioCollectionThemeServiceProvider`.
- Route files: `packages/theme-premium-portfolio-collection/routes/screenshot-fixtures.php`.
- Actions: `InstallPremiumPortfolioCollectionThemeDemoAction`.
- Command signatures: `capell:theme-premium-portfolio-collection-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\PremiumPortfolioCollection\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\PremiumPortfolioCollection\Health\ThemePremiumPortfolioCollectionHealthCheck`.
- Blade views: `packages/theme-premium-portfolio-collection/resources/views/livewire/page/page.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/page.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/screenshots/fixture.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/awarded-profiles.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/content-listing.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/creator-directory.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/cta.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/education-upsell.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/featured-portfolios.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/filter-taxonomies.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/footer.blade.php`, `packages/theme-premium-portfolio-collection/resources/views/sections/hero.blade.php`, `and 4 more`.
- Cache tags: `theme-premium-portfolio-collection`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-premium-portfolio-collection`.
- Commands: `capell:theme-premium-portfolio-collection-demo`.

## Common Pitfalls

- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/theme-premium-portfolio-collection`.
2. Run the required setup: `php artisan capell:theme-premium-portfolio-collection-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-premium-portfolio-collection/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
