# Changelog

All notable changes to `capell-app/diagnostics` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

### Added

- `RunExtensionHealthChecksAction` now resolves every health check declared in installed package manifests, classifies each as implemented, stub, or broken via reflection, and executes the runnable ones to capture real pass/fail outcomes with their declared severity. This replaces the previous behaviour where declared health checks were only counted, producing a false-green dashboard.
- `HealthCheckResultData`, `ExtensionHealthReportData`, and `HealthCheckImplementationStatus` describe the per-check and monorepo-wide health rollup.

### Changed

- `DiagnosticsHealthCheck` is now a real reference health check: it verifies package-catalog discovery, manifest metadata, system-health widget registration, and the failed-job summary instead of asserting nothing.
- Rewrote the marketplace summary, description, top-level description, and composer description to lead with the cross-package install-health value proposition; expanded composer keywords; and wired the six committed admin screenshots into `marketplace.screenshots`.
