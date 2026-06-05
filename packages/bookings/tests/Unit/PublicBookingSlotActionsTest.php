<?php

declare(strict_types=1);

use Inertia\Inertia;

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\BuildAvailableBookingSlotsAction;
use Capell\Bookings\Actions\BuildPublicBookingRequestPropsAction;
use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Rendering\BladePublicBookingRequestRenderer;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('builds available booking slots from active services and availability windows', function (): void {
    [$service, $staffMember, $location, $monday] = createPublicSlotFixture(capacity: 2);

    $slots = BuildAvailableBookingSlotsAction::run(
        serviceId: (int) $service->getKey(),
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
        timezone: 'Europe/London',
        from: $monday,
        days: 1,
    );

    expect($slots)->toHaveCount(3)
        ->and(array_column($slots, 'starts_at'))->toBe([
            '2026-06-08T09:00:00+01:00',
            '2026-06-08T09:15:00+01:00',
            '2026-06-08T09:30:00+01:00',
        ])
        ->and($slots[0]['capacity_remaining'])->toBe(2);
});

it('converts availability windows and exceptions from their stored timezone into the requested timezone', function (): void {
    config()->set('capell-bookings.public_slot_interval_minutes', 15);

    [$service, $staffMember, $location, $monday] = createPublicSlotFixture(capacity: 1);

    BookingAvailabilityException::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => BookingAvailabilityStatusEnum::Blocked,
        'date' => $monday->toDateString(),
        'starts_at' => '09:15:00',
        'ends_at' => '09:45:00',
        'timezone' => 'Europe/London',
    ]);

    $slots = BuildAvailableBookingSlotsAction::run(
        serviceId: (int) $service->getKey(),
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
        timezone: 'America/New_York',
        from: CarbonImmutable::parse('2026-06-08 00:00:00', 'America/New_York'),
        days: 1,
    );

    expect($slots)->toHaveCount(2)
        ->and(array_column($slots, 'starts_at'))->toBe([
            '2026-06-08T04:00:00-04:00',
            '2026-06-08T04:30:00-04:00',
        ])
        ->and($slots[0]['timezone'])->toBe('America/New_York')
        ->and($slots[0]['label'])->toBe('Mon 8 Jun, 04:00');
});

it('counts existing appointments in their stored timezone when building cross-timezone slots', function (): void {
    config()->set('capell-bookings.public_slot_interval_minutes', 30);

    [$service, $staffMember, $location] = createPublicSlotFixture(capacity: 1);
    $bookedStartsAt = CarbonImmutable::parse('2026-06-08 04:00:00', 'America/New_York');

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'requested_starts_at' => $bookedStartsAt,
        'requested_ends_at' => $bookedStartsAt->addMinutes(30),
        'timezone' => 'America/New_York',
    ]);

    $slots = BuildAvailableBookingSlotsAction::run(
        serviceId: (int) $service->getKey(),
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
        timezone: 'America/New_York',
        from: CarbonImmutable::parse('2026-06-08 00:00:00', 'America/New_York'),
        days: 1,
    );

    expect(array_column($slots, 'starts_at'))->toBe([
        '2026-06-08T04:30:00-04:00',
    ]);
});

it('omits slots covered by blocked availability exceptions', function (): void {
    [$service, $staffMember, $location, $monday] = createPublicSlotFixture();

    BookingAvailabilityException::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => BookingAvailabilityStatusEnum::Blocked,
        'date' => $monday->toDateString(),
        'starts_at' => '09:15:00',
        'ends_at' => '09:45:00',
        'timezone' => 'Europe/London',
    ]);

    $slots = BuildAvailableBookingSlotsAction::run(
        serviceId: (int) $service->getKey(),
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
        timezone: 'Europe/London',
        from: $monday,
        days: 1,
    );

    expect(array_column($slots, 'starts_at'))->toBe([
        '2026-06-08T09:00:00+01:00',
        '2026-06-08T09:30:00+01:00',
    ]);
});

it('omits full slots when existing appointment requests consume capacity', function (): void {
    [$service, $staffMember, $location, $monday] = createPublicSlotFixture(capacity: 1);
    $startsAt = $monday->setTime(9, 0);

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Requested,
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addMinutes(30),
        'timezone' => 'Europe/London',
    ]);

    $slots = BuildAvailableBookingSlotsAction::run(
        serviceId: (int) $service->getKey(),
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
        timezone: 'Europe/London',
        from: $monday,
        days: 1,
    );

    expect(array_column($slots, 'starts_at'))->toBe([
        '2026-06-08T09:30:00+01:00',
    ]);
});

it('builds public booking props with eager options and optional lazy slots', function (): void {
    [$service, $staffMember, $location] = createPublicSlotFixture();

    $request = Request::create('/bookings', Symfony\Component\HttpFoundation\Request::METHOD_GET, [
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'timezone' => 'Europe/London',
    ]);

    $eagerProps = BuildPublicBookingRequestPropsAction::run($request, lazySlots: false);
    $lazyProps = BuildPublicBookingRequestPropsAction::run($request, lazySlots: true);

    expect($eagerProps['options']['services'])->toHaveCount(1)
        ->and($eagerProps['slots'])->toBeArray()
        ->and($eagerProps['slots'])->not->toBeEmpty()
        ->and((string) $eagerProps['postUrl'])->toEndWith('/bookings')
        ->and($eagerProps['timezone'])->toBe('Europe/London');

    if (class_exists(Inertia::class)) {
        expect($lazyProps['slots'])->not->toBeArray();
    }
});

it('uses the blade booking request renderer by default', function (): void {
    expect(resolve(PublicBookingRequestRenderer::class))->toBeInstanceOf(BladePublicBookingRequestRenderer::class);
});

/**
 * @return array{0: BookingService, 1: BookingStaffMember, 2: BookingLocation, 3: CarbonImmutable}
 */
function createPublicSlotFixture(int $capacity = 1): array
{
    $service = BookingService::factory()->create([
        'duration_minutes' => 30,
        'lead_time_minutes' => 0,
        'buffer_before_minutes' => 0,
        'buffer_after_minutes' => 0,
        'max_future_days' => 30,
    ]);
    $staffMember = BookingStaffMember::factory()->create();
    $location = BookingLocation::factory()->create();
    $monday = CarbonImmutable::parse('2026-06-08 00:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'day_of_week' => $monday->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '10:00:00',
        'timezone' => 'Europe/London',
        'capacity' => $capacity,
        'status' => BookingAvailabilityStatusEnum::Available,
    ]);

    return [$service, $staffMember, $location, $monday];
}
