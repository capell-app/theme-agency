# Changelog

All notable changes to `capell-app/document-lifecycle` will be documented in this file.

## Unreleased

- Added review-due and expiry dates for controlled documents, plus a daily `capell:document-lifecycle:archive-expired` command that archives active expired records.
- Added signed JSON certificate downloads for individual acceptance records.
- Stored content snapshots on new document publications and added JSON diff downloads from the publications relation manager.
- Added re-acceptance detection and an outstanding acceptances CSV report for subjects whose latest known acceptance is stale.
- Added CSV export for document acceptance evidence, including optional per-publication/version filtering from the acceptances relation manager.
- Prepared package metadata and documentation for ongoing Capell 4.x package work.
- Added real factories for documents, document publications, and document acceptances so tests, demos, and screenshot seeders can use `Model::factory()` without broken autoload promises.
- Reconciled the manifest frontend surface by declaring only admin and console surfaces; the Customer Portal feed remains an authenticated supported integration, not package-owned public frontend output.
- Clarified that the marketplace manifest only lists committed marketplace assets while the admin screenshot contract remains pending capture through `docs/screenshots.json`.
- Added translated Filament labels and badge colours to `DocumentStatusEnum` and tightened `DocumentAcceptance` mass assignment to explicit fillable fields.
- Added Action-backed admin row actions for publishing controlled document versions and recording manual authenticated-admin acceptances.

## 2026-06-03

- Promoted `DocumentLifecycleHealthCheck` from a version-only stub to a real diagnostic: added `BuildDocumentLifecycleHealthReportAction` and `DocumentLifecycleHealthReportData`, asserting the required tables and `legal_acceptances` extension columns exist, the audit tables are registered as protected, the morph map is registered, and the Publishing Studio revision auto-publish listener is wired.
- Made `RecordDocumentAcceptanceAction` request-context-pure by accepting optional `ipAddress` and `userAgent` parameters (defaulting to the current request) so acceptance evidence hashes are captured on queue and console paths.
- Fixed the asymmetric `legal_acceptances` migration rollback so `down()` only drops indexes that exist, preventing failures on installs where the table pre-existed.
- Rewrote the marketplace summary, manifest description, and composer description to lead with the tamper-evident, version-pinned acceptance-evidence value proposition.
