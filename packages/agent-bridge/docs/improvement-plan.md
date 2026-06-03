# Agent Bridge — Improvement & Growth Plan
> Package: capell-app/agent-bridge · Kind: package · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Draft

## 1. Snapshot

Agent Bridge exposes Capell to AI agents over [Laravel MCP](https://github.com/laravel/mcp). It ships two HTTP MCP servers — `CapellKnowledgeServer` (read-only: list packages, read allow-listed docs, recommend packages) and `CapellSiteServer` (list/inspect/preview/run/confirm site capabilities) — plus a Boost bridge that appends `ListBoostCapabilitiesTool`/`PreviewBoostCapabilityTool` into `boost.agent-bridge.tools.include`. The mutation path is a genuinely well-built preview→confirm flow: `InvokeAgentBridgeCapabilityPreviewAction` issues a confirmation token bound to token+user+SHA-256 payload hash with a TTL, and `ConfirmAgentBridgeCapabilityAction` re-checks scope, optional policy gate, atomically claims the row (`whereNull('used_at')...->update`), then audits. Capabilities live in a `CapellAgentBridgeCapabilityRegistry` with five built-ins (cache clear; page create_draft/update_draft/disable/inspect_readiness). Per-site authz is enforced in actions via `AgentBridgePageAccess::authorizeSite`. Persistence: `capell_agent_bridge_tokens`, `_confirmations`, `_audit_entries`, `_saved_prompts` (4 migrations + 1 rename). The only admin surface is the `CapellAgentBridgePromptBuilderPage` (a prompt-template generator) plus user-resource relation managers (tokens/confirmations/audit). Deps: `laravel/mcp ^0.6|^0.7`, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `laravel-package-tools`.

Marketplace summary verbatim: *"Agent Bridge exposes Capell knowledge and site capabilities through Laravel Agent Bridge servers."* Manifest declares **1** screenshot (`docs/assets/marketplace/extension-card.jpg`); the repo actually commits **10** PNGs under `docs/screenshots/` (light+dark for 5 surfaces) plus a `screenshots.json` runner manifest — a declared-vs-committed mismatch.

## 2. Improvements (existing functionality)

- **Make the health check real** — `AgentBridgeHealthCheck` is a pure stub (`compatibleCapellApiVersion()` only), yet the manifest declares it `severity: critical` with the label "surfaces, providers, and install health are discoverable by Diagnostics." It checks nothing. Add probes: routes registered & reachable, `laravel/mcp` present, capability registry resolvable, at least one capability visible, tables migrated, settings present. — `src/Health/AgentBridgeHealthCheck.php` — **M**

- **Stop writing the token row on every request** — the auth middleware does `forceFill(['last_used_at' => now()])->save()` on every authenticated MCP call (plus a conditional `token_hash` rewrite). Every agent tool call becomes a DB write; high-traffic agents will hammer the tokens table. Throttle `last_used_at` updates (e.g. only if older than N minutes) or queue them. — `src/Http/Middleware/AuthenticateCapellAgentBridgeToken.php` — **S**

- **Reconcile the manifest with reality** — `database.settings: false` but `database/settings/...add_agent_bridge_settings.php` exists and the provider registers `AgentBridgeSettings`; `settings: []` and `permissions: []` are empty though a settings surface (`enable_user_resource_bridge`) and access gating exist; `surfaces: ["admin"]` omits the HTTP MCP + `home` routes the package registers. Update manifest so Diagnostics/marketplace reflect the package. — `capell.json` — **S**

- **Fix the screenshot declaration mismatch** — manifest lists one `extension-card.jpg`; 10 real screenshots + `screenshots.json` are committed but unreferenced. Either reference the real screenshots array in `marketplace.screenshots` or document why a single card is used. — `capell.json`, `docs/screenshots.json` — **S**

- **Default config ships the product mostly off** — `config/capell-agent-bridge.php` sets `routes.home => null`, `routes.knowledge => null`, only `routes.site` enabled. A premium "knowledge + site" product defaults knowledge to disabled while README/manifest market knowledge. Decide intentional defaults and document them; if off-by-default is intended for safety, say so loudly in setup docs. — `config/capell-agent-bridge.php` — **S**

- **Migration table-name guard is brittle** — `up()` guards on `Schema::hasTable('capell_agent-bridge_tokens')` (hyphenated) while the model targets `capell_agent_bridge_tokens` (underscored) and a later `2026_05_28` rename migration reconciles them. The hyphen-in-table-name convention is unusual and the create/rename pairing is fragile on fresh installs. Collapse to a single underscore-named create migration if no production data depends on the old names. — `database/migrations/2026_05_10_190840_01_create_capell_agent-bridge_tokens_table.php`, `..._rename_agent_bridge_tables_to_canonical_names.php` — **M**

- **Retire coverage-gaming tests** — `AgentBridgeCoveragePushTest` and `AgentBridgeResidualCoverageTest` instantiate Filament pages/relation managers and assert `getColumns()->not->toBeEmpty()`. These inflate coverage without asserting behaviour. Replace with real Livewire page tests (fill form → assert prompt) and real relation-manager record assertions. — `tests/Unit/AgentBridgeCoveragePushTest.php`, `tests/Unit/AgentBridgeResidualCoverageTest.php` — **M**

- **`RecommendPackagesTool` scoring is naive** — substring term-count over name/group/bundle/contexts only; ignores `description` and doc content, so "redirects" or "SEO" won't match unless those literal words sit in the manifest. Fold package `description` and capability descriptions into the haystack. — `src/Tools/Knowledge/RecommendPackagesTool.php`, `src/Support/KnowledgeRepository.php` — **S**

## 3. Missing Features (gaps)

Tied to declared `capabilities: ["agent-bridge", "agent-bridge-admin"]` and agent-integration norms:

- **Rate limiting / abuse controls (table stakes, missing).** No `throttle` anywhere — grep finds only `AuthenticateCapellAgentBridgeToken` on the two MCP routes. A leaked token can enumerate/inspect/preview without limit. Add per-token throttling (Laravel `RateLimiter` keyed on token id) and a per-token enable flag. — `routes/agent-bridge.php`, middleware.

- **Token lifecycle management (gap).** Tokens are created (`CreateAgentBridgeTokenAction`) but there is no rotation, no revocation surface beyond delete, no "last used / created from IP" review beyond `last_used_at`, and no scope catalog. A premium agent product needs a tokens admin resource with rotate/revoke and scope picker.

- **Capability discovery for agents is thin.** `ListBoostCapabilitiesTool`/`ListSiteCapabilitiesTool` return capability metadata but there is no machine-readable input schema per capability (`inputDataClass`/`outputDataClass` exist on `CapabilityData` but are never populated by the five built-ins). Agents must guess payload shape; the create-draft action only reveals its schema by failing validation. Populate `inputDataClass` and surface JSON schema in the list tools. — `src/Data/CapabilityData.php`, `src/Providers/AgentBridgeServiceProvider.php`.

- **Audit is write-only.** `CapellAgentBridgeAuditEntry` records events but there is no MCP tool or admin filter to query "what did this agent do," no anomaly surfacing, no retention/pruning policy. Audit value is a differentiator for a paid ops product — add a queryable audit tool + retention command.

- **Legacy unkeyed token hash is permanently accepted.** `findForPlainTextToken` falls back to `hash('sha256')` (unkeyed) forever; the upgrade to HMAC happens lazily on use but there's no migration/command to force-rehash and drop legacy. Provide a one-time rehash + a flag to reject legacy. — `src/Models/CapellAgentBridgeToken.php`.

- **No write-capability beyond pages/cache.** Capabilities are page CRUD + cache clear. Cross-sell hooks (SEO, redirects, navigation) are advertised in the prompt builder's `areaOptions` (`seo`, `redirects`, `navigation`) but no capabilities back them — the prompt builder offers areas the bridge can't actually operate. Either ship those capabilities or scope the builder to what's real.

- **Differentiator vs table-stakes:** table-stakes = token auth + MCP server (done). Differentiator = the preview→confirm→audit safety envelope with per-site authz (done, and genuinely good) plus capability schema discovery + queryable audit (missing). Lean the marketing on the safety envelope.

## 4. Issues / Risks

- **Stub health check mislabeled critical** — Diagnostics will report green for a check that asserts nothing. `src/Health/AgentBridgeHealthCheck.php`.

- **No rate limiting on authenticated MCP endpoints** — `routes/agent-bridge.php` attaches only `AuthenticateCapellAgentBridgeToken`. Leaked-token blast radius is unbounded.

- **Knowledge doc read — defense-in-depth gap.** `KnowledgeRepository::readDocument()` trims/normalizes slashes but does **not** strip `../`; it is currently safe only because the input must `===` a path already in the allow-list before `File::get(base_path($normalized))` runs. That coupling is fragile — if the allow-list build ever changes to fuzzy/prefix matching, this becomes path traversal. Add an explicit realpath-within-base_path assertion. `src/Support/KnowledgeRepository.php`.

- **Content-visibility to agents.** `InspectSiteStateTool` correctly returns only counts + package versions (no bodies) and is annotated `IsReadOnly` — good. But it leaks `app.environment` and `app.debug` to any scoped agent; consider gating those behind a scope. `src/Tools/Site/InspectSiteStateTool.php`.

- **Per-request write amplification** (perf budget: manifest `adminQueryBudget: 40`, `frontendRenderBudgetMs: 0`, not cacheable). The middleware write-per-call isn't covered by any budget; add an MCP-request budget. `src/Http/Middleware/AuthenticateCapellAgentBridgeToken.php`.

- **Prompt builder advertises unbacked operations** — `areaOptions`/`operationOptions` include seo/redirects/navigation/regenerate with no matching capability; agents prompted toward them will fail at the bridge. `src/Filament/Pages/CapellAgentBridgePromptBuilderPage.php`.

- **Test gaps:** no test exercises rate-limit absence, no test for legacy→HMAC rehash path, no negative test that `InspectSiteStateTool` omits content bodies, no test asserting confirm-token cannot be replayed across users (the action logic exists; assert it). The two "coverage" tests are synthetic. `tests/`.

- **i18n:** capability `name`/`description` strings registered in the provider are hardcoded English (`'Clear Capell caches'`, etc.), unlike admin labels which use `__()`. Agents and the list tools surface untranslated strings. `src/Providers/AgentBridgeServiceProvider.php`.

- **Commercial vs maturity:** manifest requests `first-party` certification + `paid` license at `premium` tier, but servers are `Version('0.1.0')` and the flagship health check is a stub — certification risk.

## 5. Marketplace & Selling

**Current copy critique.** Manifest summary and composer `description` ("Agent Bridge servers and capability adapters for Capell CMS") are accurate but flat and jargon-heavy ("Agent Bridge servers" twice) — they describe plumbing, not the buyer outcome (safe agent automation with human-in-the-loop confirmation and audit). They never mention the genuine differentiator: the preview→confirm→audit envelope and per-site scoping.

**Improved 1-sentence summary:** "Let AI agents safely read and operate your Capell site through scoped tokens, preview-then-confirm guardrails, and a full audit trail."

**Improved 3–4 sentence description:** "Agent Bridge connects AI agents and MCP clients to Capell over standard Laravel MCP servers — a read-only knowledge server for packages and docs, and an authenticated site server for inspecting and operating your installation. Every mutating action runs through a preview→confirm flow: the agent sees exactly what will change, you hold a short-lived confirmation token bound to the user and payload, and nothing executes until confirmed. Capabilities are scoped per token and authorized per site, and every preview, confirmation, and run is recorded in a queryable audit log. Includes an admin prompt builder so operators can hand agents correctly-scoped, policy-aware instructions."

**Media gaps.** 10 screenshots exist but the manifest references none of them — wire the real array into `marketplace.screenshots`. Missing: an MCP-client connection screenshot (the actual integration), and a token-scopes screenshot. Replace the generic `extension-card.jpg` reference.

**Pricing / tier / bundle.** Premium / Operations bundle is right. Cross-sell via deps (`capell-app/core`, `capell-app/admin`) and the **agent-delivery**, **ai-orchestrator**, and **api** Extension Suites — Agent Bridge is the safe write/execute layer those suites should route mutations through. Position as the mandatory "safety gateway" any agent-touching Capell suite requires.

**Differentiators / value props / target buyer.** Buyer = agencies and ops teams running multi-site Capell who want to delegate routine page/cache work to agents without handing over the keys. Value: human-in-the-loop by construction, per-site blast-radius control, audit for compliance.

**Keywords/tags:** mcp, ai-agents, laravel-mcp, model-context-protocol, agent-bridge, capability-tokens, preview-confirm, audit-log, cms-automation, scoped-access, human-in-the-loop, site-operations.

## 6. Prioritized Roadmap

| Item | Bucket | Effort | Impact | Section ref |
| --- | --- | --- | --- | --- |
| Implement real health check probes (routes/MCP/registry/tables) | Now | M | High | 2, 4 |
| Add per-token rate limiting on MCP routes | Now | S | High | 3, 4 |
| Reconcile manifest (settings/permissions/surfaces/database.settings) | Now | S | High | 2, 4 |
| Throttle/queue `last_used_at` token writes | Now | S | Med | 2, 4 |
| Wire real screenshots array into `marketplace.screenshots` | Now | S | Med | 5 |
| Harden `readDocument` with realpath-within-base assertion | Now | S | Med | 4 |
| Populate `inputDataClass`/JSON schema in capability discovery tools | Next | M | High | 3 |
| Rewrite marketplace summary + composer description | Next | S | High | 5 |
| Replace coverage-gaming tests with behavioural tests | Next | M | Med | 2, 4 |
| Add queryable audit MCP tool + retention/pruning command | Next | M | High | 3 |
| Force-rehash legacy token hashes + flag to reject legacy | Next | M | Med | 3, 4 |
| Translate capability name/description strings | Next | S | Med | 4 |
| Scope prompt-builder areas to backed capabilities (or ship SEO/redirect/nav capabilities) | Later | M | Med | 3, 4 |
| Tokens admin resource: rotate/revoke/scope-picker | Later | M | High | 3 |
| Collapse hyphen-create + rename migrations into one canonical create | Later | M | Low | 2 |
| Gate `app.debug`/`environment` in InspectSiteState behind a scope | Later | S | Low | 4 |
