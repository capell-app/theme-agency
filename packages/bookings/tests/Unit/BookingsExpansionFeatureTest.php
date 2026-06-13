<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\ApplyLessonBundleCreditAction;
use Capell\Bookings\Actions\BuildInstructorFuelReportAction;
use Capell\Bookings\Actions\BuildServiceAreaHeatmapAction;
use Capell\Bookings\Actions\CalculateCancellationFeeAction;
use Capell\Bookings\Actions\CreateMessagingConsentUrlAction;
use Capell\Bookings\Actions\CreatePortalLessonsUrlAction;
use Capell\Bookings\Actions\CreateReviewRequestUrlAction;
use Capell\Bookings\Actions\ExpireWaitlistOffersAction;
use Capell\Bookings\Actions\ImportClinicAttendanceCsvAction;
use Capell\Bookings\Actions\IssueBookingChangeProposalTokenAction;
use Capell\Bookings\Actions\JoinBookingWaitlistAction;
use Capell\Bookings\Actions\MarkBookingWebhookEventProcessedAction;
use Capell\Bookings\Actions\OfferWaitlistSlotAction;
use Capell\Bookings\Actions\ProposeBookingChangeAction;
use Capell\Bookings\Actions\ProposeWeatherCancellationAction;
use Capell\Bookings\Actions\RecordBookingWebhookEventAction;
use Capell\Bookings\Actions\RecordLessonNoteAction;
use Capell\Bookings\Actions\RecordLessonSkillAssessmentAction;
use Capell\Bookings\Actions\ScoreBookingRiskAction;
use Capell\Bookings\Actions\ShouldSuppressReviewRequestAction;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingLessonBundleStatusEnum;
use Capell\Bookings\Enums\BookingLessonSkillStatusEnum;
use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Enums\BookingWaitlistStatusEnum;
use Capell\Bookings\Enums\LessonNoteVisibilityEnum;
use Capell\Bookings\Enums\MessagingConsentStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingGroupSession;
use Capell\Bookings\Models\BookingLessonBundle;
use Capell\Bookings\Models\BookingReviewRequest;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\MessagingConsent;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('serves signed portal lessons and consent updates without exposing unsigned access', function (): void {
    $siteId = (int) DB::table('sites')->insertGetId([]);
    $portalAccount = PortalAccount::factory()->forSite($siteId)->create(['email' => 'jordan@example.com']);
    $service = BookingService::factory()->create(['name' => 'Learner lesson']);
    $appointmentRequest = AppointmentRequest::factory()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'service_id' => $service->getKey(),
        'customer_email' => 'jordan@example.com',
    ]);

    RecordLessonNoteAction::run($appointmentRequest, 'Reverse bay parking', 'Share this progress note.', LessonNoteVisibilityEnum::Shared);
    RecordLessonNoteAction::run($appointmentRequest, 'Instructor concern', 'Do not share this note.', LessonNoteVisibilityEnum::Private);

    $portalAccountId = filter_var($portalAccount->getKey(), FILTER_VALIDATE_INT);
    throw_if($portalAccountId === false, RuntimeException::class, 'Expected portal account key to be an integer.');

    $this->get('/bookings/portal/' . $siteId . '/' . $portalAccountId . '/lessons')->assertForbidden();

    $this->get(CreatePortalLessonsUrlAction::run($portalAccount))
        ->assertOk()
        ->assertSee('Reverse bay parking')
        ->assertDontSee('Instructor concern');

    $consentUrl = CreateMessagingConsentUrlAction::run($portalAccount);

    $this->get($consentUrl)
        ->assertOk()
        ->assertSee('Email');

    $this->post($consentUrl, [
        'channel' => BookingMessageChannelEnum::Sms->value,
        'granted' => '1',
        'recipient' => '+447700900123',
    ])->assertRedirect();

    $consent = MessagingConsent::query()->firstOrFail();

    expect($consent->channel)->toBe(BookingMessageChannelEnum::Sms)
        ->and($consent->status)->toBe(MessagingConsentStatusEnum::Granted);
});

it('accepts signed review and change proposal responses', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $siteId = (int) DB::table('sites')->insertGetId([]);
    $portalAccount = PortalAccount::factory()->forSite($siteId)->create(['email' => 'jordan@example.com']);
    $startsAt = CarbonImmutable::parse('2026-07-05 10:00:00', 'Europe/London');
    $appointmentRequest = AppointmentRequest::factory()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'customer_email' => 'jordan@example.com',
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addHour(),
        'status' => AppointmentRequestStatusEnum::Completed,
        'completed_at' => CarbonImmutable::now(),
    ]);
    $reviewRequest = BookingReviewRequest::query()->create([
        'appointment_request_id' => $appointmentRequest->getKey(),
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'status' => BookingReviewRequestStatusEnum::Scheduled,
        'scheduled_for' => CarbonImmutable::now(),
    ]);

    $reviewUrl = CreateReviewRequestUrlAction::run($reviewRequest);

    $this->post($reviewUrl, [
        'rating' => 5,
        'response' => 'Clear and useful.',
    ])->assertRedirect();

    $proposal = ProposeBookingChangeAction::run(
        $appointmentRequest,
        $startsAt->addDay(),
        $startsAt->addDay()->addHour(),
        'Customer asked for tomorrow.',
    );
    $clientParty = $proposal->parties()->where('party', 'client')->firstOrFail();
    $proposalUrl = IssueBookingChangeProposalTokenAction::run($clientParty);

    $this->post($proposalUrl, ['accepted' => '1'])->assertRedirect();

    expect($reviewRequest->refresh()->status)->toBe(BookingReviewRequestStatusEnum::Completed)
        ->and($clientParty->refresh()->accepted_at)->not->toBeNull();
});

it('records webhook events idempotently and processes waitlist offers', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $firstEvent = RecordBookingWebhookEventAction::run('stripe', 'evt_123', 'checkout.session.completed', ['amount_total' => 7500]);
    $secondEvent = RecordBookingWebhookEventAction::run('stripe', 'evt_123', 'checkout.session.completed', ['amount_total' => 9999]);
    $processedEvent = MarkBookingWebhookEventProcessedAction::run($firstEvent);

    $entry = JoinBookingWaitlistAction::run(
        customerName: 'Jordan Lee',
        customerEmail: 'Jordan@Example.com',
        preferredStartsAt: CarbonImmutable::now()->addDay(),
        preferences: ['weekday' => 'Friday'],
    );
    OfferWaitlistSlotAction::run($entry, CarbonImmutable::now()->subMinute());

    expect($secondEvent->is($firstEvent))->toBeTrue()
        ->and($processedEvent->status)->toBe('processed')
        ->and($entry->customer_email)->toBe('jordan@example.com')
        ->and(ExpireWaitlistOffersAction::run())->toBe(1)
        ->and($entry->refresh()->status)->toBe(BookingWaitlistStatusEnum::Expired);
});

it('tracks lesson skills, prepaid bundle credits, cancellation fees, and risk signals', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $appointmentStartsAt = CarbonImmutable::now()->addHours(4);
    $appointmentRequest = AppointmentRequest::factory()->create([
        'customer_email' => 'jordan@example.com',
        'customer_phone' => null,
        'requested_starts_at' => $appointmentStartsAt,
        'requested_ends_at' => $appointmentStartsAt->addHour(),
        'payment_required_amount_pence' => 10000,
        'status' => AppointmentRequestStatusEnum::Requested,
    ]);
    AppointmentRequest::factory()->create([
        'customer_email' => 'jordan@example.com',
        'requested_starts_at' => CarbonImmutable::now()->subMonth(),
        'status' => AppointmentRequestStatusEnum::Cancelled,
    ]);
    AppointmentRequest::factory()->create([
        'customer_email' => 'jordan@example.com',
        'requested_starts_at' => CarbonImmutable::now()->subWeeks(2),
        'status' => AppointmentRequestStatusEnum::NoShow,
    ]);
    $bundle = BookingLessonBundle::query()->create([
        'name' => 'Five lesson pack',
        'status' => BookingLessonBundleStatusEnum::Active,
        'credits_purchased' => 5,
        'credits_remaining' => 1,
    ]);

    $assessment = RecordLessonSkillAssessmentAction::run(
        $appointmentRequest,
        'parallel_parking',
        BookingLessonSkillStatusEnum::Practising,
        3,
        'Needs one more pass.',
    );
    $bundle = ApplyLessonBundleCreditAction::run($bundle, $appointmentRequest);
    $weatherPrompt = ProposeWeatherCancellationAction::run($appointmentRequest, 'high winds forecast');
    $risk = ScoreBookingRiskAction::run($appointmentRequest);

    expect($assessment->status)->toBe(BookingLessonSkillStatusEnum::Practising)
        ->and($bundle->credits_remaining)->toBe(0)
        ->and($bundle->status)->toBe(BookingLessonBundleStatusEnum::Exhausted)
        ->and(CalculateCancellationFeeAction::run($appointmentRequest))->toBe(5000)
        ->and($weatherPrompt->type)->toBe('weather_cancellation')
        ->and($risk['level'])->toBe('high')
        ->and($risk['signals'])->toContain('recent_cancellations_or_no_shows', 'payment_required_unpaid', 'missing_phone')
        ->and(ShouldSuppressReviewRequestAction::run($appointmentRequest))->toBeTrue();
});

it('builds operational reports for fuel, service-area demand, and clinic attendance imports', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $service = BookingService::factory()->create();
    $staffMemberId = (int) DB::table('booking_staff_members')->insertGetId([
        'display_name' => 'Avery Stone',
        'email' => 'avery@example.com',
        'active' => true,
        'created_at' => CarbonImmutable::now(),
        'updated_at' => CarbonImmutable::now(),
    ]);
    $startsAt = CarbonImmutable::parse('2026-07-03 10:00:00', 'Europe/London');
    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMemberId,
        'requested_starts_at' => $startsAt,
        'requested_ends_at' => $startsAt->addHour(),
        'fuel_allowance_pence' => 1200,
        'travel_distance_miles' => 8.25,
        'travel_duration_minutes' => 22,
        'payload' => ['postal_code' => 'LS1 1AA'],
    ]);
    AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMemberId,
        'requested_starts_at' => $startsAt->addDay(),
        'requested_ends_at' => $startsAt->addDay()->addHour(),
        'fuel_allowance_pence' => 800,
        'fuel_acknowledged_at' => CarbonImmutable::now(),
        'travel_distance_miles' => 4.75,
        'travel_duration_minutes' => 18,
        'payload' => ['postal_code' => 'LS1 8AB'],
    ]);
    $groupSession = BookingGroupSession::query()->create([
        'service_id' => $service->getKey(),
        'title' => 'Parking clinic',
        'status' => 'draft',
        'capacity' => 8,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->addHours(2),
    ]);

    $fuelReport = BuildInstructorFuelReportAction::run($staffMemberId, $startsAt->startOfMonth(), $startsAt->endOfMonth());
    $heatmap = BuildServiceAreaHeatmapAction::run();
    $groupSession = ImportClinicAttendanceCsvAction::run($groupSession, "email,name,status\njordan@example.com,Jordan Lee,attended\nriley@example.com,Riley Chen,interested");

    expect($fuelReport['appointment_count'])->toBe(2)
        ->and($fuelReport['fuel_allowance_pence'])->toBe(2000)
        ->and($fuelReport['unacknowledged_fuel_allowance_pence'])->toBe(1200)
        ->and($heatmap[0])->toMatchArray([
            'area' => 'LS1',
            'appointment_count' => 2,
            'average_travel_minutes' => 20,
            'travel_distance_miles' => 13.0,
        ])
        ->and($groupSession->social_attendance)->toHaveCount(2);
});
