<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\ConfirmAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

it('creates appointment requests inside an active availability window', function (): void {
    $service = BookingService::factory()->create(['duration_minutes' => 45]);
    $staffMember = BookingStaffMember::factory()->create();
    $location = BookingLocation::factory()->create();
    $startsAt = CarbonImmutable::parse('2026-06-01 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
    ]);

    $appointmentRequest = CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Jordan Lee',
        customerEmail: 'jordan@example.com',
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
        source: 'contact-form',
        payload: ['path' => '/appointments'],
        reminderPreferences: ['email' => true],
    ));

    expect($appointmentRequest)->toBeInstanceOf(AppointmentRequest::class)
        ->and($appointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Requested)
        ->and((int) $appointmentRequest->requested_starts_at->diffInMinutes($appointmentRequest->requested_ends_at))->toBe(45)
        ->and($appointmentRequest->calendar_uid)->not->toBeEmpty()
        ->and($appointmentRequest->service->is($service))->toBeTrue()
        ->and($appointmentRequest->staffMember?->is($staffMember))->toBeTrue()
        ->and($appointmentRequest->location?->is($location))->toBeTrue()
        ->and($appointmentRequest->payload)->toBe(['path' => '/appointments'])
        ->and($appointmentRequest->reminder_preferences)->toBe(['email' => true]);
});

it('rejects appointment requests outside availability', function (): void {
    $service = BookingService::factory()->create(['duration_minutes' => 45]);
    $startsAt = CarbonImmutable::parse('2026-06-01 18:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
    ]);

    CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Jordan Lee',
        customerEmail: 'jordan@example.com',
    ));
})->throws(ValidationException::class);

it('confirms requested appointments once', function (): void {
    $appointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Requested,
        'confirmed_at' => null,
    ]);

    $confirmedAppointmentRequest = ConfirmAppointmentRequestAction::run($appointmentRequest);

    expect($confirmedAppointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Confirmed)
        ->and($confirmedAppointmentRequest->confirmed_at)->not->toBeNull();

    ConfirmAppointmentRequestAction::run($confirmedAppointmentRequest);
})->throws(ValidationException::class);
