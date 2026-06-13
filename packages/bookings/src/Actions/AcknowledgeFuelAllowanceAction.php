<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequest $appointmentRequest)
 */
class AcknowledgeFuelAllowanceAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest): AppointmentRequest
    {
        $appointmentRequest->forceFill([
            'fuel_acknowledged_at' => CarbonImmutable::now(),
        ])->save();

        return $appointmentRequest->refresh();
    }
}
