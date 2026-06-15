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

it('builds public booking props without private package internals for renderer overrides', function (): void {
    [$service, $staffMember, $location] = createPublicSlotFixture();

    $service->forceFill([
        'instructions' => 'ServicePrivateRunbookMarker',
        'settings' => ['admin_url' => 'https://admin.example.test/bookings/services/secret'],
        'meta' => ['signed_editor_url' => 'https://admin.example.test/signed-service-editor'],
    ])->save();

    $staffMember->forceFill([
        'email' => 'avery-private@example.test',
        'phone' => 'PrivateStaffPhoneMarker',
        'calendar_feed_token' => 'PrivateCalendarFeedTokenMarker',
        'profile_url' => 'https://admin.example.test/staff/private-profile',
        'settings' => ['private_notes' => 'PrivateStaffSettingsMarker'],
        'meta' => ['filament_resource' => 'BookingStaffMemberResource'],
    ])->save();

    $location->forceFill([
        'line1' => 'Private Location Line Marker',
        'route_notes' => 'PrivateRouteNotesMarker',
        'virtual_url' => 'https://admin.example.test/private-room',
        'settings' => ['private_notes' => 'PrivateLocationSettingsMarker'],
        'meta' => ['editor_url' => 'https://admin.example.test/location-editor'],
    ])->save();

    $request = Request::create('/bookings', Symfony\Component\HttpFoundation\Request::METHOD_GET, [
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'timezone' => 'Europe/London',
    ]);

    $props = BuildPublicBookingRequestPropsAction::run($request, lazySlots: false);

    expect($props['options']['services'][0])->toHaveKeys(['id', 'name', 'description', 'duration_minutes'])
        ->not->toHaveKeys(['instructions', 'settings', 'meta'])
        ->and($props['options']['staff'][0])->toHaveKeys(['id', 'display_name', 'title'])
        ->not->toHaveKeys(['email', 'phone', 'calendar_feed_token', 'profile_url', 'settings', 'meta'])
        ->and($props['options']['locations'][0])->toHaveKeys(['id', 'name', 'type', 'city'])
        ->not->toHaveKeys(['line1', 'route_notes', 'virtual_url', 'settings', 'meta']);

    expect(publicBookingPropStrings($props))->not->toContain(
        'ServicePrivateRunbookMarker',
        'https://admin.example.test/bookings/services/secret',
        'https://admin.example.test/signed-service-editor',
        'avery-private@example.test',
        'PrivateStaffPhoneMarker',
        'PrivateCalendarFeedTokenMarker',
        'https://admin.example.test/staff/private-profile',
        'PrivateStaffSettingsMarker',
        'BookingStaffMemberResource',
        'Private Location Line Marker',
        'PrivateRouteNotesMarker',
        'https://admin.example.test/private-room',
        'PrivateLocationSettingsMarker',
        'https://admin.example.test/location-editor',
    );
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

/**
 * @param  array<string, mixed>  $props
 * @return list<string>
 */
function publicBookingPropStrings(array $props): array
{
    $strings = [];
    $stack = [$props];

    while ($stack !== []) {
        $value = array_pop($stack);

        if (is_array($value)) {
            foreach ($value as $nestedValue) {
                $stack[] = $nestedValue;
            }

            continue;
        }

        if (is_string($value)) {
            $strings[] = $value;
        }
    }

    sort($strings);

    return $strings;
}
