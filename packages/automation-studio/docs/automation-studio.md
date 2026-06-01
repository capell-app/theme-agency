# Automation Studio Foundation

## Scope

This slice creates the domain foundation, persistence layer, initial admin surface, and queue-backed trigger execution for Automation Studio. The goal is to give Capell packages a typed contract for triggers, rules, actions, persisted rules, audit-ready runs, operator rule management, and idempotent queued execution while avoiding hard dependencies on Public Actions, Form Builder, Campaign Studio, Access Gate, or Publishing Studio.

## Implemented

- `AutomationTriggerType` declares the first trigger vocabulary:
    - `form_submitted`
    - `access_approved`
    - `page_published`
    - `campaign_converted`
- `AutomationActionType` declares the first action vocabulary:
    - `send_email`
    - `webhook`
    - `tag_contact`
    - `create_note`
    - `subscribe_user`
    - `queue_agent_capability`
    - `public_action`
- `AutomationRuleData`, `AutomationRuleActionData`, and `AutomationTriggerEventData` define structured runtime boundaries.
- `AutomationTriggerRegistry`, `AutomationActionRegistry`, and `AutomationRuleRegistry` support package-local registration.
- `DispatchAutomationTriggerAction` executes active matching rules and returns per-action results.
- `DispatchPublicActionAutomationActionHandler` delegates to Public Actions when a rule uses `public_action`.
- Runtime listeners normalize known package events by string class names, so optional packages remain optional.
- Persisted `AutomationRule` and `AutomationRun` models are implemented with migrations, encrypted action/settings/context payloads, site scope, statuses, protected-table registration, and manifest table ownership.
- `LoadPersistedAutomationRulesAction` loads active persisted rules into the runtime registry.
- `RecordAutomationRunAction` records pending, succeeded, and failed automation runs from trigger/action execution context.
- Filament admin resources are implemented for mutable automation rules and read-only automation run history, with manifest contributions and package permissions.
- `QueueAutomationTriggerAction` queues trigger dispatch through `DispatchQueuedAutomationTriggerJob`.
- Queued execution uses stable idempotency keys, unique jobs, retry attempt metadata, and per-action run rows so repeated delivery updates the same run identity instead of creating duplicates.
- The native `webhook` action handler is implemented through Public Actions destinations, so webhook delivery reuses the existing public action destination/security layer instead of adding a second webhook transport.
- Native `tag_contact` and `create_note` action handlers are implemented through Contacts when that package is installed. They resolve contact identity from trigger payload/settings, tag contacts through the Contacts action boundary, and write contact note activities through Contacts.
- The native `subscribe_user` action handler is implemented through Newsletter when that package is installed. It upserts subscribers with consent evidence from trigger payload/settings and can apply newsletter tag IDs.
- The native `send_email` action handler is implemented through Email Studio when that package is installed. It resolves recipients from rule settings or trigger payloads, maps variables and headers into Email Studio DTOs, and returns the queued email message id.
- The native `queue_agent_capability` action handler is implemented through Agent Bridge when that package is installed. It invokes the Agent Bridge capability preview/execution boundary and returns Agent Bridge result or confirmation metadata.

## Deliberate deferrals

- Automation Studio listens for `Capell\CampaignStudio\Events\CampaignConverted` when Campaign Studio is installed. Campaign Studio dispatches that event when a conversion row is newly recorded.

## Next slices

1. Add richer UI affordances for configuring native handler settings without editing JSON manually.
2. Add more package-specific trigger payload enrichers where downstream automations need richer context than IDs.
