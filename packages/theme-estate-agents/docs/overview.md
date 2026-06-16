# Theme Estate Agents

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Estate Agents is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-estate-agents` and extends these surfaces: frontend, console.

Theme Estate Agents gives property teams a premium agency website built around search intent, featured listings, vendor valuations, local-area confidence, agent credibility, and viewing requests. It extends the built-in default frontend theme while keeping property copy, area guidance, and enquiry content portable in Capell data. The theme owns the property brochure rhythm: search bands, listing ledgers, valuation CTAs, neighbourhood guides, agent proof, and viewing forms. It pairs with Search for public listing discovery, Address for location context, and Form Builder for valuation or viewing capture without introducing theme-owned property records.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-estate-agents`
- Namespace: `Capell\ThemeStudio\EstateAgents`
- Theme key: `estate-agents`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium property theme for estate agencies, lettings teams, valuations, local guides, and viewing-led enquiry journeys.

runtime inheritance uses `extends: default`, so the theme keeps Foundation Theme behaviour while replacing property-specific public presentation. It requires `capell-app/foundation-theme` and `capell-app/frontend`.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Estate agency homepage (frontend, optional).
- Property search (frontend, optional).
- Vendor valuation (frontend, optional).
- Local guide (frontend, optional).
- Viewing request (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\EstateAgents\EstateAgentsThemeServiceProvider`.
- Actions: `InstallEstateAgentsThemeDemoAction`.
- Command signatures: `capell:theme-estate-agents-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\EstateAgents\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\EstateAgents\Health\ThemeEstateAgentsHealthCheck`.
- Blade views: `packages/theme-estate-agents/resources/views/page.blade.php`, `packages/theme-estate-agents/resources/views/sections/agent-team.blade.php`, `packages/theme-estate-agents/resources/views/sections/content-listing.blade.php`, `packages/theme-estate-agents/resources/views/sections/cta.blade.php`, `packages/theme-estate-agents/resources/views/sections/featured-properties.blade.php`, `packages/theme-estate-agents/resources/views/sections/features.blade.php`, `packages/theme-estate-agents/resources/views/sections/footer.blade.php`, `packages/theme-estate-agents/resources/views/sections/hero.blade.php`, `packages/theme-estate-agents/resources/views/sections/local-guide.blade.php`, `packages/theme-estate-agents/resources/views/sections/market-proof.blade.php`, `packages/theme-estate-agents/resources/views/sections/navigation.blade.php`, `packages/theme-estate-agents/resources/views/sections/proof.blade.php`, `and 3 more`.
- Cache tags: `theme-estate-agents`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-estate-agents`.
- Commands: `capell:theme-estate-agents-demo`.

## Search And Form Actions

Search, valuation, and viewing sections render public forms only when hydrated render data supplies a safe `form_action`. Search and Form Builder adapters should pass root-relative or `http(s)` public URLs. Missing or unsafe actions render non-submitting setup panels, so public output never posts to `#`, signed admin URLs, Livewire endpoints, or other authoring surfaces.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/theme-estate-agents`.
2. Run the required setup: `php artisan capell:theme-estate-agents-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Foundation Theme](../../foundation-theme/README.md), [Address](../../address/README.md), [Blog](../../blog/README.md), [Form Builder](../../form-builder/README.md), [Search](../../search/README.md), [Seo Suite](../../seo-suite/README.md).
- Focused tests: `vendor/bin/pest packages/theme-estate-agents/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
