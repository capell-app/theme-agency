# Changelog

All notable changes to `capell-app/comments` will be documented in this file.

## Unreleased

### 2026-06-04

- Implemented real `CommentsHealthCheck` diagnostics for required storage tables, settings registration, the public thread route, and the public thread Livewire component.
- Added focused health-check tests covering passing diagnostics and failure modes for missing storage, settings, and route wiring.
- Added public comment form bot-trap controls: a hidden honeypot field and a configurable minimum form age.
- Extended `CreateCommentData` and `CreateCommentAction` to reject honeypot-filled or too-fast public submissions before persisting comments.
- Updated `CommentThreadComponent` to track and reset bot-trap state during public comment submission.
- Added action and Livewire coverage proving bot-trap submissions are rejected without creating comments.

### 2026-06-03

- Rewrote the marketplace summary and package descriptions (`capell.json`,
  `composer.json`) to lead with the real differentiators: cache-safe public
  rendering, encrypted author records, and per-site moderation controls.
- Declared the `LatestCommentsWidget` dashboard widget in the manifest
  `contributes[]` list so it is discoverable by marketplace and Diagnostics
  tooling (it was registered at runtime but missing from the manifest).
- Added a manifest test asserting every registered dashboard widget is declared
  in `contributes[]`.
