# Changelog

All notable changes to `capell-app/frontend-optimizer` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
- Deepened `FrontendOptimizerHealthCheck` with translated diagnostics for manifest storage, critical-CSS storage, Node/script/Playwright generator readiness, renderer binding, and queue-driver safety.
- Added a real public head-render safety test covering anonymous and signed-in non-admin visitors through the optimizer-bound frontend asset manifest renderer.
- Removed the unused `debug_query_support` setting from active settings, admin UI, translations, and docs.
- Clarified that marketplace screenshots only list committed `docs/assets/marketplace` assets while required runner captures remain pending in `docs/screenshots.json`.
- Documented the current HTML Cache invalidation blocker for critical-CSS generation completion.

## 2026-06-03

- Replaced the stub `FrontendOptimizerHealthCheck` (declared `critical` severity but asserting nothing) with real diagnostics: it now probes that the public asset-manifest renderer is bound to the optimizer, that the manifest and critical-CSS storage disk is writable, and that critical-CSS generation is not queued on the `sync` driver while automatic generation is enabled.
- Rewrote the marketplace summary, package description, and Composer description to lead with the benefit (faster first paint and improved Core Web Vitals via real-browser critical CSS) instead of internal "profile-based delivery" jargon.
- Added the before/after public head-output comparison image to the marketplace screenshots.
