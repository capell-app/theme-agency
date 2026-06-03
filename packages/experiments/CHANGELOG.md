# Changelog

All notable changes to `capell-app/experiments` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added

- `ExperimentsHealthCheck` now performs real diagnostics (`runDiagnostics()` / `passed()`), probing that every experiment storage table exists and that each experiment model resolves to a backing table. Previously the critical health check declared compatibility only and always passed.
- `ExperimentContextData` now exposes a `subjectClass` field so request-context resolution can disambiguate experiments that share a `subject_type` and `subject_id` across different subject classes.

### Changed

- `ResolveExperimentVariantForContextAction::candidateQuery()` now filters candidate experiments by `subject_class` (matching the `experiments_subject_index`) when the context supplies one, preventing collisions between experiments that share `subject_type` + `subject_id` across different classes.
- Marketplace and Composer descriptions rewritten to describe outcomes (server-side, no-flicker A/B testing with audience targeting and goal-tracked winner reports) and aligned across `capell.json` and `composer.json`.

_Dated 2026-06-03._
