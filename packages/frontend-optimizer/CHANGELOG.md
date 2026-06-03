# Changelog

All notable changes to `capell-app/frontend-optimizer` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Replaced the stub `FrontendOptimizerHealthCheck` (declared `critical` severity but asserting nothing) with real diagnostics: it now probes that the public asset-manifest renderer is bound to the optimizer, that the manifest and critical-CSS storage disk is writable, and that critical-CSS generation is not queued on the `sync` driver while automatic generation is enabled.
- Rewrote the marketplace summary, package description, and Composer description to lead with the benefit (faster first paint and improved Core Web Vitals via real-browser critical CSS) instead of internal "profile-based delivery" jargon.
- Added the before/after public head-output comparison image to the marketplace screenshots.
