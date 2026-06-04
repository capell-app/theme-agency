# Comments Spam Providers

Comments runs spam scoring through `ScoreCommentSpamAction` before a comment
can create verification tokens, notify moderators, or become public. The
default provider keeps the local checks for link count and blocked terms, and
host apps may append external providers for services such as Akismet,
Cloudflare Turnstile, or an internal reputation service.

## Provider Contract

Implement `Capell\Comments\Contracts\CommentSpamProvider`:

```php
<?php

declare(strict_types=1);

namespace App\Comments;

use Capell\Comments\Contracts\CommentSpamProvider;
use Capell\Comments\Data\CommentSpamCheckData;
use Capell\Comments\Data\CommentSpamScoreData;

final class AkismetCommentSpamProvider implements CommentSpamProvider
{
    public function score(CommentSpamCheckData $check): CommentSpamScoreData
    {
        $isSpam = false;

        // Call the external service here. Keep timeouts and circuit breakers
        // inside the adapter so public comment submission stays predictable.

        return new CommentSpamScoreData(
            linkCount: $check->linkCount,
            reasons: $isSpam ? ['akismet:spam'] : [],
        );
    }
}
```

`CommentSpamCheckData` includes the sanitized body, detected link count, site
ID, commentable type/key context, parent public ID, public author fields,
request IP/user-agent, and authenticated user type/ID when present.

## Configuration

Append providers in `config/capell-comments.php`:

```php
'spam' => [
    'providers' => [
        Capell\Comments\Support\Spam\LocalCommentSpamProvider::class,
        App\Comments\AkismetCommentSpamProvider::class,
    ],
],
```

Providers run in order. Any returned reason marks the comment as spam, stores
the combined unique `spam_reasons`, stamps `marked_spam_at`, and skips
verification-token creation.

Keep the local provider first unless the host app intentionally wants an
external provider to be the only scoring source.
