<?php

declare(strict_types=1);

namespace Capell\Bookings\Database\Factories;

use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Models\LessonSeries;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonSeries>
 */
final class LessonSeriesFactory extends Factory
{
    protected $model = LessonSeries::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activeFrom = CarbonImmutable::now('Europe/London')->startOfWeek();

        return [
            'site_id' => null,
            'portal_account_id' => null,
            'service_id' => BookingService::factory(),
            'staff_member_id' => BookingStaffMember::factory(),
            'location_id' => BookingLocation::factory(),
            'customer_name' => 'Jordan Lee',
            'customer_email' => 'jordan@example.com',
            'customer_phone' => null,
            'day_of_week' => $activeFrom->dayOfWeek,
            'starts_at' => '10:00:00',
            'duration_minutes' => null,
            'timezone' => 'Europe/London',
            'cadence_weeks' => 1,
            'active_from' => $activeFrom,
            'active_until' => null,
            'materialized_until' => null,
            'auto_confirm_instances' => true,
            'active' => true,
            'reminder_preferences' => [],
            'payload' => [],
        ];
    }
}
