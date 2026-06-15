# Automation Studio - Improvement & Growth Plan

> Package: capell-app/automation-studio · Kind: package · Tier: premium · Product group: Capell Automation · Bundle: automation · Status: Active

## 1. Snapshot

Automation Studio provides persisted rule-based workflow orchestration for Capell package events, native actions, Public Actions, contact/newsletter/email integrations, and queued agent capabilities. It owns automation rule/run tables, admin resources, registries, queued trigger jobs, event listeners, native action handlers, dry-run rule testing, structured condition building, admin run replay, screenshot coverage, and focused unit tests. The package has strong domain breadth; remaining roadmap work is Later product depth.

## 2. Improvements (existing functionality)

1. **Implement real health diagnostics.** `AutomationStudioHealthCheck` only reports compatibility. Add checks for tables, admin resource contributions, trigger/action registry definitions, queued job class, default native handlers, and listener registration for installed optional packages. Evidence: `src/Health/AutomationStudioHealthCheck.php`, `src/Providers/AutomationStudioServiceProvider.php`, `src/Actions/RegisterAutomationStudioDefaultsAction.php`. - **M**

2. **Strengthen idempotency persistence.** `QueueAutomationTriggerAction` generates an idempotency key and `DispatchQueuedAutomationTriggerJob` is unique, but persistence should prove repeated jobs/event retries do not create duplicate successful runs or duplicate external side effects. Evidence: `src/Actions/QueueAutomationTriggerAction.php`, `src/Jobs/DispatchQueuedAutomationTriggerJob.php`, `src/Actions/PersistAutomationTriggerResultsAction.php`. - **M**

3. **Add action handler timeout/secret guidance.** Native webhook, email, newsletter, contact, and agent handlers need documented timeout, redaction, and failure behavior. Add tests for failed handlers recording safe context without leaking secrets. Evidence: `src/Support/*AutomationActionHandler*.php`, `DispatchAutomationTriggerAction`. - **M**

4. **Rewrite docs around architecture and optional bridges.** README/overview should explain trigger registry, action registry, persisted rules, queued dispatch, optional package listeners, and what happens when optional packages are missing. Evidence: `README.md`, `docs/automation-studio.md`, provider listener registration. - **S**

## 3. Missing Features (gaps)

Capabilities declared include automation rules, triggers, native actions, persistence, queue, idempotency, optional package bridges, and agent capability queueing.

- **No meaningful health pass/fail.** Marketplace cannot tell whether tables, registries, admin resources, or handlers are available.
- **Done/Shipped: admin replay/retry flow.** Pending, skipped, and failed run rows can be replayed from the Automation Runs table through `ReplayAutomationRunAction`, preserving the original run and writing a new replay attempt.
- **Done/Shipped: rule test mode.** Admins can dry-run active persisted rules against a selected trigger type and sample payload without invoking handlers or recording runs.
- **Done/Shipped: condition/filter builder.** Rule forms now use structured condition rows with field/operator/value, and runtime matching supports equals, does-not-equal, filled, and blank operators while preserving legacy key/value equality rules.

## 4. Issues / Risks

1. **Important gap: health is a placeholder.** Recommended fix: diagnostic checks for core runtime surfaces. - **P2**

2. **Important risk: retries can duplicate external side effects.** Recommended fix: persistence-level idempotency tests and handler contracts for idempotent external calls. - **P2**

3. **Important risk: automation handlers can leak secret context in run records/logs.** Recommended fix: sanitize action context and document redaction rules. - **P2**

4. **Improvement: docs under-explain optional bridge behavior.** Recommended fix: architecture docs for installed/missing optional packages. - **P3**

## 5. Marketplace & Positioning

Automation Studio should be positioned as the workflow fabric for Capell packages. For operators, emphasize rules, run history, retries, and cross-package orchestration. For developers, emphasize typed trigger/action definitions, registries, queued dispatch, and optional bridge listeners.

**Current summary:** "Automation Studio provides rule-based workflow orchestration for Capell package events, native actions, Public Actions, and agent capabilities."

**Improved summary:** "Rule-based workflow automation for Capell package events, with persisted runs, native actions, queued dispatch, optional bridges, and agent capability handoff."

**Media status:** Existing admin screenshots are useful. Add test-mode/replay screenshots when those surfaces ship.

**Cross-sell:** Public Actions, Contacts, Newsletter, Email Studio, Agent Bridge, Campaign Studio, Access Gate, Publishing Studio.

## 6. Prioritized Roadmap

| Item                                                         | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------ | ------ | ------ | ------ | ----------- |
| Add table, registry, admin, queue, and handler diagnostics   | Done   | M      | High   | §2.1, §4.1  |
| Prove retry/idempotency behavior for queued triggers         | Done   | M      | High   | §2.2, §4.2  |
| Add handler timeout/redaction docs and failure tests         | Done   | M      | High   | §2.3, §4.3  |
| Rewrite docs around registries, queues, and optional bridges | Done   | S      | Medium | §2.4, §4.4  |
| Add admin replay/retry workflow                              | Done   | M      | High   | §3          |
| Add dry-run rule test mode                                   | Done   | M      | Medium | §3          |
| Add condition/filter builder                                 | Done   | L      | Medium | §3, §5      |
| Add visual rule builder                                      | Later  | L      | Medium | §5          |
| Add scheduled automation templates                           | Later  | M      | Medium | §5          |

## 7. Verification

Implementation slices shipped the current Now rows. Re-run the package verification with:

```bash
vendor/bin/pest packages/automation-studio/tests --configuration=phpunit.xml
```

For idempotency or queue changes, include:

```bash
vendor/bin/pest packages/automation-studio/tests/Unit/Actions/QueueAutomationTriggerActionTest.php packages/automation-studio/tests/Unit/Jobs/DispatchQueuedAutomationTriggerJobTest.php --configuration=phpunit.xml
```

Replay workflow coverage:

```bash
vendor/bin/pest packages/automation-studio/tests/Unit/Actions/ReplayAutomationRunActionTest.php packages/automation-studio/tests/Unit/AdminSurfaceTest.php --configuration=phpunit.xml
```

Dry-run workflow coverage:

```bash
vendor/bin/pest packages/automation-studio/tests/Unit/Actions/DryRunAutomationRulesActionTest.php packages/automation-studio/tests/Unit/AdminSurfaceTest.php --configuration=phpunit.xml
```

Condition builder coverage:

```bash
vendor/bin/pest packages/automation-studio/tests/Unit/Support/AutomationStudioRegistryTest.php packages/automation-studio/tests/Unit/AdminSurfaceTest.php --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, health, Actions, jobs, registries, listeners, docs, and screenshots.
- [x] Capell audience pass completed for operators, automation authors, and developers.
- [x] Approved implementation slices shipped.
- [x] Focused Automation Studio verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
