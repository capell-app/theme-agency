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
class OfferTimeRangeAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest, CarbonImmutable $startsAt, CarbonImmutable $endsAt): AppointmentRequest
    {
        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'offered_window_ends_at' => __('capell-bookings::validation.appointment_end_after_start'),
            ]);
        }

        $appointmentRequest->forceFill([
            'offered_window_starts_at' => $startsAt,
            'offered_window_ends_at' => $endsAt,
            'is_time_pinned' => false,
        ])->save();

        return $appointmentRequest->refresh();
    }
}
