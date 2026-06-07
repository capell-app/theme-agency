# Changelog

All notable changes to `capell-app/knowledge-base` will be documented in this file.

## Unreleased

- Added `capell:knowledge-base-demo` to seed public collections, articles, a related link, and sample feedback for demos and screenshot fixtures.
- Registered Knowledge Base as an optional Capell Search source with public-safe article search payloads.
- Registered published Knowledge Base articles as optional Site Discovery public URLs and added public-safe Article schema data output.
- Added `/docs/llms.txt` AI-readable public output.
- Added Article edit related-article management and feedback aggregate displays.
- Corrected cache-safety metadata to the current global, single-locale content scope.
- Added configurable throttle middleware to the anonymous article feedback endpoint.
- Repeat feedback from the same visitor for the same article version now updates the existing feedback row instead of creating duplicate votes.
- Updated package README text to describe the shipped admin resources, public `/docs` routes, and feedback safety behavior.

### 2026-06-03

- Fixed a stored-XSS risk in public article rendering: the article body is emitted via Blade `{!! !!}` in `resources/views/article.blade.php`, but the author-authored HTML was previously rendered raw. Article HTML is now sanitised at the public render-data boundary in `BuildPublicKnowledgeBaseArticleDataAction` via the new `SanitizeKnowledgeBaseArticleHtmlAction`, which strips `<script>`, `<iframe>`, and inline event handlers while preserving safe rich-text markup (headings, lists, links, images, tables, code).
- Implemented real `KnowledgeBaseHealthCheck` diagnostics: the previously stubbed critical check now asserts that the Knowledge Base storage tables exist, the Eloquent models are discoverable, and the public output Actions (navigation, article, AI-readable) are autoloadable. Added `runDiagnostics()` and `passed()` following the shared Capell health-check convention.
- Rewrote the marketplace summary and package descriptions (`capell.json` and `composer.json`) to describe shipped behaviour (public doc collections, reader feedback, a theme-agnostic `/docs` site, typed public and AI-readable payloads) rather than overclaiming version-UI and full-text search that are not yet reachable through any shipped surface.
- Added `symfony/html-sanitizer` to the package dependencies to back the public article HTML sanitiser.
- Added test coverage for the article HTML sanitisation and the health-check diagnostics (pass and fail).
