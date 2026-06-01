<?php

declare(strict_types=1);

namespace Capell\Bookings\Database\Factories;

use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingAvailabilityWindow>
 */
class BookingAvailabilityWindowFactory extends Factory
{
    protected $model = BookingAvailabilityWindow::class;

    public function definition(): array
    {
        return [
            'service_id' => BookingService::factory(),
            'staff_member_id' => BookingStaffMember::factory(),
            'location_id' => BookingLocation::factory(),
            'status' => BookingAvailabilityStatusEnum::Available->value,
            'day_of_week' => 1,
            'starts_at' => '09:00:00',
            'ends_at' => '17:00:00',
            'timezone' => 'Europe/London',
            'capacity' => 1,
        ];
    }
}
