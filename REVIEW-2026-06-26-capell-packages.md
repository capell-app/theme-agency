# Capell Packages — Code Review

**Date:** 2026-06-26
**Branch:** `4.x` (including in-flight `feat/anon-read-ai-capell` work)
**Scope:** Substantial functional packages + the newly added anonymous-read AI capability surface. The ~60 `theme-*` packages and the repo-wide `screenshots.json` churn were treated as generated noise and excluded.
**Method:** Three parallel exploration passes (inventory, architecture sweep, security audit), then **manual source verification of every finding cited below**. One automated finding was downgraded after verification; the security headline was confirmed at source.

> **Update (2026-06-26): findings 1.1 + 1.2 are implemented and verified.**
> `CapabilityData::publicPayload()` now exposes only `{key, name, description, risk}`; `ListPublicCapabilitiesTool` uses it (`toPayload()` untouched for the admin surface). A regression test asserts the anonymous list payload carries no scope / package / class-string / audit-event / schema. A pre-existing L9 narrowing issue in the adjacent run test was fixed in passing (it would otherwise fail changed-file analysis). Verified: agent-bridge suite **124/124**, Pint clean, PHPStan **0 errors** on the changed files. Items 1.3 and 1.4 were then investigated and **need no code change** — the public tools aren't yet routed (no live risk) and the 7 result payloads are SAFE. §2 architecture tidies remain open.

---

## How to read this

**Severity**

| Tag | Meaning |
| --- | --- |
| 🔴 HIGH | Violates a documented non-negotiable or exposes data to anonymous users. Fix before the related branch ships. |
| 🟡 MEDIUM | Real gap; schedule deliberately. |
| 🟢 LOW | Consistency / idiom / smell. Fix when nearby. |
| ⚪ INFO | Context or "what's solid" — no action, or verify-only. |

**Verification status**

| Tag | Meaning |
| --- | --- |
| ✅ verified | Read at source by the reviewer; file:line confirmed. |
| 🔍 confirm | Spot-checked; the pattern is real but confirm exact scope before acting. |
| 📋 sweep | Reported by the automated sweep, **not** individually verified. Confirm before action. |

---

## Executive summary

| # | Finding | Severity | Status | Area |
| - | ------- | -------- | ------ | ---- |
| 1.1 | Anonymous capability list leaks internal metadata (class-strings, package names, permission scopes, audit events, schemas) | 🔴 HIGH | ✅ **FIXED** | agent-bridge (public) |
| 1.2 | No regression test proving anonymous public-tool responses exclude internal metadata | 🟡 MEDIUM | ✅ **FIXED** | agent-bridge (public) |
| 1.3 | Anonymous audit-write/flooding — **no active risk: public tools are not yet route-registered**; a reusable per-IP throttle exists for when they are | 🟢 LOW | ✅ verified | agent-bridge (public) |
| 1.4 | The 7 public result payloads — **verified SAFE**: catalogs/schemas only, no class-strings, IDs, URLs, or draft content | ⚪ INFO | ✅ verified | agent-bridge / ai-creator |
| 2.1 | Domain logic (DB queries) in Filament/Livewire instead of Actions | 🟢 LOW–🟡 MED | ✅ + 📋 | access-gate, agent-bridge, others |
| 2.2 | Backed enums skip `HasLabel`, lean on a custom `enumOptions()` helper | 🟢 LOW | ✅ verified | access-gate |
| 2.3 | Action class naming drift (missing `Action` suffix) | 🟢 LOW | ✅ verified | blog |
| 2.4 | Raw SQL + driver branching inside a Livewire component | 🟢 LOW | ✅ verified | blog |

**The one item that warranted action is 1.1** (now fixed). The in-flight branch *defines* the tokenless tools but does **not yet register them on an MCP route** (verified — see §1), so the leak was latent rather than live. Fixing it before the route is wired is exactly the right time; it contradicts the project's stated public-output-safety non-negotiable.

---

## 1. Security — anonymous-read AI capabilities

Recent commits added a **tokenless, anonymous** MCP surface:

- `ListPublicCapabilitiesTool` (`capell-public-list-capabilities`)
- `RunPublicCapabilityTool` (`capell-public-run-capability`)
- 7 ai-creator discovery/interview capabilities flagged `public: true`

> **Routing status (verified 2026-06-26):** these two tool classes are **not yet registered on any MCP server**. `CapellSiteServer`/`CapellKnowledgeServer` are the only routes (`routes/agent-bridge.php`), they list only their own tools via a static `$tools` array, and both sit behind `AuthenticateCapellAgentBridgeToken`. No anonymous endpoint is live yet — the findings below are **pre-exposure (latent)**, which is the ideal time to address them.

### ⚪ What's solid (verify-only)

The **authorization gate is well designed** — credit where due:

- `CapellAgentBridgeCapabilityRegistry::publiclyReadable()` (`packages/agent-bridge/src/Support/CapellAgentBridgeCapabilityRegistry.php:75-82`) filters on **`public === true` AND `risk === Read` AND required-package-installed**. Setting `public: true` alone is not enough to expose a write capability — defense in depth.
- `RunPublicCapabilityTool::handle()` (`packages/agent-bridge/src/Tools/Public/RunPublicCapabilityTool.php:51-58`) re-checks the allowlist server-side *before* delegating, with an explicit comment that this is the trust boundary.
- The downstream invoke action still enforces scope/policy (`packages/agent-bridge/src/Actions/InvokeAgentBridgeCapabilityPreviewAction.php:52-64`).
- All 7 public caps route through `readCapability()` with `requiresConfirmation: false` + `risk: Read`, so they take the read/execute path — an anonymous caller **cannot** trigger the confirmation-write branch.

This means the automated audit's "the risk enum is only advisory" concern is **already mitigated** by the double filter and should be treated as INFO, not a defect.

### 🔴 1.1 HIGH — Metadata leak in the public capability list ✅ verified

**Where:** `packages/agent-bridge/src/Tools/Public/ListPublicCapabilitiesTool.php:26-28` serializes every public capability via `CapabilityData::toPayload()` (`packages/agent-bridge/src/Data/CapabilityData.php:66-86`).

`toPayload()` returns the **full** internal record. For the 7 anonymous-readable caps, a tokenless caller receives:

| Field | Example value (leaked) | Forbidden by safety rule |
| ----- | ---------------------- | ------------------------ |
| `scope` | `capell.ai-creator.read` | permission names |
| `requiredPackage` | `capell-app/ai-creator` | package names |
| `outputDataClass` | `Capell\AgentBridge\Data\CapabilityResultData` | class-strings / field paths |
| `auditEvent` | `ai-creator.discovery.list_themes` | internal markers |
| `inputSchema` / `outputSchema` | full JSON schema | field paths |
| `server`, `supportsPreview`, `requiresConfirmation`, `public` | internal flags | internal metadata |

**Impact:** Information disclosure to anonymous users — a structural map of internal class names, package names, permission scopes, and audit event names. This is a direct violation of: *"anonymous and non-admin output must never reveal … field paths, labels, selectors, permissions, package names, internal capability metadata."* Not RCE, but a stated non-negotiable.

**Fix direction:** Add a minimal projection on `CapabilityData` and use it **only** in the public tool. Leave `toPayload()` untouched so the authenticated admin listing keeps full fidelity.

```php
// CapabilityData.php — illustration, not applied
/** @return array{key:string,name:string,description:string,risk:string} */
public function publicPayload(): array
{
    return [
        'key'         => $this->key,
        'name'        => $this->name,
        'description' => $this->description,
        'risk'        => $this->risk->value,
    ];
}
```

```php
// ListPublicCapabilitiesTool.php — use publicPayload() instead of toPayload()
->map(fn (CapabilityData $capability): array => $capability->publicPayload())
```

> Note: if agents genuinely need the input schema to *call* a public capability, expose it on the `RunPublicCapabilityTool` tool schema itself (already declared in `schema()`), not via the discovery list — keep class-strings, scopes, package names, and audit events out entirely.

### 🟡 1.2 MEDIUM — Missing anonymous-safety regression test ✅ verified

**Where:** `packages/agent-bridge/tests/Feature/Public/PublicCapabilityToolsTest.php` covers functional behavior but **no test asserts the response excludes** `Capell\`, `scope`, `requiredPackage`, `auditEvent`, or class-strings.

**Why it matters:** The project's own convention requires a test proving anonymous/non-admin safety for any new public surface. This test is also the guard that stops 1.1 from silently returning.

**Fix direction:**

```php
it('public capability list never exposes internal metadata', function (): void {
    $response = // invoke capell-public-list-capabilities anonymously
    $json = $response->toArray();

    $flat = json_encode($json);
    expect($flat)
        ->not->toContain('Capell\\')
        ->not->toContain('requiredPackage')
        ->not->toContain('auditEvent')
        ->not->toContain('capell.ai-creator.read'); // scope string
});
```

### 🟢 1.3 LOW — Anonymous audit-write / flooding — no active risk ✅ verified

**Confirmed:** the executed path (`InvokeAgentBridgeCapabilityPreviewAction.php:69-87`) does call `AuditAgentBridgeCapabilityAction::run(token: null, user: null)`, and that action **does persist** a `CapellAgentBridgeAuditEntry` row (`AuditAgentBridgeCapabilityAction.php:38-53`, storing event/ip/user-agent with sanitized payload+result).

**But there is no live anonymous call path:** the public tools are not registered on any MCP route (see Routing status above), so no anonymous audit writes can occur today.

**When the public route is eventually wired**, the project already has the right primitive: `ThrottleAgentBridgeRequestsByIp` (`packages/agent-bridge/src/Http/Middleware/ThrottleAgentBridgeRequestsByIp.php`) — a per-IP limiter explicitly designed to run *ahead of* authentication (default 120 req/min/IP, config-gated by `rate_limit_enabled` / `rate_limit_per_ip_per_minute`). It is already applied to both authenticated routes.

**Recommendation (prospective):** when registering the public/tokenless route, apply `ThrottleAgentBridgeRequestsByIp`, and consider a **lower** cap than 120/min for an unauthenticated endpoint. No code change needed now.

### ⚪ 1.4 INFO — Unfiltered execution result — verified SAFE ✅

**Where:** `RunPublicCapabilityTool.php:60-68` returns `Response::structured($result)` verbatim; the result is `CapabilityResultData::toPayload()` → `{ok, message, data, warnings}`.

**Verified:** all 7 public capabilities' `data` payloads were traced and contain only public-appropriate content — theme/page-type/section-type/layout **catalogs**, the deterministic interview script, the site-spec JSON schema, and spec-validation errors. Spot-checked `ListThemesAction.php:26-40`: it fetches `id` but the `->map()` returns only `{key, name, colors, font_family, container}` — **the model id is dropped**. No class-strings, package names, model IDs, signed URLs, or draft content reach the caller.

**Minor note:** discovery queries don't filter by publication status, but themes/blueprints/layouts are installed extensions (not user/draft content), so listing them to a site-building agent is the intended function. No action.

---

## 2. Architecture & consistency

These are stylistic/consistency findings against the project's documented rules. None are security or correctness bugs. Several were **downgraded** from the automated sweep after verification.

### 🟢 2.1 Domain logic in the UI layer

Rule: components/resources/Livewire delegate to Actions; no business logic inline.

**✅ Verified examples:**

- ~~`packages/access-gate/src/Filament/Resources/AccessAreas/AccessAreaResource.php:75-77` — `Site::query()->select(['name','id'])->ordered()->pluck(...)` inline in a Filament `->options()` closure.~~ **✅ DONE** — extracted to `GetAccessAreaSiteOptionsAction`; the `canScopeToSites()` capability guard stays in the resource. Behavior-preserving (access-gate suite: same 178 pass / 4 pre-existing unrelated failures).
- ~~`packages/agent-bridge/src/Livewire/PromptBuilderToolbarAction.php:215` and `:259` — two `CapellAgentBridgeSavedPrompt::query()` read lookups.~~ **✅ DONE** — extracted to `ListSavedPromptOptionsAction` + `FindSavedPromptForUserAction`. agent-bridge suite 124/124.

**📋 Sweep candidates — ✅ all verified genuine and DONE:**

- ~~`packages/address/src/Filament/Components/Forms/CountrySelect.php`~~ **✅ DONE** — 3 inline `Country::query()` callbacks → `ListCountryOptionsAction` (options + search) + `GetCountryNameAction` (label). address suite 106/106.
- `packages/blog/src/Livewire/Page/Tag.php` — re-examined: the `whereHas` is wrapped in a closure passed to `PageLoader::getPages()` (a loader service), not inline component logic. **Left as-is** (already delegated), consistent with the doc's own Tag.php note.
- ~~`packages/comments/src/Livewire/CommentThreadComponent.php`~~ **✅ DONE** — morph-resolution + lookup + site/language scoping extracted to `ResolveCommentableFromPayloadAction`; component keeps decrypt + caching. comments suite 86/86.
- ~~`packages/content-sections/src/Livewire/Assets/Table/SectionAssets.php`~~ **✅ DONE** — default-language lookup → `GetDefaultLanguageIdAction` (the `Section::query()` fallback is an empty-builder default, not business logic — left). content-sections suite 169/169.

### 🟢 2.2 Enums skip `HasLabel` — ✅ DONE

~~`packages/access-gate/src/Enums/AccessAreaStatus.php` (and siblings) are plain backed enums using a custom `enumOptions()` helper instead of Filament's native `HasLabel`.~~ **✅ DONE** — all 11 access-gate enums now implement `Filament\Support\Contracts\HasLabel` with `getLabel()` delegating to the **same** translation keys (centralization preserved). All 13 call sites across 6 resources switched to native `->options(EnumClass::class)`; the `AccessGateFilamentOptions` trait removed. Filament's `HasOptions::options()` produces `[value => getLabel()]` — identical output. PHPStan clean; access-gate suite same 178 pass / 4 pre-existing fail. (The separate `public-actions` `PublicActionFilamentOptions` trait is out of this review's scope and untouched.)

### 🟢 2.3 Action naming drift ✅ verified

~~`packages/blog/src/Actions/GenerateArchiveUrl.php:14` — class `GenerateArchiveUrl` lacks the `Action` suffix.~~ **✅ DONE** — renamed class + file + test file → `GenerateArchiveUrlAction`, updated all 6 usage sites and the `tests/.pest/shards.json` path. Repo-wide grep confirmed this was the *only* naming-drift Action. Blog tests pass (8 unit/archives + 13 feature). Note: blog `README.md` still lists the old name but is generated — refreshes on next docs build.

### 🟢 2.4 Raw SQL + driver branching in a Livewire component ✅ verified

~~`packages/blog/src/Livewire/Page/Archive.php:80-120` — date filtering uses `whereRaw` with `sqlite` vs MySQL branching inside a `modifyQuery` closure.~~ **✅ DONE** — extracted to `ApplyArchiveDateFilterAction::run($query, $year, $month)`, which encapsulates the driver branch; the component's `modifyQuery` closure is now a one-line delegation. Parameterized SQL preserved verbatim. blog archive tests pass (10/10).

---

## 3. What passed the spot-check ✅

- `declare(strict_types=1);` present across sampled `src/` files.
- **Core isolation** — no `use Capell\Blog\…` / plugin imports from core.
- Explicit parameter + return types, including closures, on sampled methods.
- The public-capability **authorization gate** (see §1, "What's solid").

---

## 4. Recommended sequencing

1. **Now (before `feat/anon-read-ai-capell` merges):** 1.1 metadata leak + 1.2 regression test. Small, scoped to agent-bridge, and it's a stated non-negotiable.
2. **When wiring the public route (not before):** apply `ThrottleAgentBridgeRequestsByIp` to the tokenless MCP route, with a cap below the 120/min default for an unauthenticated endpoint. No live risk today — the route doesn't exist yet (1.3).
3. **Done — verified SAFE, no action:** 1.4 (the 7 result payloads).
4. **Opportunistic cleanup:** §2 items as packages are touched. Do **not** schedule a churn-heavy sweep for these alone; they're idiom/consistency.

---

## Appendix — files read during verification

```
packages/agent-bridge/src/Tools/Public/ListPublicCapabilitiesTool.php
packages/agent-bridge/src/Tools/Public/RunPublicCapabilityTool.php
packages/agent-bridge/src/Data/CapabilityData.php
packages/agent-bridge/src/Actions/InvokeAgentBridgeCapabilityPreviewAction.php
packages/agent-bridge/src/Support/CapellAgentBridgeCapabilityRegistry.php
packages/agent-bridge/src/Enums/CapabilityRiskEnum.php
packages/ai-creator/src/AgentBridge/AiCreatorAgentBridgeCapabilityProvider.php
packages/agent-bridge/src/Livewire/PromptBuilderToolbarAction.php
packages/access-gate/src/Filament/Resources/AccessAreas/AccessAreaResource.php
packages/access-gate/src/Enums/AccessAreaStatus.php
packages/blog/src/Livewire/Page/Archive.php
packages/blog/src/Actions/GenerateArchiveUrl.php
```
