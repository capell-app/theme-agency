# Knowledge Base Package Implementation Plan

**Goal:** Build a first-party Capell knowledge base package with versioned docs content, public docs navigation, feedback capture, related articles, search weighting, and AI-readable output.

**Foundation patch:** Add package metadata, migrations, models, DTOs, actions, and tests for public-safe content output. Avoid public Blade and routes until the content contracts are proven.

## Slice 1: Foundation Data And Actions

- Create `packages/knowledge-base` as `capell-app/knowledge-base`.
- Persist collections, articles, article versions, article feedback, and related article links.
- Keep article author/editor morphs on version records only; public DTOs must not expose IDs, morph classes, permissions, package names, signed URLs, or admin labels.
- Provide actions for collection creation, article creation, version publishing, feedback capture, related article links, public navigation, search documents, and AI-readable docs output.
- Verify with package-local Pest tests.

## Slice 2: Admin Authoring

- Add Filament resources for collections and articles.
- Use translated labels through methods, backed enums for status/relation types, and Actions for writes.
- Add publish/version controls without putting business logic in resource pages.
- Test resources and policies narrowly.

## Slice 3: Public Docs Surface

- Add route/controller/view layer for collection and article pages.
- Build view models in Actions before Blade rendering.
- Add tests proving anonymous and non-admin output contains no authoring markers, model IDs, field paths, signed editor URLs, package names, permissions, or lazy queries.

## Slice 4: Search And Discovery

- Integrate with Search/Site Discovery through optional tagged contributors.
- Expose weighted public search documents and public URLs.
- Add cache invalidation hooks for published articles and version changes.

## Slice 5: AI-Readable Output

- Add `llms.txt` or package-specific docs export routes once public URL contracts are stable.
- Reuse the AI-readable DTO/action from the foundation slice.
- Add generated-output coverage tests beside Site Discovery/SEO Suite integrations.
