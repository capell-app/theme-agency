<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Capell\Comments\Notifications\CommentReplyNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class RequestCommentReplyNotificationAction
{
    use AsAction;

    public function handle(Comment $reply): ?CommentToken
    {
        if (! (bool) config('capell-comments.notifications.reply_authors', true)) {
            return null;
        }

        if ($reply->status !== CommentStatus::Approved || $reply->parent_id === null) {
            return null;
        }

        $reply->loadMissing(['author', 'parent.author']);

        $parent = $reply->parent;
        $parentAuthor = $parent?->author;

        if (! $parent instanceof Comment || ! $parentAuthor instanceof CommentAuthor || ! $parentAuthor->acceptsReplyNotifications()) {
            return null;
        }

        if ($reply->comment_author_id === $parentAuthor->getKey()) {
            return null;
        }

        $rawToken = Str::random(64);

        /** @var CommentToken $token */
        $token = CommentToken::query()->create([
            'site_id' => $reply->site_id,
            'comment_author_id' => $parentAuthor->getKey(),
            'comment_id' => $reply->getKey(),
            'type' => CommentTokenType::ReplyNotificationOptOut,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => null,
        ]);

        Notification::route('mail', (string) $parentAuthor->email)
            ->notify(new CommentReplyNotification($parent, $reply, $rawToken));

        return $token;
    }
}
