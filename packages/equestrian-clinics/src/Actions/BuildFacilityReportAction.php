<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Data\EquestrianFacilityReportData;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianFacilityReportData run(EquestrianTourDay $tourDay)
 */
final class BuildFacilityReportAction
{
    use AsAction;

    public function handle(EquestrianTourDay $tourDay): EquestrianFacilityReportData
    {
        $tourDay->loadMissing(['venue', 'slots.facilityBookings.facilityResource']);

        $slots = $tourDay->slots;
        $resourceCounts = $this->resourceCounts($slots);
        $slotRows = array_values($slots
            ->sortBy('starts_at')
            ->map(static fn (EquestrianTourDaySlot $slot): array => [
                'starts_at' => $slot->starts_at->toIso8601String(),
                'ends_at' => $slot->ends_at->toIso8601String(),
                'title' => $slot->title,
                'archetype' => $slot->archetype->value,
                'booked_count' => $slot->booked_count,
                'waitlist_count' => $slot->waitlist_count,
            ])
            ->values()
            ->all());

        return new EquestrianFacilityReportData(
            venueName: $tourDay->venue->name,
            tourDayTitle: $tourDay->title,
            expectedRiders: $slots->sum(static fn (EquestrianTourDaySlot $slot): int => $slot->booked_count),
            expectedHorses: (int) $slots->filter(static fn (EquestrianTourDaySlot $slot): bool => isset($slot->meta['horse_profile_id']))->count(),
            resources: $resourceCounts,
            slots: $slotRows,
        );
    }

    /**
     * @param  Collection<int, EquestrianTourDaySlot>  $slots
     * @return array<string, int>
     */
    private function resourceCounts(Collection $slots): array
    {
        $counts = [];

        foreach ($slots as $slot) {
            foreach ($slot->facilityBookings as $facilityBooking) {
                $name = $facilityBooking->facilityResource->name;
                $counts[$name] = ($counts[$name] ?? 0) + $facilityBooking->quantity;
            }
        }

        ksort($counts);

        return $counts;
    }
}
