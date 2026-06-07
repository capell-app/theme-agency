<?php

declare(strict_types=1);

namespace Capell\Events\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class EventOccurrenceViewData extends Data
{
    public function __construct(
        public string $title,
        public string $isoStartsAt,
        public string $displayStartsAt,
        public string $eventTimezone,
        public ?string $viewerDisplayStartsAt = null,
        public ?string $viewerTimezone = null,
        public ?string $url = null,
        public ?string $venueName = null,
        public ?CarbonImmutable $startsAt = null,
    ) {}
}
