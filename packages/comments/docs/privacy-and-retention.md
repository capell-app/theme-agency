# Comments Privacy And Retention

Comments stores public discussion content separately from commenter identity and
request metadata. Public thread DTOs never expose author email addresses,
visitor hashes, moderation notes, tokens, model IDs, or admin URLs.

## Stored Identifiers

- `comment_authors.name` and `comment_authors.email` are encrypted at rest.
- `comment_authors.email_hash` is an HMAC lookup key.
- `comments.visitor_ip_hash` and `comments.visitor_user_agent_hash` are HMAC
  request fingerprints used for moderation and abuse analysis.
- `comment_reactions.visitor_ip_hash` and
  `comment_reactions.visitor_user_agent_hash` are HMAC request fingerprints
  used to toggle anonymous Like reactions without exposing actor identity.
- `comment_tokens.token_hash` stores verification tokens as one-way hashes.
- `comment_authors.internal_notes` and `comments.moderation_note` are private
  admin-only fields and may contain operational context.

## Hash Secrets

Set package-specific hash secrets in production:

```env
CAPELL_COMMENTS_EMAIL_HASH_SECRET=...
CAPELL_COMMENTS_VISITOR_HASH_SECRET=...
```

If these values are omitted, Comments falls back to `app.key` for hash
generation. That keeps local installs working, but it means rotating `app.key`
changes future hashes and existing `email_hash` / visitor-hash lookups no longer
match. For production sites, keep the Comments secrets stable and rotate them
only as a planned privacy operation.

When rotating a hash secret:

1. Keep the old secret available until old lookup windows are no longer needed.
2. Run `capell-comments:privacy-retention --dry-run --json` to confirm stale
   visitor hashes and tokens are ready to prune.
3. Rotate the secret during a maintenance window.
4. Accept that old hashes cannot be reversed or re-keyed unless the original
   email / visitor value is supplied again.

## Retention Command

Run the package-owned privacy retention command from the repository or host app:

```bash
php artisan capell-comments:privacy-retention
```

The command uses `config('capell-comments.retention.days')`, defaulting to 180
days. Override it per run:

```bash
php artisan capell-comments:privacy-retention --days=90
```

Use `--dry-run` to report matched records without writing changes:

```bash
php artisan capell-comments:privacy-retention --days=90 --dry-run --json
```

The retention pass:

- Deletes expired or consumed comment tokens older than the retention cutoff.
- Clears old comment visitor IP hashes, user-agent hashes, and moderation notes.
- Clears old anonymous reaction visitor IP and user-agent hashes.
- Leaves public comment bodies, public IDs, status, thread shape, and submitted
  timestamps intact.

## Subject Erasure

To anonymize a specific commenter by email:

```bash
php artisan capell-comments:privacy-retention --email=reader@example.com
```

Use `--site-id=` to scope the email erasure to one site:

```bash
php artisan capell-comments:privacy-retention --email=reader@example.com --site-id=12
```

Subject erasure:

- Replaces the author display name with the translated anonymized-author label.
- Clears author email, email hash, user binding, trust/block/verification
  timestamps, and internal notes.
- Deletes the author's comment tokens.
- Clears visitor hashes and moderation notes on that author's comments.
- Preserves public comment bodies and thread structure.

This gives operators a practical right-to-erasure workflow without breaking
historic public discussions.
