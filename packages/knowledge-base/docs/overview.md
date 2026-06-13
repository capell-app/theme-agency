# Knowledge Base

<!-- prettier-ignore-start -->

## What This Plugin Adds

Knowledge Base is an **Available**, **Schema-owning** Capell package in the **Capell Content** product group. It ships as `capell-app/knowledge-base` and extends these surfaces: frontend, admin.

Knowledge Base turns your Capell install into a structured help centre. Authors group articles into public collections, and readers browse a fast, cacheable /docs site and tell you whether each article helped. Public navigation, article, and AI-readable payloads are emitted as typed data objects, so any theme can render them and assistants can consume them without leaking authoring internals. Article HTML is sanitised at the public render boundary, and reader feedback is captured with hashed visitor identifiers.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: content-product
- Composer package: `capell-app/knowledge-base`
- Namespace: `Capell\KnowledgeBase`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A self-hosted help centre for Capell: organise docs into public collections, capture reader feedback, and serve a fast, theme-agnostic /docs site backed by typed public and AI-readable payloads.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Knowledge Base articles admin index (admin, required).
- Knowledge Base article edit and version history (admin, required).
- Knowledge Base public docs index (frontend, required).
- Knowledge Base public article (frontend, required).

## Technical Shape

- Service providers: `Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider`, `Capell\KnowledgeBase\Providers\AdminServiceProvider`.
- Config files: `packages/knowledge-base/config/capell-knowledge-base.php`.
- Migrations: `packages/knowledge-base/database/migrations/2026_05_31_000001_create_knowledge_base_tables.php`.
- Models: `KnowledgeBaseArticle`, `KnowledgeBaseArticleFeedback`, `KnowledgeBaseArticleVersion`, `KnowledgeBaseCollection`, `KnowledgeBaseRelatedArticle`.
- Filament classes: `KnowledgeBaseArticleResource`, `CreateKnowledgeBaseArticle`, `EditKnowledgeBaseArticle`, `ListKnowledgeBaseArticles`, `ArticleVersionsRelationManager`, `RelatedArticlesRelationManager`, `KnowledgeBaseCollectionResource`, `CreateKnowledgeBaseCollection`, `EditKnowledgeBaseCollection`, `ListKnowledgeBaseCollections`.
- Route files: `packages/knowledge-base/routes/web.php`.
- Policies: `AbstractKnowledgeBaseResourcePolicy`, `KnowledgeBaseArticlePolicy`, `KnowledgeBaseCollectionPolicy`.
- Actions: `BuildAiReadableKnowledgeBaseOutputAction`, `BuildKnowledgeBaseArticleSchemaAction`, `BuildKnowledgeBaseSearchDocumentsAction`, `BuildPublicKnowledgeBaseArticleDataAction`, `BuildPublicKnowledgeBaseNavigationAction`, `CreateKnowledgeBaseArticleAction`, `CreateKnowledgeBaseArticleVersionAction`, `CreateKnowledgeBaseCollectionAction`, `PublishKnowledgeBaseArticleVersionAction`, `RecordKnowledgeBaseArticleFeedbackAction`, `RelateKnowledgeBaseArticlesAction`, `SanitizeKnowledgeBaseArticleHtmlAction`, `and 2 more`.
- Data objects: `AiReadableKnowledgeBaseArticleData`, `CreateKnowledgeBaseArticleData`, `CreateKnowledgeBaseArticleVersionData`, `CreateKnowledgeBaseCollectionData`, `KnowledgeBaseSearchDocumentData`, `PublicKnowledgeBaseArticleData`, `PublicKnowledgeBaseNavigationItemData`, `RecordKnowledgeBaseArticleFeedbackData`, `UpdateKnowledgeBaseArticleData`, `UpdateKnowledgeBaseCollectionData`.
- Command signatures: `capell:knowledge-base-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-resource: Capell\KnowledgeBase\Manifest\KnowledgeBaseArticleResourceContribution`, `admin-resource: Capell\KnowledgeBase\Manifest\KnowledgeBaseCollectionResourceContribution`, `model: Capell\KnowledgeBase\Manifest\KnowledgeBaseModelsContribution`, `route: Capell\KnowledgeBase\Manifest\KnowledgeBaseFrontendRoutesContribution`.
- Health checks: `Capell\KnowledgeBase\Health\KnowledgeBaseHealthCheck`.
- Blade views: `packages/knowledge-base/resources/views/article.blade.php`, `packages/knowledge-base/resources/views/index.blade.php`, `packages/knowledge-base/resources/views/partials/collection-navigation.blade.php`.
- Cache tags: `knowledge-base`.

## Data Model

- Required tables: `knowledge_base_collections`, `knowledge_base_articles`, `knowledge_base_article_versions`, `knowledge_base_article_feedback`, `knowledge_base_related_articles`.
- Models: `KnowledgeBaseArticle`, `KnowledgeBaseArticleFeedback`, `KnowledgeBaseArticleVersion`, `KnowledgeBaseCollection`, `KnowledgeBaseRelatedArticle`.
- Migration files: `2026_05_31_000001_create_knowledge_base_tables.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `knowledge-base`.
- Commands: `capell:knowledge-base-demo`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/knowledge-base`.
2. Run the required setup: `php artisan capell:knowledge-base-demo`.
3. Open the related Capell admin surface and verify Knowledge Base appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Search](../../search/README.md), [Seo Suite](../../seo-suite/README.md), [Site Discovery](../../site-discovery/README.md), [Theme Knowledge](../../theme-knowledge/README.md).
- Focused tests: `vendor/bin/pest packages/knowledge-base/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
