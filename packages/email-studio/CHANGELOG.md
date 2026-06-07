# Changelog

All notable changes to `capell-app/email-studio` will be documented in this file.

## Unreleased

### Changed - 2026-06-07

- Added tokenized provider-event webhook ingestion that normalizes adapter payloads, writes `email_events`, updates matching recipient delivery status/timestamps idempotently, and automatically suppresses hard-bounced or complained recipients.

### Changed - 2026-06-03

- Reworded the marketplace summary, manifest description, and composer description to describe only the currently shipped functionality (site-scoped templates, safe placeholder rendering, delivery profiles and provider adapters, suppression enforcement, and the queued auditable send pipeline). Removed claims that inbound replies and provider-event ingestion already ship; these are now framed as roadmap.
- Implemented `EmailStudioHealthCheck` so it runs real diagnostics for the four declared checks (template rendering, provider delivery, suppression enforcement, and provider-event normalization) via `runDiagnostics()` / `passed()`, instead of asserting nothing.
- `DeliverEmailMessageAction` now resolves `CheckEmailSuppressionAction` through the container instead of instantiating it directly, so it can be faked in tests and stays consistent with the rest of the codebase.

### Other

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
