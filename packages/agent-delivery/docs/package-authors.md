# Package Author Guide

Agent Delivery lets packages add public-safe structured output without changing public Blade views or themes.

## Contributor Contracts

Use the narrowest contract that matches the data you own:

- `AgentDeliveryMetadataContributor` for public metadata, provenance, author, reviewer, or content-type signals.
- `AgentDeliveryChunkContributor` for stable semantic chunks from package-owned page types or long-form sections.
- `AgentDeliveryReferenceContributor` for public source links and citations.
- `AgentDeliveryRelatedUrlContributor` for related public URLs.
- `AgentDeliveryContributor` remains available for legacy aggregate metadata and chunks.

Tag implementations in the service container with the contract `TAG` constant.

Specialised metadata contributors and aggregate contributors are merged in explicit registration order; when keys collide, the later registered contributor wins. Keep keys namespaced enough to avoid accidental overwrites.

Contributor failures are isolated per contribution surface. If a contributor throws while building metadata, chunks, references, or related URLs, Agent Delivery logs a warning with the contributor type and surface, skips that failed contribution, and continues returning safe output from other contributors and the core page builder.

## Public Safety Rules

Contributors must only return content already safe for anonymous public visitors.

Do not return:

- Admin URLs, preview URLs, signed URLs, permissions, or role data.
- Drafts, unpublished translations, scheduled content not yet public, or private page state.
- Model IDs, package internals, field paths, editor selectors, prompt text, or AI provider metadata.
- Hidden authoring markers or data that would make public cached HTML unsafe.

Agent Delivery recursively strips common unsafe metadata keys and values before public output, but contributors should still treat public safety as their responsibility rather than relying on sanitisation.

## Chunk Stability

Chunk IDs should be stable across normal edits when the section identity has not changed. Prefer slugs based on stable section keys or public headings over database IDs.

Each chunk should include:

- A stable `id`.
- A public `heading`.
- A canonical `sourceUrl`, including an anchor when useful.
- Plain text or Markdown-safe `body`.
- `dependsOn` entries such as `url:https://example.com/page` for cache invalidation.

Keep contributor chunks close to `capell-agent-delivery.public_pages.chunk_target_words`. Oversized chunks are still returned, but the chunks endpoint reports `chunk_body_exceeds_target_words` in `meta.budget.warnings` so operators can identify package contributors that need tighter splitting.
