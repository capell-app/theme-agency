# AI Creator

<!-- prettier-ignore-start -->

## What This Plugin Adds

AI Creator is an **Available**, **Schema-owning** Capell package in the **Capell Commercial** product group. It ships as `capell-app/ai-creator` and extends these surfaces: admin.

Reviewed AI-assisted creation sessions for Capell content, pages, layouts, and extension recommendations.

After install, the package contributes admin-facing extension points. Docs gap: no concrete Filament resource or page was detected.

Status details:

- Status: Available
- Tier: premium
- Bundle: commercial
- Composer package: `capell-app/ai-creator`
- Namespace: `Capell\AiCreator`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, and models instead of pushing this behaviour into core or application code.

**For teams:** AI Creator helps editors create Capell content, pages, and plans while recommending the extensions that make each result stronger.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- AI Creator session preview (admin, required).
- AI Creator Agent Bridge confirmation (admin, required).

## Technical Shape

- Service providers: `Capell\AiCreator\Providers\AiCreatorServiceProvider`.
- Migrations: `packages/ai-creator/database/migrations/2026_06_22_000001_create_capell_ai_creator_sessions_table.php`.
- Models: `AiCreatorSession`.
- Actions: `ApplyAiCreatorSessionAction`, `BuildAiCreatorSessionPreviewAction`, `PreviewAiCreatorSessionAction`, `RecommendAiCreatorPackagesAction`, `StartAiCreatorSessionAction`.
- Data objects: `AiCreatorPackageRecommendationData`, `AiCreatorPreviewData`, `AiCreatorStartSessionData`.
- Manifest contributions: `admin-page: Capell\AiCreator\Manifest\AiCreatorAdminPageContribution`, `agent-capability: Capell\AiCreator\Manifest\AiCreatorAgentBridgeCapabilitiesContribution`, `health-check: Capell\AiCreator\Health\AiCreatorHealthCheck`, `model: Capell\AiCreator\Manifest\AiCreatorModelsContribution`.
- Health checks: `Capell\AiCreator\Health\AiCreatorHealthCheck`.

## Data Model

- Required tables: `capell_ai_creator_sessions`.
- Models: `AiCreatorSession`.
- Migration files: `2026_06_22_000001_create_capell_ai_creator_sessions_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: none declared.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |

## Quick Start

1. Install the package: `composer require capell-app/ai-creator`.
2. Run the required setup: `php artisan migrate`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Agent Bridge](../agent-bridge/README.md), [Ai Orchestrator](../ai-orchestrator/README.md), [Block Library](../block-library/README.md), [Blog](../blog/README.md), [Campaign Studio](../campaign-studio/README.md), [Content Sections](../content-sections/README.md), [Events](../events/README.md), [Form Builder](../form-builder/README.md), [Foundation Theme](../theme-foundation/README.md), [Frontend Authoring](../frontend-authoring/README.md), [Frontend Optimizer](../frontend-optimizer/README.md), [Ga4 Reports](../ga4-reports/README.md), [Html Cache](../html-cache/README.md), [Insights](../insights/README.md), [Layout Builder](../layout-builder/README.md), [Media Ai](../media-ai/README.md), [Media Library](../media-library/README.md), [Navigation](../navigation/README.md), [Newsletter](../newsletter/README.md), [Publishing Studio](../publishing-studio/README.md), [Search](../search/README.md), [Seo Suite](../seo-suite/README.md), [Site Monitor](../site-monitor/README.md), [Structured Content Library](../structured-content-library/README.md).
- Focused tests: `vendor/bin/pest packages/ai-creator/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
