# Agent Delivery

Agent Delivery exposes already-public Capell page content as structured manifests and stable semantic chunks for agents, search assistants, and machine readers.

## At A Glance

- Package: `capell-app/agent-delivery`
- Namespace: `Capell\AgentDelivery\`
- Surfaces: public HTTP JSON endpoints
- Service provider: `packages/agent-delivery/src/Providers/AgentDeliveryServiceProvider.php`
- Capell dependencies: `capell-app/core`

## What It Adds

- `GET /api/capell/agent/v1/pages/manifest?url=/path` returns canonical URL, locale, alternates, title, headings, summary, plain-text body, public metadata, references, and timestamps.
- `GET /api/capell/agent/v1/pages/chunks?url=/path` returns stable chunk records suitable for long-form agent consumption.
- Focused contributor contracts let packages contribute public-safe metadata, chunks, references, and related URLs without putting package-specific logic into themes or public Blade.

## Public Safety

Agent Delivery resolves pages through Core public URL resolution. Drafts, disabled URLs, unpublished pages, admin state, prompts, signed URLs, authoring markers, model permissions, and package internals are not part of the output contract.

The package is deliberately separate from `agent-bridge`: Agent Delivery is anonymous, read-only, and public-safe. Agent Bridge remains the authenticated surface for trusted agent actions.

## Extension

Register package-specific contributors by tagging implementations of:

- `AgentDeliveryContributor::TAG` for legacy aggregate metadata and chunks.
- `AgentDeliveryMetadataContributor::TAG` for public-safe metadata and provenance.
- `AgentDeliveryChunkContributor::TAG` for stable semantic chunks.
- `AgentDeliveryReferenceContributor::TAG` for public source/reference links.
- `AgentDeliveryRelatedUrlContributor::TAG` for related public URLs.

Contributors must only return already-public content. Do not include draft state, editor field paths, model IDs, signed URLs, permissions, prompts, or internal package metadata.

## Docs

- [Overview](docs/overview.md)
- [Contract](docs/contract.md)
