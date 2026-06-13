<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class DayPlanStopData extends Data
{
    /**
     * @param  list<string>  $warnings
     */
    public function __construct(
        public int $appointmentRequestId,
        public ?int $locationId,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public bool $timePinned,
        public int $travelBeforeMinutes = 0,
        public float $travelBeforeMiles = 0.0,
        public array $warnings = [],
    ) {}
}
