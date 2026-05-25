<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentModerationEvent;
use Lorisleiva\Actions\Concerns\AsAction;

class CommentModerationEventAction
{
    use AsAction;

    public function handle(
        ?Comment $comment,
        string $action,
        ?CommentStatus $previousStatus = null,
        ?CommentStatus $newStatus = null,
        ?CommentAuthor $author = null,
        ?int $moderatorId = null,
        ?string $note = null,
    ): CommentModerationEvent {
        $eventAuthor = $author ?? $comment?->author;
        $siteId = $comment instanceof Comment ? $comment->site_id : $eventAuthor?->site_id;

        /** @var CommentModerationEvent $event */
        $event = CommentModerationEvent::query()->create([
            'site_id' => $siteId,
            'comment_id' => $comment?->getKey(),
            'comment_author_id' => $eventAuthor?->getKey(),
            'moderator_id' => $moderatorId,
            'action' => $action,
            'previous_status' => $previousStatus?->value,
            'new_status' => $newStatus?->value,
            'note' => $note,
            'occurred_at' => now()->toImmutable(),
        ]);

        return $event;
    }
}
