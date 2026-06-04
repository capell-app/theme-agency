<?php

declare(strict_types=1);

namespace Capell\Events\Notifications;

use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistrationNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(
        private readonly EventRegistration $registration,
        private readonly EventNotificationTypeEnum $type,
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
        $occurrence = $this->registration->occurrence;
        $event = $occurrence->event;

        return (new MailMessage)
            ->subject($this->subject($event->name))
            ->line($event->name)
            ->line($occurrence->starts_at->setTimezone($occurrence->timezone)->format('j F Y H:i') ?? '')
            ->line(__('capell-events::notification.thank_you'));
    }

    private function subject(string $eventName): string
    {
        return match ($this->type) {
            EventNotificationTypeEnum::Cancellation => __('capell-events::notification.cancellation_subject', ['event' => $eventName]),
            EventNotificationTypeEnum::Reminder => __('capell-events::notification.reminder_subject', ['event' => $eventName]),
            EventNotificationTypeEnum::WaitlistPromotion => __('capell-events::notification.waitlist_subject', ['event' => $eventName]),
            default => __('capell-events::notification.confirmation_subject', ['event' => $eventName]),
        };
    }
}
