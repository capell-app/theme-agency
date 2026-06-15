<?php

declare(strict_types=1);

namespace Capell\Bookings\Contracts;

use Capell\Bookings\Data\TravelEstimateData;
use Capell\Bookings\Models\BookingLocation;
use Carbon\CarbonImmutable;

interface TravelTimeProvider
{
    public function estimate(
        ?BookingLocation $origin,
        ?BookingLocation $destination,
        ?CarbonImmutable $departAt = null,
    ): TravelEstimateData;
}
