# Agent Delivery Contract

## Routes

- `GET /api/capell/agent/v1/pages/manifest?url=/example`
- `GET /api/capell/agent/v1/pages/chunks?url=/example`

Both routes resolve the site from the request host and only serve content that Core resolves as public.

## Contributor Contracts

Packages can tag focused contributor contracts:

- `AgentDeliveryMetadataContributor::TAG` for public metadata/provenance.
- `AgentDeliveryChunkContributor::TAG` for package-specific stable chunks.
- `AgentDeliveryReferenceContributor::TAG` for public source/reference links.
- `AgentDeliveryRelatedUrlContributor::TAG` for related public URLs.
- `AgentDeliveryContributor::TAG` remains supported as a legacy aggregate contributor for metadata and chunks.

Contributors must not include admin/editor state, model IDs, signed URLs, prompts, permissions, package internals, field paths, unpublished content, or hidden authoring markers.
