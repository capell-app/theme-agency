# Automation Studio — Improvement & Growth Plan

> Package: capell-app/automation-studio · Kind: package · Tier: premium · Product group: Capell Automation · Bundle: automation · Status: Draft

## 1. Snapshot

Automation Studio is a rule-based workflow engine that maps Capell package events (form submitted, access approved, page published, campaign converted) to native actions (send email, webhook, tag contact, create note, subscribe user, queue agent capability, run Public Action). Surfaces are `admin` and `console` (`capell.json`), though no console commands actually ship. Core domain lives in `src/Actions` (`DispatchAutomationTriggerAction`, `QueueAutomationTriggerAction`, `DispatchQueuedAutomationTriggerJob`, `RecordAutomationRunAction`, `PersistAutomationTriggerResultsAction`, `LoadPersistedAutomationRulesAction`, `RegisterAutomationStudioDefaultsAction`), with `AutomationRule`/`AutomationRun` models (tables `automation_rules`, `automation_runs`, both protected, payloads `encrypted:array`), three singleton registries (`AutomationRuleRegistry`, `AutomationActionRegistry`, `AutomationTriggerRegistry`), seven `AutomationActionHandler` implementations, four event listeners, and two Filament resources (mutable rules, read-only run history). Deps: `requires` `capell-app/admin` + `capell-app/core`; `supports` access-gate, agent-bridge, campaign-studio, contacts, email-studio, form-builder, newsletter, public-actions, publishing-studio. Marketplace summary (verbatim): _"Automation Studio connects Capell package events to rule-based native actions, Public Actions, and agent capability workflows."_ Screenshot count: **0**.

## 2. Improvements (existing functionality)

- **[CRITICAL] Route event listeners through the queue path, not synchronous dispatch** — All four listeners call `DispatchAutomationTriggerAction->handle()` inline (in the request/event thread), so `QueueAutomationTriggerAction`, `DispatchQueuedAutomationTriggerJob`, `RecordAutomationRunAction`, and `PersistAutomationTriggerResultsAction` are **never reached from production triggers** (`grep` confirms `QueueAutomationTriggerAction` is referenced only by its own file + test). Listeners should build the `AutomationTriggerEventData` and call `QueueAutomationTriggerAction::run(...)`. — `src/Listeners/DispatchAutomationFrom*.php` (all four), `src/Actions/QueueAutomationTriggerAction.php` — **M**

- **[CRITICAL] Run history is never written from real triggers** — Because the sync path skips `PersistAutomationTriggerResultsAction`, the `automation_runs` table stays empty in normal operation; the read-only "Runs" Filament resource (`AutomationRunResource`) shows nothing despite the manifest advertising `automation-persistence`. Fixing the wiring above resolves this, but add a synchronous-fallback persistence call for the non-queued code path too. — `src/Filament/Resources/AutomationRuns/AutomationRunResource.php`, `src/Actions/DispatchAutomationTriggerAction.php` — **M**

- **Conditions are exact-equality on flat keys only** — `AutomationRuleRegistry::matches()` does `Arr::get($payload, $key) !== $expectedValue`. No operators (`!=`, `>`, `contains`, `in`, `exists`), no AND/OR groups, no type coercion (a numeric `5` form value never equals string `"5"` from the `KeyValue` admin field). This is the single biggest functional limiter for buyers. Introduce a small condition DSL / `AutomationConditionData` evaluated by a dedicated `EvaluateAutomationConditionsAction`. — `src/Support/AutomationRuleRegistry.php`, `src/Data/AutomationRuleData.php` — **L**

- **Admin action/condition config is raw `KeyValue` JSON** — `AutomationRuleResource` exposes `conditions` and per-action `settings` as untyped `KeyValue` maps; operators must know each handler's magic keys (`email_template_key`, `public_action_key`, `newsletter_email`, etc.) by memory. Make the action `Repeater`'s settings schema reactive on the selected `type` (Filament `Get`/`Set`), rendering typed fields per action. This is literally listed as docs "Next slice #1". — `src/Filament/Resources/AutomationRules/AutomationRuleResource.php` — **L**

- **Idempotency key over-includes `occurredAt`** — `QueueAutomationTriggerAction::idempotencyKey()` hashes `occurred_at` into the digest, so two physically identical deliveries with different timestamps dedupe as _different_ runs, weakening the uniqueness guarantee the manifest claims (`automation-idempotency`). Prefer hashing `(siteId, triggerType, sourceType, sourceId)` plus an explicit caller-supplied dedup token; drop `occurredAt` unless a caller opts in. — `src/Actions/QueueAutomationTriggerAction.php` — **S**

- **No `Skipped` run status is ever recorded** — `AutomationRunStatus::Skipped` exists and is indexed, but `RecordAutomationRunAction` only writes `Pending`/`Succeeded`/`Failed`. Rules that match-but-no-op (e.g. condition filtered, handler intentionally inert) leave no audit trail. Record `Skipped` when a rule matches the trigger type but conditions exclude it, so operators can see "why didn't this fire". — `src/Actions/RecordAutomationRunAction.php`, `src/Support/AutomationRuleRegistry.php` — **M**

- **`LoadPersistedAutomationRulesAction` reloads + re-registers on every job** — The queued job calls `LoadPersistedAutomationRulesAction->handle()` each run, doing an unindexed-on-`status` query then hydrating every active rule into the singleton registry. Under burst load this is N queries for N events. Cache the compiled rule set per `(siteId, triggerType)` with tag `automation-studio` (already a declared `cacheTag`) and invalidate on `AutomationRule` save. — `src/Actions/LoadPersistedAutomationRulesAction.php` — **M**

- **Inconsistent optional-event references in `RegisterAutomationStudioDefaultsAction`** — Three triggers reference optional event classes directly (`RegistrationApproved::class`, `WorkspaceStateChanged::class`, `CampaignConverted::class` via `use` imports) while `FormSubmitted` uses a string `implode('\\', [...])`. `::class` is compile-time and won't autoload, so it's not a crash today, but it is fragile (any future `instanceof`/`is_a`/static analysis tooling resolving these will break when the optional package is absent) and stylistically diverges from the deliberately-guarded `ServiceProvider::listenIfClassExists()`. Use string class names consistently for optional packages. — `src/Actions/RegisterAutomationStudioDefaultsAction.php` — **S**

- **`AutomationRun` payload/context are `encrypted:array` but also `searchable()` in admin** — `AutomationRunResource` marks `rule_key`, `action_key`, `idempotency_key` searchable (fine, plaintext columns), but encrypted `payload`/`context` can never be queried/filtered server-side and bloat row size (`longText` + encryption). Confirm encryption is required for `payload` (it can contain PII from form submissions — likely yes) but consider a non-encrypted, redacted summary column for filtering/observability. — `src/Models/AutomationRun.php` — **M**

- **No per-run detail / infolist view** — The Runs resource is a flat table with `index` page only; operators cannot open a run to inspect the full message/context/payload or the originating rule. Add a read-only `ViewAutomationRun` page with an Infolist. — `src/Filament/Resources/AutomationRuns/` — **S**

## 3. Missing Features (gaps)

Tied to declared `capabilities[]` and workflow-automation norms.

**Table-stakes gaps (expected of any "Automation Studio"):**

- **Delays / scheduling** — No delay, no "wait N minutes/days", no scheduled/cron triggers. The job has no `delay()` support and there is no `time`-based trigger type. Buyers expect "tag contact, wait 2 days, send follow-up".
- **Branching / multi-step sequences** — `AutomationRuleData::$actions` is a flat list executed top-to-bottom regardless of prior results. No conditional branches, no "stop on failure", no step-output → next-step-input passing.
- **Manual retry / replay from admin** — `automation_runs` records attempts, but there is no admin action to retry a failed run or replay a trigger. Retries are only the queue's automatic `tries = 3`.
- **Rule-level enable/disable toggle in the table** — Status is editable only via the full edit form; no inline `ToggleColumn` or pause/resume row action.
- **Webhook signing / retry policy surfaced** — Webhook delegates entirely to Public Actions destinations (good reuse), but there's no Automation-Studio-level retry/backoff config or signing-secret affordance visible to the rule author.
- **Condition operators & grouping** — see §2; equality-only is below the bar set by Zapier/Make/n8n-style tools.
- **Audit log of rule edits** — `automation_runs` audits _executions_, but there's no audit of _who changed a rule_ (Capell has `login-audit`; a similar admin-event hook is missing here despite `AdminEventRegistry` being available).

**Capability claims not backed by shipping code:**

- **`console` surface / `automation-queue`** — `capell.json` lists `surfaces: ["console"]` and capability `automation-queue`, but there are **no Console commands** (no `src/Console`, no `Commands` registered) and the queue path is unreachable from triggers (§2). A `automation:dispatch`, `automation:replay {run}`, and `automation:prune-runs` command set would make `console` truthful.
- **Run retention / pruning** — Encrypted `automation_runs` grows unbounded; no scheduled prune command or retention setting. No `config/automation-studio.php` exists at all.

**Differentiators (where this package can win vs table-stakes):**

- **Agent capability orchestration** (`queue_agent_capability` via Agent Bridge) and **Public Action reuse** for webhooks are genuinely differentiated — most CMS automation tools don't pipe events into AI-agent capabilities. Lean into this in marketing and build first-class multi-step "event → agent capability → email" recipes/templates.
- **Native, optional-package-guarded handlers** (Email Studio, Newsletter, Contacts) mean automations work across the Capell suite without glue code — a strong cross-sell story.

## 4. Issues / Risks

- **[High] Dead queue/persistence/idempotency path (correctness + false advertising)** — Confirmed by grep: `QueueAutomationTriggerAction`, `DispatchQueuedAutomationTriggerJob`, `RecordAutomationRunAction`, `PersistAutomationTriggerResultsAction` are unreachable from the four production listeners, which dispatch synchronously. Result: no run history, no idempotency, no queueing in real use; the `automation-queue`, `automation-idempotency`, and `automation-persistence` capabilities and `queueInvalidation: true` in the manifest are unmet. — `src/Listeners/DispatchAutomationFrom*.php` — **fix = §2 item 1**

- **[High] Synchronous handlers run inside the originating request** — Because dispatch is inline, a slow handler (SendEmail, Webhook → external HTTP, QueueAgentCapability) blocks the form submission / publish / approval request that fired the event. A failing external webhook can degrade the public form-submit UX. `frontendRenderBudgetMs: 0` in the manifest is effectively violated whenever a form-submit trigger fires a network action synchronously. — `src/Listeners/DispatchAutomationFromFormSubmission.php` — **M**

- **[High] Test coverage gaps on the most failure-prone code** — `tests/` is **Unit-only**; there is **no Feature, no Integration, no Arch** test directory (verified). Specifically untested:
    - `DispatchQueuedAutomationTriggerJob` — no test for `ShouldBeUnique`/`uniqueId()`/`uniqueFor`, `tries`, attempt metadata, or that it persists per-action runs.
    - `RecordAutomationRunAction` — no test for `updateOrCreate` idempotent upsert vs `create`, status derivation, `Skipped` (which it can't even produce).
    - `PersistAutomationTriggerResultsAction` — no test for the `idempotencyKey:rule:action` composition or rule/action resolution from context.
    - `LoadPersistedAutomationRulesAction` — no test for site-scope filtering (`whereNull('site_id') OR site_id = ?`).
    - Listeners: only `FormSubmission` and `CampaignConversion` have tests; **`AccessApproval` and `WorkspaceStateChanged` listeners are untested**.
    - No public-output / anonymous-safety test (see next item).
      — `tests/Unit/...` — **L**

- **[Medium] No public-output safety test despite Capell rule** — Capell mandates tests proving anonymous/non-admin output never leaks model IDs, field paths, package names, or internals. Automation Studio has no public surface today (admin/console only) so leakage risk is low, but the handlers embed `rule_key`/`action_key`/`error_type`/exception messages into `AutomationRun.context`/`message`. If any future surface (or an email/webhook body built from `payload`) renders these, internals leak. Add an arch/assertion test that handler result `message`/`context` written to outbound channels are scrubbed of class names and keys. — `src/Actions/DispatchAutomationTriggerAction.php` (embeds `error_type => $exception::class`) — **M**

- **[Medium] Exception messages persisted verbatim** — `DispatchAutomationTriggerAction` stores `$exception->getMessage()` and `$exception::class` into the run's `message`/`context`. Raw exception text can contain connection strings, file paths, or third-party API errors. These are encrypted at rest (good) but are surfaced in the admin `message` column unredacted. Wrap in a sanitized operator message + log the raw detail to the channel logger. — `src/Actions/DispatchAutomationTriggerAction.php` — **S**

- **[Medium] Unbounded `automation_runs` growth** — Every action of every matching rule writes an encrypted `longText` row; no retention/pruning (no config, no scheduled command). On a busy form, this table balloons. — `database/migrations/...02_create_automation_runs_table.php` — **M**

- **[Medium] `adminQueryBudget: 20` unverified; `withCount('runs')` on list** — `AutomationRuleResource::getEloquentQuery()` adds `withCount('runs')`; with the `runs_count` column sortable this is fine, but there's no test asserting the rules/runs list stays within the declared 20-query budget. — `capell.json`, `src/Filament/Resources/AutomationRules/AutomationRuleResource.php` — **S**

- **[Low] No config file at all** — `config/` is empty; queue connection/name, retry count (`tries` hard-coded to 3), `uniqueFor` (3600), and run retention are not operator-configurable. — package root — **S**

- **[Low] i18n: hard-coded English fallbacks scattered** — `RegisterAutomationStudioDefaultsAction`, `DispatchAutomationTriggerAction`, and handlers each carry `ucwords(str_replace('_',' ', ...))` / `sprintf('No Automation Studio handler...')` fallbacks for the no-translator case. Consistent and defensible, but duplicated; centralize the "translator-or-fallback" helper. — `src/Actions/*`, `src/Support/Handlers/*` — **S**

- **[Low] `php` constraint mismatch** — `composer.json` requires `php: ^8.3` but Capell/skill targets PHP 8.4; `AutomationStudioHealthCheck` only asserts API version, not PHP. Minor, but align. — `composer.json` — **S**

## 5. Marketplace & Selling

**Critique of current copy.** The marketplace `summary` and composer `description` are near-duplicates ("connects Capell package events to rule-based native actions, Public Actions, and agent capability workflows" / "Rule-based workflow orchestration for Capell CMS package events and actions"). Both are accurate but feature-list-flavored and inward-facing — they name internal concepts ("native actions", "Public Actions") rather than the buyer outcome, and bury the genuinely differentiated hook (turning CMS events into **AI agent** workflows). Neither mentions audit trail, idempotency, or no-code rule building, which are the trust signals automation buyers look for.

**Improved 1-sentence summary:**

> Turn any Capell event — a form submission, a new member, a published page — into automated follow-ups: send emails, tag contacts, fire webhooks, or hand off to AI agents, all from no-code rules with a full execution audit trail.

**Improved 3–4 sentence description:**

> Automation Studio is the workflow engine for Capell CMS. Build rules in the admin that listen for events across your installed packages — Form Builder, Access Gate, Campaign Studio, Publishing Studio — and react with native actions: send a templated email via Email Studio, subscribe the contact to a Newsletter, tag them in Contacts, deliver a signed webhook through Public Actions, or queue an AI capability via Agent Bridge. Every execution is recorded as an idempotent, queue-backed run with attempt tracking, so you get a complete, replayable audit log instead of fire-and-forget side effects. Handlers degrade gracefully when an optional package isn't installed, so automations stay safe as your stack grows.

(Note: the description above describes the _intended_ idempotent/queue-backed/audited behavior — ship the §2 fixes before publishing these claims, or the copy oversells the current build.)

**Screenshot / media gaps.** `screenshots: []` — zero. Minimum set to ship: (1) the rule list table with trigger/status/runs badges, (2) the rule edit form showing trigger + reactive action settings, (3) the run-history table with succeeded/failed badges, (4) a single run detail/infolist, (5) an animated GIF of "form submitted → run appears → email sent". Media is the #1 conversion lever for a paid premium package currently showing none.

**Pricing / tier / bundle positioning.** Correctly `premium` tier, `automation` bundle, `paid` license, `first-party` certification requested. Position as the **anchor of the automation bundle** and a cross-sell magnet: it has zero hard runtime deps beyond core/admin, yet unlocks value from nine `supports` packages. Recommended cross-sell pairings via deps + Extension Suites:

- **+ Email Studio + Newsletter + Contacts** → "Lifecycle automation suite" (lead capture → nurture).
- **+ Agent Bridge + AI Orchestrator** → "AI automation suite" (the differentiator).
- **+ Campaign Studio + Form Builder** → "Conversion automation suite".
  Bundle discount these so Automation Studio drives attach revenue on the dependency packages.

**Differentiators / value props / target buyer.**

- _Differentiators:_ AI-agent capability as a first-class action; webhook reuse of the hardened Public Actions destination layer (not a second transport); optional-package-guarded handlers that never hard-fail.
- _Value props:_ no-code rules, cross-package reach, audited/idempotent execution (once §2 lands).
- _Target buyer:_ agencies and in-house teams running multi-package Capell sites who want event-driven side effects (notifications, CRM sync, AI hand-offs) without writing listeners/jobs.

**Keywords/tags (8–12):** `workflow automation`, `event-driven`, `triggers and actions`, `no-code rules`, `webhooks`, `email automation`, `contact tagging`, `newsletter automation`, `AI agent automation`, `audit log`, `idempotent jobs`, `Filament admin`.

## 6. Prioritized Roadmap

| Item                                                                                            | Bucket | Effort | Impact   | Section ref |
| ----------------------------------------------------------------------------------------------- | ------ | ------ | -------- | ----------- |
| Route listeners through `QueueAutomationTriggerAction` (queue + persist + idempotency live)     | Now    | M      | Critical | §2, §4      |
| Persist runs on the (fallback) sync path so admin Runs history is populated                     | Now    | M      | Critical | §2, §4      |
| Move external/slow handlers off the request thread (don't block form-submit)                    | Now    | M      | High     | §4          |
| Add Feature/Integration tests for job uniqueness, run upsert, persist, load, untested listeners | Now    | L      | High     | §4          |
| Consistent string class refs for optional events in RegisterDefaults                            | Now    | S      | Med      | §2, §4      |
| Sanitize/redact exception messages before persisting to runs                                    | Now    | S      | Med      | §4          |
| Condition operators + AND/OR groups (`EvaluateAutomationConditionsAction`)                      | Next   | L      | High     | §2, §3      |
| Reactive per-action settings schema in admin (typed fields by action type)                      | Next   | L      | High     | §2, §3      |
| Run detail/infolist page + inline retry/replay action                                           | Next   | M      | High     | §3          |
| Add `config/automation-studio.php` (queue, tries, uniqueFor, retention)                         | Next   | S      | Med      | §4          |
| Console commands (`automation:replay`, `:prune-runs`) to make `console` surface real            | Next   | M      | Med      | §3          |
| Record `Skipped` runs for matched-but-filtered rules                                            | Next   | M      | Med      | §2          |
| Capture 5 marketplace screenshots + GIF; rewrite summary/description                            | Next   | S      | High     | §5          |
| Cache compiled active-rule set per (site, trigger) with `automation-studio` tag                 | Later  | M      | Med      | §2          |
| Delays / scheduled triggers + multi-step branching (sequence engine)                            | Later  | L      | High     | §3          |
| Rule-edit audit log via `AdminEventRegistry`                                                    | Later  | M      | Med      | §3          |
| Cross-sell bundles (Lifecycle / AI / Conversion automation suites)                              | Later  | S      | Med      | §5          |
