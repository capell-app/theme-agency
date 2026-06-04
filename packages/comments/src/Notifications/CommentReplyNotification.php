<?php

declare(strict_types=1);

namespace Capell\Comments\Notifications;

use Capell\Comments\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CommentReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Comment $parent,
        private readonly Comment $reply,
        private readonly string $optOutToken,
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
            ->subject(__('capell-comments::messages.reply_notification_subject'))
            ->line(__('capell-comments::messages.reply_notification_line'))
            ->line(__('capell-comments::messages.reply_notification_parent_excerpt', [
                'comment' => $this->excerpt($this->parent),
            ]))
            ->line($this->excerpt($this->reply))
            ->action(__('capell-comments::messages.reply_notification_opt_out'), route('capell-comments.reply-notifications.disable', [
                'token' => $this->optOutToken,
            ]));
    }

    private function excerpt(Comment $comment): string
    {
        return str($comment->body)
            ->squish()
            ->limit(160)
            ->toString();
    }
}
