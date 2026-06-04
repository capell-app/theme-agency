# Changelog

All notable changes to `capell-app/comments` will be documented in this file.

## Unreleased

### 2026-06-04

- Added the `CommentSpamProvider` contract, configured provider chain, default `LocalCommentSpamProvider`, and create-flow context data for external Akismet/Turnstile-style spam adapters.
- Added `capell-comments:privacy-retention` and `ApplyCommentPrivacyRetentionAction` for pruning old visitor hashes, moderation notes, expired tokens, and anonymizing matching author PII by email.
- Added performance-budget coverage for public thread hydration/rendering and admin comment widgets, and batched sibling reply counts to avoid empty-grandchild query fanout.
- Locale-pinned public timestamp labels to the resolved commentable language and serialized those labels through the Livewire-safe public comment DTO.
- Memoized the resolved public commentable model for each Livewire request so submit refreshes do not re-query the same page.
- Hardened public comment sanitization by stripping invisible Unicode format controls and ASCII control bytes before storage.
- Broadened spam link detection to count scheme, `www.`, and bare-domain links while avoiding email-domain false positives.
- Added settings-aware reply pagination for public comment threads, including per-parent "load more replies" support and bounded child hydration.
- Made public comment DTOs Livewire-serializable so approved nested threads can survive Livewire request cycles safely.
- Added auto-inject regression coverage proving cached comment shells do not expose comment bodies, author PII, model identifiers, moderation state, Livewire snapshots, or admin URLs.
- Added Comments architecture coverage for strict equality, public-runtime admin/authoring isolation, and public Blade database-access/authoring-marker guards.
- Wired queued moderator notifications for configured moderator email addresses when new comments enter pending approval or pending email verification.
- Added listener registration and notification coverage for moderator emails, invalid-address filtering, duplicate suppression, and spam/approved suppression.
- Hardened the public comment submission throttle key so changing the author email no longer resets the primary commentable/IP rate-limit bucket.
- Added Livewire coverage proving repeated submissions to the same thread/IP are throttled even when the email changes.
- Added automatic spam scoring for configured link-count and blocked-term rules, storing `spam_reasons` and routing flagged comments to `Spam`.
- Added direct score and create-flow coverage proving auto-spam comments skip verification token creation.
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
