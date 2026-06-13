<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class DayPlanData extends Data
{
    /**
     * @param  Collection<int, DayPlanStopData>  $stops
     * @param  list<string>  $warnings
     */
    public function __construct(
        public int $staffMemberId,
        public CarbonImmutable $date,
        public Collection $stops,
        public int $totalTravelMinutes,
        public float $totalTravelMiles,
        public array $warnings = [],
    ) {}
}
