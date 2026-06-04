# Changelog

All notable changes to `capell-app/contacts` will be documented in this file.

## Unreleased

### 2026-06-04

- Exposed contact privacy workflows to operators through the `capell-contacts:privacy` command and Contact admin row actions.
- Added audited privacy workflow actions: `AuditContactPrivacyExportAction` records export events without storing exported PII, and `AnonymizeContactWithAuditAction` records a non-PII erasure audit after anonymization clears sensitive contact, lead, and activity data.
- Added explicit Contact policy methods and manifest permissions for privacy export and anonymization workflows.
- Added tests covering admin privacy action registration, manifest wiring, audited privacy activity rows, and console export/anonymization reachability.
- Queued all first-party Contacts source listeners and isolated adapter failures so form submissions, comments, registrations, campaign conversions, and Shopify sync events are not broken by CRM sync exceptions.

### 2026-06-03

- Implemented real `ContactsHealthCheck` diagnostics: the previously stubbed critical check now asserts that the contacts storage tables exist, the CRM models are registered in the morph map, and an identity hash secret is configured. Added `runDiagnostics()` and `passed()` following the shared Capell health-check convention.
- Fixed broken admin search: the `display_name` and `email` columns on `ContactResource` are encrypted at rest, so `->searchable()` emitted `LIKE` queries against ciphertext that could never match. Removed `->searchable()` from both columns until a hash-based or plaintext search column exists.
- Truth-in-advertising: dropped the unimplemented `contacts-deduplication-rules` capability from `capell.json`. Only first-match identity lookup is shipped; there is no merge or rules engine.
- Rewrote the marketplace summary and package descriptions (`capell.json` and `composer.json`) to be buyer-facing and to describe shipped behaviour (identity matching by email, phone, or source) rather than overclaiming deduplication.
- Added test coverage for the health-check diagnostics and the encrypted-column search safety.
- Added explicit site scoping to `BuildContactsOverviewStatsAction` so CRM overview totals can be generated for one site without leaking cross-site aggregate counts. Calling the action without a site id remains supported for global package dashboards.
