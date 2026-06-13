<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianFacilityBooking;
use Capell\EquestrianClinics\Models\EquestrianFacilityResource;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianFacilityBooking run(EquestrianFacilityResource $facilityResource, EquestrianTourDaySlot $slot, int $quantity = 1)
 */
final class ReserveFacilityResourceAction
{
    use AsAction;

    public function handle(EquestrianFacilityResource $facilityResource, EquestrianTourDaySlot $slot, int $quantity = 1): EquestrianFacilityBooking
    {
        return DB::transaction(function () use ($facilityResource, $slot, $quantity): EquestrianFacilityBooking {
            /** @var EquestrianFacilityResource $lockedResource */
            $lockedResource = EquestrianFacilityResource::query()
                ->whereKey($facilityResource->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $reservedQuantity = EquestrianFacilityBooking::query()
                ->where('facility_resource_id', $lockedResource->getKey())
                ->where('starts_at', '<', $slot->ends_at)
                ->where('ends_at', '>', $slot->starts_at)
                ->sum('quantity');

            if ($reservedQuantity + $quantity > $lockedResource->capacity) {
                throw ValidationException::withMessages([
                    'facility_resource_id' => __('capell-equestrian-clinics::validation.facility_capacity_exceeded'),
                ]);
            }

            /** @var EquestrianFacilityBooking $booking */
            $booking = EquestrianFacilityBooking::query()->create([
                'facility_resource_id' => $lockedResource->getKey(),
                'tour_day_slot_id' => $slot->getKey(),
                'starts_at' => $slot->starts_at,
                'ends_at' => $slot->ends_at,
                'quantity' => $quantity,
            ]);

            return $booking;
        });
    }
}
