# Comments

<!-- prettier-ignore-start -->

## What This Plugin Adds

Comments is an **Available**, **Schema-owning** Capell package in the **Capell Engagement** product group. It ships as `capell-app/comments` and extends these surfaces: admin, frontend.

Add moderated, threaded discussion to any Capell page or article - with cache-safe public rendering, encrypted author records, and per-site moderation controls, no custom code required.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: comments
- Composer package: `capell-app/comments`
- Namespace: `Capell\Comments`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Add moderated, threaded discussion to any Capell page or article - with cache-safe public rendering, encrypted author records, and per-site moderation controls, no custom code required.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Comment moderation inbox (admin, required).
- Comments admin resource (admin, required).
- Comment authors admin resource (admin, required).
- Public comment thread (frontend, required).

## Technical Shape

- Service providers: `Capell\Comments\Providers\CommentsServiceProvider`, `Capell\Comments\Providers\AdminServiceProvider`, `Capell\Comments\Providers\FrontendServiceProvider`.
- Config files: `packages/comments/config/capell-comments.php`.
- Migrations: `packages/comments/database/migrations/2026_05_24_000001_create_comment_authors_table.php`, `packages/comments/database/migrations/2026_05_24_000002_create_comments_table.php`, `packages/comments/database/migrations/2026_05_24_000003_create_comment_tokens_table.php`, `packages/comments/database/migrations/2026_05_24_000004_create_comment_moderation_events_table.php`, `packages/comments/database/migrations/2026_05_24_000006_add_reply_notification_opt_out_to_comment_authors_table.php`, `packages/comments/database/migrations/2026_05_24_000007_create_comment_reactions_table.php`.
- Settings migrations: `packages/comments/database/settings/2026_05_24_000005_create_comments_settings.php`.
- Settings classes: `CommentSettings`.
- Models: `Comment`, `CommentAuthor`, `CommentModerationEvent`, `CommentReaction`, `CommentToken`.
- Filament classes: `CommentModerationInbox`, `CommentAuthorResource`, `ListCommentAuthors`, `CommentResource`, `ListComments`, `CommentsTable`, `CommentSettingsSchema`, `CommentsDashboardSettingsContributor`, `CommentStatsWidget`, `LatestCommentsWidget`.
- Livewire components: `CommentThreadComponent`.
- Route files: `packages/comments/routes/web.php`.
- Policies: `CommentAuthorPolicy`, `CommentPolicy`.
- Events: `CommentCreated`.
- Listeners: `NotifyModeratorsOfNewComment`.
- Actions: `ApplyCommentPrivacyRetentionAction`, `BuildPublicThreadAction`, `CommentModerationEventAction`, `CreateCommentAction`, `DisableCommentAuthorReplyNotificationsAction`, `InstallCommentsPackageAction`, `InvalidateCommentableCacheAction`, `RegisterCommentEmailTemplatesAction`, `RegisterDefaultCommentablesAction`, `RequestCommentEmailVerificationAction`, `RequestCommentReplyNotificationAction`, `ResolvePublicCommentableThreadAction`, `and 5 more`.
- Data objects: `CommentEmailTemplateData`, `CommentPrivacyRetentionResultData`, `CommentReactionResultData`, `CommentSpamCheckData`, `CommentSpamScoreData`, `CommentableTypeData`, `CreateCommentData`, `PublicCommentData`, `PublicCommentableThreadData`.
- Command signatures: `capell-comments:install`, `capell-comments:privacy-retention`.
- Console command classes: `InstallCommentsCommand`, `PruneCommentPrivacyDataCommand`.
- Manifest contributions: `dashboard-widget: Capell\Comments\Filament\Widgets\CommentStatsWidget`, `dashboard-widget: Capell\Comments\Filament\Widgets\LatestCommentsWidget`, `frontend-component: Capell\Comments\Livewire\CommentThreadComponent`.
- Health checks: `Capell\Comments\Health\CommentsHealthCheck`.
- Blade views: `packages/comments/resources/views/emails/approved.blade.php`, `packages/comments/resources/views/emails/pending-moderation.blade.php`, `packages/comments/resources/views/emails/rejected.blade.php`, `packages/comments/resources/views/emails/reply-notification.blade.php`, `packages/comments/resources/views/emails/verify-email.blade.php`, `packages/comments/resources/views/filament/comment-context.blade.php`, `packages/comments/resources/views/filament/moderation-inbox.blade.php`, `packages/comments/resources/views/livewire/partials/comment-list.blade.php`, `packages/comments/resources/views/livewire/thread-livewire.blade.php`, `packages/comments/resources/views/livewire/thread-shell.blade.php`, `packages/comments/resources/views/livewire/thread.blade.php`, `packages/comments/resources/views/reply-notifications-disabled.blade.php`, `and 1 more`.
- Cache tags: `comments`.

## Data Model

- Required tables: `comment_authors`, `comments`, `comment_tokens`, `comment_moderation_events`, `comment_reactions`.
- Models: `Comment`, `CommentAuthor`, `CommentModerationEvent`, `CommentReaction`, `CommentToken`.
- Migration files: `2026_05_24_000001_create_comment_authors_table.php`, `2026_05_24_000002_create_comments_table.php`, `2026_05_24_000003_create_comment_tokens_table.php`, `2026_05_24_000004_create_comment_moderation_events_table.php`, `2026_05_24_000006_add_reply_notification_opt_out_to_comment_authors_table.php`, `2026_05_24_000007_create_comment_reactions_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: `Capell\Comments\Settings\CommentSettings`.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `comments`.
- Commands: `capell-comments:install`, `capell-comments:privacy-retention`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/comments`.
2. Run the required setup: `php artisan capell-comments:install`.
3. Open the related Capell admin surface and verify Comments appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Blog](../../blog/README.md), [Email Studio](../../email-studio/README.md), [Html Cache](../../html-cache/README.md).
- Focused tests: `vendor/bin/pest packages/comments/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
