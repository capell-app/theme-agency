<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\TravelTimeProvider;
use Capell\Bookings\Data\TravelEstimateData;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingTravelAdjustment;
use Capell\Bookings\Models\BookingTravelObservation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static TravelEstimateData run(?BookingLocation $origin, ?BookingLocation $destination, ?CarbonImmutable $departAt = null)
 */
class EstimateTravelTimeAction
{
    use AsAction;

    public function handle(
        ?BookingLocation $origin,
        ?BookingLocation $destination,
        ?CarbonImmutable $departAt = null,
    ): TravelEstimateData {
        $providerEstimate = app(TravelTimeProvider::class)->estimate($origin, $destination, $departAt);
        $observedEstimate = $this->observedEstimate($origin, $destination);
        $activeAdjustmentMinutes = $this->activeAdjustmentMinutes($origin, $destination, $departAt ?? CarbonImmutable::now());
        $accessOverheadMinutes = $destination instanceof BookingLocation ? $destination->access_overhead_minutes : 0;

        $durationMinutes = max(
            0,
            ($observedEstimate instanceof TravelEstimateData ? $observedEstimate->durationMinutes : $providerEstimate->durationMinutes)
                + $activeAdjustmentMinutes
                + $accessOverheadMinutes,
        );

        return new TravelEstimateData(
            durationMinutes: $durationMinutes,
            distanceMiles: $observedEstimate instanceof TravelEstimateData ? $observedEstimate->distanceMiles : $providerEstimate->distanceMiles,
            provider: $observedEstimate instanceof TravelEstimateData ? 'observed' : $providerEstimate->provider,
            confidence: $observedEstimate instanceof TravelEstimateData ? 75 : $providerEstimate->confidence,
            meta: [
                'adjustment_minutes' => $activeAdjustmentMinutes,
                'access_overhead_minutes' => $accessOverheadMinutes,
            ],
        );
    }

    private function observedEstimate(?BookingLocation $origin, ?BookingLocation $destination): ?TravelEstimateData
    {
        /** @var EloquentCollection<int, BookingTravelObservation> $observations */
        $observations = BookingTravelObservation::query()
            ->where('origin_location_id', $origin?->getKey())
            ->where('destination_location_id', $destination?->getKey())
            ->latest('observed_at')
            ->limit(10)
            ->get();

        if ($observations->isEmpty()) {
            return null;
        }

        $durations = $observations->map(static fn (BookingTravelObservation $observation): int => $observation->duration_minutes)->sort()->values();
        $distances = $observations->map(static fn (BookingTravelObservation $observation): float => $observation->distance_miles)->sort()->values();
        $middleIndex = intdiv($durations->count(), 2);

        return new TravelEstimateData(
            durationMinutes: (int) $durations->get($middleIndex),
            distanceMiles: (float) $distances->get($middleIndex),
            provider: 'observed',
            confidence: 75,
        );
    }

    private function activeAdjustmentMinutes(?BookingLocation $origin, ?BookingLocation $destination, CarbonImmutable $departAt): int
    {
        return (int) BookingTravelAdjustment::query()
            ->where('active', true)
            ->where(function (Builder $query) use ($origin): void {
                $query->whereNull('origin_location_id')
                    ->orWhere('origin_location_id', $origin?->getKey());
            })
            ->where(function (Builder $query) use ($destination): void {
                $query->whereNull('destination_location_id')
                    ->orWhere('destination_location_id', $destination?->getKey());
            })
            ->where(function (Builder $query) use ($departAt): void {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $departAt);
            })
            ->where(function (Builder $query) use ($departAt): void {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $departAt);
            })
            ->sum('extra_minutes');
    }
}
