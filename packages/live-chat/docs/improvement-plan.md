# Live Chat - Improvement & Growth Plan

> Package: capell-app/live-chat · Kind: package · Tier: premium · Product group: Capell Growth · Bundle: growth-product · Status: Active

## 1. Snapshot

Live Chat is a premium Capell Growth package that adds a public website chat widget plus admin-managed conversation, routing, availability, installation, and knowledge-source surfaces. It owns ten tables (`live_chat_conversations`, `live_chat_messages`, `live_chat_installations`, `live_chat_ai_runs`, availability windows/exceptions, escalation rules, knowledge sources/documents/gaps), public frontend routes under the configurable `live-chat` prefix, and Filament resources for installations, conversations, availability windows, escalation rules, and knowledge sources. The public widget can run as a same-site render hook or as an external embed by public key, with origin allow-listing for the public-key API path. Domain behavior is mostly Action-backed: start/store message, handoff, origin guard, availability resolution, intent/escalation, AI reply/audit, knowledge search/indexing, transcript/summaries, Contacts sync, Agent Bridge capability exposure, and AI Orchestrator module registration. Marketplace media currently promotes only the static extension card while `docs/screenshots.json` declares real widget and admin conversation captures that still need runner output.

## 2. Improvements (existing functionality)

1. **Done/Shipped: harden same-host public routes before further product work.** Same-site `/live-chat/conversations`, `/live-chat/conversations/{conversation}/messages`, and `/live-chat/conversations/{conversation}/handoff` writes now resolve an active `LiveChatInstallation`, require same-site `Origin`/`Referer`, and enforce visitor-token continuity through the same installation-scoped resolver as external embeds. Evidence: `GuardLiveChatSameSiteRequestAction`, `StoreLiveChatConversationController`, `StoreLiveChatMessageController`, `RequestLiveChatHandoffController`, `LiveChatPublicRoutesTest`. - **M**

2. **Done/Shipped: stop returning internal message IDs to the browser.** Public conversation and message JSON responses no longer include `messages[].id`; focused public-route tests assert the response shape stays free of message primary keys. Evidence: `StoreLiveChatConversationController::messagePayload()`, `StoreLiveChatMessageController::messagePayload()`, `LiveChatPublicRoutesTest`. - **M**

3. **Done/Shipped: wrap conversation writes, assistant reply, contact sync, and attachment storage in a failure-safe boundary.** Conversation/message/assistant persistence now runs inside transactions; responder failures create a human-handoff fallback assistant message; Contacts sync runs after the chat write and logs failures; stored attachments are deleted when downstream conversation/message creation fails. Evidence: `DeleteLiveChatAttachmentsAction`, `StartLiveChatConversationAction`, `StoreLiveChatMessageAction`, public-route and conversation-flow tests. - **M**

4. **Done/Shipped: make `LiveChatHealthCheck` match its critical label.** The check now verifies required tables, morph aliases, declared Actions, public route names, admin resource classes, widget views, widget renderer binding, and the public render-hook class. Manifest coverage pins the expanded health probes. Evidence: `src/Health/LiveChatHealthCheck.php`, `capell.json healthChecks[0]`, `LiveChatManifestTest`. - **S**

5. **Done/Shipped: add a package README and docs index in the standard shape.** The package now has a root README and docs index covering package impact, public route boundaries, data safety, related packages, screenshot contract, and focused verification commands. Evidence: `README.md`, `docs/README.md`. - **S**

6. **Done/Shipped: make public route docs explicit about cache and privacy boundaries.** The README/docs index now state that only the widget shell/script may be cached briefly, while conversation, message, and handoff responses are private `no-store` API responses. Evidence: `README.md`, `docs/README.md`, controller `Cache-Control` headers. - **S**

7. **Done/Shipped: close the Agent Bridge capability manifest gap.** Live Chat now declares its optional `agent-capability` contribution, marker class, provider/action contract, site scopes, capability keys, confirmation-required mutating operations, and audit events. The deferred contribution traceability entry is empty again, and README/docs copy explains the AI Orchestrator and Agent Bridge boundary for package adopters. Evidence: `capell.json`, `LiveChatAgentBridgeCapabilitiesContribution`, `LiveChatManifestTest`, `README.md`, `docs/README.md`. - **S**

## 3. Missing Features (gaps)

Capabilities declared: `live-chat`, `live-chat-widget`, external embeds, domain allow-list, message/details-first flows, AI responder contract, Contacts sync, office hours, after-hours response, human handoff, escalation rules, proactive prompts, file uploads, transcripts, analytics, knowledge sources, AI Orchestrator, Agent Bridge, operator summaries, suggested replies, AI audit, knowledge gaps, and knowledge documents.

- **Operator reply workflow is incomplete as a product surface.** The Action layer has `SuggestLiveChatHumanReplyAction`, transcript builders, summaries, and conversation status updates, but the admin resource needs a clear operator inbox workflow: reply, assign/queue, mark read, close, export transcript, and hand off to Contacts without editing raw model fields.
- **Realtime/polling delivery is basic.** Public POST responses include the assistant reply immediately, but there is no event stream, polling endpoint, read receipts, or async reply state for slow AI/human handoff. This limits production chat behavior once AI or operators are not instantaneous.
- **Privacy Center integration is advertised only as supported dependency.** Sensitive conversation data is encrypted, but no retention/export/erasure bridge is visible in the current package surface. Add Privacy Center contribution or document the host-owned privacy boundary.
- **Email Studio handoff/notification support is declared as a supported dependency but not surfaced in the plan/docs.** Add queued notifications for handoff/offline leads or remove the support claim until wired.
- **Marketplace proof is card-only.** `docs/screenshots.json` declares widget and admin conversation captures, but `capell.json` promotes only the static extension card. A premium chat package needs real widget, conversation inbox, installation settings, and knowledge-source screenshots.

## 4. Issues / Risks

1. **Resolved: CSRF-exempt write routes can create conversations without installation validation.** Same-host fallback routes now require an active installation, same-site `Origin`/`Referer`, visitor-token continuity, and installation-scoped conversation resolution. Evidence: `GuardLiveChatSameSiteRequestAction`, public route controllers, `LiveChatPublicRoutesTest`. - **P1**

2. **Resolved: public API leaks database primary keys for messages.** Public conversation/message JSON now omits internal message IDs, and tests assert the response shape. Evidence: public route controllers, `LiveChatPublicRoutesTest`. - **P2**

3. **Resolved: synchronous assistant/contact failures can make chat writes fragile.** Responder failures now produce a deterministic human-handoff fallback inside the chat write, while Contacts sync runs after the write and logs failures without breaking the visitor response. Evidence: `StartLiveChatConversationAction`, `StoreLiveChatMessageAction`, `LiveChatConversationFlowTest`. - **P2**

4. **Resolved: attachment cleanup is failure-safe.** Public controllers delete stored attachment files if downstream conversation/message creation throws after upload. Evidence: `DeleteLiveChatAttachmentsAction`, public-route cleanup coverage. - **P2**

5. **Improvement: docs under-explain the package boundary.** The current overview is buyer-friendly but does not list real routes, models, Actions, queues, privacy implications, or the public-key embed contract. Recommended fix: add README/docs index and a troubleshooting table. - **P3**

## 5. Marketplace & Positioning

Live Chat belongs in `Capell Growth` as a premium lead capture and support triage package. For site owners, the value is fast answers, cleaner contact capture, and fewer lost enquiries after hours. For admins/operators, the value is a conversation inbox with office-hours routing, escalation rules, and Contacts sync. For developers, the differentiator is that chat sessions, AI boundaries, knowledge sources, and CRM handoff are package-owned Actions and models rather than one-off widget code in the app.

**Current summary:** "AI first website chat for Capell with message-first capture, details-first enquiries, after-hours replies, human handoff, and Contacts CRM sync."

**Improved summary:** "Turn a Capell site into a qualified conversation channel: AI-assisted chat, office-hours handoff, lead capture, and Contacts sync from one governed widget."

**Improved description:** "Live Chat adds a public website chat widget that can answer from approved knowledge, capture visitor details only when needed, and escalate sensitive or high-intent conversations to a person. Operators manage installations, office hours, escalation rules, knowledge sources, and conversation history from Capell admin while Contacts remains the CRM record of truth. External embeds use public keys and allowed domains, and visitor messages/contact fields are encrypted at rest. Built for growth teams that want chat to create useful leads without leaking admin state into the public site."

**Media status:** The static extension card remains listing artwork. Committed package-rendered proof PNGs now cover the public widget and operator conversation inbox so Marketplace metadata no longer depends on missing optional runner outputs from the package workbench.

**Cross-sell:** Contacts is a hard dependency and should be positioned as the CRM record. Knowledge Base supplies approved answers. AI Orchestrator supplies provider-backed responses. Agent Bridge exposes controlled capabilities. Email Studio should become the notification/escalation channel. Privacy Center should own retention/export/erasure integration.

**Keywords/tags:** `live-chat`, `ai-chat`, `lead-capture`, `support`, `crm`, `contacts`, `handoff`, `office-hours`, `knowledge-base`, `chat-widget`, `visitor-conversations`, `growth`.

## 6. Prioritized Roadmap

| Item                                                                                                     | Bucket | Effort | Impact | Section ref      |
| -------------------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ---------------- |
| Require installation/origin validation for every CSRF-exempt public write route                          | Done   | M      | High   | §2.1, §4.1       |
| Remove public database message IDs from conversation/message API responses                               | Done   | M      | High   | §2.2, §4.2       |
| Add package README and docs index in the standard Capell shape                                           | Done   | S      | Medium | §2.5, §5         |
| Extend `LiveChatHealthCheck` to verify routes, admin resources, widget renderer, and public hook binding | Done   | S      | Medium | §2.4             |
| Declare Agent Bridge capability contribution metadata and remove deferred manifest traceability          | Done   | S      | Medium | §2.7, §5         |
| Make attachment storage and assistant/contact side effects failure-safe                                  | Done   | M      | Medium | §2.3, §4.3, §4.4 |
| Recapture and promote widget and conversation inbox screenshots                                          | Done   | S      | High   | §3, §5           |
| Build the operator inbox workflow around reply/assign/read/close/export actions                          | Later  | L      | High   | §3               |
| Add async/realtime delivery states for slow AI and human replies                                         | Later  | L      | Medium | §3               |
| Add Privacy Center retention/export/erasure bridge                                                       | Later  | M      | Medium | §3, §5           |
| Add Email Studio handoff/offline notification integration or remove the support claim                    | Later  | M      | Medium | §3, §5           |

## 7. Verification

Focused implementation verification completed for the first slice:

```bash
vendor/bin/pest packages/live-chat/tests/Unit/LiveChatManifestTest.php --configuration=phpunit.xml
vendor/bin/pest packages/live-chat/tests/Feature/LiveChatPublicRoutesTest.php --configuration=phpunit.xml
vendor/bin/pest packages/live-chat/tests/Unit/LiveChatAdminAuthorizationTest.php --configuration=phpunit.xml
```

Package verification completed:

```bash
vendor/bin/pest packages/live-chat/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, and tests.
- [x] Comprehensive local review pass completed for public routes, Actions, manifest, health check, docs, and screenshot contract.
- [x] Capell audience pass completed for site owners/operators, admin users, and package adopters.
- [x] Initial Now implementation slice shipped.
- [x] Focused Live Chat public-route verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
