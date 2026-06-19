# Theme Dense News Analysis

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme Dense News Analysis is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-dense-news-analysis` and extends these surfaces: frontend.

Dense News Analysis extends the default Capell frontend with a serious newsroom direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for top stories, live briefs, topic navigation, opinion, video, missed-it recaps, archives, and newsletter inserts.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-dense-news-analysis`
- Namespace: `Capell\ThemeStudio\DenseNewsAnalysis`
- Theme key: `dense-news-analysis`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for serious news publishers, analysis desks, opinion pages, video rows, live coverage, and reader briefings.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Dense News Analysis Homepage (frontend, optional).
- Dense News Analysis Landing page (frontend, optional).
- Dense News Analysis List page (frontend, optional).
- Dense News Analysis Search results (frontend, optional).
- Dense News Analysis Contact form (frontend, optional).

## Technical Shape

- Service providers: `Capell\ThemeStudio\DenseNewsAnalysis\DenseNewsAnalysisThemeServiceProvider`.
- Actions: `InstallDenseNewsAnalysisThemeDemoAction`.
- Command signatures: `capell:theme-dense-news-analysis-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\DenseNewsAnalysis\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\DenseNewsAnalysis\Health\ThemeDenseNewsAnalysisHealthCheck`.
- Blade views: `packages/theme-dense-news-analysis/resources/views/livewire/page/page.blade.php`, `packages/theme-dense-news-analysis/resources/views/page.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/topic-navigation.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/content-listing.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/cta.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/opinion-analysis.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/footer.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/hero.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/live-brief.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/video-row.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/navigation.blade.php`, `packages/theme-dense-news-analysis/resources/views/sections/newsletter.blade.php`, `and 3 more`.
- Cache tags: `theme-dense-news-analysis`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-dense-news-analysis`.
- Commands: `capell:theme-dense-news-analysis-demo`.

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

1. Install the package: `composer require capell-app/theme-dense-news-analysis`.
2. Run the required setup: `php artisan capell:theme-dense-news-analysis-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../../blog/README.md), [Form Builder](../../form-builder/README.md), [Newsletter](../../newsletter/README.md), [Shopify Commerce](../../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/theme-dense-news-analysis/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
