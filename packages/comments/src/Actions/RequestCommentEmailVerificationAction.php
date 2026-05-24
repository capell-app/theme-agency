<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Capell\Comments\Notifications\ConfirmCommentAuthorEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestCommentEmailVerificationAction
{
    use AsAction;

    public function handle(CommentAuthor $author, ?Comment $comment = null): CommentToken
    {
        $rawToken = Str::random(64);

        /** @var CommentToken $token */
        $token = CommentToken::query()->create([
            'site_id' => $author->site_id,
            'comment_author_id' => $author->getKey(),
            'comment_id' => $comment?->getKey(),
            'type' => CommentTokenType::VerifyEmail,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => now()->addHours((int) config('capell-comments.token_expiry_hours', 72)),
        ]);

        $token->setAttribute('plain_text_token', $rawToken);

        if (is_string($author->email) && $author->email !== '') {
            Notification::route('mail', $author->email)
                ->notify(new ConfirmCommentAuthorEmailNotification($rawToken));
        }

        return $token;
    }
}
