# Changelog

All notable changes to `capell-app/filament-peek` will be documented in this file.

## Unreleased

### 2026-06-06

- Demoted and removed the broken product screenshots from `marketplace.screenshots`; real Capell runner captures are still required for the Page edit preview action, signed preview, and expired-preview states.

### 2026-06-05

- Added the proprietary `LICENSE` file declared by the package Composer manifest.

### 2026-06-04

- Split cached preview snapshot lookup into `FindPagePreviewSnapshotAction`, so the signed preview controller no longer depends on the snapshot creation action for reads.
- Added a shared preview context concern for the current admin user, cache store, TTL, and cache-key contracts used by Page and Layout Builder preview snapshots.

### 2026-06-03

- Replaced the stub `FilamentPeekHealthCheck` with real diagnostics: it now verifies the signed `capell-filament-peek.preview` route is registered, that the snapshot create and render actions resolve from the container, that the upstream `pboivin/filament-peek` plugin is installed, and that the configured preview cache store is reachable. Output reports only check labels and the store name, never tokens, cache keys, or snapshot contents.
- Added public-output-safety tests proving that a validly-signed preview URL is rejected for both unauthenticated requests and authenticated non-admin users who do not own the snapshot.
- Aligned the `Cache-Control` header literal (`no-store, private`) between the preview controller and its tests so the source and assertions match before Symfony canonicalisation.
- Removed the dead `PagePreviewSnapshotData::$path` property and its assignment; it was written into every cached snapshot but never read.
- Rewrote the marketplace summary, manifest description, and Composer description to lead with the buyer outcome (previewing unsaved Page and Layout Builder edits through the live theme with nothing persisted) instead of the upstream brand name.
- Promoted the shipped hero images and the page-edit, preview-panel, and workspace-draft-review product screenshots into `marketplace.screenshots` with captions.
