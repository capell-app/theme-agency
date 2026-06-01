<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Spatie\LaravelData\Data;

class AvailabilityWindowData extends Data
{
    public function __construct(
        public int $dayOfWeek,
        public string $startsAt,
        public string $endsAt,
        public string $timezone,
        public BookingAvailabilityStatusEnum $status = BookingAvailabilityStatusEnum::Available,
        public ?int $serviceId = null,
        public ?int $staffMemberId = null,
        public ?int $locationId = null,
        public int $capacity = 1,
    ) {}
}
