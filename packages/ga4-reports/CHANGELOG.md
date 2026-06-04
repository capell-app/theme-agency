# Changelog

All notable changes to `capell-app/ga4-reports` will be documented in this file.

## Unreleased

### 2026-06-04

- Added bounded retry/backoff configuration for GA4 token and Data API calls, including transient connection failures, common upstream error statuses, `Retry-After`, and GA4 quota exhaustion responses.
- Report final GA4 quota failures with a specific quota-exhaustion message instead of a generic HTTP failure.
- Documented the new GA4 HTTP retry configuration and retry behavior.

### 2026-06-03

- Replaced the stub `Ga4ReportsHealthCheck` with real diagnostics that verify the snapshot storage tables exist and the integration is configured (enabled, property ID, and a readable service-account credentials file) without disclosing the property ID or credentials path.
- Bound `NullGA4ReportsDataClient` when GA4 Reports is unconfigured so the data-client binding matches the documented behaviour; the real client is bound only when enabled with a property ID and credentials path.
- Removed the orphan `GA4ReportsSettingsPage` (settings are surfaced through the settings group registry).
- Fixed the stray "GA4 Reports 4" wording across the composer description, README, language file, credits doc, and sync command description.
- Rewrote the marketplace summary and description for clarity and buyer benefit.

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
