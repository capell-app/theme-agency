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
- `VerifyCommentAuthorEmailController` handles author verification links.
- `CommentsHealthCheck` reports real Diagnostics results for package storage,
  settings, route, and component wiring.

## Data And Retention

The package owns:

- `comment_authors`
- `comments`
- `comment_tokens`
- `comment_moderation_events`

Settings are stored through `CommentSettings`. Email and visitor identifiers are
handled through hash helpers and token records rather than exposed in public
thread DTOs.

## Commands And Routes

- Install command: `capell-comments:install`
- Public thread route name: `capell-comments.thread`
- Verification route names: `capell-comments.verify`,
  `capell-comments.verify.store`
- Default route prefix: `capell/comments`

## Safety Notes

- Public output uses `PublicCommentData` and `PublicCommentableThreadData`.
- Public DTOs should stay free of moderation status, model IDs, author email,
  visitor hashes, tokens, permissions, and admin URLs.
- The public thread endpoint should not be cached as shared HTML.
- The Livewire public form passes honeypot and render-age metadata into
  `CreateCommentAction`; keep those checks before persistence.

## Verification

```bash
vendor/bin/pest packages/comments/tests --configuration=phpunit.xml
```

The current focused tests cover settings registration, settings resolution,
manifest requirements, health diagnostics, email verification, public thread
rendering, component submission, and bot-trap rejection.
