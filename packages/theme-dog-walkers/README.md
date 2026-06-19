# Theme Dog Walkers

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Dog Walkers is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-dog-walkers` and extends these surfaces: frontend, console.

Warm, trust-led pet care theme for independent dog walkers, sitters, and small neighbourhood teams.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-dog-walkers`
- Namespace: `Capell\ThemeStudio\DogWalkers`
- Theme key: `dog-walkers`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A trust-led Capell theme for dog walkers and pet-care teams, built around walk enquiries, service-area confidence, safety proof, and warm neighbourhood presentation.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Theme admin list showing Dog Walkers (admin, required).
- Frontend page rendered with Dog Walkers theme (frontend, required).
- Dog walking homepage (frontend, required).
- Walk options (frontend, required).
- Service areas (frontend, required).
- Enquiry form (frontend, required).
- Route board and visit proof (frontend, required).
- Service resources (frontend, required).
- Signed admin preview route (admin, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\DogWalkers\DogWalkersThemeServiceProvider`.
- Actions: `InstallDogWalkersThemeDemoAction`.
- Command signatures: `capell:theme-dog-walkers-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\DogWalkers\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\DogWalkers\Health\ThemeDogWalkersHealthCheck`.
- Blade views: `packages/theme-dog-walkers/resources/views/page.blade.php`, `packages/theme-dog-walkers/resources/views/sections/contact.blade.php`, `packages/theme-dog-walkers/resources/views/sections/content-listing.blade.php`, `packages/theme-dog-walkers/resources/views/sections/cta.blade.php`, `packages/theme-dog-walkers/resources/views/sections/enquiry-form.blade.php`, `packages/theme-dog-walkers/resources/views/sections/faq.blade.php`, `packages/theme-dog-walkers/resources/views/sections/features.blade.php`, `packages/theme-dog-walkers/resources/views/sections/footer.blade.php`, `packages/theme-dog-walkers/resources/views/sections/hero.blade.php`, `packages/theme-dog-walkers/resources/views/sections/meet-the-walkers.blade.php`, `packages/theme-dog-walkers/resources/views/sections/navigation.blade.php`, `packages/theme-dog-walkers/resources/views/sections/opening-hours.blade.php`, `and 8 more`.
- Cache tags: `theme-dog-walkers`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-dog-walkers`.
- Commands: `capell:theme-dog-walkers-demo`.

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

1. Install the package: `composer require capell-app/theme-dog-walkers`.
2. Run the required setup: `php artisan capell:theme-dog-walkers-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Address](../address/README.md), [Blog](../blog/README.md), [Bookings](../bookings/README.md), [Events](../events/README.md), [Frontend Authoring](../frontend-authoring/README.md), [Form Builder](../form-builder/README.md), [Layout Builder](../layout-builder/README.md), [Publishing Studio](../publishing-studio/README.md), [Seo Suite](../seo-suite/README.md).
- Focused tests: `vendor/bin/pest packages/theme-dog-walkers/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
