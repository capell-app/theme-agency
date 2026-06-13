# Live Chat

Live Chat adds an AI first website chat widget for lead capture, support triage, out-of-office replies, and human handoff.

## Status

- Tier: premium
- Bundle: Capell Growth
- Certification: first-party
- Surfaces: frontend widget, admin conversation review, admin routing configuration

## Buyer Value

Live Chat lets site owners answer common questions immediately, collect useful contact details only when needed, and move high-intent or sensitive enquiries into the shared Contacts timeline. Visitors can ask first or leave details first, and after-hours conversations get a clear fallback message with contact capture.

## Package Split

The live-chat package owns widget sessions, message history, AI response boundaries, office-hours state, handoff rules, knowledge source references, and public chat endpoints. Contacts remains the CRM record of truth. Handoff and capture actions sync into Contacts leads and activity with the full transcript context.

## Setup Notes

Install `capell-app/contacts` first. Enable the package and keep `auto_inject` enabled for automatic `BodyEnd` widget injection, or include `capell-live-chat::widget` manually in a theme.

## Screenshot Contract

Initial marketplace assets are committed as static extension previews. Replace them with real Capell admin and frontend runner captures after the package is installed in a demo harness.

## Safety

Public widget output contains no admin URLs, editor state, model IDs, permissions, signed editor URLs, or authoring selectors. Visitor contact fields, message bodies, transcript payloads, and contact handoff context are encrypted at rest.
