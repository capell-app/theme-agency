<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Enums\BookingLocationTypeEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static array{services: int, staff: int, locations: int, availability_windows: int, appointment_requests: int} run()
 */
final class InstallBookingsDemoAction
{
    use AsObject;

    /**
     * @return array{services: int, staff: int, locations: int, availability_windows: int, appointment_requests: int}
     */
    public function handle(): array
    {
        $service = BookingService::query()->updateOrCreate(
            ['name' => 'Demo consultation'],
            [
                'active' => true,
                'description' => 'A seeded consultation service for testing the public booking request flow.',
                'duration_minutes' => 60,
                'buffer_before_minutes' => 15,
                'buffer_after_minutes' => 15,
                'lead_time_minutes' => 120,
                'max_future_days' => 45,
                'confirmation_required' => true,
                'meta' => ['demo' => true],
            ],
        );

        $staffMember = BookingStaffMember::query()->updateOrCreate(
            ['display_name' => 'Avery Morgan'],
            [
                'active' => true,
                'email' => 'avery@example.test',
                'phone' => '+44 7700 900123',
                'timezone' => 'Europe/London',
                'title' => 'Lead consultant',
                'meta' => ['demo' => true],
            ],
        );

        $location = BookingLocation::query()->updateOrCreate(
            ['name' => 'Demo studio'],
            [
                'active' => true,
                'access_overhead_minutes' => 10,
                'city' => 'London',
                'country' => 'GB',
                'line1' => '1 Demo Street',
                'postal_code' => 'SW1A 1AA',
                'service_area' => 'Central London',
                'timezone' => 'Europe/London',
                'type' => BookingLocationTypeEnum::Physical,
                'meta' => ['demo' => true],
            ],
        );

        BookingAvailabilityWindow::query()->updateOrCreate(
            [
                'service_id' => $service->getKey(),
                'staff_member_id' => $staffMember->getKey(),
                'location_id' => $location->getKey(),
                'day_of_week' => 2,
            ],
            [
                'capacity' => 2,
                'starts_at' => '09:00:00',
                'ends_at' => '16:00:00',
                'timezone' => 'Europe/London',
                'status' => BookingAvailabilityStatusEnum::Available,
                'meta' => ['demo' => true],
            ],
        );

        $startsAt = CarbonImmutable::now('Europe/London')->next('Tuesday')->setTime(10, 0)->utc();

        AppointmentRequest::query()->updateOrCreate(
            [
                'service_id' => $service->getKey(),
                'staff_member_id' => $staffMember->getKey(),
                'location_id' => $location->getKey(),
                'customer_email' => 'jordan@example.test',
                'requested_starts_at' => $startsAt,
            ],
            [
                'calendar_uid' => (string) Str::uuid(),
                'customer_name' => 'Jordan Lee',
                'customer_phone' => '+44 7700 900456',
                'requested_at' => CarbonImmutable::now('UTC'),
                'requested_ends_at' => $startsAt->addMinutes(60),
                'site_id' => $this->siteId(),
                'source' => 'bookings-demo',
                'status' => AppointmentRequestStatusEnum::Requested,
                'timezone' => 'Europe/London',
                'payload' => ['demo' => true],
            ],
        );

        return [
            'services' => BookingService::query()->where('meta->demo', true)->count(),
            'staff' => BookingStaffMember::query()->where('meta->demo', true)->count(),
            'locations' => BookingLocation::query()->where('meta->demo', true)->count(),
            'availability_windows' => BookingAvailabilityWindow::query()->where('meta->demo', true)->count(),
            'appointment_requests' => AppointmentRequest::query()->where('source', 'bookings-demo')->count(),
        ];
    }

    private function siteId(): ?int
    {
        if (! Schema::hasTable('sites')) {
            return null;
        }

        $siteId = DB::table('sites')->value('id');

        return is_numeric($siteId) ? (int) $siteId : null;
    }
}
