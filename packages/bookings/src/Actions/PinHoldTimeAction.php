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
class PinHoldTimeAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest, CarbonImmutable $startsAt, CarbonImmutable $endsAt): AppointmentRequest
    {
        if ($appointmentRequest->hold_expires_at !== null && $appointmentRequest->hold_expires_at->lessThanOrEqualTo(CarbonImmutable::now())) {
            throw ValidationException::withMessages([
                'hold_expires_at' => __('capell-bookings::validation.appointment_hold_expired'),
            ]);
        }

        $appointmentRequest = ProposeSlotTimeAction::run($appointmentRequest, $startsAt, $endsAt);

        $appointmentRequest->forceFill(['is_time_pinned' => true])->save();

        return $appointmentRequest->refresh();
    }
}
