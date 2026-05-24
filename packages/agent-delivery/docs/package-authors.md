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

## Public Safety Rules

Contributors must only return content already safe for anonymous public visitors.

Do not return:

- Admin URLs, preview URLs, signed URLs, permissions, or role data.
- Drafts, unpublished translations, scheduled content not yet public, or private page state.
- Model IDs, package internals, field paths, editor selectors, prompt text, or AI provider metadata.
- Hidden authoring markers or data that would make public cached HTML unsafe.

## Chunk Stability

Chunk IDs should be stable across normal edits when the section identity has not changed. Prefer slugs based on stable section keys or public headings over database IDs.

Each chunk should include:

- A stable `id`.
- A public `heading`.
- A canonical `sourceUrl`, including an anchor when useful.
- Plain text or Markdown-safe `body`.
- `dependsOn` entries such as `url:https://example.com/page` for cache invalidation.
