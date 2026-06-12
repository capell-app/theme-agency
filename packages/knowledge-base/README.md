# Knowledge Base

Knowledge Base turns a Capell install into a structured help centre with collections, versioned articles, public `/docs` routes, reader feedback, search payloads, and AI-readable documentation output.

## At A Glance

| Field            | Value                                                                                                                |
| ---------------- | -------------------------------------------------------------------------------------------------------------------- |
| Composer package | `capell-app/knowledge-base`                                                                                          |
| Namespace        | `Capell\KnowledgeBase`                                                                                               |
| Product group    | Capell Content, premium content-product bundle                                                                       |
| Surfaces         | Admin, frontend                                                                                                      |
| Providers        | `Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider`, `Capell\KnowledgeBase\Providers\AdminServiceProvider` |
| Requires         | `capell-app/admin`, `capell-app/core`, `capell-app/frontend`                                                         |
| Supports         | Search, SEO Suite, Site Discovery, Theme Knowledge                                                                   |
| Public routes    | `/docs`, article pages, feedback, AI-readable output                                                                 |
| Demo command     | `capell:knowledge-base-demo` in a host Capell app                                                                    |

## Why It Helps Your Capell Workflow

Owners get a searchable support/docs surface inside Capell instead of a detached documentation tool. Editors can organise collections, draft and publish article versions, relate articles, and review feedback from the admin panel.

Readers browse fast public docs and submit helpful/not-helpful feedback. Developers get public-safe DTOs, sanitised article HTML, search document builders, and AI-readable output Actions that keep authoring internals out of cached frontend responses.

## What It Adds

- Filament resources for knowledge base collections and articles.
- Versioned article publishing through `CreateKnowledgeBaseArticleVersionAction` and `PublishKnowledgeBaseArticleVersionAction`.
- Public navigation and article payloads through `BuildPublicKnowledgeBaseNavigationAction` and `BuildPublicKnowledgeBaseArticleDataAction`.
- Sanitised public article HTML through `SanitizeKnowledgeBaseArticleHtmlAction`.
- Reader feedback through `RecordKnowledgeBaseArticleFeedbackAction`, with throttling from `capell-knowledge-base.feedback.throttle`.
- Search documents, schema output, site discovery URLs, and `/docs/llms.txt` AI-readable output.

## Boundaries

Knowledge Base owns help-centre collections, articles, versions, related links, feedback, and docs-specific public payloads. Themes render hydrated DTOs and should not query package models directly in public Blade.

Public docs output must not expose admin URLs, package internals, article model IDs, authoring state, or editor-only metadata. Article body output is sanitised at the package boundary; preserve that Action when adding new public renderers.

## Runtime Surface

- Providers: `src/Providers/`
- Controllers: `src/Http/Controllers/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Models: `src/Models/`
- Public URL support: `src/Support/`
- Demo command: `src/Console/Commands/DemoCommand.php`
- Tests: `packages/knowledge-base/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Improvement plan](docs/improvement-plan.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/knowledge-base/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                     | Likely cause                                                         | Check                                                                                 | Fix                                                                                                    |
| --------------------------- | -------------------------------------------------------------------- | ------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| `/docs` has no articles     | Articles are drafts, unpublished, or missing collection routing      | Check article status/version records in the admin resource                            | Publish a version through the package Action/admin flow                                                |
| Feedback is rejected        | Feedback throttle or duplicate visitor/article/version row is active | Check `capell-knowledge-base.feedback.throttle` and `knowledge_base_article_feedback` | Wait for the throttle window or update the existing feedback row through the Action                    |
| AI-readable output is stale | Public URL/search payloads were not rebuilt after article changes    | Check Site Discovery/Search integrations and article publish timestamps               | Re-run the relevant package sync in the host app and verify `BuildAiReadableKnowledgeBaseOutputAction` |
