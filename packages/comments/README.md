# Comments

Comments adds moderated, configurable, cache-safe threaded discussion surfaces
to registered Capell content.

## At A Glance

- Package: `capell-app/comments`
- Namespace: `Capell\Comments\`
- Product group: Capell Engagement
- Tier: premium
- Surfaces: admin, frontend
- Service providers:
  `Capell\Comments\Providers\CommentsServiceProvider`,
  `Capell\Comments\Providers\AdminServiceProvider`,
  `Capell\Comments\Providers\FrontendServiceProvider`
- Install command: `capell-comments:install`
- Required tables: `comment_authors`, `comments`, `comment_tokens`,
  `comment_moderation_events`

## Why It Helps Your Capell Workflow

- Site owners can add public discussion to pages or package content without
  building a custom moderation system.
- Editors get a moderation inbox, author records, status transitions, and
  dashboard widgets instead of managing comments from raw tables.
- Developers register commentable content types once and let the package resolve
  public-safe thread data for the frontend.
- Operators can keep cached pages safe because the public thread is loaded
  separately with private, no-store response headers.

## Best Used With

- [Blog](../blog/README.md) for article discussion.
- [Email Studio](../email-studio/README.md) for richer notification workflows.
- [HTML Cache](../html-cache/README.md) when public pages are cached.

## What It Adds

- Comment authors, comments, verification tokens, and moderation event records.
- Configurable identity, publication, verification, throttling, spam, and
  notification settings in `config/capell-comments.php`.
- Public `capell-comments.thread` and email verification routes under the
  configured `route_prefix`.
- `CommentThreadComponent` for post-load public thread rendering.
- Public comment form bot-trap controls: a hidden honeypot field and
  configurable minimum form age.
- Automatic spam scoring for configured link-count and blocked-term rules,
  storing reasons on the comment and routing flagged submissions to `Spam`
  before verification mail is sent.
- Diagnostics-ready `CommentsHealthCheck` coverage for storage tables, settings
  registration, the public thread route, and the public thread Livewire
  component.
- Admin resources for comment and author moderation.
- `CommentStatsWidget` and `LatestCommentsWidget` for admin visibility.
- `ResolvePublicCommentableThreadAction` and `BuildPublicThreadAction` for
  converting registered content into public thread DTOs.

## Public Safety

- Public thread reads return nothing when comments are disabled, publication is
  disabled, or the commentable model is not publicly visible.
- Public DTOs include public IDs, sanitized body text, author display names,
  timestamps, depth, reply counts, and children.
- Public DTOs do not expose moderation status, model IDs, commentable IDs,
  author email, visitor hashes, tokens, or admin URLs.
- The thread endpoint is designed for dynamic frontend loading rather than
  embedding moderation state in cached page HTML.

## Runtime Surface

| Area          | Path                                                                                                           |
| ------------- | -------------------------------------------------------------------------------------------------------------- |
| Config        | `config/capell-comments.php`                                                                                   |
| Routes        | `routes/web.php`                                                                                               |
| Install       | `src/Console/Commands/InstallCommentsCommand.php`, `src/Actions/InstallCommentsPackageAction.php`              |
| Public thread | `src/Livewire/CommentThreadComponent.php`, `src/Http/Controllers/RenderCommentThreadController.php`            |
| Verification  | `src/Actions/RequestCommentEmailVerificationAction.php`, `src/Actions/VerifyCommentAuthorEmailAction.php`      |
| Moderation    | `src/Actions/TransitionCommentStatusAction.php`, `src/Filament/Pages/CommentModerationInbox.php`               |
| Settings      | `src/Settings/CommentSettings.php`, `src/Filament/Settings/CommentSettingsSchema.php`                          |
| Data objects  | `src/Data/PublicCommentData.php`, `src/Data/PublicCommentableThreadData.php`, `src/Data/CreateCommentData.php`, `src/Data/CommentSpamScoreData.php` |
| Models        | `src/Models/Comment.php`, `src/Models/CommentAuthor.php`, `src/Models/CommentToken.php`                        |

## Install Impact

- Adds package migrations and settings migration.
- Adds frontend routes under `capell/comments` by default.
- Adds admin moderation resources and dashboard widgets.
- Does not make every model commentable automatically; commentable types are
  resolved through the package registry and defaults.

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot manifest](docs/screenshots.json)
- [Package documentation standard](../../docs/package-documentation-standard.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/comments/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Keep public thread data anonymous-safe and moderation-free.
- Keep registration logic in package providers or Actions rather than public
  Blade.
- Keep bot-trap fields public-form only; API/programmatic comment creation may
  omit them, but public forms should pass them through `CreateCommentData`.
- Keep spam scoring in `ScoreCommentSpamAction`; new heuristics should return
  explicit `spam_reasons` and must not leak into public thread DTOs.
- Keep the primary public submission throttle keyed to commentable + IP data;
  attacker-controlled fields such as author email must not reset that bucket.
- Keep `CommentsHealthCheck` aligned with the manifest's critical package-health
  claim whenever routes, storage, settings, or frontend component aliases change.
- Add focused docs when new commentable registries, notification flows, or
  moderation transitions become public extension points.
