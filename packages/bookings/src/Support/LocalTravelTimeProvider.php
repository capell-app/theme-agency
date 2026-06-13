<?php

declare(strict_types=1);

namespace Capell\Bookings\Support;

use Capell\Bookings\Contracts\TravelTimeProvider;
use Capell\Bookings\Data\TravelEstimateData;
use Capell\Bookings\Models\BookingLocation;
use Carbon\CarbonImmutable;

final class LocalTravelTimeProvider implements TravelTimeProvider
{
    public function estimate(
        ?BookingLocation $origin,
        ?BookingLocation $destination,
        ?CarbonImmutable $departAt = null,
    ): TravelEstimateData {
        if ($origin === null || $destination === null || $origin->is($destination)) {
            return new TravelEstimateData(durationMinutes: 0, distanceMiles: 0.0);
        }

        $distanceMiles = $this->distanceMiles($origin, $destination);
        $durationMinutes = max(5, (int) ceil(($distanceMiles / 24.0) * 60.0));

        return new TravelEstimateData(
            durationMinutes: $durationMinutes,
            distanceMiles: round($distanceMiles, 2),
            provider: 'local',
            confidence: 40,
        );
    }

    private function distanceMiles(BookingLocation $origin, BookingLocation $destination): float
    {
        if ($origin->latitude !== null && $origin->longitude !== null && $destination->latitude !== null && $destination->longitude !== null) {
            return $this->haversineMiles(
                (float) $origin->latitude,
                (float) $origin->longitude,
                (float) $destination->latitude,
                (float) $destination->longitude,
            );
        }

        return $origin->postal_code === $destination->postal_code ? 2.0 : 8.0;
    }

    private function haversineMiles(float $originLatitude, float $originLongitude, float $destinationLatitude, float $destinationLongitude): float
    {
        $earthRadiusMiles = 3958.7613;
        $latitudeDelta = deg2rad($destinationLatitude - $originLatitude);
        $longitudeDelta = deg2rad($destinationLongitude - $originLongitude);
        $originLatitudeRadians = deg2rad($originLatitude);
        $destinationLatitudeRadians = deg2rad($destinationLatitude);

        $angle = sin($latitudeDelta / 2) ** 2
            + cos($originLatitudeRadians) * cos($destinationLatitudeRadians) * sin($longitudeDelta / 2) ** 2;

        return $earthRadiusMiles * (2 * atan2(sqrt($angle), sqrt(1 - $angle)));
    }
}
