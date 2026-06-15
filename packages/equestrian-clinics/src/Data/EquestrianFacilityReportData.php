<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Data;

use Spatie\LaravelData\Data;

final class EquestrianFacilityReportData extends Data
{
    /**
     * @param  array<string, int>  $resources
     * @param  list<array<string, mixed>>  $slots
     */
    public function __construct(
        public string $venueName,
        public string $tourDayTitle,
        public int $expectedRiders,
        public int $expectedHorses,
        public array $resources,
        public array $slots,
    ) {}
}
