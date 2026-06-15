# Agent Delivery Contract

## Routes

- `GET /api/capell/agent/v1/pages`
- `GET /api/capell/agent/v1/pages/manifest?url=/example`
- `GET /api/capell/agent/v1/pages/chunks?url=/example`

All routes resolve the site from the request host and only serve content that Core resolves as public. Pass `?locale=en` when the caller needs a specific language instead of the host/domain default.

The package manifest marks these routes as the shipped `public-api-endpoint` surface. They are public `GET` JSON endpoints, use the `api` middleware group and `throttle:capell-agent-delivery` by default, and can be wrapped in host auth middleware through `capell-agent-delivery.public_pages.auth_middleware` when a site wants private agent access.

Successful responses include:

- `X-Capell-Agent-Delivery-Version`
- `X-Capell-Cache-Tags`
- `Cache-Control`
- `ETag`
- `Last-Modified` when a page timestamp is available

Requests with a matching `If-None-Match` return `304`.

## Page Index

`GET /pages` returns `data[]` entries with `canonicalUrl`, `url`, `language`, `lastUpdatedAt`, `manifestUrl`, and `chunksUrl`, plus `meta.count` and `meta.generatedAt`.

## Manifest

`GET /pages/manifest` returns a `data` object with canonical URL, locale, alternates, title, headings, summary, plain-text body, public metadata, references, related URLs, timestamps, and a first-class `schema` object using schema.org `WebPage`.

## Chunks

`GET /pages/chunks` returns `data[]` chunk records and a `meta` envelope. Package contributors can provide custom chunks; otherwise Agent Delivery builds heading-aware default chunks using the configured word target and overlap.

## Opt-out

A public page is excluded from Agent Delivery when page or translation metadata has `agent_delivery.enabled: false`, `agent_delivery.exclude: true`, or a `noai` robots directive.

## Contributor Contracts

Packages can tag focused contributor contracts:

- `AgentDeliveryMetadataContributor::TAG` for public metadata/provenance.
- `AgentDeliveryChunkContributor::TAG` for package-specific stable chunks.
- `AgentDeliveryReferenceContributor::TAG` for public source/reference links.
- `AgentDeliveryRelatedUrlContributor::TAG` for related public URLs.
- `AgentDeliveryContributor::TAG` remains supported as a legacy aggregate contributor for metadata and chunks.

Contributors must not include admin/editor state, model IDs, signed URLs, prompts, permissions, package internals, field paths, unpublished content, or hidden authoring markers. Metadata contributors are sanitised recursively before output; specialised metadata contributors override aggregate contributors by explicit registration order.

Contributor exceptions are isolated to the failed surface. Agent Delivery logs a warning, skips the failing contributor result, and continues building the public response from core data and other registered contributors.
