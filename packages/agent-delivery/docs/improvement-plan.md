# Agent Delivery - Improvement & Growth Plan

> Package: capell-app/agent-delivery · Kind: package · Tier: premium · Product group: Capell Publishing Pro · Bundle: publishing-pro · Status: Active

## 1. Snapshot

Agent Delivery exposes published Capell pages as public-safe JSON manifests and semantic chunks for AI agents, RAG systems, and answer engines. It owns frontend API routes under `api/capell/agent/v1`, a contributor registry for metadata/chunks/references/related URLs, rate limiting, Site Discovery coverage integration, JSON screenshots, and route/controller tests. The core Actions already sanitize contributor metadata, but the health check is currently only an API-version stub and the Marketplace intentionally has no buyer-facing screenshots because raw JSON output is not product UI.

## 2. Improvements (existing functionality)

1. **Implement real health diagnostics.** `AgentDeliveryHealthCheck` only returns API compatibility. Add diagnostics for route registration, configured middleware, rate limiter, registry binding, Site Discovery coverage tag registration, and JSON response headers. Evidence: `src/Health/AgentDeliveryHealthCheck.php`, `routes/agent-delivery.php`, `src/Providers/AgentDeliveryServiceProvider.php`. - **M**

2. **Isolate failing contributors.** The registry trusts contributor methods. A single throwing contributor can break public JSON responses. Wrap metadata/chunk/reference/related URL contributors with logged skip behavior and tests proving one bad contributor does not suppress safe core output. Evidence: `src/Support/AgentDeliveryRegistry.php`, contributor contracts. - **M**

3. **Add cache and variation headers for agent responses.** The package has cache tags in docs/manifest, but route responses should make language/site/page variation and public cache behavior explicit. Add headers or documented non-cache behavior in controllers. Evidence: `src/Http/Controllers/*Controller.php`, `capell.json performance.cacheSafety`. - **M**

4. **Clarify JSON-media marketplace policy.** `capell.json` has zero marketplace screenshots while screenshot contract contains raw JSON response PNGs. Docs should state these are runner evidence only until a styled endpoint explorer exists. Evidence: `docs/screenshots.json`, `capell.json marketplace.screenshots`, repo screenshot audit notes. - **S**

## 3. Missing Features (gaps)

Capabilities declared: `agent-delivery`, `public-page-manifest`, and `public-page-chunks`.

- **No styled endpoint explorer.** Raw JSON screenshots are useful evidence, but not buyer-facing Marketplace media.
- **No ETag/conditional request support.** Agent clients can poll manifests; responses should support stable cache validation.
- **No chunk budget diagnostics.** Chunk target/overlap config exists, but there is no health report for overlarge pages or chunk counts.
- **No discovery document bridge.** A host-level `llms.txt` or answer-engine discovery endpoint would make the package easier for crawlers to find.

## 4. Issues / Risks

1. **Important gap: health is a placeholder.** Public JSON delivery can be misconfigured while Marketplace health still appears compatible. Recommended fix: route/registry/middleware diagnostics. - **P2**

2. **Important risk: contributor exceptions can make public agent output unavailable.** Recommended fix: isolate and log contributor failures while preserving safe output. - **P2**

3. **Important gap: cache semantics are not explicit enough for public JSON agent consumers.** Recommended fix: headers, docs, and tests around variation and invalidation. - **P2**

4. **Improvement: buyer proof needs a styled surface.** Recommended fix: keep raw JSON as runner evidence and build an endpoint explorer before promoting media. - **P3**

## 5. Marketplace & Positioning

Agent Delivery should be positioned as controlled AI/answer-engine distribution for published Capell content. For teams, emphasize clean page manifests, semantic chunks, no scraping, and no admin leakage. For developers, emphasize contributor contracts, metadata sanitization, and stable versioned endpoints.

**Current summary:** "Serve your published Capell pages to AI agents and answer engines as clean, public-safe JSON manifests and RAG-ready semantic chunks - no scraping, no admin leakage."

**Improved summary:** "Versioned, public-safe page manifests and semantic chunks for AI agents, RAG pipelines, and answer engines consuming Capell content."

**Media status:** No marketplace screenshots should be promoted until a styled endpoint explorer or admin diagnostics surface exists. Keep `docs/screenshots/*.png` as runner evidence only.

**Cross-sell:** API for general JSON page delivery, Site Discovery for output coverage, SEO for indexing, Knowledge Base for documentation content.

## 6. Prioritized Roadmap

| Item                                                        | Bucket | Effort | Impact | Section ref |
| ----------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add route, middleware, registry, rate-limit health checks   | Done   | M      | High   | §2.1, §4.1  |
| Isolate/log failing contributors                            | Done   | M      | High   | §2.2, §4.2  |
| Add explicit cache/variation headers or documented no-cache | Done   | M      | Medium | §2.3, §4.3  |
| Document JSON screenshot evidence as non-marketplace media  | Now    | S      | Medium | §2.4, §4.4  |
| Add ETag/conditional request support                        | Next   | M      | Medium | §3          |
| Add chunk budget diagnostics                                | Next   | M      | Medium | §3          |
| Add `llms.txt` or agent discovery bridge                    | Next   | M      | Medium | §3, §5      |
| Build a styled endpoint explorer for Marketplace proof      | Later  | L      | Medium | §3, §5      |
| Add optional authenticated premium agent endpoints          | Later  | L      | Medium | §5          |

## 7. Verification

Implementation slice 1 exposed public JSON routes, contributor contracts, and health checks as manifest contributions, and made `AgentDeliveryHealthCheck` verify route registration, rate limiting, registry binding, and optional Site Discovery coverage registration. Verify with:

Implementation slice 2 closed the deferred public-api-endpoint traceability gap by declaring the public JSON API metadata on the route contribution, recording the throttled route names in security metadata, and documenting the public API contract for buyers, admins, and package authors.

Implementation slice 3 isolated contributor failures at the registry boundary. Metadata, chunk, reference, and related URL contributors are skipped and logged when they throw, while core public output and other safe contributors continue to render.

```bash
vendor/bin/pest packages/agent-delivery/tests --configuration=phpunit.xml
```

For registry or controller changes, include:

```bash
vendor/bin/pest packages/agent-delivery/tests/Feature/Http packages/agent-delivery/tests/Unit/Support/AgentDeliveryRegistryTest.php --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for routes, provider, registry, Actions, docs, screenshots, and public JSON boundaries.
- [x] Capell audience pass completed for AI consumers, developers, and Marketplace buyers.
- [x] Approved implementation slice 1 shipped: route/contract manifest metadata and health diagnostics.
- [x] Public API endpoint traceability gap closed in manifest metadata, tests, and docs.
- [x] Contributor failures isolated and covered for metadata, chunks, references, and related URLs.
- [x] Focused Agent Delivery verification passed.
- [x] Package tests passed.
- [x] Scoped Agent Delivery manifest/preflight check passed; broad manifest audit is blocked by unrelated `content-sections` traceability drift.
