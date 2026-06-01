<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class AvailabilityExceptionData extends Data
{
    public function __construct(
        public CarbonImmutable $date,
        public BookingAvailabilityStatusEnum $status = BookingAvailabilityStatusEnum::Blocked,
        public ?string $startsAt = null,
        public ?string $endsAt = null,
        public string $timezone = 'UTC',
        public ?int $serviceId = null,
        public ?int $staffMemberId = null,
        public ?int $locationId = null,
        public ?int $capacity = null,
        public ?string $reason = null,
        public ?string $notes = null,
        public array $meta = [],
    ) {}
}
