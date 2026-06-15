<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Spatie\LaravelData\Data;

class TravelEstimateData extends Data
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public int $durationMinutes,
        public float $distanceMiles,
        public string $provider = 'local',
        public int $confidence = 50,
        public array $meta = [],
    ) {}
}
