<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingTravelObservation;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingTravelObservation run(?BookingLocation $origin, ?BookingLocation $destination, int $durationMinutes, float $distanceMiles = 0.0, string $source = 'observed', ?CarbonImmutable $observedAt = null)
 */
class RecordTravelObservationAction
{
    use AsAction;

    public function handle(
        ?BookingLocation $origin,
        ?BookingLocation $destination,
        int $durationMinutes,
        float $distanceMiles = 0.0,
        string $source = 'observed',
        ?CarbonImmutable $observedAt = null,
    ): BookingTravelObservation {
        if ($durationMinutes < 0 || $distanceMiles < 0) {
            throw ValidationException::withMessages([
                'duration_minutes' => __('capell-bookings::validation.travel_observation_positive'),
            ]);
        }

        /** @var BookingTravelObservation $observation */
        $observation = BookingTravelObservation::query()->create([
            'origin_location_id' => $origin?->getKey(),
            'destination_location_id' => $destination?->getKey(),
            'duration_minutes' => $durationMinutes,
            'distance_miles' => $distanceMiles,
            'source' => $source,
            'observed_at' => $observedAt ?? CarbonImmutable::now(),
        ]);

        return $observation;
    }
}
