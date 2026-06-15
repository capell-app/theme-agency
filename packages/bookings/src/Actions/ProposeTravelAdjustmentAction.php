<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingTravelAdjustment;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingTravelAdjustment run(?BookingLocation $origin, ?BookingLocation $destination, int $extraMinutes, ?string $reason = null, ?CarbonImmutable $effectiveFrom = null, ?CarbonImmutable $effectiveUntil = null)
 */
class ProposeTravelAdjustmentAction
{
    use AsAction;

    public function handle(
        ?BookingLocation $origin,
        ?BookingLocation $destination,
        int $extraMinutes,
        ?string $reason = null,
        ?CarbonImmutable $effectiveFrom = null,
        ?CarbonImmutable $effectiveUntil = null,
    ): BookingTravelAdjustment {
        /** @var BookingTravelAdjustment $adjustment */
        $adjustment = BookingTravelAdjustment::query()->create([
            'origin_location_id' => $origin?->getKey(),
            'destination_location_id' => $destination?->getKey(),
            'extra_minutes' => $extraMinutes,
            'reason' => $reason,
            'effective_from' => $effectiveFrom,
            'effective_until' => $effectiveUntil,
            'active' => true,
        ]);

        return $adjustment;
    }
}
