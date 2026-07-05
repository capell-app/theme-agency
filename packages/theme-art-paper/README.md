# Theme Art Paper

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Art Paper is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-art-paper` and extends these surfaces: frontend.

Art Paper puts photography-led features on a gallery-white page - editor picks, trend lists, and product credits. Two presets: cool photographic gloss, or the warm illustrated "Warm Ink" voice.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-art-paper`
- Namespace: `Capell\ThemeStudio\ArtPaper`
- Theme key: `art-paper`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for design and architecture magazines - photography-led features on a gallery-white page, with a warm illustrated preset for creative-culture voices.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Art Paper Homepage (frontend, optional).
- Art Paper Homepage - Tablet (frontend, optional).
- Art Paper Homepage - Mobile (frontend, optional).
- Art Paper Landing page (frontend, optional).
- Art Paper Landing page - Tablet (frontend, optional).
- Art Paper Landing page - Mobile (frontend, optional).
- Art Paper List page (frontend, optional).
- Art Paper List page - Tablet (frontend, optional).
- Art Paper List page - Mobile (frontend, optional).
- Art Paper Search results (frontend, optional).
- Art Paper Search results - Tablet (frontend, optional).
- Art Paper Search results - Mobile (frontend, optional).
- Art Paper Contact form (frontend, optional).
- Art Paper Contact form - Tablet (frontend, optional).
- Art Paper Contact form - Mobile (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\ArtPaper\ArtPaperThemeServiceProvider`.
- Actions: `InstallArtPaperThemeDemoAction`.
- Command signatures: `capell:theme-art-paper-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\ArtPaper\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\ArtPaper\Health\ThemeArtPaperHealthCheck`.
- Blade views: `packages/theme-art-paper/resources/views/livewire/page/page.blade.php`, `packages/theme-art-paper/resources/views/page.blade.php`, `packages/theme-art-paper/resources/views/sections/content-listing.blade.php`, `packages/theme-art-paper/resources/views/sections/cta.blade.php`, `packages/theme-art-paper/resources/views/sections/editor-picks.blade.php`, `packages/theme-art-paper/resources/views/sections/footer.blade.php`, `packages/theme-art-paper/resources/views/sections/gallery-feature.blade.php`, `packages/theme-art-paper/resources/views/sections/hero.blade.php`, `packages/theme-art-paper/resources/views/sections/lead-story.blade.php`, `packages/theme-art-paper/resources/views/sections/navigation.blade.php`, `packages/theme-art-paper/resources/views/sections/newsletter.blade.php`, `packages/theme-art-paper/resources/views/sections/product-credits.blade.php`, `and 3 more`.
- Cache tags: `theme-art-paper`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-art-paper`.
- Commands: `capell:theme-art-paper-demo`.

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

1. Install the package: `composer require capell-app/theme-art-paper`.
2. Run the required setup: `php artisan capell:theme-art-paper-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../blog/README.md), [Form Builder](../form-builder/README.md), [Newsletter](../newsletter/README.md), [Shopify Commerce](../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-art-paper/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
