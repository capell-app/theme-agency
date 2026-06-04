# Comments

Status: **Available, schema impact** · Kind: **package** · Tier:
**premium** · Bundle: **comments** · Contexts: **admin, frontend** · Product
group: **Capell Engagement**

Comments adds moderated public discussion to registered Capell content. A site
owner gets a controlled way for visitors to respond to pages or articles, while
editors keep approval and author-management work inside Capell Admin.

## What This Package Adds

- A public comment thread component that can load after the page response.
- Comment author, comment, token, and moderation event tables.
- Email verification and moderation transitions.
- Admin resources, moderation inbox, settings schema, and dashboard widgets.
- Settings for identity mode, publication policy, verification flow, page size,
  depth, throttling, spam checks, and moderator notifications.
- Public form bot-trap controls for honeypot-filled and too-fast submissions.
- Automatic local spam scoring for configured link-count and blocked-term
  rules, including scheme, `www.`, and bare-domain link detection.
- Pluggable external spam provider support through `CommentSpamProvider`,
  layered with the local provider by default.
- Comment body sanitization that strips tags plus invisible/control characters
  before storage.
- Settings-aware reply pagination with per-parent "load more replies" support.
- Batched sibling reply-count hydration to avoid empty-grandchild query fanout.
- Request-local Livewire commentable memoization during public submit and
  refresh workflows.
- Locale-pinned public timestamp labels based on the commentable page language.
- Queued moderator notifications for configured moderator email addresses.
- Public Like reactions with aggregate counts on approved public comments.
- Approved-reply notifications for verified parent authors, plus tokenized
  reply-notification opt-out handling.
- Privacy retention tooling for old visitor hashes, moderation notes, tokens,
  and author email erasure.
- Diagnostics checks for storage tables, settings registration, thread route
  registration, and Livewire component registration.

## Why It Matters

Without this package, a Capell build that needs comments has to choose between
custom frontend forms, external embeds, or bespoke moderation tables. Comments
keeps that workflow in Capell and keeps the public output boundary explicit.

For a non-technical site owner, the benefit is straightforward: visitors can
discuss content, editors can approve or reject comments, and the public page does
not reveal internal moderation data.

## Runtime Shape

- `CommentsServiceProvider` registers package config, migrations, settings,
  routes, commands, translations, views, and default commentable support.
- `AdminServiceProvider` registers admin resources, settings contributors, and
  widgets.
- `FrontendServiceProvider` registers the public Livewire thread component.
- `RenderCommentThreadController` serves the dynamic thread endpoint.
- `BuildPublicThreadAction` hydrates approved public thread DTOs with bounded
  root/reply pagination, batched sibling reply counts, and locale-pinned
  timestamp labels.
- `CommentThreadComponent` memoizes the decrypted commentable model for the
  current Livewire request only.
- `VerifyCommentAuthorEmailController` handles author verification links.
- `ScoreCommentSpamAction` delegates to configured `CommentSpamProvider`
  implementations before comment verification or public visibility decisions.
- `LocalCommentSpamProvider` preserves the built-in link-count and blocked-term
  checks, while `ConfiguredCommentSpamProvider` combines configured provider
  scores.
- `CommentBodySanitizer` normalizes public body text and reports link counts
  for spam scoring.
- `NotifyModeratorsOfNewComment` listens for `CommentCreated` and notifies
  configured moderators when comments need review.
- `ToggleCommentReactionAction` toggles Like reactions for approved comments
  using authenticated user IDs or hashed visitor request data.
- `RequestCommentReplyNotificationAction` sends approved-reply notifications
  to verified parent authors, while `DisableCommentAuthorReplyNotificationsAction`
  handles tokenized opt-outs.
- `ApplyCommentPrivacyRetentionAction` prunes old private identifiers and
  anonymizes author records by email while preserving public comment history.
- `CommentsHealthCheck` reports real Diagnostics results for package storage,
  settings, route, and component wiring.

## Data And Retention

The package owns:

- `comment_authors`
- `comments`
- `comment_tokens`
- `comment_moderation_events`
- `comment_reactions`

Settings are stored through `CommentSettings`. Email and visitor identifiers are
handled through hash helpers and token records rather than exposed in public
thread DTOs.

## Marketplace Gallery

`capell.json` lists a committed marketplace gallery for the extension card plus
four illustrated SVG previews for the required screenshot-contract surfaces:

- moderation inbox
- comments admin resource
- comment authors admin resource
- public comment thread

Those gallery files live under `docs/assets/marketplace/` so marketplace
validation only references committed assets. They are gallery illustrations, not
live runtime captures.

The runtime capture contract remains open in `docs/screenshots.json`; deployment
screenshot runs should use that file to produce full PNG captures for QA and
marketing artifacts before the screenshot-capture roadmap item is considered
complete.

## Commands And Routes

- Install command: `capell-comments:install`
- Retention command: `capell-comments:privacy-retention {--days=} {--email=}
{--site-id=} {--dry-run} {--json}`
- Public thread route name: `capell-comments.thread`
- Reply notification opt-out route name:
  `capell-comments.reply-notifications.disable`
- Verification route names: `capell-comments.verify`,
  `capell-comments.verify.store`
- Default route prefix: `capell/comments`

## Safety Notes

- Public output uses `PublicCommentData` and `PublicCommentableThreadData`.
- Public DTOs should stay free of moderation status, model IDs, author email,
  visitor hashes, tokens, permissions, and admin URLs.
- The public thread endpoint should not be cached as shared HTML.
- The auto-injected cached shell has regression coverage proving it does not
  expose comments, author PII, model identifiers, moderation state, Livewire
  snapshots, or admin URLs before the no-store thread endpoint loads.
- The Livewire public form passes honeypot and render-age metadata into
  `CreateCommentAction`; keep those checks before persistence.

## Verification

```bash
vendor/bin/pest packages/comments/tests --configuration=phpunit.xml
```

The current focused tests cover settings registration, settings resolution,
manifest requirements, health diagnostics, email verification, local and
external-provider spam scoring, moderator notification wiring, public thread
rendering, component submission, sanitization hardening, auto-inject shell
safety, reply pagination, reaction toggling, approved-reply notifications,
package architecture boundaries, commentable memoization, locale-pinned public
timestamps, performance budgets, privacy retention, throttling, and bot-trap
rejection.
