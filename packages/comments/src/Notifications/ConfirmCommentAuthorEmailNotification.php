<?php

declare(strict_types=1);

namespace Capell\Comments\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmCommentAuthorEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token) {}

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
            ->subject(__('capell-comments::messages.verify_email_subject'))
            ->line(__('capell-comments::messages.verify_email_line'))
            ->action(__('capell-comments::messages.verify_email_action'), route('capell-comments.verify', [
                'token' => $this->token,
            ]));
    }
}
