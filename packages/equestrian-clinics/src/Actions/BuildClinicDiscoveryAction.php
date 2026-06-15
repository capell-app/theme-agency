<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianTourDayStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{tour_days: Collection<int, array<string, mixed>>, venues: Collection<int, array{id: int, name: string}>, heatmap: Collection<int, array{region: string, requests: int, expected_riders: int}>} run(array<string, mixed> $filters = [], ?int $siteId = null)
 */
final class BuildClinicDiscoveryAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{tour_days: Collection<int, array<string, mixed>>, venues: Collection<int, array{id: int, name: string}>, heatmap: Collection<int, array{region: string, requests: int, expected_riders: int}>}
     */
    public function handle(array $filters = [], ?int $siteId = null): array
    {
        $search = $this->stringFilter($filters['search'] ?? null);
        $postcode = $this->stringFilter($filters['postcode'] ?? null);
        $venueId = $this->integerFilter($filters['venue_id'] ?? null);
        $latitude = $this->floatFilter($filters['latitude'] ?? null);
        $longitude = $this->floatFilter($filters['longitude'] ?? null);

        $tourDayModels = EquestrianTourDay::query()
            ->with(['venue', 'slots'])
            ->where('is_public', true)
            ->where('status', EquestrianTourDayStatusEnum::Published)
            ->when($siteId !== null, static fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->when($venueId !== null, static fn (Builder $query): Builder => $query->where('venue_id', $venueId))
            ->when($search !== null, static function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('venue', static function (Builder $venueQuery) use ($search): void {
                            $venueQuery
                                ->where('name', 'like', '%' . $search . '%')
                                ->orWhere('address_line', 'like', '%' . $search . '%')
                                ->orWhere('postal_code', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($postcode !== null, static function (Builder $query) use ($postcode): Builder {
                return $query->whereHas('venue', static function (Builder $venueQuery) use ($postcode): void {
                    $venueQuery->where('postal_code', 'like', '%' . $postcode . '%');
                });
            })
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->limit($latitude !== null && $longitude !== null ? 100 : 24)
            ->get();

        $tourDays = $tourDayModels
            ->map(fn (EquestrianTourDay $tourDay): array => $this->mapTourDay($tourDay, $latitude, $longitude));

        if ($latitude !== null && $longitude !== null) {
            $tourDays = $tourDays->sortBy('distance_miles')->values();
        }

        $tourDays = $tourDays->take(24)->values();

        $venues = EquestrianTourDay::query()
            ->with('venue')
            ->where('is_public', true)
            ->where('status', EquestrianTourDayStatusEnum::Published)
            ->when($siteId !== null, static fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->where('starts_at', '>=', now()->startOfDay())
            ->get()
            ->map(static fn (EquestrianTourDay $tourDay): array => [
                'id' => $tourDay->venue->id,
                'name' => $tourDay->venue->name,
            ])
            ->unique('id')
            ->sortBy('name')
            ->values();

        return [
            'tour_days' => $tourDays,
            'venues' => $venues,
            'heatmap' => BuildOpenSlotDemandHeatmapAction::run($siteId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTourDay(EquestrianTourDay $tourDay, ?float $latitude = null, ?float $longitude = null): array
    {
        $slots = $tourDay->slots
            ->sortBy('starts_at')
            ->map(static fn (EquestrianTourDaySlot $slot): array => [
                'id' => $slot->id,
                'title' => $slot->title,
                'archetype' => $slot->archetype->getLabel(),
                'starts_at' => $slot->starts_at->format('H:i'),
                'ends_at' => $slot->ends_at->format('H:i'),
                'remaining_capacity' => $slot->remainingCapacity(),
                'capacity_max' => $slot->capacity_max,
                'skill_tier' => $slot->skill_tier,
                'price_pence' => $slot->price_pence,
                'waitlist_count' => $slot->waitlist_count,
                'is_full' => $slot->remainingCapacity() === 0,
            ])
            ->values();

        return [
            'id' => $tourDay->id,
            'title' => $tourDay->title,
            'date' => $tourDay->starts_at->format('D j M Y'),
            'starts_at' => $tourDay->starts_at->format('H:i'),
            'ends_at' => $tourDay->ends_at->format('H:i'),
            'venue' => [
                'id' => $tourDay->venue->id,
                'name' => $tourDay->venue->name,
                'address_line' => $tourDay->venue->address_line,
                'postal_code' => $tourDay->venue->postal_code,
                'google_maps_url' => $tourDay->venue->google_maps_url,
                'facility_notes' => $tourDay->venue->facility_notes,
            ],
            'distance_miles' => $this->distanceMiles($tourDay, $latitude, $longitude),
            'spots_remaining' => $slots->sum(static fn (array $slot): int => $slot['remaining_capacity']),
            'slots' => $slots,
        ];
    }

    private function stringFilter(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function integerFilter(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $filteredValue = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($filteredValue) ? $filteredValue : null;
    }

    private function floatFilter(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $filteredValue = filter_var($value, FILTER_VALIDATE_FLOAT);

        return is_float($filteredValue) ? $filteredValue : null;
    }

    private function distanceMiles(EquestrianTourDay $tourDay, ?float $latitude, ?float $longitude): ?float
    {
        if ($latitude === null || $longitude === null || $tourDay->venue->latitude === null || $tourDay->venue->longitude === null) {
            return null;
        }

        $earthRadiusMiles = 3958.7613;
        $latitudeDelta = deg2rad($tourDay->venue->latitude - $latitude);
        $longitudeDelta = deg2rad($tourDay->venue->longitude - $longitude);
        $originLatitude = deg2rad($latitude);
        $venueLatitude = deg2rad($tourDay->venue->latitude);

        $haversine = sin($latitudeDelta / 2) ** 2
            + cos($originLatitude) * cos($venueLatitude) * sin($longitudeDelta / 2) ** 2;

        return round($earthRadiusMiles * 2 * asin(min(1.0, sqrt($haversine))), 1);
    }
}
