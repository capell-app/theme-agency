# Changelog

All notable changes to `capell-app/media-ai` will be documented in this file.

## Unreleased

### 2026-06-03

- Rewrote the marketplace summary, package description, and composer description to state concrete outcomes (background removal, cleanup, upscaling) instead of the generic "AI-assisted media actions" line.
- Added the in-repo light and dark `media-ai-doctor-image` captures to the marketplace screenshot set.
- Replaced the `MediaAIHealthCheck` stub with real diagnostics (`runDiagnostics()` + `passed()`) covering the image doctor adapter binding, the edit-action registration, and structured-request construction. No provider credentials are read or surfaced.
- Validated the `operation` against a canonical allow-list (`ImageDoctorRequest::OPERATIONS`) in the request constructor and via the action's Select rule, so a crafted payload can't reach a provider with an arbitrary operation.
- Added optional `locale` context to `ImageDoctorRequest`, the Filament Doctor image action, and the AI Orchestrator adapter so provider messages can be returned translated for the active editor locale.
- Moved Filament Doctor image provider execution into `RunImageDoctorJob`, with immediate queued feedback and completion notifications for the initiating admin.
- Added configurable Doctor image rate limits plus budget/model hints on `ImageDoctorRequest` and AI Orchestrator context.
- Added optional `altText` and `caption` result fields and persisted successful provider metadata to localized media translation `meta`.
- Corrected the Boost guideline to describe the actual behaviour (fires the `ImageDoctor` contract and notifies; writes no metadata).

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
