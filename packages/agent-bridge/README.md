# Agent Bridge

<!-- prettier-ignore-start -->

## What This Plugin Adds

Agent Bridge is an **Available**, **Schema-owning** Capell package in the **Capell Operations** product group. It ships as `capell-app/agent-bridge` and extends these surfaces: admin, frontend.

Connect AI agents and MCP clients to Capell with read-only package knowledge, scoped site capabilities, preview-then-confirm execution, and audited operations.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/agent-bridge`
- Namespace: `Capell\AgentBridge`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Let AI agents safely read and operate your Capell site through scoped tokens, preview-then-confirm guardrails, and a full audit trail.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Agent Bridge prompt builder page (admin, required).
- Token management or setup surface (admin, required).
- Capability preview and confirmation flow (admin, required).
- Audit entry review (admin, required).
- Agent Bridge server health output (admin, required).

## Technical Shape

- Service providers: `Capell\AgentBridge\Providers\AgentBridgeServiceProvider`.
- Config files: `packages/agent-bridge/config/capell-agent-bridge.php`.
- Migrations: `packages/agent-bridge/database/migrations/2026_05_10_190840_01_create_capell_agent-bridge_tokens_table.php`, `packages/agent-bridge/database/migrations/2026_05_10_190840_02_create_capell_agent-bridge_confirmations_table.php`, `packages/agent-bridge/database/migrations/2026_05_10_190840_03_create_capell_agent-bridge_audit_entries_table.php`, `packages/agent-bridge/database/migrations/2026_05_27_000001_create_capell_agent-bridge_saved_prompts_table.php`.
- Settings migrations: `packages/agent-bridge/database/settings/2026_05_10_190841_01_add_agent_bridge_settings.php`.
- Settings classes: `AgentBridgeSettings`.
- Models: `CapellAgentBridgeAuditEntry`, `CapellAgentBridgeConfirmation`, `CapellAgentBridgeSavedPrompt`, `CapellAgentBridgeToken`.
- Filament classes: `CapellAgentBridgePromptBuilderPage`, `AgentBridgeAuditEntriesRelationManager`, `AgentBridgeConfirmationsRelationManager`, `AgentBridgeTokensRelationManager`, `AgentBridgeSettingsSchema`.
- Livewire components: `PromptBuilderToolbarAction`.
- Route files: `packages/agent-bridge/routes/agent-bridge.php`.
- Actions: `AuditAgentBridgeCapabilityAction`, `BuildAgentBridgePromptAction`, `ClearCapellCacheCapabilityAction`, `ConfirmAgentBridgeCapabilityAction`, `CreateAgentBridgeTokenAction`, `DeleteAgentBridgePromptAction`, `InvokeAgentBridgeCapabilityPreviewAction`, `CreateDraftPageCapabilityAction`, `DisablePageCapabilityAction`, `InspectPagePublishingReadinessCapabilityAction`, `UpdateDraftPageCapabilityAction`, `PruneAgentBridgeAuditEntriesAction`, `and 4 more`.
- Data objects: `AgentBridgePromptData`, `AuthenticatedAgentBridgeClientData`, `ClearCacheCapabilityInputData`, `CreateDraftPageCapabilityInputData`, `PageIdCapabilityInputData`, `UpdateDraftPageCapabilityInputData`, `CapabilityData`, `CapabilityInvocationData`, `CapabilityResultData`.
- Command signatures: `capell:agent-bridge-prune-audit`.
- Console command classes: `PruneAgentBridgeAuditEntriesCommand`.
- Manifest contributions: `admin-page: Capell\AgentBridge\Manifest\AgentBridgeAdminPageContribution`, `agent-capability: Capell\AgentBridge\Manifest\AgentBridgeBuiltInCapabilitiesContribution`, `console-command: Capell\AgentBridge\Manifest\AgentBridgeConsoleCommandsContribution`, `health-check: Capell\AgentBridge\Health\AgentBridgeHealthCheck`, `migration: Capell\AgentBridge\Manifest\AgentBridgeMigrationsContribution`, `model: Capell\AgentBridge\Manifest\AgentBridgeModelsContribution`, `route: Capell\AgentBridge\Manifest\AgentBridgeRoutesContribution`, `schema-extender: Capell\AgentBridge\Manifest\AgentBridgeUserSchemaExtenderContribution`, `setting: Capell\AgentBridge\Manifest\AgentBridgeSettingsContribution`.
- Health checks: `Capell\AgentBridge\Health\AgentBridgeHealthCheck`.
- Blade views: `packages/agent-bridge/resources/views/filament/pages/prompt-builder.blade.php`, `packages/agent-bridge/resources/views/livewire/prompt-builder-toolbar-action.blade.php`.

## Data Model

- Required tables: `capell_agent_bridge_tokens`, `capell_agent_bridge_confirmations`, `capell_agent_bridge_audit_entries`, `capell_agent_bridge_saved_prompts`.
- Models: `CapellAgentBridgeAuditEntry`, `CapellAgentBridgeConfirmation`, `CapellAgentBridgeSavedPrompt`, `CapellAgentBridgeToken`.
- Migration files: `2026_05_10_190840_01_create_capell_agent-bridge_tokens_table.php`, `2026_05_10_190840_02_create_capell_agent-bridge_confirmations_table.php`, `2026_05_10_190840_03_create_capell_agent-bridge_audit_entries_table.php`, `2026_05_27_000001_create_capell_agent-bridge_saved_prompts_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `agent-bridge.manage-tokens`, `agent-bridge.view-audit`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: `Capell\AgentBridge\Settings\AgentBridgeSettings`.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: `capell:agent-bridge-prune-audit`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/agent-bridge`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Agent Bridge appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/agent-bridge/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
