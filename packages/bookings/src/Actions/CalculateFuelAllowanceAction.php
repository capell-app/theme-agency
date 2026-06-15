<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequest $appointmentRequest, float $distanceMiles)
 */
class CalculateFuelAllowanceAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest, float $distanceMiles): AppointmentRequest
    {
        $fuelRateConfig = config('capell-bookings.fuel_rate_pence_per_mile', 500);
        $fuelRatePencePerMile = is_numeric($fuelRateConfig) ? (int) $fuelRateConfig : 500;
        $fuelAllowancePence = (int) round(max(0.0, $distanceMiles) * $fuelRatePencePerMile);

        $appointmentRequest->forceFill([
            'travel_distance_miles' => round($distanceMiles, 2),
            'fuel_allowance_pence' => $fuelAllowancePence,
        ])->save();

        return $appointmentRequest->refresh();
    }
}
