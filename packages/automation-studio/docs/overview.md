# Automation Studio Overview

Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Bundle: **automation** · Contexts: **admin, console/queue** · Product group: **Capell Automation**

Automation Studio turns package events into auditable workflow runs. Operators configure rules in Capell admin; developers keep each package's domain behavior in its own Actions and let Automation Studio coordinate cross-package follow-up.

## Non-Technical Overview

Automation Studio is useful when a site needs repeatable follow-up work after customer or editor activity:

- a form submission starts a nurture workflow;
- an access request approval sends a claim email and tags the contact;
- a page publish event queues a downstream notification;
- a campaign conversion records a note and triggers an agent review.

The package gives admins rule and run-history screens so workflow behavior is visible. It does not add public widgets or JavaScript to visitor pages.

## What This Package Adds

- Filament resources for mutable automation rules and read-only automation runs.
- Persisted `automation_rules` and `automation_runs` tables.
- Trigger definitions for form submissions, access approvals, page publishes, and campaign conversions.
- Native action definitions for email, webhooks, contact tags, contact notes, newsletter subscriptions, agent capabilities, and Public Actions.
- Queue-backed dispatch with idempotency metadata.
- Optional-package guarded handlers for Contacts, Newsletter, Email Studio, Agent Bridge, and Public Actions.

## Developer Deep Dive

Use these package entry points rather than calling handler classes directly:

| Need                                              | Entry point                                                               |
| ------------------------------------------------- | ------------------------------------------------------------------------- |
| Queue a package event for automation              | `QueueAutomationTriggerAction::run(AutomationTriggerEventData $event)`    |
| Dispatch a trigger immediately                    | `DispatchAutomationTriggerAction::run(AutomationTriggerEventData $event)` |
| Load persisted active rules into runtime matching | `LoadPersistedAutomationRulesAction::run()`                               |
| Record or update run audit state                  | `RecordAutomationRunAction::run(...)`                                     |
| Register trigger/action defaults                  | `RegisterAutomationStudioDefaultsAction::run()`                           |

Use `AutomationTriggerRegistry`, `AutomationActionRegistry`, and `AutomationRuleRegistry` for extension work. Handler output should be an `AutomationActionResultData` object so the run history can store success, failure, message, and provider metadata consistently.

## Example Trigger Dispatch

```php
use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;

QueueAutomationTriggerAction::run(new AutomationTriggerEventData(
    triggerType: AutomationTriggerType::CampaignConverted,
    sourceType: 'campaign',
    sourceId: (string) $campaign->getKey(),
    payload: [
        'email' => $conversion->email,
        'campaign_id' => $campaign->getKey(),
    ],
), siteId: $campaign->site_id);
```

## Boundaries

- Automation Studio owns orchestration, matching, queueing, idempotency, and audit records.
- Contacts, Newsletter, Email Studio, Agent Bridge, and Public Actions own their domain writes.
- Missing optional integrations should return unavailable action results, not throw unhandled errors from normal rule dispatch.
- The package has no public frontend surface; public-output safety concerns belong to the packages that render the originating page or form.

## Screenshot Plan

`docs/screenshots.json` covers the rules index, rule edit screen, and run history index. Keep those captures aligned with the admin resources declared in `capell.json`.

## Verification

```bash
vendor/bin/pest packages/automation-studio/tests --configuration=phpunit.xml
```
