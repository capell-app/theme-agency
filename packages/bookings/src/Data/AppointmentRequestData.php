<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class AppointmentRequestData extends Data
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $reminderPreferences
     */
    public function __construct(
        public int $serviceId,
        public CarbonImmutable $requestedStartsAt,
        public string $timezone,
        public string $customerName,
        public string $customerEmail,
        public ?CarbonImmutable $requestedEndsAt = null,
        public ?int $staffMemberId = null,
        public ?int $locationId = null,
        public ?string $customerPhone = null,
        public ?string $notes = null,
        public ?string $source = null,
        public array $payload = [],
        public array $reminderPreferences = [],
    ) {}
}
