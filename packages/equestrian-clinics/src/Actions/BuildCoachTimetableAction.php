<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianFacilityBooking;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, mixed> run(EquestrianTourDay $tourDay)
 */
final class BuildCoachTimetableAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(EquestrianTourDay $tourDay): array
    {
        $tourDay->loadMissing(['venue', 'slots.facilityBookings.facilityResource']);

        return [
            'title' => $tourDay->title,
            'date' => $tourDay->starts_at->format('D j M Y'),
            'window' => $tourDay->starts_at->format('H:i') . ' - ' . $tourDay->ends_at->format('H:i'),
            'venue' => [
                'name' => $tourDay->venue->name,
                'address_line' => $tourDay->venue->address_line,
                'postal_code' => $tourDay->venue->postal_code,
                'facility_notes' => $tourDay->venue->facility_notes,
                'parking_notes' => $tourDay->venue->parking_notes,
                'google_maps_url' => $tourDay->venue->google_maps_url,
            ],
            'minimum_viable_clinic' => [
                'paid_attendees' => $tourDay->minimum_paid_attendees,
                'revenue_pence' => $tourDay->minimum_revenue_pence,
                'current_attendees' => $tourDay->slots->sum(static fn (EquestrianTourDaySlot $slot): int => $slot->booked_count),
                'current_revenue_pence' => (int) $tourDay->slots->sum(static fn (EquestrianTourDaySlot $slot): int => $slot->booked_count * $slot->price_pence),
            ],
            'slots' => $tourDay->slots
                ->sortBy('starts_at')
                ->map(static fn (EquestrianTourDaySlot $slot): array => [
                    'time' => $slot->starts_at->format('H:i') . ' - ' . $slot->ends_at->format('H:i'),
                    'title' => $slot->title,
                    'archetype' => $slot->archetype->getLabel(),
                    'skill_tier' => $slot->skill_tier,
                    'booked_count' => $slot->booked_count,
                    'capacity_max' => $slot->capacity_max,
                    'waitlist_count' => $slot->waitlist_count,
                    'horse_profile_id' => $slot->meta['horse_profile_id'] ?? null,
                    'resources' => $slot->facilityBookings
                        ->map(static fn (EquestrianFacilityBooking $facilityBooking): string => $facilityBooking->facilityResource->name . ' x' . $facilityBooking->quantity)
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
        ];
    }
}
