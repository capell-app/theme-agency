<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

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

            $lockedAppointmentRequest->forceFill([
                'status' => AppointmentRequestStatusEnum::Confirmed,
                'confirmed_at' => CarbonImmutable::now(),
            ])->save();

            return $lockedAppointmentRequest;
        });
    }
}
