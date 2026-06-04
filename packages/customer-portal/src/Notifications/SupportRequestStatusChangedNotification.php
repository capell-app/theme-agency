<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Notifications;

use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SupportRequestStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PortalSupportRequest $supportRequest,
        private readonly SupportRequestStatus $previousStatus,
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
            ->subject(__('capell-customer-portal::generic.notifications.support_status_changed_subject'))
            ->line(__('capell-customer-portal::generic.notifications.support_status_changed_line'))
            ->line(__('capell-customer-portal::generic.notifications.support_subject', [
                'subject' => $this->supportRequest->subject,
            ]))
            ->line(__('capell-customer-portal::generic.notifications.support_status_changed_status', [
                'previous' => $this->previousStatus->getLabel(),
                'status' => $this->supportRequest->status->getLabel(),
            ]))
            ->action(
                __('capell-customer-portal::generic.notifications.open_portal'),
                route('capell-customer-portal.dashboard'),
            );
    }
}
