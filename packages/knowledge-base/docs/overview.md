# Knowledge Base Overview

Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Bundle: **content-product** · Contexts: **admin, frontend** · Product group: **Capell Content**

Knowledge Base turns a Capell install into a structured help centre with collections, versioned articles, feedback, related links, public navigation data, search documents, and AI-readable output.

## Non-Technical Overview

Use Knowledge Base when a site needs first-party docs instead of a separate help-centre product. Authors manage collections and articles in Capell admin; readers browse `/docs`, follow related articles, and submit helpful/not-helpful feedback.

The package can also emit public-safe article payloads for Search, Site Discovery, SEO Suite, Theme Knowledge, and agent-readable docs surfaces.

## What This Package Adds

- Filament resources for collections and articles.
- Public `/docs` article and collection routes.
- Public `/docs/llms.txt` output for AI-readable docs.
- Versioned article publishing and related-article links.
- Reader feedback with throttling and repeat-feedback updates.
- Search document building and public navigation payload actions.
- A demo command: `capell:knowledge-base-demo`.

## Developer Deep Dive

Use package Actions and DTOs to keep cached frontend output public-safe:

| Need                         | Entry point                                 |
| ---------------------------- | ------------------------------------------- |
| Build public article data    | `BuildPublicKnowledgeBaseArticleDataAction` |
| Build public docs navigation | `BuildPublicKnowledgeBaseNavigationAction`  |
| Record article feedback      | `RecordKnowledgeBaseArticleFeedbackAction`  |
| Publish article version      | `PublishKnowledgeBaseArticleVersionAction`  |
| Build search documents       | `BuildKnowledgeBaseSearchDocumentsAction`   |
| Build AI-readable output     | `BuildAiReadableKnowledgeBaseOutputAction`  |

Public Blade should receive hydrated data from controllers, Actions, or view components. It should not query models directly, lazy-load relationships, or expose authoring metadata.

## Boundaries

- Knowledge Base owns docs content, versions, feedback, related article links, public docs routes, and docs-specific payloads.
- Search owns search indexing/runtime; Knowledge Base only contributes public-safe documents.
- Site Discovery owns discovery/sitemap workflows; Knowledge Base contributes published docs URLs when installed.
- Theme Knowledge owns visual presentation. Knowledge Base keeps article and navigation data theme-agnostic.

## Screenshot Plan

`docs/screenshots.json` covers admin article screens and public docs route captures. Marketplace media currently promotes the admin article index and article edit/version-history captures.

## Verification

```bash
vendor/bin/pest packages/knowledge-base/tests --configuration=phpunit.xml
```
