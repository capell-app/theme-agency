<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianHostRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, array{region: string, requests: int, expected_riders: int}> run(?int $siteId = null)
 */
final class BuildOpenSlotDemandHeatmapAction
{
    use AsAction;

    /**
     * @return Collection<int, array{region: string, requests: int, expected_riders: int}>
     */
    public function handle(?int $siteId = null): Collection
    {
        return EquestrianHostRequest::query()
            ->when($siteId !== null, static fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->get()
            ->groupBy(static fn (EquestrianHostRequest $request): string => $request->preferred_region ?: $request->postal_code ?: 'Unspecified')
            ->map(fn (Collection $requests, string $region): array => $this->mapDemandRow($requests, $region))
            ->sortByDesc('expected_riders')
            ->values();
    }

    /**
     * @param  Collection<int, EquestrianHostRequest>  $requests
     * @return array{region: string, requests: int, expected_riders: int}
     */
    private function mapDemandRow(Collection $requests, string $region): array
    {
        return [
            'region' => $region,
            'requests' => $this->integerValue($requests->count()),
            'expected_riders' => $requests->sum(static fn (EquestrianHostRequest $request): int => $request->expected_riders ?? 0),
        ];
    }

    private function integerValue(int $value): int
    {
        return $value;
    }
}
