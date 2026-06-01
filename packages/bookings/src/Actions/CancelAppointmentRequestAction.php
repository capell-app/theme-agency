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
 * @method static AppointmentRequest run(AppointmentRequest $appointmentRequest, ?string $reason = null)
 */
class CancelAppointmentRequestAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest, ?string $reason = null): AppointmentRequest
    {
        return DB::transaction(function () use ($appointmentRequest, $reason): AppointmentRequest {
            /** @var AppointmentRequest $lockedAppointmentRequest */
            $lockedAppointmentRequest = AppointmentRequest::query()
                ->whereKey($appointmentRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedAppointmentRequest->status->canCancel()) {
                throw ValidationException::withMessages([
                    'status' => __('capell-bookings::validation.appointment_not_cancellable'),
                ]);
            }

            $previousStatus = $lockedAppointmentRequest->status;

            $lockedAppointmentRequest->forceFill([
                'status' => AppointmentRequestStatusEnum::Cancelled,
                'cancelled_at' => CarbonImmutable::now(),
            ])->save();

            RecordAppointmentAuditLogAction::run(
                appointmentRequest: $lockedAppointmentRequest,
                event: AppointmentAuditEventEnum::Cancelled,
                statusFrom: $previousStatus,
                statusTo: AppointmentRequestStatusEnum::Cancelled,
                message: $reason,
            );

            QueueAppointmentNotificationAction::run(
                appointmentRequest: $lockedAppointmentRequest,
                type: AppointmentNotificationTypeEnum::Cancellation,
            );

            return $lockedAppointmentRequest;
        });
    }
}
