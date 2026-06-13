<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Capell\Bookings\Enums\ConfirmationPolicyEnum;
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
        public ?int $siteId = null,
        public ?int $portalAccountId = null,
        public ?int $lessonSeriesId = null,
        public ?CarbonImmutable $seriesOccurrenceDate = null,
        public ConfirmationPolicyEnum $confirmationPolicy = ConfirmationPolicyEnum::Manual,
        public ?CarbonImmutable $holdExpiresAt = null,
        public ?CarbonImmutable $offeredWindowStartsAt = null,
        public ?CarbonImmutable $offeredWindowEndsAt = null,
        public bool $isTimePinned = true,
        public ?int $staffMemberId = null,
        public ?int $locationId = null,
        public ?string $customerPhone = null,
        public ?string $notes = null,
        public ?string $source = null,
        public array $payload = [],
        public array $reminderPreferences = [],
    ) {}
}
