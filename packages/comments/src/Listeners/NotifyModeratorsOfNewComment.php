<?php

declare(strict_types=1);

namespace Capell\Comments\Listeners;

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Events\CommentCreated;
use Capell\Comments\Notifications\ModerateCommentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final class NotifyModeratorsOfNewComment implements ShouldQueue
{
    use Queueable;

    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;

        if (! in_array($comment->status, [
            CommentStatus::PendingApproval,
            CommentStatus::PendingEmailVerification,
        ], true)) {
            return;
        }

        foreach ($this->moderatorEmails() as $email) {
            Notification::route('mail', $email)
                ->notify(new ModerateCommentNotification($comment));
        }
    }

    /**
     * @return list<string>
     */
    private function moderatorEmails(): array
    {
        $moderators = config('capell-comments.notifications.moderators', []);

        if (is_string($moderators)) {
            $moderators = [$moderators];
        }

        if (! is_array($moderators)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map(
                static fn (mixed $email): string => is_string($email) ? trim($email) : '',
                $moderators,
            ),
            static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
        )));
    }
}
