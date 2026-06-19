# Theme Travel & Tourism

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Travel & Tourism is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-travel-tourism` and extends these surfaces: frontend.

Travel & Tourism extends the default Capell frontend with a travel & tourism visual direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for tour operators, travel agencies.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-travel-tourism`
- Namespace: `Capell\ThemeStudio\TravelTourism`
- Theme key: `travel-tourism`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for tour operators, travel agencies.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Travel Tourism Homepage (frontend, required).
- Travel Tourism Directory (frontend, required).
- Travel Tourism Detail (frontend, required).
- Travel Tourism Contact (frontend, required).
- Travel Tourism Empty State (frontend, required).
- Travel Tourism 404 State (frontend, required).
- Travel Tourism Conversion CTA (frontend, required).
- Travel Tourism Destinations (frontend, required).
- Travel Tourism Itineraries (frontend, required).
- Travel Tourism Travel Enquiry (frontend, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\TravelTourism\TravelTourismThemeServiceProvider`.
- Actions: `InstallTravelTourismThemeDemoAction`.
- Command signatures: `capell:theme-travel-tourism-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\TravelTourism\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\TravelTourism\Health\ThemeTravelTourismHealthCheck`.
- Blade views: `packages/theme-travel-tourism/resources/views/livewire/page/page.blade.php`, `packages/theme-travel-tourism/resources/views/page.blade.php`, `packages/theme-travel-tourism/resources/views/sections/content-listing.blade.php`, `packages/theme-travel-tourism/resources/views/sections/cta.blade.php`, `packages/theme-travel-tourism/resources/views/sections/destination-grid.blade.php`, `packages/theme-travel-tourism/resources/views/sections/enquiry-panel.blade.php`, `packages/theme-travel-tourism/resources/views/sections/features.blade.php`, `packages/theme-travel-tourism/resources/views/sections/footer.blade.php`, `packages/theme-travel-tourism/resources/views/sections/guide-profiles.blade.php`, `packages/theme-travel-tourism/resources/views/sections/hero.blade.php`, `packages/theme-travel-tourism/resources/views/sections/itinerary-builder.blade.php`, `packages/theme-travel-tourism/resources/views/sections/navigation.blade.php`, `and 2 more`.
- Cache tags: `theme-travel-tourism`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-travel-tourism`.
- Commands: `capell:theme-travel-tourism-demo`.

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

1. Install the package: `composer require capell-app/theme-travel-tourism`.
2. Run the required setup: `php artisan capell:theme-travel-tourism-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/theme-travel-tourism/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
