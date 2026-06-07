# Changelog

All notable changes to `capell-app/frontend-optimizer` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
- Deepened `FrontendOptimizerHealthCheck` with translated diagnostics for manifest storage, critical-CSS storage, Node/script/Playwright generator readiness, renderer binding, and queue-driver safety.
- Added a real public head-render safety test covering anonymous and signed-in non-admin visitors through the optimizer-bound frontend asset manifest renderer.
- Removed the unused `debug_query_support` setting from active settings, admin UI, translations, and docs.
- Clarified that marketplace screenshots only list committed `docs/assets/marketplace` assets while required runner captures remain pending in `docs/screenshots.json`.
- Wired successful critical-CSS generation to the frontend cache invalidation registry without importing HTML Cache internals.
- Moved render-profile manifest writes off the normal public render path so synchronous queue renders and already-queued profiles do not write local manifest files.
- Taught the optimizer renderer to use manifest-level critical CSS, package-name, and JavaScript loading-strategy hints for non-Foundation assets while preserving Foundation fallbacks.
- Added stale render-profile pruning through `PruneRenderProfilesAction` and `capell:frontend-optimizer:prune-profiles`, including dry-run and JSON output.
- Reconciled the improvement plan's listener, job, settings, health, and public head-output test gap against existing focused coverage.
- Added manifest-driven resource hints so preload, modulepreload, font preload metadata, and LCP image `fetchpriority` survive render-profile optimization.

## 2026-06-03

- Replaced the stub `FrontendOptimizerHealthCheck` (declared `critical` severity but asserting nothing) with real diagnostics: it now probes that the public asset-manifest renderer is bound to the optimizer, that the manifest and critical-CSS storage disk is writable, and that critical-CSS generation is not queued on the `sync` driver while automatic generation is enabled.
- Rewrote the marketplace summary, package description, and Composer description to lead with the benefit (faster first paint and improved Core Web Vitals via real-browser critical CSS) instead of internal "profile-based delivery" jargon.
- Added the before/after public head-output comparison image to the marketplace screenshots.
