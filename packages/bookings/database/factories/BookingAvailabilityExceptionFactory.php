<?php

declare(strict_types=1);

namespace Capell\Bookings\Database\Factories;

use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingAvailabilityException>
 */
class BookingAvailabilityExceptionFactory extends Factory
{
    protected $model = BookingAvailabilityException::class;

    public function definition(): array
    {
        return [
            'service_id' => BookingService::factory(),
            'staff_member_id' => BookingStaffMember::factory(),
            'location_id' => BookingLocation::factory(),
            'status' => BookingAvailabilityStatusEnum::Blocked->value,
            'date' => '2026-06-01',
            'starts_at' => null,
            'ends_at' => null,
            'timezone' => 'Europe/London',
            'capacity' => null,
            'reason' => 'Holiday',
        ];
    }
}
