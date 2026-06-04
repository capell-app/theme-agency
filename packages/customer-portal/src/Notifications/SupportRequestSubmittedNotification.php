<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Notifications;

use Capell\CustomerPortal\Models\PortalSupportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SupportRequestSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PortalSupportRequest $supportRequest,
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
            ->subject(__('capell-customer-portal::generic.notifications.support_submitted_subject'))
            ->line(__('capell-customer-portal::generic.notifications.support_submitted_line'))
            ->line(__('capell-customer-portal::generic.notifications.support_subject', [
                'subject' => $this->supportRequest->subject,
            ]))
            ->action(
                __('capell-customer-portal::generic.notifications.open_portal'),
                route('capell-customer-portal.dashboard'),
            );
    }
}
