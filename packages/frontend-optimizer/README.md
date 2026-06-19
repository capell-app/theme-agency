# Frontend Optimizer

<!-- prettier-ignore-start -->

## What This Plugin Adds

Frontend Optimizer is an **Available**, **Schema-owning** Capell package in the **Capell Foundation** product group. It ships as `capell-app/frontend-optimizer` and extends these surfaces: admin, console, frontend.

Frontend Optimizer speeds up your public Capell site by inlining the exact above-the-fold CSS each layout needs and deferring everything else, so visitors see styled content sooner and your Core Web Vitals improve. Unlike source-scanning tools, it generates critical CSS from your pages as a real browser renders them - Layout Builder content, theme markup, render hooks, and responsive styles all included - across mobile and desktop viewports. Generation runs on your queue, never in the public response, and falls back safely to standard stylesheet delivery until critical CSS exists. Per-layout render profiles mean equivalent pages reuse one optimized result, and page types you don't want to optimize can opt out with a single toggle.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: foundation
- Composer package: `capell-app/frontend-optimizer`
- Namespace: `Capell\FrontendOptimizer`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, and Filament classes instead of pushing this behaviour into core or application code.

**For teams:** Faster first paint for public Capell pages: automatic above-the-fold critical CSS extracted from your real rendered pages, plus smart deferring of non-critical CSS and JavaScript.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Frontend Optimizer profile asset output (frontend, required).
- Frontend Optimizer critical CSS output (frontend, required).

## Technical Shape

- Service providers: `Capell\FrontendOptimizer\Providers\FrontendOptimizerServiceProvider`.
- Config files: `packages/frontend-optimizer/config/capell-frontend-optimizer.php`.
- Migrations: `packages/frontend-optimizer/database/migrations/2026_05_10_190851_01_create_frontend_optimizer_tables.php`.
- Settings migrations: `packages/frontend-optimizer/database/settings/2026_05_24_000001_add_frontend_optimizer_settings.php`.
- Settings classes: `FrontendOptimizerSettings`.
- Models: `FrontendOptimizationRun`, `FrontendRenderProfile`.
- Filament classes: `FrontendOptimizerPageTypeConfigurator`, `FrontendOptimizerSettingsSchema`.
- Listeners: `CaptureCriticalCssPageTypeOptOut`.
- Actions: `BuildOptimizedImageMarkupAction`, `GenerateCriticalCssAction`, `InvalidateGeneratedCriticalCssCacheAction`, `PersistRenderProfileAction`, `PrepareRenderProfileAction`, `PruneRenderProfilesAction`, `RenderProfileAssetsAction`, `ResolveOptimizationScopeAction`, `ResolveRenderProfileAction`, `StoreRenderProfileManifestAction`.
- Data objects: `FrontendAssetDefinitionData`, `FrontendResourceHintData`, `PruneRenderProfilesResultData`, `RenderProfileData`.
- Jobs: `GenerateCriticalCssJob`.
- Command signatures: `capell:frontend-optimizer:prune-profiles`.
- Console command classes: `PruneRenderProfilesCommand`.
- Manifest contributions: `configurator: Capell\FrontendOptimizer\Manifest\FrontendOptimizerConfiguratorContribution`, `console-command: Capell\FrontendOptimizer\Manifest\FrontendOptimizerConsoleCommandsContribution`, `health-check: Capell\FrontendOptimizer\Manifest\FrontendOptimizerHealthContribution`, `model: Capell\FrontendOptimizer\Manifest\FrontendOptimizerModelsContribution`, `setting: Capell\FrontendOptimizer\Manifest\FrontendOptimizerSettingsContribution`.
- Health checks: `Capell\FrontendOptimizer\Health\FrontendOptimizerHealthCheck`.
- Cache tags: `frontend-optimizer`.

## Data Model

- Models: `FrontendOptimizationRun`, `FrontendRenderProfile`.
- Migration files: `2026_05_10_190851_01_create_frontend_optimizer_tables.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: `Capell\FrontendOptimizer\Settings\FrontendOptimizerSettings`.
- Queues or schedules: review package jobs or schedules before install.
- Cache tags: `frontend-optimizer`.
- Commands: `capell:frontend-optimizer:prune-profiles`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/frontend-optimizer`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Frontend Optimizer appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Media Library](../media-library/README.md).
- Focused tests: `vendor/bin/pest packages/frontend-optimizer/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
