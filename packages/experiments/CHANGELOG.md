# Changelog

All notable changes to `capell-app/experiments` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added

- Goal event recording is now idempotent when callers provide an `event_key`, backed by a unique allocation/goal/event-key database constraint.
- Added a package README documenting runtime variant resolution, goal-event recording, public-output boundaries, and current integration ownership.
- `ExperimentsHealthCheck` now performs real diagnostics (`runDiagnostics()` / `passed()`), probing that every experiment storage table exists and that each experiment model resolves to a backing table. Previously the critical health check declared compatibility only and always passed.
- `ExperimentContextData` now exposes a `subjectClass` field so request-context resolution can disambiguate experiments that share a `subject_type` and `subject_id` across different subject classes.
- Winner reports now include minimum sample size, confidence level, baseline, lift, p-value, and statistical-significance metadata; declarations only persist a winner after the sample floor and confidence gate are satisfied.
- Variant allocation results now expose the allocation row id when one is persisted.

### Changed

- `ResolveExperimentVariantForContextAction::candidateQuery()` now filters candidate experiments by `subject_class` (matching the `experiments_subject_index`) when the context supplies one, preventing collisions between experiments that share `subject_type` + `subject_id` across different classes.
- Marketplace and Composer descriptions rewritten to describe outcomes (server-side, no-flicker A/B testing with audience targeting and goal-tracked winner reports) and aligned across `capell.json` and `composer.json`.
- `AllocateVariantAction` now honours `allocation_strategy`: `sticky_weighted` reuses an existing allocation for the same visitor hash, while `weighted` records a fresh weighted allocation without using the sticky lookup.
- Manifest metadata no longer advertises a production frontend surface before the cache-safe frontend integration exists, and now declares the shipped `statistical-significance` capability.
- Scheduled and expired experiments now transition through `capell:experiments:sync-statuses`, scheduled every five minutes with overlap protection.

_Dated 2026-06-04._
