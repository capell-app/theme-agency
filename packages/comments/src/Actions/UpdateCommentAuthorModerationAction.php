<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Models\CommentAuthor;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCommentAuthorModerationAction
{
    use AsAction;

    public function trust(CommentAuthor $author): CommentAuthor
    {
        return $this->handle($author, trusted: true);
    }

    public function block(CommentAuthor $author): CommentAuthor
    {
        return $this->handle($author, blocked: true);
    }

    public function unblock(CommentAuthor $author): CommentAuthor
    {
        return $this->handle($author, blocked: false);
    }

    public function verify(CommentAuthor $author): CommentAuthor
    {
        return $this->handle($author, verified: true);
    }

    public function handle(
        CommentAuthor $author,
        ?bool $trusted = null,
        ?bool $blocked = null,
        ?bool $verified = null,
    ): CommentAuthor {
        $updates = [];

        if ($trusted !== null) {
            $updates['trusted_at'] = $trusted ? now() : null;
        }

        if ($blocked !== null) {
            $updates['blocked_at'] = $blocked ? now() : null;
        }

        if ($verified !== null) {
            $updates['email_verified_at'] = $verified ? now() : null;
        }

        if ($updates !== []) {
            $author->forceFill($updates)->save();
        }

        return $author->refresh();
    }
}
