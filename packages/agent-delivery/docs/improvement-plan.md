# Agent Delivery — Improvement & Growth Plan
> Package: capell-app/agent-delivery · Kind: package · Tier: premium · Product group: Capell Publishing Pro · Bundle: publishing-pro · Status: Draft

## 1. Snapshot

Agent Delivery exposes two anonymous, public-safe JSON endpoints — `GET /api/capell/agent/v1/pages/manifest` and `.../pages/chunks` (both keyed by `?url=`) — that turn an already-public Capell page into a compact structured manifest (`canonicalUrl`, `title`, `headings`, `summary`, `body`, `metadata`, `references`, `alternates`, `relatedUrls`, `publishedAt`, `lastUpdatedAt`) and a list of semantic chunks. The surface is `frontend` only; it ships **no migrations, no settings, no admin UI, no console commands** (`capell.json` → `database`, `settings`, `commands` all empty/false). Core flow: `ResolveAgentDeliveryPageAction` (host → site/language/URL resolution via Core's `LoadSiteDomainFromUrlAction` + `ResolvePublicPageByUrlAction`) → `BuildAgentDeliveryPageAction` (HTML-stripping, heading extraction, reference filtering) → `BuildAgentDeliveryChunksAction` (one body chunk fallback, or contributor chunks). Extensibility is a container-tagged `AgentDeliveryRegistry` with five contributor contracts (`...Contributor`, `...MetadataContributor`, `...ChunkContributor`, `...ReferenceContributor`, `...RelatedUrlContributor`). Deps: `capell-app/core` (required), `capell-app/site-discovery` (soft, wires `AgentDeliveryGeneratedOutputCoverageSource`). Marketplace `summary` verbatim: *"Public-safe structured page manifests and semantic chunks for agents and machine readers."* Screenshot count: **0** in `capell.json` `marketplace.screenshots`, although `docs/screenshots.json` defines **2** required capture entries — a manifest mismatch (the manifest array was never populated).

**Overlap warning (load-bearing for §3/§5):** `seo-suite`'s **AI Discovery** already ships the richer AI-content-delivery surface — `/llms.txt`, `/llms-full.txt`, page Markdown at `/{url}.md`, `Accept: text/markdown` negotiation, robots AI-crawler rules, editor inclusion controls, and `AiDiscoveryGeneratedOutputCoverageSource` (`seo-suite/src/Http/Controllers/PageMarkdownController.php`, `seo-suite/docs/ai-discovery.md`). Both Agent Delivery and seo-suite register a `GeneratedOutputCoverageSource` into `site-discovery`'s registry. Agent Delivery is positioned as the structured-JSON/chunk complement to seo-suite's Markdown/llms.txt surface, but this boundary is undocumented and the value props collide.

## 2. Improvements (existing functionality)

- **Stop emitting a 200-style cache-tag header on 404s** — `notFound()` routes through `json()` which always attaches `X-Capell-Agent-Delivery-Version` and `X-Capell-Cache-Tags: agent-delivery` even on "Page not found". Caches/CDNs keyed on the tag header may treat misses as taggable. Split `notFound()` to omit cache tags. — `src/Http/Controllers/PageManifestController.php`, `src/Http/Controllers/PageChunksController.php` — S
- **De-duplicate the two controllers** — `PageManifestController` and `PageChunksController` are ~90% identical (`json()`, `notFound()`, `cacheTags()`, install guard, API_VERSION const all copied). Extract a shared `AbstractAgentDeliveryController` or a `RespondsWithAgentDelivery` trait. — `src/Http/Controllers/*` — S
- **Add `Cache-Control` / `ETag` to responses** — manifest output is deterministic for a `(site,locale,url)` tuple and `lastUpdatedAt` is already computed, but responses carry no HTTP caching headers, so every agent/crawler hit re-runs full resolution + HTML stripping against the 20ms budget. Emit `Cache-Control: public, max-age=…` and a `lastUpdatedAt`-derived `ETag` with `304` support. — `src/Http/Controllers/PageManifestController.php` — M
- **Avoid lazy-loading inside the build action** — `BuildAgentDeliveryPageAction::alternates()` calls `$page->loadMissing('pageUrls.language', 'pageUrls.siteDomain')` mid-build, an unbudgeted query triggered per request. Resolve alternates in `ResolveAgentDeliveryPageAction` (which already eager-loads site relations) and pass hydrated data in, consistent with the Capell rule that render data is hydrated upstream. — `src/Actions/BuildAgentDeliveryPageAction.php:90` — M
- **Surface chunk count / total in the chunks payload envelope** — chunks return a bare `data: [...]` with no `meta` (count, page canonicalUrl, generatedAt). Agents paginating or caching need an envelope; add a `meta` block without breaking `data`. — `src/Http/Controllers/PageChunksController.php` — S
- **Make `summary` truncation word-safe** — `Str::limit($body, 240, '')` hard-cuts mid-word with no ellipsis. Use word-boundary truncation for cleaner agent summaries. — `src/Actions/BuildAgentDeliveryPageAction.php` (`summary()`) — S
- **Heading extraction is regex-on-HTML** — `preg_match_all('/<h[1-6]...>/')` misses headings in structured/block content (the `content === array` path yields zero headings, only the title). For block-based pages, headings should come through a contributor or a Core block walker. — `src/Actions/BuildAgentDeliveryPageAction.php` (`headings()`) — M

## 3. Missing Features (gaps)

Declared `capabilities[]`: `agent-delivery`, `public-page-manifest`, `public-page-chunks` — all three are reachable in production (routes registered, controllers invoke the actions). No dead capability. Gaps against AI-content-delivery norms:

- **No site-level index / discovery manifest** — there is no `GET .../pages` listing or sitemap-style index of which URLs expose manifests. Agents must already know a URL. seo-suite's `llms.txt` is exactly this index; Agent Delivery has no JSON equivalent. **Table-stakes gap** for agent feeds. — `routes/agent-delivery.php`
- **No freshness/conditional-request support** — no `If-None-Modified`/`ETag`, no `lastUpdatedAt`-driven `since` filter, despite `lastUpdatedAt` being computed and `cacheSafety.cacheable: false`. Agents re-fetch full bodies. **Table-stakes.**
- **No structured/JSON-LD or schema export in the manifest** — `metadata` is freeform contributor output; there is no first-class schema.org / JSON-LD block, the canonical thing answer-engines consume. seo-suite owns schema audit but does not emit it here. **Differentiator gap.**
- **No `llms.txt` / Markdown variant** — by design these live in seo-suite, but the boundary is undocumented, so buyers can't tell which package to install. Either document the split crisply or expose a Markdown chunk representation. (See §5.)
- **No per-page opt-out / access control** — every public page is unconditionally machine-readable. There is no editor toggle to exclude a page from agent delivery, no `noai`/per-page robots equivalent. seo-suite has explicit "include in AI index" editor controls; Agent Delivery has none. **Differentiator + compliance gap.**
- **No locale/alternate negotiation on the endpoint** — `alternates` are listed but you cannot request a specific locale's manifest other than via the resolved domain; no `?locale=` override. — `src/Actions/ResolveAgentDeliveryPageAction.php`
- **Chunking is trivial** — when no contributor supplies chunks, the entire body becomes a single chunk (`BuildAgentDeliveryChunksAction`). No heading-based segmentation, token/size targeting, or overlap — the core value of "semantic chunks" for RAG is unimplemented in the default path. **Differentiator gap** (this is the headline feature in the name).

## 4. Issues / Risks

- **Health check is a no-op vs. its manifest promise** — `AgentDeliveryHealthCheck` implements only `compatibleCapellApiVersion(): '^4.0'`; `ChecksExtensionHealth` is an empty marker interface, so the check asserts nothing about "surfaces, providers, and public manifest health" as the `capell.json` `healthChecks[].label` claims (severity `critical`). Note: sibling packages (e.g. `seo-suite/src/Health/SeoSuiteHealthCheck.php`) follow the same version-only pattern, so this is a repo-wide convention, not unique — but the *label* here over-promises. Either soften the label or add real assertions (routes registered, registry resolvable, package installed). — `src/Health/AgentDeliveryHealthCheck.php`
- **Public-output safety is under-tested for contributors** — the public-safety story is strong in the default path: `plainText()` strips `<script>/<style>` and tags; `references()`/`relatedUrls()` enforce `http(s)`-only via `isPublicUrl()`; the registry re-filters references and related URLs; the array-content test (`AgentDeliveryRegistryTest` "does not expose raw array content") proves structured content with `internal_model_id`/`field_path`/`secret_prompt` yields `null` body. **But** contributor `metadata()` output is merged verbatim (`array_replace_recursive`) with **no leakage filtering** — a third-party `MetadataContributor` returning model IDs, prompts, or admin URLs is emitted unredacted into the public manifest. The docs forbid this (`docs/package-authors.md` → Public Safety Rules) but nothing enforces or tests it. Add a metadata-sanitisation pass and a leakage test. — `src/Support/AgentDeliveryRegistry.php` (`metadata()`), `src/Actions/BuildAgentDeliveryPageAction.php`
- **No dedicated chunks controller test** — `PageChunksController` is exercised only inside `PageManifestControllerTest` ("returns stable semantic chunks"). There is no test for: chunks 404 when uninstalled, chunks empty-body case, chunk ordering via `usort`, or multi-contributor chunk merge through the HTTP layer. — `tests/Feature/Http/`
- **`metadata()` merge collisions are silent** — multiple metadata contributors with overlapping keys silently overwrite via `array_replace_recursive` with no precedence rule or conflict signal; output becomes registration-order-dependent and non-deterministic across boots. — `src/Support/AgentDeliveryRegistry.php`
- **Performance budget unverified** — manifest budget is `frontendRenderBudgetMs: 20` but there is no perf/query-count test, and `alternates()` adds an unbudgeted lazy load (§2). HTML regex stripping on large bodies is also unbenchmarked. — `capell.json` `performance.frontendRenderBudgetMs`
- **i18n** — `language` field falls back `locale ?? code`; the "Page not found" message is a hard-coded English string in both controllers, not translated. Minor for a machine endpoint but inconsistent with Capell's translation convention. — `src/Http/Controllers/*` (`notFound()`)
- **Manifest screenshot mismatch** — `capell.json` `marketplace.screenshots: []` while `docs/screenshots.json` declares 2 required entries. Populate the manifest after captures land. — `capell.json`, `docs/screenshots.json`

## 5. Marketplace & Selling

**Current `marketplace.summary`:** *"Public-safe structured page manifests and semantic chunks for agents and machine readers."*
**Current composer `description`:** *"Public-safe structured delivery endpoints for Capell content consumed by agents and machine readers."*
Critique: both are accurate but feature-flat and indistinguishable from seo-suite's AI Discovery to a buyer. They lead with *what it emits* ("manifests", "chunks", "endpoints") not *the outcome* ("get your pages cited / answered correctly by AI agents") and never state the boundary vs. seo-suite — the single biggest source of buyer confusion (§1, §3). Also note the `capell.json` `description` and composer `description` differ in wording; align them.

**Improved 1-sentence summary:** "Serve your published Capell pages to AI agents and answer engines as clean, public-safe JSON manifests and RAG-ready semantic chunks — no scraping, no admin leakage."

**Improved 3–4 sentence description:** "Agent Delivery gives AI agents, answer engines, and machine readers a stable, anonymous JSON contract for your published Capell pages: a compact page manifest (canonical URL, title, headings, summary, body, references, alternates) and ordered semantic chunks built for retrieval. Every response is resolved through Core's public URL pipeline, so drafts, signed URLs, prompts, model IDs, and admin state never appear. Packages extend the output through focused contributor contracts — metadata, chunks, references, and related URLs — instead of bolting bespoke JSON endpoints onto every content type. Pairs with SEO Suite's AI Discovery (llms.txt and Markdown views) and registers into Site Discovery so you can see exactly which public URLs are agent-covered."

**Screenshot/media gaps:** `marketplace.screenshots` is empty; capture and register the 2 entries already specified in `docs/screenshots.json` (manifest JSON response, chunks JSON response). Add a third showing the Site Discovery coverage report including agent-delivery rows.

**Pricing / tier / bundle:** premium tier in `publishing-pro` bundle is defensible *if* the chunking and schema/JSON-LD gaps (§3) are closed — today the default output is title+stripped-body+one chunk, which is thin for a paid premium SKU and overlaps a feature already inside seo-suite. Recommend: keep premium but ship heading-based chunking + JSON-LD export before charging, or fold into the seo-suite AI bundle as the "structured/JSON" companion surface.

**Cross-sell:** required `capell-app/core`; soft `capell-app/site-discovery` (coverage reporting). Strong bundle story with **seo-suite** (AI Discovery / llms.txt / Markdown — the human/Markdown side; Agent Delivery = the machine/JSON side), **agent-bridge** (authenticated agent actions — explicitly the write/trusted counterpart per `README.md`), and **ai-orchestrator** (prompt execution). Position as: *Site Discovery = registry · seo-suite = llms.txt + Markdown · Agent Delivery = structured JSON + chunks · Agent Bridge = authenticated actions.*

**Differentiators / value props / target buyer:** public-safe-by-construction (Core resolution, no DB in Blade), contributor extensibility, zero-config (no migrations/settings). Target buyer: documentation/knowledge sites and content platforms that want predictable agent/RAG ingestion without exposing admin internals.

**Keywords/tags:** agent delivery, page manifest, semantic chunks, RAG ingestion, llms.txt companion, answer engine optimization, machine-readable content, structured content API, public-safe JSON, AI crawler feed, content provenance, site discovery coverage.

## 6. Prioritized Roadmap

| Item | Bucket | Effort | Impact | Section ref |
|------|--------|--------|--------|-------------|
| Sanitise/whitelist contributor `metadata()` + add leakage test | Now | M | High | §4 |
| Drop cache-tag headers from 404 responses | Now | S | Med | §2 |
| Add dedicated PageChunksController tests (404, empty, ordering, merge) | Now | S | Med | §4 |
| Populate `marketplace.screenshots` from `docs/screenshots.json` | Now | S | Med | §4, §5 |
| Rewrite marketplace summary + description; align composer/capell.json + document seo-suite boundary | Now | S | High | §5 |
| Heading-based semantic chunking in default path (size/overlap targets) | Next | M | High | §3 |
| `ETag` + `Cache-Control` + `304` conditional requests | Next | M | High | §2, §3 |
| First-class JSON-LD / schema.org block in manifest | Next | M | High | §3 |
| Move `alternates` resolution upstream; eliminate mid-build lazy load | Next | M | Med | §2 |
| Add real assertions to health check (routes/registry/installed) or soften label | Next | S | Med | §4 |
| De-duplicate the two controllers into shared base/trait | Next | S | Low | §2 |
| Site-level index endpoint (`GET .../pages`) for agent discovery | Later | L | High | §3 |
| Per-page agent-delivery opt-out (editor control / `noai`) | Later | M | Med | §3 |
| Performance/query-count test against 20ms budget | Later | S | Med | §4 |
| `?locale=` override + chunks payload `meta` envelope | Later | S | Low | §2, §3 |
