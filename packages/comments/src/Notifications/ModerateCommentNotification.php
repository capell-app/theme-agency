<?php

declare(strict_types=1);

namespace Capell\Comments\Notifications;

use Capell\Comments\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ModerateCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Comment $comment,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('capell-comments::messages.moderator_notification_subject'))
            ->line(__('capell-comments::messages.moderator_notification_line', [
                'status' => $this->comment->status->getLabel(),
            ]))
            ->line(__('capell-comments::messages.moderator_notification_author', [
                'author' => $this->authorName(),
            ]));
    }

    private function authorName(): string
    {
        $this->comment->loadMissing('author');
        $authorName = $this->comment->author?->name;

        return is_string($authorName) && trim($authorName) !== ''
            ? trim($authorName)
            : (string) __('capell-comments::generic.comment');
    }
}
