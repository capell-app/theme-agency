# Agent Bridge - Improvement & Growth Plan

> Package: capell-app/agent-bridge · Kind: package · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Active

## 1. Snapshot

Agent Bridge connects AI agents and MCP clients to Capell through scoped tokens, MCP server routes, read-only knowledge/site capabilities, preview-then-confirm execution, saved prompts, audit entries, settings, Boost integration, an admin prompt-builder surface, a token lifecycle filter, and a read-only capability catalog resource. It already has a meaningful health check and preview/confirm tests. The current risk has shifted from route/config hardening to deeper policy diagnostics and real seeded screenshot fixture support.

## 2. Improvements (existing functionality)

1. **Move the default home route away from `/` or make it opt-in.** `routes/agent-bridge.php` registers the configured home route when non-empty, and config defaults appear to expose a JSON home document. A package route should not risk shadowing a host homepage. Evidence: `routes/agent-bridge.php`, `config/capell-agent-bridge.php`, `tests/Feature/HomeRouteTest.php`. - **S**

2. **Fix package health remediation language.** `AgentBridgeHealthCheck` tells users to run a host-app migration command, which conflicts with this repo's package workflow and the repo convention to use package commands/composer overlay. Update remediation to package/install guidance and cover it in tests. Evidence: `src/Health/AgentBridgeHealthCheck.php`, `tests/Unit/AgentBridgeHealthCheckTest.php`. - **S**

3. **Add audit/token retention automation to manifest and docs.** The provider registers `PruneAgentBridgeAuditEntriesCommand`, but `capell.json commands` does not expose install/setup/doctor/prune information and docs should explain retention defaults. Evidence: `src/Providers/AgentBridgeServiceProvider.php`, `src/Actions/PruneAgentBridgeAuditEntriesAction.php`, `capell.json`. - **M**

4. **Harden audit payload redaction.** Preview/execute/confirm flows audit payloads and results. Add a sanitizer that strips secrets, access tokens, passwords, prompts marked private, and signed admin URLs before persistence. Evidence: `src/Actions/AuditAgentBridgeCapabilityAction.php`, `src/Support/AgentBridgeAuditSanitizer.php`, `tests/Feature/PreviewConfirmWorkflowTest.php`. - **M** **Shipped in slice 2.**

## 3. Missing Features (gaps)

Capabilities declared: `agent-bridge` and `agent-bridge-admin`.

- **No first-class token management manifest contribution.** Admin surfaces exist through bridge/extension page registration, but manifest traceability is thin.
- **No explicit audit retention command in marketplace metadata.** Pruning exists in code but is not surfaced in `capell.json`.
- **Done/Shipped: operator-facing capability catalog.** `BuildAgentBridgeCapabilityCatalogAction` and `capell://agent-bridge/capabilities` expose registered capability scope, risk, server, preview, confirmation, required package, and policy ability metadata outside the prompt builder.
- **Done/Shipped: token lifecycle review controls.** Token records now expose a reusable lifecycle status enum and the user relation manager can filter active, expired, and revoked tokens without leaking token secrets.
- **Done/Shipped: server-specific RBAC and policy diagnostics.** `AgentBridgeHealthCheck` now reports site-server policy coverage by verifying registered site capabilities have token scopes and mutating capabilities require confirmation, while also surfacing how many capabilities add host policy abilities. — `src/Health/AgentBridgeHealthCheck.php`, `tests/Unit/AgentBridgeHealthCheckTest.php`
- **Done/Shipped: richer MCP schema export and compatibility tests.** `capell://agent-bridge/capabilities/schema` exposes the capability catalog as JSON, including input/output Data classes and MCP-compatible schemas for registered capabilities. Compatibility coverage verifies stable schema fields for agent discovery.
- **Screenshot recapture for token/audit/server surfaces is deferred.** Visual review confirmed the committed token, confirmation, audit, and health PNGs duplicate the prompt-builder page. They remain runner evidence only; marketplace media promotes only verified prompt-builder captures until user-resource and health-panel runner fixtures exist.

## 4. Issues / Risks

1. **Critical risk: a default package home route can shadow host public output.** A package should not claim `/` unless explicitly configured by the host. Recommended fix: default to disabled or a namespaced health path. - **P1**

2. **Important risk: audit records may persist sensitive payload fragments.** AI capability calls can include prompts, tokens, user data, and signed URLs. Recommended fix: central redaction before audit persistence. - **P2**

3. **Important gap: health remediation points to the wrong operational command style.** Recommended fix: update copy/tests to package-safe guidance. - **P2**

4. **Improvement: admin/buyer positioning should emphasize governance.** The differentiator is preview/confirm, scope, and audit. Recommended fix: docs and marketplace copy around governed operations. - **P3**

## 5. Marketplace & Positioning

Agent Bridge should be positioned as a governed AI operations bridge, not a generic AI chat layer. For operators, emphasize scoped tokens, confirmation, audit, and package knowledge. For developers, emphasize the capability registry, typed Data contracts, MCP route boundaries, and Boost integration.

**Current summary:** "Connect AI agents and MCP clients to Capell with read-only package knowledge, scoped site capabilities, preview-then-confirm execution, and audited operations."

**Improved summary:** "A governed MCP and AI-agent bridge for Capell, with scoped tokens, preview-and-confirm capabilities, Boost integration, and auditable execution."

**Media status:** Keep buyer media conservative until token management, audit review, and server health surfaces are recaptured as real Capell UI rather than duplicated prompt-builder captures.

**Cross-sell:** AI Orchestrator for package capability governance, Automation Studio for queued capability actions, Diagnostics for health, Live Chat for support-side capability examples.

## 6. Prioritized Roadmap

| Item                                                                                                                                                                                                                                       | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------ | ------ | ------ | ----------- |
| Disable or namespace the default home route                                                                                                                                                                                                | Done   | S      | High   | §2.1, §4.1  |
| Fix health remediation copy away from host-app migration text                                                                                                                                                                              | Done   | S      | Medium | §2.2, §4.3  |
| Surface audit/token pruning command in manifest/docs                                                                                                                                                                                       | Done   | M      | Medium | §2.3        |
| Add central audit payload/result redaction                                                                                                                                                                                                 | Done   | M      | High   | §2.4, §4.2  |
| Add capability catalog/admin inventory surface                                                                                                                                                                                             | Done   | M      | Medium | §3, §5      |
| Add token scope lifecycle UI and tests                                                                                                                                                                                                     | Done   | M      | Medium | §3          |
| Recapture token/audit/server health marketplace screenshots                                                                                                                                                                                | Later  | M      | Medium | §3, §5      |
| Done/Shipped: Add server-specific RBAC and policy diagnostics. Evidence: health diagnostics verify site capabilities have token scopes, mutating capabilities require confirmation, and policy ability coverage is surfaced for operators. | Done   | M      | Medium | §3          |
| Add richer MCP schema export and compatibility tests                                                                                                                                                                                       | Done   | M      | Medium | §5          |

## 7. Verification

Implementation slice 1 hardened route defaults so discovery remains disabled unless configured, fixed migration-health remediation copy, and exposed the shipped admin page, user schema extender, models, routes, settings, migrations, prune command, built-in capabilities, and health check as manifest contributions. Verify with:

```bash
vendor/bin/pest packages/agent-bridge/tests --configuration=phpunit.xml
```

For route/health changes, include:

```bash
vendor/bin/pest packages/agent-bridge/tests/Feature/HomeRouteTest.php packages/agent-bridge/tests/Unit/AgentBridgeHealthCheckTest.php --configuration=phpunit.xml
```

Implementation slice 2 adds centralized audit redaction before persistence while preserving capability execution and confirmation hashing. It redacts secret-like keys, bearer/token strings, private prompt payloads, and signed admin URLs from audit payloads and results. Verify with:

```bash
vendor/bin/pest packages/agent-bridge/tests/Feature/PreviewConfirmWorkflowTest.php --configuration=phpunit.xml
vendor/bin/pest packages/agent-bridge/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```

Implementation slice 3 adds a machine-readable MCP resource at `capell://agent-bridge/capabilities/schema` and includes input/output schema metadata in the shared catalog action. Verify with:

```bash
vendor/bin/pest packages/agent-bridge/tests/Unit/BridgeToolsTest.php --configuration=phpunit.xml
vendor/bin/pest packages/agent-bridge/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for routes, provider, health, capability execution, audits, docs, and marketplace media.
- [x] Capell audience pass completed for operators, AI-agent developers, and buyers.
- [x] Approved implementation slice 1 shipped: route hardening, health remediation, and manifest contribution metadata.
- [x] Focused Agent Bridge verification passed for audit redaction.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
