<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\ApplyLessonBundleCreditAction;
use Capell\Bookings\Actions\BuildInstructorFuelReportAction;
use Capell\Bookings\Actions\BuildServiceAreaHeatmapAction;
use Capell\Bookings\Actions\CalculateCancellationFeeAction;
use Capell\Bookings\Actions\CaptureReviewAction;
use Capell\Bookings\Actions\CreateMessagingConsentUrlAction;
use Capell\Bookings\Actions\CreatePortalLessonsUrlAction;
use Capell\Bookings\Actions\CreateReviewLoopAction;
use Capell\Bookings\Actions\CreateReviewRequestUrlAction;
use Capell\Bookings\Actions\DispatchReviewRequestAction;
use Capell\Bookings\Actions\ExpireWaitlistOffersAction;
use Capell\Bookings\Actions\ImportClinicAttendanceCsvAction;
use Capell\Bookings\Actions\IssueBookingChangeProposalTokenAction;
use Capell\Bookings\Actions\IssueReviewParticipantUrlAction;
use Capell\Bookings\Actions\JoinBookingWaitlistAction;
use Capell\Bookings\Actions\MarkBookingWebhookEventProcessedAction;
use Capell\Bookings\Actions\OfferWaitlistSlotAction;
use Capell\Bookings\Actions\ProposeBookingChangeAction;
use Capell\Bookings\Actions\ProposeWeatherCancellationAction;
use Capell\Bookings\Actions\RecordBookingWebhookEventAction;
use Capell\Bookings\Actions\RecordLessonNoteAction;
use Capell\Bookings\Actions\RecordLessonSkillAssessmentAction;
use Capell\Bookings\Actions\ReplayBookingWebhookEventAction;
use Capell\Bookings\Actions\RetryBookingMessageAction;
use Capell\Bookings\Actions\ScoreBookingRiskAction;
use Capell\Bookings\Actions\ShouldSuppressReviewRequestAction;
use Capell\Bookings\Contracts\BookingMessageChannel;
use Capell\Bookings\Data\BookingMessageResultData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingLessonBundleStatusEnum;
use Capell\Bookings\Enums\BookingLessonSkillStatusEnum;
use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\BookingMessageStatusEnum;
use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Enums\BookingWaitlistStatusEnum;
use Capell\Bookings\Enums\LessonNoteVisibilityEnum;
use Capell\Bookings\Enums\MessagingConsentStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingGroupSession;
use Capell\Bookings\Models\BookingLessonBundle;
use Capell\Bookings\Models\BookingMessageLog;
use Capell\Bookings\Models\BookingReviewParticipant;
use Capell\Bookings\Models\BookingReviewRequest;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\MessagingConsent;
use Capell\Bookings\Tests\Fixtures\RecordingBookingMessageChannel;
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

    $lessonsUrl = CreatePortalLessonsUrlAction::run($portalAccount);

    expect($lessonsUrl)->not->toContain('/' . $siteId . '/' . $portalAccountId . '/');

    $this->get('/bookings/portal/' . $siteId . '/' . $portalAccountId . '/lessons')->assertNotFound();

    $this->get($lessonsUrl)
        ->assertOk()
        ->assertSee('Reverse bay parking')
        ->assertDontSee('Instructor concern');

    $consentUrl = CreateMessagingConsentUrlAction::run($portalAccount);

    expect($consentUrl)->not->toContain('/' . $siteId . '/' . $portalAccountId . '/');

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
    $reviewRequestId = filter_var($reviewRequest->getKey(), FILTER_VALIDATE_INT);
    throw_if($reviewRequestId === false, RuntimeException::class, 'Expected review request key to be an integer.');
    $reviewPathSegments = explode('/', trim((string) parse_url($reviewUrl, PHP_URL_PATH), '/'));

    expect($reviewPathSegments)->not->toContain((string) $reviewRequestId);

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
    $clientPartyId = filter_var($clientParty->getKey(), FILTER_VALIDATE_INT);
    throw_if($clientPartyId === false, RuntimeException::class, 'Expected proposal party key to be an integer.');

    expect($proposalUrl)->not->toContain('/proposal/' . $clientPartyId . '/');

    $this->post($proposalUrl, ['accepted' => '1'])->assertRedirect();

    expect($reviewRequest->refresh()->status)->toBe(BookingReviewRequestStatusEnum::Completed)
        ->and($clientParty->refresh()->accepted_at)->not->toBeNull();
});

it('runs multi participant review loops with required completion and signed token isolation', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $siteId = (int) DB::table('sites')->insertGetId([]);
    $portalAccount = PortalAccount::factory()->forSite($siteId)->create(['email' => 'jordan@example.com']);
    $startsAt = CarbonImmutable::parse('2026-07-05 10:00:00', 'Europe/London');
    $appointmentRequest = AppointmentRequest::factory()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'customer_name' => 'Jordan Lee',
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
    $portalAccountId = filter_var($portalAccount->getKey(), FILTER_VALIDATE_INT);
    throw_if($portalAccountId === false, RuntimeException::class, 'Expected portal account key to be an integer.');

    CreateReviewLoopAction::run($reviewRequest, [
        [
            'role' => 'learner',
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'portal_account_id' => $portalAccountId,
            'required' => true,
        ],
        [
            'role' => 'guardian',
            'name' => 'Pat Lee',
            'email' => 'pat@example.com',
            'required' => true,
        ],
        [
            'role' => 'instructor',
            'name' => 'Avery Stone',
            'email' => 'avery@example.com',
            'required' => false,
        ],
    ]);

    $learner = BookingReviewParticipant::query()->where('role', 'learner')->firstOrFail();
    $guardian = BookingReviewParticipant::query()->where('role', 'guardian')->firstOrFail();
    $instructor = BookingReviewParticipant::query()->where('role', 'instructor')->firstOrFail();
    $learnerUrl = IssueReviewParticipantUrlAction::run($learner);
    $guardianUrl = IssueReviewParticipantUrlAction::run($guardian);
    $learnerId = filter_var($learner->getKey(), FILTER_VALIDATE_INT);
    throw_if($learnerId === false, RuntimeException::class, 'Expected participant key to be an integer.');
    $guardianToken = basename((string) parse_url($guardianUrl, PHP_URL_PATH));
    $learnerToken = basename((string) parse_url($learnerUrl, PHP_URL_PATH));
    $forgedUrl = str_replace('/review-participant/' . $learnerToken, '/review-participant/' . $guardianToken, $learnerUrl);

    expect($learnerUrl)->not->toContain('/review-participant/' . $learnerId . '/');

    $this->post($forgedUrl, [
        'rating' => 1,
        'response' => 'Forged',
    ])->assertForbidden();

    $this->get($learnerUrl)
        ->assertOk()
        ->assertSee('learner');

    $this->post($learnerUrl, [
        'rating' => 5,
        'response' => 'Learner response.',
    ])->assertRedirect();

    expect($reviewRequest->refresh()->status)->toBe(BookingReviewRequestStatusEnum::Scheduled)
        ->and($learner->refresh()->status)->toBe(BookingReviewParticipantStatusEnum::Completed)
        ->and($instructor->refresh()->status)->toBe(BookingReviewParticipantStatusEnum::Pending);

    $this->post($guardianUrl, [
        'rating' => 4,
        'response' => 'Guardian response.',
    ])->assertRedirect();

    $reviewRequest->refresh();

    expect($reviewRequest->status)->toBe(BookingReviewRequestStatusEnum::Completed)
        ->and($reviewRequest->rating)->toBe(5)
        ->and($reviewRequest->response)->toContain('learner: Learner response.', 'guardian: Guardian response.')
        ->and($reviewRequest->meta['average_rating'] ?? null)->toBe(4.5)
        ->and($reviewRequest->meta['total_participants'] ?? null)->toBe(3);
});

it('keeps legacy parent review capture compatible by creating a default participant', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $appointmentRequest = AppointmentRequest::factory()->create([
        'customer_name' => 'Jordan Lee',
        'customer_email' => 'jordan@example.com',
        'status' => AppointmentRequestStatusEnum::Completed,
        'completed_at' => CarbonImmutable::now(),
    ]);
    $reviewRequest = BookingReviewRequest::query()->create([
        'appointment_request_id' => $appointmentRequest->getKey(),
        'status' => BookingReviewRequestStatusEnum::Scheduled,
        'scheduled_for' => CarbonImmutable::now(),
    ]);

    CaptureReviewAction::run($reviewRequest, 3, 'Simple review.');

    $participant = BookingReviewParticipant::query()->firstOrFail();

    expect($reviewRequest->refresh()->status)->toBe(BookingReviewRequestStatusEnum::Completed)
        ->and($participant->role)->toBe('customer')
        ->and($participant->email)->toBe('jordan@example.com')
        ->and($participant->status)->toBe(BookingReviewParticipantStatusEnum::Completed);
});

it('does not let legacy parent review capture complete required multi participant loops early', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $appointmentRequest = AppointmentRequest::factory()->create([
        'customer_name' => 'Jordan Lee',
        'customer_email' => 'jordan@example.com',
        'status' => AppointmentRequestStatusEnum::Completed,
        'completed_at' => CarbonImmutable::now(),
    ]);
    $reviewRequest = BookingReviewRequest::query()->create([
        'appointment_request_id' => $appointmentRequest->getKey(),
        'status' => BookingReviewRequestStatusEnum::Scheduled,
        'scheduled_for' => CarbonImmutable::now(),
    ]);

    CreateReviewLoopAction::run($reviewRequest, [
        [
            'role' => 'learner',
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'required' => true,
        ],
        [
            'role' => 'guardian',
            'name' => 'Pat Lee',
            'email' => 'pat@example.com',
            'required' => true,
        ],
    ]);

    CaptureReviewAction::run($reviewRequest, 5, 'Learner response.');

    expect($reviewRequest->refresh()->status)->toBe(BookingReviewRequestStatusEnum::Scheduled)
        ->and($reviewRequest->completed_at)->toBeNull()
        ->and(BookingReviewParticipant::query()->where('status', BookingReviewParticipantStatusEnum::Completed)->count())->toBe(1);
});

it('dispatches individual participant review reminder messages with tokenized urls', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $appointmentRequest = AppointmentRequest::factory()->create([
        'customer_name' => 'Jordan Lee',
        'customer_email' => 'jordan@example.com',
        'status' => AppointmentRequestStatusEnum::Completed,
        'completed_at' => CarbonImmutable::now(),
    ]);
    $reviewRequest = BookingReviewRequest::query()->create([
        'appointment_request_id' => $appointmentRequest->getKey(),
        'status' => BookingReviewRequestStatusEnum::Scheduled,
        'scheduled_for' => CarbonImmutable::now(),
    ]);

    CreateReviewLoopAction::run($reviewRequest, [
        [
            'role' => 'learner',
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'required' => true,
        ],
        [
            'role' => 'guardian',
            'name' => 'Pat Lee',
            'email' => 'pat@example.com',
            'required' => true,
        ],
    ]);

    DispatchReviewRequestAction::run($reviewRequest);

    $participantMessages = BookingMessageLog::query()
        ->where('type', 'like', 'review_participant_request:%')
        ->orderBy('recipient')
        ->get();
    $reviewUrls = $participantMessages
        ->pluck('meta')
        ->map(static function (mixed $meta): string {
            if (! is_array($meta)) {
                return '';
            }

            $reviewUrl = $meta['review_url'] ?? null;

            return is_string($reviewUrl) ? $reviewUrl : '';
        })
        ->all();

    expect($participantMessages)->toHaveCount(2)
        ->and($participantMessages->pluck('recipient')->all())->toBe(['jordan@example.com', 'pat@example.com'])
        ->and(BookingReviewParticipant::query()->where('status', BookingReviewParticipantStatusEnum::Sent)->count())->toBe(2);

    foreach ($reviewUrls as $reviewUrl) {
        expect($reviewUrl)->toContain('/bookings/review-participant/');
    }
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

it('retries failed booking message logs in place for operator remediation', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $appointmentRequest = AppointmentRequest::factory()->create([
        'customer_email' => 'jordan@example.com',
    ]);
    $messageLog = BookingMessageLog::query()->create([
        'appointment_request_id' => $appointmentRequest->getKey(),
        'site_id' => $appointmentRequest->site_id,
        'portal_account_id' => $appointmentRequest->portal_account_id,
        'channel' => BookingMessageChannelEnum::Email,
        'type' => 'confirmation',
        'status' => BookingMessageStatusEnum::Failed,
        'recipient' => 'jordan@example.com',
        'subject' => 'Confirmed',
        'body' => 'Your booking is confirmed.',
        'error' => 'Provider timed out',
        'meta' => ['original' => true],
    ]);
    $channel = new RecordingBookingMessageChannel(new BookingMessageResultData(
        sent: true,
        providerMessageId: 'retry-123',
        meta: ['provider' => 'fake'],
    ));

    app()->instance(BookingMessageChannel::class, $channel);

    $retriedMessageLog = RetryBookingMessageAction::run($messageLog);

    expect($retriedMessageLog->is($messageLog))->toBeTrue()
        ->and($retriedMessageLog->status)->toBe(BookingMessageStatusEnum::Sent)
        ->and($retriedMessageLog->provider_message_id)->toBe('retry-123')
        ->and($retriedMessageLog->error)->toBeNull()
        ->and($retriedMessageLog->meta['retry_count'] ?? null)->toBe(1)
        ->and($retriedMessageLog->meta['original'] ?? null)->toBeTrue()
        ->and($channel->messages)->toHaveCount(1)
        ->and($channel->messages[0]->recipient)->toBe('jordan@example.com')
        ->and($channel->messages[0]->context['retry'] ?? null)->toBeTrue();
});

it('resets processed webhook events for replay without duplicating provider events', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 09:00:00', 'Europe/London'));

    $webhookEvent = MarkBookingWebhookEventProcessedAction::run(
        RecordBookingWebhookEventAction::run('stripe', 'evt_replay_123', 'checkout.session.completed', ['amount_total' => 7500]),
    );

    expect($webhookEvent->status)->toBe('processed')
        ->and($webhookEvent->processed_at)->not->toBeNull();

    $replayedWebhookEvent = ReplayBookingWebhookEventAction::run($webhookEvent);
    $duplicateWebhookEvent = RecordBookingWebhookEventAction::run('stripe', 'evt_replay_123', 'checkout.session.completed', ['amount_total' => 9999]);

    expect($replayedWebhookEvent->is($webhookEvent))->toBeTrue()
        ->and($replayedWebhookEvent->status)->toBe('received')
        ->and($replayedWebhookEvent->processed_at)->toBeNull()
        ->and($duplicateWebhookEvent->is($webhookEvent))->toBeTrue()
        ->and(DB::table('booking_webhook_events')->where('provider_event_id', 'evt_replay_123')->count())->toBe(1);
});

it('accepts configured webhook route events without exposing an open ingestion endpoint', function (): void {
    config(['capell-bookings.webhook_tokens.stripe' => 'secret-token']);

    $this->postJson('/bookings/webhooks/stripe', [
        'id' => 'evt_forbidden',
        'type' => 'checkout.session.completed',
    ])->assertForbidden();

    $this->withHeader('X-Capell-Webhook-Token', 'secret-token')->postJson('/bookings/webhooks/stripe', [
        'id' => 'evt_route_123',
        'type' => 'checkout.session.completed',
        'amount_total' => 7500,
    ])->assertCreated()
        ->assertJsonPath('status', 'received');

    $this->withHeader('Authorization', 'Bearer secret-token')->postJson('/bookings/webhooks/stripe', [
        'id' => 'evt_route_123',
        'type' => 'checkout.session.completed',
        'amount_total' => 9999,
    ])->assertOk();

    expect(BookingMessageLog::query()->count())->toBe(0)
        ->and(DB::table('booking_webhook_events')->where('provider_event_id', 'evt_route_123')->count())->toBe(1);
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
