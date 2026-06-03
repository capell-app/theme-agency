# Agent Delivery

Agent Delivery exposes already-public Capell page content as structured manifests and stable semantic chunks for agents, search assistants, and machine readers.

## At A Glance

- Package: `capell-app/agent-delivery`
- Namespace: `Capell\AgentDelivery\`
- Surfaces: public HTTP JSON endpoints
- Service provider: `packages/agent-delivery/src/Providers/AgentDeliveryServiceProvider.php`
- Capell dependencies: `capell-app/core`

## Why It Helps Your Capell Workflow

- Site owners can make public Capell pages easier for search assistants and
  agent tools to understand without exposing private admin state.
- Developers get a stable manifest and chunk contract instead of writing
  package-specific JSON endpoints for every public content type.
- Operators can keep the surface anonymous and read-only, separate from the
  authenticated Agent Bridge workflow.

## Best Used With

- [Site Discovery](../site-discovery/README.md) — public URL registry and
  generated-output coverage reporting.
- [SEO Suite](../seo-suite/README.md) — AI Discovery (`llms.txt`,
  `/llms-full.txt`, page Markdown at `/{url}.md`, `Accept: text/markdown`
  negotiation, robots AI-crawler rules, and editor inclusion controls).
- [Agent Bridge](../agent-bridge/README.md) — authenticated agent actions
  (the write/trusted counterpart to Agent Delivery's read-only surface).

## Boundary with SEO Suite AI Discovery

Agent Delivery and SEO Suite's AI Discovery serve complementary audiences:

| Concern         | Agent Delivery                                      | SEO Suite AI Discovery                     |
| --------------- | --------------------------------------------------- | ------------------------------------------ |
| Format          | Structured JSON manifests and semantic chunks       | Markdown, `llms.txt`, content negotiation  |
| Audience        | RAG pipelines, answer engines, structured consumers | LLM crawlers, human-readable AI indexes    |
| Endpoint style  | `?url=` query parameter                             | Path-based (`.md` suffix, `/llms.txt`)     |
| Coverage source | `AgentDeliveryGeneratedOutputCoverageSource`        | `AiDiscoveryGeneratedOutputCoverageSource` |

Install **Agent Delivery** when agents need structured JSON with typed fields,
ordered chunks, and contributor-extensible metadata. Install **SEO Suite** when
you need `llms.txt`, Markdown views, and editor-level AI inclusion controls.
Install both for full coverage: SEO Suite provides the human/Markdown side,
Agent Delivery provides the machine/JSON side, and Site Discovery unifies the
coverage report.

## What It Adds

- `GET /api/capell/agent/v1/pages` lists public, agent-readable page URLs for the resolved site and locale.
- `GET /api/capell/agent/v1/pages/manifest?url=/path` returns canonical URL, locale, alternates, title, headings, summary, plain-text body, public metadata, schema.org data, references, and timestamps.
- `GET /api/capell/agent/v1/pages/chunks?url=/path` returns heading-aware chunk records suitable for long-form agent consumption.
- Focused contributor contracts let packages contribute public-safe metadata, chunks, references, and related URLs without putting package-specific logic into themes or public Blade.
- All endpoints support `?locale=` overrides and deterministic `ETag`/`Cache-Control` responses.

## Public Safety

Agent Delivery resolves pages through Core public URL resolution. Drafts, disabled URLs, unpublished pages, admin state, prompts, signed URLs, authoring markers, model permissions, and package internals are not part of the output contract.

Set page or translation metadata `agent_delivery.enabled` to `false`, `agent_delivery.exclude` to `true`, or include a `noai` robots directive to hide a public page from Agent Delivery without changing normal public rendering.

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
- [Screenshot manifest](docs/screenshots.json)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/agent-delivery/tests --configuration=phpunit.xml
```
