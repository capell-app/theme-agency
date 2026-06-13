<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequest $appointmentRequest, CarbonImmutable $startsAt, CarbonImmutable $endsAt)
 */
class ProposeSlotTimeAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest, CarbonImmutable $startsAt, CarbonImmutable $endsAt): AppointmentRequest
    {
        if (
            $appointmentRequest->offered_window_starts_at === null
            || $appointmentRequest->offered_window_ends_at === null
            || $startsAt->lessThan($appointmentRequest->offered_window_starts_at)
            || $endsAt->greaterThan($appointmentRequest->offered_window_ends_at)
            || $endsAt->lessThanOrEqualTo($startsAt)
        ) {
            throw ValidationException::withMessages([
                'requested_starts_at' => __('capell-bookings::validation.appointment_outside_offered_window'),
            ]);
        }

        $appointmentRequest->forceFill([
            'requested_starts_at' => $startsAt,
            'requested_ends_at' => $endsAt,
            'is_time_pinned' => false,
        ])->save();

        return $appointmentRequest->refresh();
    }
}
