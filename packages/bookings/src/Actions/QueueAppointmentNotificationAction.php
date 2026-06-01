<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentNotificationTypeEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Notifications\AppointmentWorkflowNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static void run(AppointmentRequest $appointmentRequest, AppointmentNotificationTypeEnum $type, ?CarbonImmutable $sendAt = null)
 */
class QueueAppointmentNotificationAction
{
    use AsAction;

    public function handle(
        AppointmentRequest $appointmentRequest,
        AppointmentNotificationTypeEnum $type,
        ?CarbonImmutable $sendAt = null,
    ): void {
        $notification = new AppointmentWorkflowNotification(
            appointmentRequest: $appointmentRequest->loadMissing('service'),
            type: $type,
        );

        if ($sendAt !== null) {
            $notification->delay($sendAt);
        }

        Notification::route('mail', $appointmentRequest->customer_email)
            ->notify($notification);

        RecordAppointmentAuditLogAction::run(
            appointmentRequest: $appointmentRequest,
            event: $type === AppointmentNotificationTypeEnum::Reminder
                ? AppointmentAuditEventEnum::ReminderQueued
                : AppointmentAuditEventEnum::NotificationQueued,
            statusFrom: $appointmentRequest->status,
            statusTo: $appointmentRequest->status,
            payload: [
                'channel' => 'mail',
                'email' => $appointmentRequest->customer_email,
                'type' => $type->value,
                'send_at' => $sendAt?->toIso8601String(),
            ],
        );
    }
}
