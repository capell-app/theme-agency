# Theme Crypto DeFi

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Crypto DeFi is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-crypto-defi` and extends these surfaces: frontend.

Crypto DeFi extends the default Capell frontend with a crypto defi visual direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for defi protocols, on-chain lending markets.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-crypto-defi`
- Namespace: `Capell\ThemeStudio\CryptoDefi`
- Theme key: `crypto-defi`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for defi protocols, on-chain lending markets.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Crypto DeFi Homepage (frontend, required).
- Crypto DeFi Directory (frontend, required).
- Crypto DeFi Detail (frontend, required).
- Crypto DeFi Contact (frontend, required).
- Crypto DeFi Empty State (frontend, required).
- Crypto DeFi 404 State (frontend, required).
- Crypto DeFi Conversion CTA (frontend, required).
- Crypto DeFi Markets (frontend, required).
- Crypto DeFi How It Works (frontend, required).
- Crypto DeFi Audit & Risk (frontend, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\CryptoDefi\CryptoDefiThemeServiceProvider`.
- Actions: `InstallCryptoDefiThemeDemoAction`.
- Command signatures: `capell:theme-crypto-defi-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\CryptoDefi\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\CryptoDefi\Health\ThemeCryptoDefiHealthCheck`.
- Blade views: `packages/theme-crypto-defi/resources/views/livewire/page/page.blade.php`, `packages/theme-crypto-defi/resources/views/page.blade.php`, `packages/theme-crypto-defi/resources/views/sections/audit-badges.blade.php`, `packages/theme-crypto-defi/resources/views/sections/content-listing.blade.php`, `packages/theme-crypto-defi/resources/views/sections/cta.blade.php`, `packages/theme-crypto-defi/resources/views/sections/features.blade.php`, `packages/theme-crypto-defi/resources/views/sections/footer.blade.php`, `packages/theme-crypto-defi/resources/views/sections/hero.blade.php`, `packages/theme-crypto-defi/resources/views/sections/how-it-works.blade.php`, `packages/theme-crypto-defi/resources/views/sections/navigation.blade.php`, `packages/theme-crypto-defi/resources/views/sections/proof.blade.php`, `packages/theme-crypto-defi/resources/views/sections/protocol-stats.blade.php`, `and 2 more`.
- Cache tags: `theme-crypto-defi`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-crypto-defi`.
- Commands: `capell:theme-crypto-defi-demo`.

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

1. Install the package: `composer require capell-app/theme-crypto-defi`.
2. Run the required setup: `php artisan capell:theme-crypto-defi-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/theme-crypto-defi/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
