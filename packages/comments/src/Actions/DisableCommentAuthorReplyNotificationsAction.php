<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class DisableCommentAuthorReplyNotificationsAction
{
    use AsAction;

    public function handle(string $token): ?CommentAuthor
    {
        return DB::transaction(function () use ($token): ?CommentAuthor {
            /** @var CommentToken|null $commentToken */
            $commentToken = CommentToken::query()
                ->where('type', CommentTokenType::ReplyNotificationOptOut)
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $commentToken instanceof CommentToken || ! $commentToken->isUsable()) {
                return null;
            }

            $author = $commentToken->author;

            if (! $author instanceof CommentAuthor) {
                return null;
            }

            $author->forceFill(['reply_notifications_disabled_at' => now()])->save();
            $commentToken->forceFill(['consumed_at' => now()])->save();

            return $author->refresh();
        });
    }
}
