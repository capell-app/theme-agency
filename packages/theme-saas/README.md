# Theme Saas

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Saas is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-saas` and extends these surfaces: frontend.

Theme SaaS gives software and subscription businesses a product-led storefront out of the box: an activation-first hero, metric and logo proof, a plan-comparison and pricing layout, an ROI calculator, docs onboarding, and a demo-request flow - all rendered from portable Capell content with no presentation markup stored in your pages. It extends the built-in default frontend theme, so your content stays clean while this theme owns the conversion layout, palette, and rhythm. Pairs with Form Builder for live demo/trial capture and Document Lifecycle for in-theme docs, and reads brand tokens so the SaaS preset re-skins the whole site. Built on Blade + Tailwind, cache-safe, and translation-ready.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-saas`
- Namespace: `Capell\ThemeStudio\Saas`
- Theme key: `saas`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A conversion-focused premium theme for software and subscription products - hero, feature proof, pricing comparison, calculator, docs, and demo-request sections that turn a Capell site into a product-led landing experience.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Theme preset selection showing SaaS (admin, required).
- Signed admin preview route (admin, required).
- Frontend page rendered with SaaS theme (frontend, required).
- SaaS homepage (frontend, required).
- SaaS directory page (frontend, required).
- SaaS detail page (frontend, required).
- SaaS contact page (frontend, required).
- SaaS empty state page (frontend, required).
- SaaS CTA (frontend, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\Saas\SaasThemeServiceProvider`.
- Actions: `InstallSaasThemeDemoAction`.
- Command signatures: `capell:theme-saas-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\Saas\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\Saas\Health\ThemeSaasHealthCheck`.
- Blade views: `packages/theme-saas/resources/views/blog/article.blade.php`, `packages/theme-saas/resources/views/blog/index.blade.php`, `packages/theme-saas/resources/views/livewire/page/page.blade.php`, `packages/theme-saas/resources/views/page.blade.php`, `packages/theme-saas/resources/views/sections/blog.blade.php`, `packages/theme-saas/resources/views/sections/calculator.blade.php`, `packages/theme-saas/resources/views/sections/comparison.blade.php`, `packages/theme-saas/resources/views/sections/content-listing.blade.php`, `packages/theme-saas/resources/views/sections/cta.blade.php`, `packages/theme-saas/resources/views/sections/demo-request.blade.php`, `packages/theme-saas/resources/views/sections/docs-onboarding.blade.php`, `packages/theme-saas/resources/views/sections/faq.blade.php`, `and 8 more`.
- Cache tags: `theme-saas`.

## Test Command

Run package tests from the repository root.

From the repository root, run `vendor/bin/pest packages/theme-saas/tests`; this package does not ship its own PHPUnit config.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-saas`.
- Commands: `capell:theme-saas-demo`.

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

1. Install the package: `composer require capell-app/theme-saas`.
2. Run the required setup: `php artisan capell:theme-saas-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md).
- Focused tests: `vendor/bin/pest packages/theme-saas/tests`.

<!-- prettier-ignore-end -->
