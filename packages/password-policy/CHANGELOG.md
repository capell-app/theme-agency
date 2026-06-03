# Changelog

All notable changes to `capell-app/password-policy` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Replaced the stub `PasswordPolicyHealthCheck` with real install-health diagnostics: each enabled control (forced change, expiry, history) now fails Diagnostics when its backing column or table is missing.
- Reconciled marketplace copy: `capell.json` `description`/`marketplace.summary` and `composer.json` `description` now share the same buyer-facing summary.
- Added a `down()` method to the password policy settings migration so rollbacks remove the `password_policy.*` settings instead of orphaning them.
