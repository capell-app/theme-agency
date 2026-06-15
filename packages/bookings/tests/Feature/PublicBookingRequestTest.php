<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\BuildAvailableBookingSlotsAction;
use Capell\Bookings\Actions\BuildPublicBookingRequestPropsAction;
use Capell\Bookings\Actions\CreateStaffCalendarFeedUrlAction;
use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Providers\BookingsServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('throttles public booking request submissions and install-gates public routes', function (): void {
    $providerFile = (new ReflectionClass(BookingsServiceProvider::class))->getFileName();
    throw_unless(is_string($providerFile), RuntimeException::class, 'Expected bookings service provider to have a source file.');

    $providerSource = file_get_contents($providerFile);

    expect(Route::getRoutes()->getByName('capell-bookings.request.store')?->gatherMiddleware())
        ->toContain('throttle:capell-bookings-request')
        ->and($providerSource)->toBeString()
        ->and($providerSource)->not->toContain("->hasRoute('web')")
        ->and($providerSource)->toContain('loadRoutesFrom(__DIR__ . \'/../../routes/web.php\')');
});

it('renders a public booking request form without exposing admin internals', function (): void {
    BookingService::factory()->create([
        'name' => 'Consultation',
        'instructions' => 'ServicePrivateRunbookMarker',
        'settings' => ['admin_url' => 'https://admin.example.test/bookings/services/secret'],
        'meta' => ['signed_editor_url' => 'https://admin.example.test/signed-service-editor'],
    ]);
    BookingStaffMember::factory()->create([
        'display_name' => 'Avery Morgan',
        'email' => 'avery-private@example.test',
        'phone' => 'PrivateStaffPhoneMarker',
        'calendar_feed_token' => 'PrivateCalendarFeedTokenMarker',
        'profile_url' => 'https://admin.example.test/staff/private-profile',
        'settings' => ['private_notes' => 'PrivateStaffSettingsMarker'],
        'meta' => ['filament_resource' => 'BookingStaffMemberResource'],
    ]);
    BookingLocation::factory()->create([
        'name' => 'Main office',
        'line1' => 'Private Location Line Marker',
        'route_notes' => 'PrivateRouteNotesMarker',
        'virtual_url' => 'https://admin.example.test/private-room',
        'settings' => ['private_notes' => 'PrivateLocationSettingsMarker'],
        'meta' => ['editor_url' => 'https://admin.example.test/location-editor'],
    ]);

    $response = $this->get(route('capell-bookings.request'));

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Request an appointment')
        ->assertSee('Consultation')
        ->assertSee('Avery Morgan')
        ->assertSee('Main office')
        ->assertSee('<select', false)
        ->assertSee('Europe/London')
        ->assertDontSee('capell-app/bookings', false)
        ->assertDontSee('Filament', false)
        ->assertDontSee('BookingServiceResource', false)
        ->assertDontSee('BookingStaffMemberResource', false)
        ->assertDontSee('admin', false)
        ->assertDontSee('signed-service-editor', false)
        ->assertDontSee('ServicePrivateRunbookMarker', false)
        ->assertDontSee('avery-private@example.test', false)
        ->assertDontSee('PrivateStaffPhoneMarker', false)
        ->assertDontSee('PrivateCalendarFeedTokenMarker', false)
        ->assertDontSee('PrivateStaffSettingsMarker', false)
        ->assertDontSee('Private Location Line Marker', false)
        ->assertDontSee('PrivateRouteNotesMarker', false)
        ->assertDontSee('PrivateLocationSettingsMarker', false);
});

it('allows the public booking request renderer to be replaced', function (): void {
    $this->app->bind(PublicBookingRequestRenderer::class, static fn (): PublicBookingRequestRenderer => new class implements PublicBookingRequestRenderer
    {
        public function render(Request $request): Response
        {
            return new Response('Custom booking request renderer');
        }
    });

    $this->get(route('capell-bookings.request'))
        ->assertOk()
        ->assertSee('Custom booking request renderer');
});

it('builds hydrated public booking request props with available slots', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));
    config()->set('capell-bookings.public_slot_interval_minutes', 30);

    $service = BookingService::factory()->create([
        'duration_minutes' => 30,
        'lead_time_minutes' => 0,
        'buffer_before_minutes' => 0,
        'buffer_after_minutes' => 0,
        'max_future_days' => 7,
    ]);
    $staffMember = BookingStaffMember::factory()->create();
    $location = BookingLocation::factory()->create();
    $startsAt = CarbonImmutable::parse('2026-06-03 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '10:00:00',
        'ends_at' => '11:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 2,
        'status' => BookingAvailabilityStatusEnum::Available,
    ]);

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addMinutes(30),
        'timezone' => 'Europe/London',
    ]);

    $request = Request::create('/bookings', Symfony\Component\HttpFoundation\Request::METHOD_GET, [
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'timezone' => 'Europe/London',
    ]);

    $props = BuildPublicBookingRequestPropsAction::run($request, false);

    expect($props['postUrl'])->toBe(route('capell-bookings.request.store'))
        ->and($props['timezone'])->toBe('Europe/London')
        ->and($props['timezoneOptions'])->toContain('Europe/London')
        ->and($props['options']['services'])->toHaveCount(1)
        ->and($props['slots'])->toHaveCount(2)
        ->and($props['slots'][0]['starts_at'])->toBe($startsAt->toIso8601String())
        ->and($props['slots'][0]['capacity_remaining'])->toBe(1)
        ->and($props['slots'][1]['capacity_remaining'])->toBe(2);
});

it('builds slots from date-specific available exceptions without weekly windows', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));
    config()->set('capell-bookings.public_slot_interval_minutes', 30);

    $service = BookingService::factory()->create([
        'duration_minutes' => 30,
        'lead_time_minutes' => 0,
        'buffer_before_minutes' => 0,
        'buffer_after_minutes' => 0,
        'max_future_days' => 7,
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-06 10:00:00', 'Europe/London');

    BookingAvailabilityException::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'status' => BookingAvailabilityStatusEnum::Available,
        'date' => $startsAt,
        'starts_at' => '10:00:00',
        'ends_at' => '11:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 1,
    ]);

    $slots = BuildAvailableBookingSlotsAction::run(
        serviceId: (int) $service->getKey(),
        timezone: 'Europe/London',
        from: $startsAt->startOfDay(),
        days: 1,
    );

    expect($slots)->toHaveCount(2)
        ->and($slots[0]['starts_at'])->toBe($startsAt->toIso8601String())
        ->and($slots[1]['starts_at'])->toBe($startsAt->addMinutes(30)->toIso8601String());
});

it('stores public appointment requests through the booking action', function (): void {
    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
        'buffer_before_minutes' => 0,
        'buffer_after_minutes' => 0,
    ]);
    $staffMember = BookingStaffMember::factory()->create();
    $location = BookingLocation::factory()->create();
    $startsAt = CarbonImmutable::now('Europe/London')->next(CarbonImmutable::MONDAY)->setTime(10, 0);

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 1,
        'status' => BookingAvailabilityStatusEnum::Available,
    ]);

    $response = $this->post(route('capell-bookings.request.store'), [
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'requested_starts_at' => $startsAt->format('Y-m-d\TH:i'),
        'timezone' => 'Europe/London',
        'customer_name' => 'Morgan Customer',
        'customer_email' => 'morgan@example.test',
        'customer_phone' => '01234 567890',
        'notes' => 'Morning appointment preferred.',
    ]);

    $response->assertRedirect(route('capell-bookings.request'));

    $appointmentRequest = AppointmentRequest::query()->firstOrFail();

    expect($appointmentRequest->service_id)->toBe($service->getKey())
        ->and($appointmentRequest->staff_member_id)->toBe($staffMember->getKey())
        ->and($appointmentRequest->location_id)->toBe($location->getKey())
        ->and($appointmentRequest->customer_email)->toBe('morgan@example.test')
        ->and($appointmentRequest->source)->toBe('bookings-public')
        ->and($appointmentRequest->payload)->toBe(['submitted_from' => 'public-booking-request']);
});

it('rejects public appointment requests with invalid timezone identifiers', function (): void {
    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
    ]);

    $response = $this->post(route('capell-bookings.request.store'), [
        'service_id' => $service->getKey(),
        'requested_starts_at' => CarbonImmutable::now('Europe/London')->addDay()->format('Y-m-d\TH:i'),
        'timezone' => 'Europe/NotARealZone',
        'customer_name' => 'Morgan Customer',
        'customer_email' => 'morgan@example.test',
    ]);

    $response->assertSessionHasErrors('timezone');
});

it('serves opaque staff calendar feeds for confirmed appointments only', function (): void {
    $service = BookingService::factory()->create(['name' => 'Consultation']);
    $staffMember = BookingStaffMember::factory()->create(['display_name' => 'Avery Morgan']);
    $location = BookingLocation::factory()->create(['name' => 'Main office']);
    $startsAt = CarbonImmutable::parse('2026-06-03 10:00:00', 'Europe/London');

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'customer_name' => 'Confirmed Customer',
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addMinutes(45),
        'timezone' => 'Europe/London',
    ]);

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Requested,
        'customer_name' => 'Requested Customer',
        'requested_starts_at' => $startsAt->addHour(),
        'requested_ends_at' => $startsAt->addHour()->addMinutes(45),
        'timezone' => 'Europe/London',
    ]);

    $feedUrl = CreateStaffCalendarFeedUrlAction::run($staffMember);
    $staffMember->refresh();

    expect($staffMember->calendar_feed_token)->toBeString()
        ->and(strlen((string) $staffMember->calendar_feed_token))->toBe(64)
        ->and($feedUrl)->toContain((string) $staffMember->calendar_feed_token)
        ->and($feedUrl)->not->toContain('/' . $staffMember->getKey() . '.ics');

    $response = $this->get($feedUrl);

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('BEGIN:VCALENDAR', false)
        ->assertSee('BEGIN:VEVENT', false)
        ->assertSee('SUMMARY:Consultation', false)
        ->assertSee('Confirmed Customer', false)
        ->assertDontSee('Requested Customer', false)
        ->assertDontSee('capell-app/bookings', false)
        ->assertDontSee('Filament', false)
        ->assertDontSee('admin', false)
        ->assertDontSee('BookingStaffMemberResource', false);
});
