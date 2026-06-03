# Changelog

All notable changes to `capell-app/contacts` will be documented in this file.

## Unreleased

### 2026-06-03

- Implemented real `ContactsHealthCheck` diagnostics: the previously stubbed critical check now asserts that the contacts storage tables exist, the CRM models are registered in the morph map, and an identity hash secret is configured. Added `runDiagnostics()` and `passed()` following the shared Capell health-check convention.
- Fixed broken admin search: the `display_name` and `email` columns on `ContactResource` are encrypted at rest, so `->searchable()` emitted `LIKE` queries against ciphertext that could never match. Removed `->searchable()` from both columns until a hash-based or plaintext search column exists.
- Truth-in-advertising: dropped the unimplemented `contacts-deduplication-rules` capability from `capell.json`. Only first-match identity lookup is shipped; there is no merge or rules engine.
- Rewrote the marketplace summary and package descriptions (`capell.json` and `composer.json`) to be buyer-facing and to describe shipped behaviour (identity matching by email, phone, or source) rather than overclaiming deduplication.
- Added test coverage for the health-check diagnostics and the encrypted-column search safety.
