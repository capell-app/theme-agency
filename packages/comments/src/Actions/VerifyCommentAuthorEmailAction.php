<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class VerifyCommentAuthorEmailAction
{
    use AsAction;

    public function handle(string $token): ?CommentAuthor
    {
        return DB::transaction(function () use ($token): ?CommentAuthor {
            /** @var CommentToken|null $publicToken */
            $publicToken = CommentToken::query()
                ->where('type', CommentTokenType::VerifyEmail)
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $publicToken instanceof CommentToken || ! $publicToken->isUsable()) {
                return null;
            }

            $author = $publicToken->author;
            if (! $author instanceof CommentAuthor) {
                return null;
            }

            $author->forceFill(['email_verified_at' => now()])->save();
            $publicToken->forceFill(['consumed_at' => now()])->save();

            if ($publicToken->comment instanceof Comment) {
                $publicToken->comment->forceFill([
                    'email_verified_at' => now(),
                    'status' => $publicToken->comment->status === CommentStatus::PendingEmailVerification
                        ? CommentStatus::PendingApproval
                        : $publicToken->comment->status,
                ])->save();
            }

            CommentModerationEventAction::run(
                comment: $publicToken->comment,
                action: 'email_verified',
                author: $author,
            );

            return $author->refresh();
        });
    }
}
