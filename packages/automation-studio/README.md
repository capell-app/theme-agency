# Automation Studio

Automation Studio connects Capell package events to reviewed workflow rules, native actions, Public Actions, and agent capability handoffs.

## At A Glance

- Package: `capell-app/automation-studio`
- Namespace: `Capell\AutomationStudio\`
- Surfaces: Filament admin, console/queue runtime
- Service providers: `Capell\AutomationStudio\Providers\AutomationStudioServiceProvider`, `Capell\AutomationStudio\Providers\AdminServiceProvider`
- Capell dependencies: `capell-app/admin`, `capell-app/core`
- Optional integrations: Access Gate, Agent Bridge, Campaign Studio, Contacts, Email Studio, Form Builder, Newsletter, Public Actions, Publishing Studio

## Why It Helps Your Capell Workflow

For teams, Automation Studio gives operators one admin-owned place to describe follow-up work: when a form is submitted, access is approved, a page is published, or a campaign converts, the matching rule can send email, call a webhook, tag a contact, create a note, subscribe a user, queue an agent capability, or delegate to Public Actions.

For developers, the package keeps orchestration out of feature packages. Packages emit their own domain events or call `QueueAutomationTriggerAction`; Automation Studio normalizes the trigger, matches persisted rules, dispatches registered action handlers, and records auditable run rows.

## What It Adds

- Persisted automation rules and run history in `automation_rules` and `automation_runs`.
- Filament resources for editing rules and reviewing read-only run history.
- Typed trigger, rule, action, result, and event data objects.
- Registries for package-owned trigger definitions, action definitions, handlers, and runtime rules.
- Queue-backed trigger dispatch through `DispatchQueuedAutomationTriggerJob`.
- Idempotency keys so repeated delivery updates the same logical run instead of creating duplicate audit records.
- Optional native handlers for Contacts, Newsletter, Email Studio, Agent Bridge, and Public Actions.

## Current Trigger And Action Vocabulary

Triggers:

- `form_submitted`
- `access_approved`
- `page_published`
- `campaign_converted`

Actions:

- `send_email`
- `webhook`
- `tag_contact`
- `create_note`
- `subscribe_user`
- `queue_agent_capability`
- `public_action`

Native handlers are optional-package guarded. When the target package is not installed, the handler reports an unavailable dependency instead of hard-failing the trigger dispatch.

## Developer Deep Dive

Use `AutomationRuleData`, `AutomationRuleActionData`, and `AutomationTriggerEventData` at package boundaries. Do not pass loose arrays from feature packages into handlers.

Dispatch a trigger from package code:

```php
use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;

QueueAutomationTriggerAction::run(new AutomationTriggerEventData(
    triggerType: AutomationTriggerType::FormSubmitted,
    sourceType: 'form-submission',
    sourceId: (string) $submissionId,
    payload: [
        'email' => $email,
        'form_id' => $formId,
    ],
), siteId: $siteId);
```

Implement a custom action handler by registering an `AutomationActionHandler` with `AutomationActionRegistry`. The handler should return `AutomationActionResultData` and leave package-specific writes inside the owning package action boundary.

## Runtime Surface

| Area       | Path                                                                         |
| ---------- | ---------------------------------------------------------------------------- |
| Actions    | `packages/automation-studio/src/Actions`                                     |
| Data       | `packages/automation-studio/src/Data`                                        |
| Enums      | `packages/automation-studio/src/Enums`                                       |
| Jobs       | `packages/automation-studio/src/Jobs/DispatchQueuedAutomationTriggerJob.php` |
| Listeners  | `packages/automation-studio/src/Listeners`                                   |
| Handlers   | `packages/automation-studio/src/Support/Handlers`                            |
| Registries | `packages/automation-studio/src/Support`                                     |
| Models     | `packages/automation-studio/src/Models`                                      |
| Filament   | `packages/automation-studio/src/Filament`                                    |
| Migrations | `packages/automation-studio/database/migrations`                             |
| Tests      | `packages/automation-studio/tests`                                           |

## Boundaries

- Automation Studio owns rule matching, action dispatch, queueing, idempotency, and run audit records.
- Owning packages keep their domain writes. Contacts owns contact tags and notes; Newsletter owns subscribers; Email Studio owns message dispatch; Agent Bridge owns capability execution; Public Actions owns public action destinations.
- Public frontend output is not rendered by Automation Studio. It should not add Blade, authoring markers, editor URLs, model IDs, or package internals to public HTML.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [automation-studio.md](docs/automation-studio.md)
- [screenshots.json](docs/screenshots.json)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/automation-studio/tests --configuration=phpunit.xml
```
