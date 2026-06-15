<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<array{area: string, appointment_count: int, travel_distance_miles: float, average_travel_minutes: int|null}> run(?int $siteId = null)
 */
class BuildServiceAreaHeatmapAction
{
    use AsAction;

    /**
     * @return list<array{area: string, appointment_count: int, travel_distance_miles: float, average_travel_minutes: int|null}>
     */
    public function handle(?int $siteId = null): array
    {
        /** @var list<AppointmentRequest> $appointments */
        $appointments = AppointmentRequest::query()
            ->when($siteId !== null, static fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->get()
            ->all();

        $areas = [];

        foreach ($appointments as $appointmentRequest) {
            $payload = is_array($appointmentRequest->payload) ? $appointmentRequest->payload : [];
            $postalCodeValue = $payload['postal_code'] ?? $payload['postcode'] ?? null;
            $postalCode = is_scalar($postalCodeValue) ? strtoupper(trim((string) $postalCodeValue)) : '';
            $area = $postalCode !== '' ? strtok($postalCode, ' ') : __('capell-bookings::admin.service_area_heatmap.unknown_area');

            if (! is_string($area) || $area === '') {
                $area = __('capell-bookings::admin.service_area_heatmap.unknown_area');
            }

            $areas[$area] ??= [
                'appointment_count' => 0,
                'travel_distance_miles' => 0.0,
                'travel_minutes' => 0,
                'travel_samples' => 0,
            ];

            $areas[$area]['appointment_count']++;
            $areas[$area]['travel_distance_miles'] += (float) ($appointmentRequest->travel_distance_miles ?? 0);

            if ($appointmentRequest->travel_duration_minutes !== null) {
                $areas[$area]['travel_minutes'] += $appointmentRequest->travel_duration_minutes;
                $areas[$area]['travel_samples']++;
            }
        }

        $rows = [];

        foreach ($areas as $area => $areaData) {
            $travelSamples = (int) $areaData['travel_samples'];

            $rows[] = [
                'area' => (string) $area,
                'appointment_count' => (int) $areaData['appointment_count'],
                'average_travel_minutes' => $travelSamples > 0 ? (int) round(((int) $areaData['travel_minutes']) / $travelSamples) : null,
                'travel_distance_miles' => round((float) $areaData['travel_distance_miles'], 2),
            ];
        }

        usort($rows, static fn (array $left, array $right): int => $right['appointment_count'] <=> $left['appointment_count']);

        return $rows;
    }
}
