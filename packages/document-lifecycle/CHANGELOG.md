# Changelog

All notable changes to `capell-app/document-lifecycle` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Promoted `DocumentLifecycleHealthCheck` from a version-only stub to a real diagnostic: added `BuildDocumentLifecycleHealthReportAction` and `DocumentLifecycleHealthReportData`, asserting the required tables and `legal_acceptances` extension columns exist, the audit tables are registered as protected, the morph map is registered, and the Publishing Studio revision auto-publish listener is wired.
- Made `RecordDocumentAcceptanceAction` request-context-pure by accepting optional `ipAddress` and `userAgent` parameters (defaulting to the current request) so acceptance evidence hashes are captured on queue and console paths.
- Fixed the asymmetric `legal_acceptances` migration rollback so `down()` only drops indexes that exist, preventing failures on installs where the table pre-existed.
- Rewrote the marketplace summary, manifest description, and composer description to lead with the tamper-evident, version-pinned acceptance-evidence value proposition.
