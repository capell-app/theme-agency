# Comments Reactions And Reply Notifications

Comments includes a lightweight engagement layer for public threads:

- visitors can toggle a Like reaction on approved public comments;
- public thread DTOs expose aggregate reaction counts only;
- verified parent authors can receive a queued email when an approved reply
  appears under their comment;
- reply notification emails include a tokenized opt-out link.

## Reactions

`ToggleCommentReactionAction` accepts a registered commentable model, a public
comment ID, and either an authenticated user or visitor request metadata. The
Action only accepts approved comments that belong to the supplied commentable.

Guest reactions are keyed by hashed IP and user-agent values through
`VisitorHasher`; authenticated reactions are keyed by user morph type and ID.
Neither actor key is exposed through public DTOs or Blade views.

`BuildPublicThreadAction` exposes `reactionCount` on each `PublicCommentData`
item. The public Livewire thread renders that count beside a translated Like
button and refreshes the thread after toggling.

## Reply Notifications

`RequestCommentReplyNotificationAction` runs when a reply becomes approved,
including both trusted-author auto-publish and moderator status transitions.
Notifications are skipped when:

- `capell-comments.notifications.reply_authors` is false;
- the comment is not an approved reply;
- the parent author is unverified, blocked, missing an email, or opted out;
- the reply author is the same author as the parent comment.

Opt-out tokens use `CommentTokenType::ReplyNotificationOptOut`.
`DisableCommentAuthorReplyNotificationsAction` consumes the token and stamps
`reply_notifications_disabled_at` on the author record.
