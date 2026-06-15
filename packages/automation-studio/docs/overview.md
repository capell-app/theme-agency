# Automation Studio

<!-- prettier-ignore-start -->

## What This Plugin Adds

Automation Studio is an **Available**, **Schema-owning** Capell package in the **Capell Automation** product group. It ships as `capell-app/automation-studio` and extends these surfaces: admin, console.

Automation Studio provides rule-based workflow orchestration for Capell package events, native actions, Public Actions, and agent capabilities.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: automation
- Composer package: `capell-app/automation-studio`
- Namespace: `Capell\AutomationStudio`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, and Filament classes instead of pushing this behaviour into core or application code.

**For teams:** Automation Studio connects Capell package events to rule-based native actions, Public Actions, and agent capability workflows. Operators can replay pending, skipped, or failed run actions from the run history without overwriting the original audit row.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Automation rules admin index (admin, required).
- Automation rule edit screen (admin, required).
- Automation runs admin index (admin, required).
- Replay action on pending, skipped, or failed automation runs (admin).

## Technical Shape

- Service providers: `Capell\AutomationStudio\Providers\AutomationStudioServiceProvider`, `Capell\AutomationStudio\Providers\AdminServiceProvider`.
- Migrations: `packages/automation-studio/database/migrations/2026_05_31_170000_01_create_automation_rules_table.php`, `packages/automation-studio/database/migrations/2026_05_31_170000_02_create_automation_runs_table.php`.
- Models: `AutomationRule`, `AutomationRun`.
- Filament classes: `AutomationRuleResource`, `CreateAutomationRule`, `EditAutomationRule`, `ListAutomationRules`, `AutomationRunResource`, `ListAutomationRuns`.
- Listeners: `DispatchAutomationFromAccessApproval`, `DispatchAutomationFromCampaignConversion`, `DispatchAutomationFromFormSubmission`, `DispatchAutomationFromWorkspaceStateChanged`.
- Actions: `DispatchAutomationTriggerAction`, `LoadPersistedAutomationRulesAction`, `PersistAutomationTriggerResultsAction`, `QueueAutomationTriggerAction`, `RecordAutomationRunAction`, `RegisterAutomationStudioDefaultsAction`, `ReplayAutomationRunAction`.
- Data objects: `AutomationActionDefinitionData`, `AutomationActionResultData`, `AutomationRuleActionData`, `AutomationRuleData`, `AutomationTriggerDefinitionData`, `AutomationTriggerEventData`.
- Jobs: `DispatchQueuedAutomationTriggerJob`.
- Manifest contributions: `admin-resource: Capell\AutomationStudio\Manifest\AutomationRuleResourceContribution`, `admin-resource: Capell\AutomationStudio\Manifest\AutomationRunResourceContribution`, `model: Capell\AutomationStudio\Manifest\AutomationStudioModelsContribution`.
- Health checks: `Capell\AutomationStudio\Health\AutomationStudioHealthCheck`.
- Cache tags: `automation-studio`.

## Data Model

- Required tables: `automation_rules`, `automation_runs`.
- Models: `AutomationRule`, `AutomationRun`.
- Migration files: `2026_05_31_170000_01_create_automation_rules_table.php`, `2026_05_31_170000_02_create_automation_runs_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:AutomationRule`, `Create:AutomationRule`, `Update:AutomationRule`, `Delete:AutomationRule`, `View:AutomationRun`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: review package jobs or schedules before install.
- Cache tags: `automation-studio`.
- Commands: none declared.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |

## Handler Failure Safety

Automation Studio delegates native action delivery to the owning packages. Public Actions, Email Studio, Agent Bridge, Contacts, and Newsletter own their transport timeout and retry settings. Automation Studio records safe run results only: downstream exceptions become a translated handler failure with `handler_failed` context, while raw exception messages, exception classes, API tokens, provider payloads, and transport details stay out of admin-visible run history.

## Replay Safety

The Automation Runs table exposes a confirmed Replay action for pending, skipped, and failed action rows. Replay rebuilds the original trigger event from the persisted run payload, executes only the original rule action, and writes a new run row with a replay-specific idempotency key so the original audit record remains intact.

## Quick Start

1. Install the package: `composer require capell-app/automation-studio`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Automation Studio appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Access Gate](../../access-gate/README.md), [Agent Bridge](../../agent-bridge/README.md), [Campaign Studio](../../campaign-studio/README.md), [Contacts](../../contacts/README.md), [Email Studio](../../email-studio/README.md), [Form Builder](../../form-builder/README.md), [Newsletter](../../newsletter/README.md), [Public Actions](../../public-actions/README.md), [Publishing Studio](../../publishing-studio/README.md).
- Focused tests: `vendor/bin/pest packages/automation-studio/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
