# Agent Delivery

<!-- prettier-ignore-start -->

## What This Plugin Adds

Agent Delivery is an **Available**, **No schema impact** Capell package in the **Capell Publishing Pro** product group. It ships as `capell-app/agent-delivery` and extends these surfaces: frontend.

Serve your published Capell pages to AI agents and answer engines as clean, public-safe JSON manifests and RAG-ready semantic chunks - no scraping, no admin leakage.

After install, the package affects public rendering, public routes, or frontend runtime behaviour.

Status details:

- Status: Available
- Tier: premium
- Bundle: publishing-pro
- Composer package: `capell-app/agent-delivery`
- Namespace: `Capell\AgentDelivery`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, and Laravel routes instead of pushing this behaviour into core or application code.

**For teams:** Agent Delivery gives AI agents, RAG pipelines, and answer engines a controlled way to consume published site content without scraping pages or exposing anything from the admin workspace.

## Public API Surface

Agent Delivery contributes three versioned `GET` JSON endpoints under `/api/capell/agent/v1`. They are public by default, throttled with `throttle:capell-agent-delivery`, and only return content that Capell already resolves as published public output for the request host and locale.

- `capell-agent-delivery.pages.index`
- `capell-agent-delivery.pages.manifest`
- `capell-agent-delivery.pages.chunks`

The endpoints expose cache headers, ETags, and package cache tags so API consumers can poll responsibly. They do not expose admin URLs, editor state, model IDs, signed preview links, permissions, field paths, prompts, or unpublished content.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Public page manifest JSON response (frontend, required).
- Public page chunks JSON response (frontend, required).

The JSON captures in `docs/screenshots.json` are runner evidence for endpoint behavior, not buyer-facing Marketplace media. Keep `capell.json marketplace.screenshots` empty until the package has a styled Capell endpoint explorer, admin surface, or another visual route that helps a buyer understand the feature without promoting raw JSON output.

## Technical Shape

- Service providers: `Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider`.
- Config files: `packages/agent-delivery/config/capell-agent-delivery.php`.
- Route files: `packages/agent-delivery/routes/agent-delivery.php`.
- Actions: `BuildAgentDeliveryChunksAction`, `BuildAgentDeliveryPageAction`, `BuildAgentDeliveryPageIndexAction`, `ResolveAgentDeliveryPageAction`.
- Data objects: `AgentDeliveryChunkData`, `AgentDeliveryPageData`, `AgentDeliveryPageIndexEntryData`, `ResolvedAgentDeliveryPageData`.
- Manifest contributions: `agent-capability: Capell\AgentDelivery\Manifest\AgentDeliveryContractsContribution`, `health-check: Capell\AgentDelivery\Health\AgentDeliveryHealthCheck`, `route/public-api-endpoint: Capell\AgentDelivery\Manifest\AgentDeliveryRoutesContribution`.
- Health checks: `Capell\AgentDelivery\Health\AgentDeliveryHealthCheck`.
- Cache tags: `agent-delivery`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

Docs gap: document extension points here if the package delegates persistence to a host package.

## Install Impact

- Admin navigation: no admin surface declared.
- Permissions: none declared in `capell.json`.
- Public routes: versioned public JSON endpoints are declared in `capell.json`, throttled by default, and limited to already-published page content.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `agent-delivery`.
- Commands: none declared.

## Common Pitfalls

- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Do not promote raw JSON response captures as Marketplace screenshots; keep them as deployment-runner evidence only.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/agent-delivery`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Site Discovery](../site-discovery/README.md).
- Focused tests: `vendor/bin/pest packages/agent-delivery/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
