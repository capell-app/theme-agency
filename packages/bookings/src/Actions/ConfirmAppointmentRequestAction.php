<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentNotificationTypeEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequest $appointmentRequest)
 */
class ConfirmAppointmentRequestAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest): AppointmentRequest
    {
        return DB::transaction(function () use ($appointmentRequest): AppointmentRequest {
            /** @var AppointmentRequest $lockedAppointmentRequest */
            $lockedAppointmentRequest = AppointmentRequest::query()
                ->whereKey($appointmentRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedAppointmentRequest->status->canConfirm()) {
                throw ValidationException::withMessages([
                    'status' => __('capell-bookings::validation.appointment_not_confirmable'),
                ]);
            }

            if (
                $lockedAppointmentRequest->status === AppointmentRequestStatusEnum::Provisional
                && $lockedAppointmentRequest->hold_expires_at !== null
                && $lockedAppointmentRequest->hold_expires_at->lessThanOrEqualTo(CarbonImmutable::now())
            ) {
                throw ValidationException::withMessages([
                    'hold_expires_at' => __('capell-bookings::validation.appointment_hold_expired'),
                ]);
            }

            $previousStatus = $lockedAppointmentRequest->status;

            $lockedAppointmentRequest->forceFill([
                'status' => AppointmentRequestStatusEnum::Confirmed,
                'confirmed_at' => CarbonImmutable::now(),
            ])->save();

            RecordAppointmentAuditLogAction::run(
                appointmentRequest: $lockedAppointmentRequest,
                event: AppointmentAuditEventEnum::Confirmed,
                statusFrom: $previousStatus,
                statusTo: AppointmentRequestStatusEnum::Confirmed,
            );

            QueueAppointmentNotificationAction::run(
                appointmentRequest: $lockedAppointmentRequest,
                type: AppointmentNotificationTypeEnum::Confirmation,
            );

            return $lockedAppointmentRequest;
        });
    }
}
