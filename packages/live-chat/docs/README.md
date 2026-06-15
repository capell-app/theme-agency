# Live Chat Docs

Start with the [package README](../README.md) for install impact, public route boundaries, and verification commands.

## Documents

| Document                                  | Use                                                                       |
| ----------------------------------------- | ------------------------------------------------------------------------- |
| [Overview](overview.md)                   | Generated package shape, data model, install impact, and troubleshooting. |
| [Improvement plan](improvement-plan.md)   | Current roadmap, risks, missing features, and implementation priorities.  |
| [Screenshot contract](screenshots.json)   | Runner targets for widget and conversation-inbox captures.                |
| [Marketplace assets](assets/marketplace/) | Interim listing artwork until route-backed screenshots are recaptured.    |

## Public Route Notes

Same-site public writes require an active installation, same-site request origin, and visitor-token continuity. External embeds require a public key and an allowed domain. Conversation/message/handoff API responses are private `no-store` responses and must not be served from HTML cache.

## Related Packages

| Package                                            | Why it matters                                       |
| -------------------------------------------------- | ---------------------------------------------------- |
| [Contacts](../../contacts/README.md)               | CRM record of truth for captured visitors and leads. |
| [Knowledge Base](../../knowledge-base/README.md)   | Approved source content for chat answers.            |
| [AI Orchestrator](../../ai-orchestrator/README.md) | Provider-backed AI capability boundary.              |
| [Agent Bridge](../../agent-bridge/README.md)       | Controlled agent capability exposure.                |
| [Email Studio](../../email-studio/README.md)       | Future handoff/offline notification channel.         |
| [Privacy Center](../../privacy-center/README.md)   | Future retention, export, and erasure boundary.      |

## Verification

```bash
vendor/bin/pest packages/live-chat/tests/Feature/LiveChatPublicRoutesTest.php --configuration=phpunit.xml
vendor/bin/pest packages/live-chat/tests --configuration=phpunit.xml
```
