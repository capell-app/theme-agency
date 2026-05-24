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
- `AgentDeliveryContributor` lets packages contribute public-safe metadata and chunks without putting package-specific logic into themes or public Blade.

## Public Safety

Agent Delivery resolves pages through Core public URL resolution. Drafts, disabled URLs, unpublished pages, admin state, prompts, signed URLs, authoring markers, model permissions, and package internals are not part of the output contract.

The package is deliberately separate from `agent-bridge`: Agent Delivery is anonymous, read-only, and public-safe. Agent Bridge remains the authenticated surface for trusted agent actions.

## Docs

- [Overview](docs/overview.md)
- [Contract](docs/contract.md)
