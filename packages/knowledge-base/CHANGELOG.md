# Changelog

All notable changes to `capell-app/knowledge-base` will be documented in this file.

## Unreleased

### 2026-06-03

- Fixed a stored-XSS risk in public article rendering: the article body is emitted via Blade `{!! !!}` in `resources/views/article.blade.php`, but the author-authored HTML was previously rendered raw. Article HTML is now sanitised at the public render-data boundary in `BuildPublicKnowledgeBaseArticleDataAction` via the new `SanitizeKnowledgeBaseArticleHtmlAction`, which strips `<script>`, `<iframe>`, and inline event handlers while preserving safe rich-text markup (headings, lists, links, images, tables, code).
- Implemented real `KnowledgeBaseHealthCheck` diagnostics: the previously stubbed critical check now asserts that the Knowledge Base storage tables exist, the Eloquent models are discoverable, and the public output Actions (navigation, article, AI-readable) are autoloadable. Added `runDiagnostics()` and `passed()` following the shared Capell health-check convention.
- Rewrote the marketplace summary and package descriptions (`capell.json` and `composer.json`) to describe shipped behaviour (public doc collections, reader feedback, a theme-agnostic `/docs` site, typed public and AI-readable payloads) rather than overclaiming version-UI and full-text search that are not yet reachable through any shipped surface.
- Added `symfony/html-sanitizer` to the package dependencies to back the public article HTML sanitiser.
- Added test coverage for the article HTML sanitisation and the health-check diagnostics (pass and fail).
