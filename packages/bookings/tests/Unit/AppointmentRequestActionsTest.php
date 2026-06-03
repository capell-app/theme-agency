<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\CancelAppointmentRequestAction;
use Capell\Bookings\Actions\ConfirmAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAvailabilityExceptionAction;
use Capell\Bookings\Actions\QueueAppointmentReminderAction;
use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Data\AvailabilityExceptionData;
use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Models\AppointmentAuditLog;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Notifications\AppointmentWorkflowNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\artisan;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('creates appointment requests inside an active availability window', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-29 08:00:00', 'Europe/London'));

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
        ->and($appointmentRequest->reminder_preferences)->toBe(['email' => true])
        ->and($appointmentRequest->auditLogs()->where('event', AppointmentAuditEventEnum::Created->value)->exists())->toBeTrue();
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

it('rejects appointment requests blocked by holiday exceptions', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-03 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
    ]);

    $availabilityException = CreateAvailabilityExceptionAction::run(new AvailabilityExceptionData(
        date: $startsAt,
        status: BookingAvailabilityStatusEnum::Blocked,
        timezone: 'Europe/London',
        serviceId: (int) $service->getKey(),
        reason: 'Bank holiday',
    ));

    expect($availabilityException)->toBeInstanceOf(BookingAvailabilityException::class)
        ->and($availabilityException->status)->toBe(BookingAvailabilityStatusEnum::Blocked);

    CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Jordan Lee',
        customerEmail: 'jordan@example.com',
    ));
})->throws(ValidationException::class);

it('allows appointment requests inside date-specific availability exceptions without a weekly window', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-06 10:00:00', 'Europe/London');

    CreateAvailabilityExceptionAction::run(new AvailabilityExceptionData(
        date: $startsAt,
        status: BookingAvailabilityStatusEnum::Available,
        startsAt: '10:00:00',
        endsAt: '12:00:00',
        timezone: 'Europe/London',
        serviceId: (int) $service->getKey(),
        capacity: 1,
        reason: 'Special opening',
    ));

    $appointmentRequest = CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Jordan Lee',
        customerEmail: 'jordan@example.com',
    ));

    expect($appointmentRequest)->toBeInstanceOf(AppointmentRequest::class)
        ->and($appointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Requested);
});

it('uses date-specific availability exception capacity before weekly capacity', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-03 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 1,
    ]);

    CreateAvailabilityExceptionAction::run(new AvailabilityExceptionData(
        date: $startsAt,
        status: BookingAvailabilityStatusEnum::Available,
        startsAt: '09:00:00',
        endsAt: '17:00:00',
        timezone: 'Europe/London',
        serviceId: (int) $service->getKey(),
        capacity: 2,
        reason: 'Extra staff',
    ));

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addMinutes(45),
    ]);

    $appointmentRequest = CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Jordan Lee',
        customerEmail: 'jordan@example.com',
    ));

    expect($appointmentRequest)->toBeInstanceOf(AppointmentRequest::class)
        ->and($appointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Requested);
});

it('rejects appointment requests before the service lead time', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 180,
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-01 09:00:00', 'Europe/London');

    CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Jordan Lee',
        customerEmail: 'jordan@example.com',
    ));
})->throws(ValidationException::class);

it('rejects appointment requests beyond the service future booking window', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
        'max_future_days' => 14,
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-20 10:00:00', 'Europe/London');

    CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Jordan Lee',
        customerEmail: 'jordan@example.com',
    ));
})->throws(ValidationException::class);

it('rejects appointment requests when matching capacity is exhausted', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
    ]);
    $staffMember = BookingStaffMember::factory()->create();
    $location = BookingLocation::factory()->create();
    $startsAt = CarbonImmutable::parse('2026-06-03 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 1,
    ]);

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addMinutes(45),
    ]);

    CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Riley Chen',
        customerEmail: 'riley@example.com',
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
    ));
})->throws(ValidationException::class);

it('allows overlapping appointment requests when availability capacity remains', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
    ]);
    $staffMember = BookingStaffMember::factory()->create();
    $location = BookingLocation::factory()->create();
    $startsAt = CarbonImmutable::parse('2026-06-03 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 2,
    ]);

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Requested,
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addMinutes(45),
    ]);

    $appointmentRequest = CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt,
        timezone: 'Europe/London',
        customerName: 'Riley Chen',
        customerEmail: 'riley@example.com',
        staffMemberId: (int) $staffMember->getKey(),
        locationId: (int) $location->getKey(),
    ));

    expect($appointmentRequest)->toBeInstanceOf(AppointmentRequest::class)
        ->and($appointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Requested);
});

it('treats service buffers as capacity blocking time', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 30,
        'buffer_after_minutes' => 15,
        'lead_time_minutes' => 0,
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-03 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 1,
    ]);

    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addMinutes(30),
    ]);

    CreateAppointmentRequestAction::run(new AppointmentRequestData(
        serviceId: (int) $service->getKey(),
        requestedStartsAt: $startsAt->addMinutes(35),
        timezone: 'Europe/London',
        customerName: 'Riley Chen',
        customerEmail: 'riley@example.com',
    ));
})->throws(ValidationException::class);

it('confirms requested appointments once', function (): void {
    Notification::fake();

    $appointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Requested,
        'confirmed_at' => null,
    ]);

    $confirmedAppointmentRequest = ConfirmAppointmentRequestAction::run($appointmentRequest);

    expect($confirmedAppointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Confirmed)
        ->and($confirmedAppointmentRequest->confirmed_at)->not->toBeNull()
        ->and($confirmedAppointmentRequest->auditLogs()->where('event', AppointmentAuditEventEnum::Confirmed->value)->exists())->toBeTrue();

    Notification::assertSentOnDemand(AppointmentWorkflowNotification::class);

    ConfirmAppointmentRequestAction::run($confirmedAppointmentRequest);
})->throws(ValidationException::class);

it('cancels requested appointments with audit logs and queued notifications', function (): void {
    Notification::fake();

    $appointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Requested,
        'cancelled_at' => null,
    ]);

    $cancelledAppointmentRequest = CancelAppointmentRequestAction::run(
        appointmentRequest: $appointmentRequest,
        reason: 'Customer requested a new time.',
    );

    expect($cancelledAppointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Cancelled)
        ->and($cancelledAppointmentRequest->cancelled_at)->not->toBeNull()
        ->and($cancelledAppointmentRequest->auditLogs()->where('event', AppointmentAuditEventEnum::Cancelled->value)->exists())->toBeTrue()
        ->and($cancelledAppointmentRequest->auditLogs()->where('event', AppointmentAuditEventEnum::NotificationQueued->value)->exists())->toBeTrue();

    Notification::assertSentOnDemand(AppointmentWorkflowNotification::class);
});

it('queues reminders for confirmed appointments only', function (): void {
    Notification::fake();

    $appointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'confirmed_at' => CarbonImmutable::now(),
    ]);
    $sendAt = CarbonImmutable::now()->addHour();

    QueueAppointmentReminderAction::run($appointmentRequest, $sendAt);

    expect(AppointmentAuditLog::query()
        ->where('appointment_request_id', $appointmentRequest->getKey())
        ->where('event', AppointmentAuditEventEnum::ReminderQueued->value)
        ->exists())->toBeTrue();

    Notification::assertSentOnDemand(AppointmentWorkflowNotification::class);
});

it('queues due confirmed appointment reminders once from the console command', function (): void {
    Notification::fake();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 08:00:00', 'UTC'));

    $dueStartsAt = CarbonImmutable::parse('2026-06-01 09:00:00', 'UTC');
    $dueAppointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'confirmed_at' => CarbonImmutable::now(),
        'requested_starts_at' => $dueStartsAt,
        'requested_ends_at' => $dueStartsAt->addMinutes(45),
    ]);
    $alreadyQueuedAppointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'confirmed_at' => CarbonImmutable::now(),
        'requested_starts_at' => $dueStartsAt->addMinutes(15),
        'requested_ends_at' => $dueStartsAt->addMinutes(60),
    ]);
    $futureAppointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'confirmed_at' => CarbonImmutable::now(),
        'requested_starts_at' => $dueStartsAt->addHours(4),
        'requested_ends_at' => $dueStartsAt->addHours(4)->addMinutes(45),
    ]);

    AppointmentAuditLog::query()->create([
        'appointment_request_id' => $alreadyQueuedAppointmentRequest->getKey(),
        'event' => AppointmentAuditEventEnum::ReminderQueued,
        'status_from' => AppointmentRequestStatusEnum::Confirmed,
        'status_to' => AppointmentRequestStatusEnum::Confirmed,
        'occurred_at' => CarbonImmutable::now(),
    ]);

    $command = artisan('capell:bookings:send-due-reminders', ['--lead-minutes' => 90]);

    throw_if(is_int($command), RuntimeException::class, 'Expected pending artisan command.');

    $command
        ->assertSuccessful()
        ->expectsOutputToContain('Queued 1 booking appointment reminder.')
        ->run();

    expect(AppointmentAuditLog::query()
        ->where('appointment_request_id', $dueAppointmentRequest->getKey())
        ->where('event', AppointmentAuditEventEnum::ReminderQueued->value)
        ->exists())->toBeTrue()
        ->and(AppointmentAuditLog::query()
            ->where('appointment_request_id', $alreadyQueuedAppointmentRequest->getKey())
            ->where('event', AppointmentAuditEventEnum::ReminderQueued->value)
            ->count())->toBe(1)
        ->and(AppointmentAuditLog::query()
            ->where('appointment_request_id', $futureAppointmentRequest->getKey())
            ->where('event', AppointmentAuditEventEnum::ReminderQueued->value)
            ->exists())->toBeFalse();

    Notification::assertSentOnDemand(AppointmentWorkflowNotification::class);
});

it('rejects reminder queues for unconfirmed appointments', function (): void {
    Notification::fake();

    $appointmentRequest = AppointmentRequest::factory()->create([
        'status' => AppointmentRequestStatusEnum::Requested,
    ]);

    QueueAppointmentReminderAction::run($appointmentRequest);
})->throws(ValidationException::class);
