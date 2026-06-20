# Theme Experimental Directory

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Experimental Directory is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-experimental-directory` and extends these surfaces: frontend.

Experimental Directory extends the default Capell frontend with a creative industry directory direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for pale neutral pages, confident black text, tight grid rules, oversized project titles, compact metadata, status badges, large image-led feature blocks, latest submissions, winners, collections, profiles, resources, sponsor modules, score chips, and metadata reveals.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-experimental-directory`
- Namespace: `Capell\ThemeStudio\ExperimentalDirectory`
- Theme key: `experimental-directory`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A tight Capell theme for creative industry directories with featured-today modules, large project slabs, submissions, winners, collections, profiles, resources, sponsors, and metadata badges.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Experimental Directory Homepage (frontend, optional).
- Experimental Directory Landing page (frontend, optional).
- Experimental Directory Project archive (frontend, optional).
- Experimental Directory Search results (frontend, optional).
- Experimental Directory Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\ExperimentalDirectory\ExperimentalDirectoryThemeServiceProvider`.
- Actions: `InstallExperimentalDirectoryThemeDemoAction`.
- Command signatures: `capell:theme-experimental-directory-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\ExperimentalDirectory\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\ExperimentalDirectory\Health\ThemeExperimentalDirectoryHealthCheck`.
- Blade views: `packages/theme-experimental-directory/resources/views/livewire/page/page.blade.php`, `packages/theme-experimental-directory/resources/views/page.blade.php`, `packages/theme-experimental-directory/resources/views/sections/metadata-filters.blade.php`, `packages/theme-experimental-directory/resources/views/sections/content-listing.blade.php`, `packages/theme-experimental-directory/resources/views/sections/cta.blade.php`, `packages/theme-experimental-directory/resources/views/sections/latest-submissions.blade.php`, `packages/theme-experimental-directory/resources/views/sections/footer.blade.php`, `packages/theme-experimental-directory/resources/views/sections/hero.blade.php`, `packages/theme-experimental-directory/resources/views/sections/winners-collections.blade.php`, `packages/theme-experimental-directory/resources/views/sections/profiles-resources.blade.php`, `packages/theme-experimental-directory/resources/views/sections/navigation.blade.php`, `packages/theme-experimental-directory/resources/views/sections/newsletter.blade.php`, `and 3 more`.
- Cache tags: `theme-experimental-directory`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-experimental-directory`.
- Commands: `capell:theme-experimental-directory-demo`.

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

1. Install the package: `composer require capell-app/theme-experimental-directory`.
2. Run the required setup: `php artisan capell:theme-experimental-directory-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../../blog/README.md), [Form Builder](../../form-builder/README.md), [Newsletter](../../newsletter/README.md), [Shopify Commerce](../../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-experimental-directory/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
