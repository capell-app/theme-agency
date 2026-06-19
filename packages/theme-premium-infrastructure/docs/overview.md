# Theme Premium Infrastructure

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Premium Infrastructure is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-premium-infrastructure` and extends these surfaces: frontend.

Premium Infrastructure extends the default Capell frontend with a premium infrastructure direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for product panels, solutions, global scale, developer tools, case studies, news, trust, compliance, metrics strips, code/product split sections, and layered UI mockups.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-premium-infrastructure`
- Namespace: `Capell\ThemeStudio\PremiumInfrastructure`
- Theme key: `premium-infrastructure`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for complex infrastructure platforms, B2B SaaS, developer tool companies, global fintech products, enterprise APIs, and trust-heavy product marketing.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Premium Infrastructure Homepage (frontend, optional).
- Premium Infrastructure Landing page (frontend, optional).
- Premium Infrastructure List page (frontend, optional).
- Premium Infrastructure Search results (frontend, optional).
- Premium Infrastructure Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\PremiumInfrastructure\PremiumInfrastructureThemeServiceProvider`.
- Actions: `InstallPremiumInfrastructureThemeDemoAction`.
- Command signatures: `capell:theme-premium-infrastructure-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\PremiumInfrastructure\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\PremiumInfrastructure\Health\ThemePremiumInfrastructureHealthCheck`.
- Blade views: `packages/theme-premium-infrastructure/resources/views/livewire/page/page.blade.php`, `packages/theme-premium-infrastructure/resources/views/page.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/solutions.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/content-listing.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/cta.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/global-scale.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/footer.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/hero.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/developer-tools.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/case-studies-news.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/navigation.blade.php`, `packages/theme-premium-infrastructure/resources/views/sections/newsletter.blade.php`, `and 3 more`.
- Cache tags: `theme-premium-infrastructure`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-premium-infrastructure`.
- Commands: `capell:theme-premium-infrastructure-demo`.

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

1. Install the package: `composer require capell-app/theme-premium-infrastructure`.
2. Run the required setup: `php artisan capell:theme-premium-infrastructure-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../../blog/README.md), [Form Builder](../../form-builder/README.md), [Newsletter](../../newsletter/README.md), [Shopify Commerce](../../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-premium-infrastructure/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
