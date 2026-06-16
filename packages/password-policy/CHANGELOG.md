# Changelog

All notable changes to `capell-app/password-policy` will be documented in this file.

## Unreleased

- Added configurable password expiry warning notifications and the `capell:password-policy:send-expiry-warnings` command for host schedulers.
- Restricted the forced password change page to users whose evaluated password policy status requires it, and redirected successful changes through the current Filament panel URL.
- Added configurable password complexity settings for minimum length, mixed case, numbers, and symbols, and wired them into the password validator.
- Moved admin user-edit password history recording to the post-save path so exactly the previous hash is stored after a successful password change.
- Removed the unimplemented console surface/capability from package metadata.
- Prevented expiry enablement from locking out legacy admins: install now backfills `password_changed_at`, null legacy timestamps are no longer treated as expired, and the expired-users filter excludes unknown timestamps.
- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Replaced the stub `PasswordPolicyHealthCheck` with real install-health diagnostics: each enabled control (forced change, expiry, history) now fails Diagnostics when its backing column or table is missing.
- Reconciled marketplace copy: `capell.json` `description`/`marketplace.summary` and `composer.json` `description` now share the same buyer-facing summary.
- Added a `down()` method to the password policy settings migration so rollbacks remove the `password_policy.*` settings instead of orphaning them.
