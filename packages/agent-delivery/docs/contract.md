# Agent Delivery Contract

## Routes

- `GET /api/capell/agent/v1/pages/manifest?url=/example`
- `GET /api/capell/agent/v1/pages/chunks?url=/example`

Both routes resolve the site from the request host and only serve content that Core resolves as public.

## Contributor Contract

Packages can tag implementations of `Capell\AgentDelivery\Contracts\AgentDeliveryContributor` with `AgentDeliveryContributor::TAG`.

Contributors may add:

- Public metadata.
- Stable semantic chunks.

Contributors must not include admin/editor state, model IDs, signed URLs, prompts, permissions, package internals, field paths, or hidden authoring markers.
