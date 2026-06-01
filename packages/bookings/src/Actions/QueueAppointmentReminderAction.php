<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentNotificationTypeEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static void run(AppointmentRequest $appointmentRequest, ?CarbonImmutable $sendAt = null)
 */
class QueueAppointmentReminderAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest, ?CarbonImmutable $sendAt = null): void
    {
        if ($appointmentRequest->status !== AppointmentRequestStatusEnum::Confirmed) {
            throw ValidationException::withMessages([
                'status' => __('capell-bookings::validation.appointment_reminder_requires_confirmed'),
            ]);
        }

        QueueAppointmentNotificationAction::run(
            appointmentRequest: $appointmentRequest,
            type: AppointmentNotificationTypeEnum::Reminder,
            sendAt: $sendAt,
        );
    }
}
