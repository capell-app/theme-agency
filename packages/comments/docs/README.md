# Comments Docs

Comments lets a Capell site collect public replies on registered content while
keeping moderation, verification, and author records in the admin workflow.

## Read Next

- [Overview](overview.md)
- [Privacy and retention](privacy-and-retention.md)
- [Package README](../README.md)
- [Package documentation standard](../../../docs/package-documentation-standard.md)

## Developer Starting Points

| Need                         | Start Here                                                                                        |
| ---------------------------- | ------------------------------------------------------------------------------------------------- |
| Install the package          | `src/Console/Commands/InstallCommentsCommand.php`                                                 |
| Register commentable content | `src/Support/CommentableRegistry.php`, `src/Actions/RegisterDefaultCommentablesAction.php`        |
| Render a public thread       | `src/Livewire/CommentThreadComponent.php`, `src/Actions/ResolvePublicCommentableThreadAction.php` |
| Create and verify comments   | `src/Actions/CreateCommentAction.php`, `src/Actions/VerifyCommentAuthorEmailAction.php`           |
| Moderate comments            | `src/Filament/Pages/CommentModerationInbox.php`, `src/Actions/TransitionCommentStatusAction.php`  |
| Prune privacy data           | `src/Actions/ApplyCommentPrivacyRetentionAction.php`, `src/Console/Commands/PruneCommentPrivacyDataCommand.php` |
| Tune settings                | `config/capell-comments.php`, `src/Settings/CommentSettings.php`                                  |

## Public Safety Checklist

- Public views must use public thread DTOs, not raw comment models.
- Do not expose author email, visitor hashes, tokens, commentable IDs, model IDs,
  moderation status, or admin URLs.
- Keep public thread responses private and uncached.
- Keep admin moderation state out of cached frontend HTML.
