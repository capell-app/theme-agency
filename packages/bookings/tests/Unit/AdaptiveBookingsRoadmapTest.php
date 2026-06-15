<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\AttachLessonPhotoAction;
use Capell\Bookings\Actions\BuildDayPlanAction;
use Capell\Bookings\Actions\BuildOwnerDigestAction;
use Capell\Bookings\Actions\BuildPortalLessonRowsAction;
use Capell\Bookings\Actions\CalculateFuelAllowanceAction;
use Capell\Bookings\Actions\CaptureMessagingConsentAction;
use Capell\Bookings\Actions\CaptureReviewAction;
use Capell\Bookings\Actions\ConfirmAppointmentRequestAction;
use Capell\Bookings\Actions\ConfirmBookingChangeAction;
use Capell\Bookings\Actions\CreateGroupSessionAction;
use Capell\Bookings\Actions\DispatchBookingMessageAction;
use Capell\Bookings\Actions\DispatchReviewRequestAction;
use Capell\Bookings\Actions\DraftFacebookPostCopyAction;
use Capell\Bookings\Actions\EnforceWorkZoneAction;
use Capell\Bookings\Actions\ErasePortalBookingDataAction;
use Capell\Bookings\Actions\EstimateTravelTimeAction;
use Capell\Bookings\Actions\ExportPortalBookingDataAction;
use Capell\Bookings\Actions\MarkBookingPaymentFulfilledAction;
use Capell\Bookings\Actions\OfferTimeRangeAction;
use Capell\Bookings\Actions\PinHoldTimeAction;
use Capell\Bookings\Actions\PlaceProvisionalHoldAction;
use Capell\Bookings\Actions\ProposeBookingChangeAction;
use Capell\Bookings\Actions\ProposeScheduleOptimisationAction;
use Capell\Bookings\Actions\ProposeTravelAdjustmentAction;
use Capell\Bookings\Actions\PublishClinicToFacebookAction;
use Capell\Bookings\Actions\RecordLessonNoteAction;
use Capell\Bookings\Actions\RecordTravelObservationAction;
use Capell\Bookings\Actions\RegisterGroupAttendeeAction;
use Capell\Bookings\Actions\ScheduleReviewRequestsAction;
use Capell\Bookings\Actions\SyncFacebookAttendanceAction;
use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingGroupSessionStatusEnum;
use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\BookingMessageStatusEnum;
use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Enums\ConfirmationPolicyEnum;
use Capell\Bookings\Enums\LessonNoteVisibilityEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingMessageLog;
use Capell\Bookings\Models\BookingReviewRequest;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingWorkZone;
use Capell\Bookings\Models\LessonNote;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('builds portal lesson records without leaking private notes across accounts', function (): void {
    $siteId = (int) DB::table('sites')->insertGetId([]);
    $portalAccount = PortalAccount::factory()->forSite($siteId)->create(['email' => 'jordan@example.com']);
    $otherPortalAccount = PortalAccount::factory()->forSite($siteId)->create(['email' => 'riley@example.com']);
    $service = BookingService::factory()->create(['name' => 'Driving lesson']);
    $startsAt = CarbonImmutable::parse('2026-07-01 10:00:00', 'Europe/London');

    $appointmentRequest = AppointmentRequest::factory()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'service_id' => $service->getKey(),
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addHour(),
    ]);
    AppointmentRequest::factory()->create([
        'site_id' => $siteId,
        'portal_account_id' => $otherPortalAccount->getKey(),
        'service_id' => $service->getKey(),
    ]);

    $sharedLessonNote = RecordLessonNoteAction::run(
        appointmentRequest: $appointmentRequest,
        summary: 'Parallel parking',
        body: 'Share this with the learner.',
        visibility: LessonNoteVisibilityEnum::Shared,
    );
    RecordLessonNoteAction::run(
        appointmentRequest: $appointmentRequest,
        summary: 'Instructor-only note',
        body: 'Do not expose this in portal.',
        visibility: LessonNoteVisibilityEnum::Private,
    );
    AttachLessonPhotoAction::run($sharedLessonNote, 321, $portalAccount);

    $rows = BuildPortalLessonRowsAction::run($siteId, $portalAccount);

    expect($rows)->toHaveCount(1)
        ->and($rows->first()?->sharedNotes)->toHaveCount(1)
        ->and($rows->first()?->sharedNotes[0]['summary'])->toBe('Parallel parking')
        ->and($rows->first()?->sharedNotes[0]['photo_media_ids'])->toBe([321]);

    BuildPortalLessonRowsAction::run($siteId + 1, $portalAccount);
})->throws(ValidationException::class);

it('logs multi-channel messages with consent checks and dedupe', function (): void {
    $siteId = (int) DB::table('sites')->insertGetId([]);
    $portalAccount = PortalAccount::factory()->forSite($siteId)->create(['email' => 'jordan@example.com']);
    $appointmentRequest = AppointmentRequest::factory()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'customer_email' => 'jordan@example.com',
        'customer_phone' => '+447700900123',
    ]);

    $emailLog = DispatchBookingMessageAction::run(
        appointmentRequest: $appointmentRequest,
        channel: BookingMessageChannelEnum::Email,
        type: 'confirmation',
        body: 'Confirmed',
        subject: 'Confirmed',
    );
    $skippedSmsLog = DispatchBookingMessageAction::run(
        appointmentRequest: $appointmentRequest,
        channel: BookingMessageChannelEnum::Sms,
        type: 'reminder',
        body: 'Reminder',
    );

    CaptureMessagingConsentAction::run($portalAccount, BookingMessageChannelEnum::Sms, true, '+447700900123');

    $sentSmsLog = DispatchBookingMessageAction::run(
        appointmentRequest: $appointmentRequest,
        channel: BookingMessageChannelEnum::Sms,
        type: 'reminder-opt-in',
        body: 'Reminder',
    );
    $duplicateSmsLog = DispatchBookingMessageAction::run(
        appointmentRequest: $appointmentRequest,
        channel: BookingMessageChannelEnum::Sms,
        type: 'reminder-opt-in',
        body: 'Reminder',
    );

    expect($emailLog->status)->toBe(BookingMessageStatusEnum::Sent)
        ->and($skippedSmsLog->status)->toBe(BookingMessageStatusEnum::Skipped)
        ->and($sentSmsLog->status)->toBe(BookingMessageStatusEnum::Sent)
        ->and($duplicateSmsLog->is($sentSmsLog))->toBeTrue()
        ->and(BookingMessageLog::query()->where('appointment_request_id', $appointmentRequest->getKey())->count())->toBe(3);
});

it('estimates travel, enforces work zones, builds day plans, and calculates fuel', function (): void {
    Config::set('capell-bookings.fuel_rate_pence_per_mile', 500);
    $service = BookingService::factory()->create();
    $staffMemberId = (int) DB::table('booking_staff_members')->insertGetId([
        'display_name' => 'Avery Stone',
        'email' => 'avery@example.com',
        'active' => true,
        'created_at' => CarbonImmutable::now(),
        'updated_at' => CarbonImmutable::now(),
    ]);
    $originLocation = BookingLocation::factory()->create([
        'name' => 'Home base',
        'latitude' => 53.8008,
        'longitude' => -1.5491,
        'postal_code' => 'LS1 1AA',
        'service_area' => 'leeds',
    ]);
    $destinationLocation = BookingLocation::factory()->create([
        'name' => 'Learner pickup',
        'latitude' => 53.7420,
        'longitude' => -1.4636,
        'postal_code' => 'LS15 8ZB',
        'service_area' => 'leeds',
        'access_overhead_minutes' => 3,
    ]);
    $firstStartsAt = CarbonImmutable::parse('2026-07-02 10:00:00', 'Europe/London');
    $secondStartsAt = CarbonImmutable::parse('2026-07-02 11:00:00', 'Europe/London');

    BookingWorkZone::query()->create([
        'name' => 'Leeds',
        'service_area' => 'leeds',
        'postal_code_prefixes' => ['LS'],
        'active' => true,
    ]);
    RecordTravelObservationAction::run($originLocation, $destinationLocation, 20, 7.5);
    ProposeTravelAdjustmentAction::run($originLocation, $destinationLocation, 5, 'School run');

    $firstAppointmentRequest = AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMemberId,
        'location_id' => $originLocation->getKey(),
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'requested_starts_at' => $firstStartsAt,
        'requested_ends_at' => $firstStartsAt->addMinutes(45),
    ]);
    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMemberId,
        'location_id' => $destinationLocation->getKey(),
        'status' => AppointmentRequestStatusEnum::Confirmed,
        'requested_starts_at' => $secondStartsAt,
        'requested_ends_at' => $secondStartsAt->addMinutes(45),
    ]);

    $estimate = EstimateTravelTimeAction::run($originLocation, $destinationLocation, $firstStartsAt->addMinutes(45));
    $fuelledAppointmentRequest = CalculateFuelAllowanceAction::run($firstAppointmentRequest, $estimate->distanceMiles);
    $dayPlan = BuildDayPlanAction::run($staffMemberId, $firstStartsAt);

    expect(EnforceWorkZoneAction::run($destinationLocation))->toBeTrue()
        ->and($estimate->durationMinutes)->toBe(28)
        ->and($estimate->distanceMiles)->toBe(7.5)
        ->and($fuelledAppointmentRequest->fuel_allowance_pence)->toBe(3750)
        ->and($dayPlan->stops)->toHaveCount(2)
        ->and($dayPlan->warnings)->not->toBeEmpty();
});

it('handles provisional holds, payment gates, and accepted change proposals', function (): void {
    Notification::fake();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 08:00:00', 'Europe/London'));

    $service = BookingService::factory()->create([
        'duration_minutes' => 45,
        'lead_time_minutes' => 0,
    ]);
    $startsAt = CarbonImmutable::parse('2026-07-03 10:00:00', 'Europe/London');

    BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => null,
        'location_id' => null,
        'day_of_week' => $startsAt->dayOfWeek,
        'starts_at' => '09:00:00',
        'ends_at' => '17:00:00',
        'timezone' => 'Europe/London',
        'capacity' => 2,
    ]);

    $appointmentRequest = PlaceProvisionalHoldAction::run(
        new AppointmentRequestData(
            serviceId: (int) $service->getKey(),
            requestedStartsAt: $startsAt,
            timezone: 'Europe/London',
            customerName: 'Jordan Lee',
            customerEmail: 'jordan@example.com',
            confirmationPolicy: ConfirmationPolicyEnum::Payment,
        ),
        $startsAt->addDay(),
    );

    $appointmentRequest->forceFill(['payment_required_amount_pence' => 7500])->save();
    $appointmentRequest = OfferTimeRangeAction::run($appointmentRequest, $startsAt, $startsAt->addHours(2));
    $appointmentRequest = PinHoldTimeAction::run($appointmentRequest, $startsAt->addMinutes(30), $startsAt->addMinutes(75));

    expect($appointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Provisional)
        ->and($appointmentRequest->is_time_pinned)->toBeTrue();

    expect(fn (): AppointmentRequest => ConfirmAppointmentRequestAction::run($appointmentRequest))->toThrow(ValidationException::class);

    $appointmentRequest = MarkBookingPaymentFulfilledAction::run($appointmentRequest, 'pi_test_123', 7500, 'cs_test_123');
    $confirmedAppointmentRequest = ConfirmAppointmentRequestAction::run($appointmentRequest);
    $proposal = ProposeBookingChangeAction::run(
        $confirmedAppointmentRequest,
        $startsAt->addDay(),
        $startsAt->addDay()->addMinutes(45),
        'Learner requested a new slot.',
    );

    ConfirmBookingChangeAction::run($proposal, 'client', true);
    $acceptedProposal = ConfirmBookingChangeAction::run($proposal, 'staff', true);

    expect($confirmedAppointmentRequest->refresh()->status)->toBe(AppointmentRequestStatusEnum::Confirmed)
        ->and($acceptedProposal->status->value)->toBe('accepted')
        ->and($confirmedAppointmentRequest->refresh()->requested_starts_at->toDateString())->toBe('2026-07-04');
});

it('covers group clinics, social attendance, reviews, owner prompts, and GDPR erasure', function (): void {
    Config::set('capell-bookings.review_offsets_days', [1]);
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-10 09:00:00', 'Europe/London'));

    $siteId = (int) DB::table('sites')->insertGetId([]);
    $portalAccount = PortalAccount::factory()->forSite($siteId)->create(['email' => 'jordan@example.com']);
    $service = BookingService::factory()->create(['duration_minutes' => 45]);
    $location = BookingLocation::factory()->create();
    $sessionStartsAt = CarbonImmutable::parse('2026-07-12 14:00:00', 'Europe/London');
    $groupSession = CreateGroupSessionAction::run(
        serviceId: (int) $service->getKey(),
        title: 'Parallel parking clinic',
        startsAt: $sessionStartsAt,
        endsAt: $sessionStartsAt->addHours(2),
        siteId: $siteId,
        locationId: (int) $location->getKey(),
        capacity: 2,
    );
    $portalAccountId = filter_var($portalAccount->getKey(), FILTER_VALIDATE_INT);
    throw_if($portalAccountId === false, RuntimeException::class, 'Expected portal account key to be an integer.');

    $firstAttendee = RegisterGroupAttendeeAction::run($groupSession, 'Jordan Lee', 'jordan@example.com', $portalAccountId);
    $secondAttendee = RegisterGroupAttendeeAction::run($groupSession, 'Riley Chen', 'riley@example.com');

    expect(fn (): AppointmentRequest => RegisterGroupAttendeeAction::run($groupSession, 'Morgan Fox', 'morgan@example.com'))->toThrow(ValidationException::class);

    PublishClinicToFacebookAction::run($groupSession, 'Join the parallel parking clinic.');
    SyncFacebookAttendanceAction::run($groupSession);
    $socialPrompt = DraftFacebookPostCopyAction::run($groupSession);

    DB::table('appointment_requests')
        ->where('id', $firstAttendee->getKey())
        ->update([
            'status' => AppointmentRequestStatusEnum::Completed->value,
            'completed_at' => CarbonImmutable::now()->subDay(),
            'updated_at' => CarbonImmutable::now(),
        ]);
    $firstAttendee->refresh();
    $scheduledCount = ScheduleReviewRequestsAction::run(CarbonImmutable::now());
    expect(AppointmentRequest::query()->whereNotNull('completed_at')->count())->toBe(1)
        ->and($scheduledCount)->toBe(1);

    $reviewRequest = BookingReviewRequest::query()->firstOrFail();
    DispatchReviewRequestAction::run($reviewRequest);
    CaptureReviewAction::run($reviewRequest, 5, 'Helpful lesson.');
    $reviewRequestStatus = $reviewRequest->refresh()->status;

    RecordLessonNoteAction::run($firstAttendee, 'Clinic progress', 'Ready for more practice.', LessonNoteVisibilityEnum::Shared);
    DispatchBookingMessageAction::run($firstAttendee, BookingMessageChannelEnum::Email, 'follow_up', 'Thanks for attending.');
    $export = ExportPortalBookingDataAction::run($siteId, $portalAccount);
    $digest = BuildOwnerDigestAction::run($siteId, CarbonImmutable::now()->subDay(), CarbonImmutable::now()->addWeek());
    $schedulePrompt = ProposeScheduleOptimisationAction::run($siteId);
    $erasedCount = ErasePortalBookingDataAction::run($siteId, $portalAccount);

    expect($groupSession->refresh()->status)->toBe(BookingGroupSessionStatusEnum::Published)
        ->and($groupSession->external_event_id)->toBe('local-group-' . $groupSession->id)
        ->and($secondAttendee->refresh()->group_session_id)->toBe($groupSession->getKey())
        ->and($socialPrompt->status)->toBe(BookingOwnerPromptStatusEnum::Proposed)
        ->and($scheduledCount)->toBe(1)
        ->and($reviewRequestStatus)->toBe(BookingReviewRequestStatusEnum::Completed)
        ->and($export['appointments'])->toHaveCount(1)
        ->and($digest['needs_attention'])->toBeTrue()
        ->and($schedulePrompt->type)->toBe('schedule_optimisation')
        ->and($erasedCount)->toBe(1)
        ->and($firstAttendee->refresh()->portal_account_id)->toBeNull()
        ->and(LessonNote::query()->where('appointment_request_id', $firstAttendee->getKey())->exists())->toBeFalse()
        ->and(BookingMessageLog::query()->where('appointment_request_id', $firstAttendee->getKey())->exists())->toBeFalse()
        ->and(BookingReviewRequest::query()->where('appointment_request_id', $firstAttendee->getKey())->exists())->toBeFalse();
});
