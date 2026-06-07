# Changelog

All notable changes to `capell-app/privacy-center` will be documented in this file.

## Unreleased

- Added focused admin/scheduled-retention coverage for Privacy Center workflow actions, retention rule table actions, and command manifest alignment.

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
- Added the `privacy:apply-retention` console command, registered it with the package provider, scheduled it daily, and exposed the command/frequency in `capell.json` so retention execution is reachable outside tests.
- Added Action-backed privacy request edit-page workflow actions for marking DSAR requests verified, fulfilled, or rejected without bypassing the audit timestamp fields.
- Expanded README and overview documentation around shipped admin/console surfaces, DSAR export and erasure boundaries, consent mirroring, retention execution, install/config notes, and public-output safety limits.

## 2026-06-03

- Replaced the stub `PrivacyCenterHealthCheck` with real diagnostics matching its `critical` manifest claim: it now verifies every privacy ledger storage table exists, that the five privacy models are registered in the morph map, and that an identity hash secret is configured for consent-evidence hashing. Reports table and morph-alias names only — never hashed values or secrets.
- Hardened `PrivacyIdentifier::hashSecret()` to fail loudly instead of silently falling back to the guessable literal salt `'capell-privacy-center'` when neither `capell-privacy-center.hash_secret` nor `app.key` is configured. A predictable salt would have made hashed compliance evidence (IP, user-agent) trivially reversible across installs.
- Reconciled marketplace copy: `capell.json` `description`/`marketplace.summary` and `composer.json` `description` now share the same buyer-facing summary, leading with the auditable-ledger outcome rather than internal "foundation" plumbing.
- Added tests covering the new health-check pass/fail paths and `PrivacyIdentifier` hash determinism, secret variance, and the fail-loud guard.
