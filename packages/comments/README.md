# Comments

Comments adds moderated public comment threads to registered Capell content models without exposing admin or moderation state in cached frontend HTML.

## What It Adds

- Comment authors, comments, verification tokens, and moderation event records.
- A post-load Livewire thread endpoint with `no-store, private` cache headers.
- `ResolvePublicCommentableThreadAction` for resolving a public-safe thread DTO from a registered commentable model.
- `BuildPublicThreadAction` for approved comment DTOs only.
- Comment settings for site and commentable-type overrides.

## Public Safety

- Public thread reads return nothing when comments are disabled, publication is disabled, or the commentable is not publicly visible.
- Public DTOs include public IDs, sanitized body text, author display names, timestamps, depth, reply counts, and children.
- Public DTOs do not expose moderation status, model IDs, commentable IDs, author email, visitor hashes, tokens, or admin URLs.

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/comments/tests --configuration=phpunit.xml
```
