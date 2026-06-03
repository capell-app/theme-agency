# Changelog

All notable changes to `capell-app/comments` will be documented in this file.

## Unreleased

### 2026-06-03

- Rewrote the marketplace summary and package descriptions (`capell.json`,
  `composer.json`) to lead with the real differentiators: cache-safe public
  rendering, encrypted author records, and per-site moderation controls.
- Declared the `LatestCommentsWidget` dashboard widget in the manifest
  `contributes[]` list so it is discoverable by marketplace and Diagnostics
  tooling (it was registered at runtime but missing from the manifest).
- Added a manifest test asserting every registered dashboard widget is declared
  in `contributes[]`.
