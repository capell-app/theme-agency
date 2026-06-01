<?php

declare(strict_types=1);

namespace Capell\Bookings\Notifications;

use Capell\Bookings\Enums\AppointmentNotificationTypeEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentWorkflowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly AppointmentRequest $appointmentRequest,
        private readonly AppointmentNotificationTypeEnum $type,
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
            ->subject($this->type->subject())
            ->greeting($this->type->greeting())
            ->line($this->type->body())
            ->line(__('capell-bookings::notification.service', [
                'service' => $this->appointmentRequest->service?->name ?? '',
            ]))
            ->line(__('capell-bookings::notification.starts_at', [
                'starts_at' => $this->appointmentRequest->requested_starts_at
                    ->setTimezone($this->appointmentRequest->timezone)
                    ->format('Y-m-d H:i'),
            ]))
            ->line(__('capell-bookings::notification.timezone', [
                'timezone' => $this->appointmentRequest->timezone,
            ]));
    }
}
