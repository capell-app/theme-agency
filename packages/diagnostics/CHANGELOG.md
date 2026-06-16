# Changelog

All notable changes to `capell-app/diagnostics` will be documented in this file.

## Unreleased

- Queue operations stats now report oldest pending job age and liveness status so operators can spot stale queues.
- Prepared package metadata and documentation for ongoing Capell 4.x package work.
- Package health reporting now reflects declared health-check classes and shows implemented/stub/broken counts instead of presenting raw manifest counts as health.
- Command palette output is redacted before it is returned or persisted to `command_palette_runs.output`.
- Dynamic `capell:*` palette commands now use an explicit risk map and require confirmation by default when they are not mapped.
- Added the `capell:diagnostics:health` doctor command to run extension health checks from the console with table or JSON output.
- `DiagnosticsHealthCheck` can run assertions by manifest key so the package's four declared health checks map to addressable checks.
- Infrastructure status warning drivers are now configurable so environments that intentionally use local-only cache, queue, or mail transports can avoid false warnings.

## 2026-06-03

### Added

- `RunExtensionHealthChecksAction` now resolves every health check declared in installed package manifests, classifies each as implemented, stub, or broken via reflection, and executes the runnable ones to capture real pass/fail outcomes with their declared severity. This replaces the previous behaviour where declared health checks were only counted, producing a false-green dashboard.
- `HealthCheckResultData`, `ExtensionHealthReportData`, and `HealthCheckImplementationStatus` describe the per-check and monorepo-wide health rollup.

### Changed

- `DiagnosticsHealthCheck` is now a real reference health check: it verifies package-catalog discovery, manifest metadata, system-health widget registration, and the failed-job summary instead of asserting nothing.
- Rewrote the marketplace summary, description, top-level description, and composer description to lead with the cross-package install-health value proposition; expanded composer keywords; and wired the six committed admin screenshots into `marketplace.screenshots`.
