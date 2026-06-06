# Changelog

All notable changes to `capell-app/login-audit` will be documented in this file.

## Unreleased

- Added Capell admin alerts for new-device, failed-login, and suspicious-login audit events with settings-backed toggles.
- Added suspicious-login detection for repeated failures, failed-device success, rapid country changes, and optional unusual login hours.
- Added CSV export actions to the Login Audit resource and user authentication history relation manager.
- Added route-backed Capell runner captures for the user edit access summary and user authentication history relation manager.
- Added trusted-device, device-name, and last-activity surfaces to Login Audit resource tables and user relation manager history.
- Added daily authentication-log purging with retention synced into the vendor purge command and a last-purged settings timestamp recorded after successful scheduled runs.
- Added admin activity tracking controls and a last-seen write throttle to reduce per-request write volume.
- Added a Login Audit capture configuration health check that verifies vendor authentication-log capture targets the package table and listener map.
- Added real Laravel auth event coverage for successful and failed authentication logs.

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Replaced the stubbed `LoginAuditHealthCheck` with a real Diagnostics probe that verifies the `login_audit` storage table exists, the vendor authentication-log listeners are wired, and the `frontend.activity` middleware alias is registered.
- Fixed `UserActivityMiddleware` so it stamps `last_seen_at` on the most recent matching session instead of an arbitrary historical row, aligning its ordering with `AdminActivityMiddleware`.
- Clarified the defensive `filament-authentication-log` resource alias that shims the upstream `AutenticationLogResource` config typo.
- Translated the Login Audit dashboard settings contributor label and group strings.
- Rewrote the marketplace summary, package description, and composer description, and promoted the committed admin screenshots into the marketplace manifest.
