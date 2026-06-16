<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Notifications;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PasswordExpiryWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CarbonImmutable $expiresAt,
        private readonly int $daysRemaining,
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
            ->subject(__('capell-password-policy::notifications.expiry_warning_subject'))
            ->line(__('capell-password-policy::notifications.expiry_warning_intro', [
                'days' => $this->daysRemaining,
                'date' => $this->expiresAt->toFormattedDateString(),
            ]))
            ->line(__('capell-password-policy::notifications.expiry_warning_action'));
    }
}
