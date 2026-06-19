# Live Chat

<!-- prettier-ignore-start -->

## What This Plugin Adds

Live Chat is an **Available**, **Schema-owning** Capell package in the **Capell Growth** product group. It ships as `capell-app/live-chat` and extends these surfaces: admin, frontend.

Live Chat adds an AI first website chat widget with message-first and details-first flows, office-hours routing, human handoff, contact capture, and Contacts CRM sync.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: growth-product
- Composer package: `capell-app/live-chat`
- Namespace: `Capell\LiveChat`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** AI first website chat for Capell with message-first capture, details-first enquiries, after-hours replies, human handoff, and Contacts CRM sync.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Live Chat widget (frontend, required).
- Live Chat conversation inbox (admin, required).

## Technical Shape

- Service providers: `Capell\LiveChat\Providers\LiveChatServiceProvider`.
- Config files: `packages/live-chat/config/capell-live-chat.php`.
- Migrations: `packages/live-chat/database/migrations/2026_06_13_000001_create_live_chat_conversations_table.php`, `packages/live-chat/database/migrations/2026_06_13_000002_create_live_chat_messages_table.php`, `packages/live-chat/database/migrations/2026_06_13_000003_create_live_chat_availability_tables.php`, `packages/live-chat/database/migrations/2026_06_13_000004_create_live_chat_rules_and_sources_table.php`, `packages/live-chat/database/migrations/2026_06_13_000005_create_live_chat_installations_table.php`, `packages/live-chat/database/migrations/2026_06_13_000006_create_live_chat_ai_source_tables.php`.
- Models: `LiveChatAIRun`, `LiveChatAvailabilityException`, `LiveChatAvailabilityWindow`, `LiveChatConversation`, `LiveChatEscalationRule`, `LiveChatInstallation`, `LiveChatKnowledgeDocument`, `LiveChatKnowledgeGap`, `LiveChatKnowledgeSource`, `LiveChatMessage`.
- Filament classes: `AvailabilityWindowResource`, `CreateAvailabilityWindow`, `EditAvailabilityWindow`, `ListAvailabilityWindows`, `ScopesLiveChatResourcesToSites`, `ConversationResource`, `EditConversation`, `ListConversations`, `EscalationRuleResource`, `CreateEscalationRule`, `EditEscalationRule`, `ListEscalationRules`, `and 8 more`.
- Route files: `packages/live-chat/routes/web.php`.
- Policies: `AbstractLiveChatResourcePolicy`, `LiveChatAIRunPolicy`, `LiveChatAvailabilityExceptionPolicy`, `LiveChatAvailabilityWindowPolicy`, `LiveChatConversationPolicy`, `LiveChatEscalationRulePolicy`, `LiveChatInstallationPolicy`, `LiveChatKnowledgeDocumentPolicy`, `LiveChatKnowledgeGapPolicy`, `LiveChatKnowledgeSourcePolicy`.
- Actions: `ApplyLiveChatCorsHeadersAction`, `BuildLiveChatAnalyticsAction`, `BuildLiveChatOperatorStateAction`, `BuildLiveChatSuggestedReplyAction`, `BuildLiveChatTranscriptAction`, `BuildLiveChatWidgetConfigAction`, `CloseLiveChatConversationAction`, `DeleteLiveChatAttachmentsAction`, `DetectLiveChatIntentAction`, `DetermineLiveChatEscalationAction`, `GenerateLiveChatSummaryAction`, `GuardLiveChatInstallationOriginAction`, `and 17 more`.
- Data objects: `IncomingLiveChatMessageData`, `LiveChatAIRunData`, `LiveChatAvailabilityData`, `LiveChatEscalationDecisionData`, `LiveChatKnowledgeDocumentData`, `LiveChatKnowledgeSearchResultData`, `LiveChatResponseData`, `LiveChatVisitorData`, `LiveChatWidgetConfigData`.
- Manifest contributions: `admin-resource: Capell\LiveChat\Manifest\LiveChatAdminResourcesContribution`, `agent-capability: Capell\LiveChat\Manifest\LiveChatAgentBridgeCapabilitiesContribution`, `frontend-component: Capell\LiveChat\Manifest\LiveChatWidgetContribution`, `health-check: Capell\LiveChat\Manifest\LiveChatHealthContribution`, `model: Capell\LiveChat\Manifest\LiveChatModelsContribution`, `route: Capell\LiveChat\Manifest\LiveChatFrontendRoutesContribution`.
- Health checks: `Capell\LiveChat\Health\LiveChatHealthCheck`.
- Blade views: `packages/live-chat/resources/views/script.blade.php`, `packages/live-chat/resources/views/widget.blade.php`.
- Cache tags: `live-chat`.

## Data Model

- Required tables: `live_chat_conversations`, `live_chat_installations`, `live_chat_messages`, `live_chat_ai_runs`, `live_chat_availability_windows`, `live_chat_availability_exceptions`, `live_chat_escalation_rules`, `live_chat_knowledge_documents`, `live_chat_knowledge_gaps`, `live_chat_knowledge_sources`.
- Models: `LiveChatAIRun`, `LiveChatAvailabilityException`, `LiveChatAvailabilityWindow`, `LiveChatConversation`, `LiveChatEscalationRule`, `LiveChatInstallation`, `LiveChatKnowledgeDocument`, `LiveChatKnowledgeGap`, `LiveChatKnowledgeSource`, `LiveChatMessage`.
- Migration files: `2026_06_13_000001_create_live_chat_conversations_table.php`, `2026_06_13_000002_create_live_chat_messages_table.php`, `2026_06_13_000003_create_live_chat_availability_tables.php`, `2026_06_13_000004_create_live_chat_rules_and_sources_table.php`, `2026_06_13_000005_create_live_chat_installations_table.php`, `2026_06_13_000006_create_live_chat_ai_source_tables.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:LiveChatInstallation`, `Create:LiveChatInstallation`, `Update:LiveChatInstallation`, `Delete:LiveChatInstallation`, `View:LiveChatAIRun`, `View:LiveChatKnowledgeDocument`, `Create:LiveChatKnowledgeDocument`, `Update:LiveChatKnowledgeDocument`, `Delete:LiveChatKnowledgeDocument`, `View:LiveChatKnowledgeGap`, `Update:LiveChatKnowledgeGap`, `View:LiveChatConversation`, `Update:LiveChatConversation`, `View:LiveChatAvailabilityWindow`, `Create:LiveChatAvailabilityWindow`, `Update:LiveChatAvailabilityWindow`, `Delete:LiveChatAvailabilityWindow`, `View:LiveChatEscalationRule`, `Create:LiveChatEscalationRule`, `Update:LiveChatEscalationRule`, `Delete:LiveChatEscalationRule`, `View:LiveChatKnowledgeSource`, `Create:LiveChatKnowledgeSource`, `Update:LiveChatKnowledgeSource`, `Delete:LiveChatKnowledgeSource`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `live-chat`.
- Commands: none declared.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/live-chat`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Live Chat appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Contacts](../../contacts/README.md), [Agent Bridge](../../agent-bridge/README.md), [Ai Orchestrator](../../ai-orchestrator/README.md), [Email Studio](../../email-studio/README.md), [Knowledge Base](../../knowledge-base/README.md), [Privacy Center](../../privacy-center/README.md).
- Focused tests: `vendor/bin/pest packages/live-chat/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
