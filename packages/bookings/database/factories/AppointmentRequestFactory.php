<?php

declare(strict_types=1);

namespace Capell\Bookings\Database\Factories;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AppointmentRequest>
 */
class AppointmentRequestFactory extends Factory
{
    protected $model = AppointmentRequest::class;

    public function definition(): array
    {
        $startsAt = CarbonImmutable::now('UTC')->addWeek()->setTime(10, 0);

        return [
            'service_id' => BookingService::factory(),
            'staff_member_id' => BookingStaffMember::factory(),
            'location_id' => BookingLocation::factory(),
            'status' => AppointmentRequestStatusEnum::Requested->value,
            'requested_starts_at' => $startsAt,
            'requested_ends_at' => $startsAt->addMinutes(45),
            'timezone' => 'Europe/London',
            'customer_name' => 'Jordan Lee',
            'customer_email' => 'jordan@example.com',
            'calendar_uid' => (string) Str::uuid(),
            'requested_at' => CarbonImmutable::now('UTC'),
        ];
    }
}
