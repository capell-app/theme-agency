# Automation Studio Foundation

## Scope

This slice creates the domain foundation for Automation Studio without adding persistence, admin UI, or queue workers yet. The goal is to give Capell packages a typed contract for triggers, rules, and actions while avoiding edits to Public Actions, Form Builder, Campaign Studio, Access Gate, or Publishing Studio.

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

## Deliberate deferrals

- No rule models or migrations yet. Persisted rules need admin UX decisions around condition builders, per-site scoping, secrets, retries, and audit trails.
- No email, note, tag, subscribe, or agent worker implementations yet. Those need package-specific ownership and idempotency contracts.
- Campaign Studio does not currently expose a conversion event class. Automation Studio registers a listener for `Capell\CampaignStudio\Events\CampaignConverted` if that event is added later.

## Next slices

1. Add persisted `AutomationRule` and `AutomationRun` models with encrypted action settings, status, site scope, and indexes for trigger type and status.
2. Build a small Filament rule resource with trigger/action selectors backed by the registries.
3. Add queue-backed execution with idempotency keys, retry metadata, and per-action audit rows.
4. Implement first native action handlers:
    - webhook through Public Actions destinations,
    - send email through Email Studio,
    - tag contact through Tags or Newsletter where installed,
    - create note through Notes,
    - queue agent capability through Agent Bridge.
5. Add package integration events where missing, starting with Campaign Studio conversion recording.
