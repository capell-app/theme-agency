<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\CreateStaffCalendarFeedUrlAction;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Carbon\CarbonImmutable;

it('renders a public booking request form without exposing admin internals', function (): void {
    BookingService::factory()->create(['name' => 'Consultation']);
    BookingStaffMember::factory()->create(['display_name' => 'Avery Morgan']);
    BookingLocation::factory()->create(['name' => 'Main office']);

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
        ->assertDontSee('admin', false)
        ->assertDontSee('signed', false);
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
