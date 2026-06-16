<?php

declare(strict_types=1);

use Capell\CustomerPortal\Actions\ResolvePortalDashboardItemsAction;
use Capell\CustomerPortal\Actions\ResolvePortalProfileAction;
use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\EquestrianClinics\Actions\AllocateHorseToSlotAction;
use Capell\EquestrianClinics\Actions\BuildFacilityReportAction;
use Capell\EquestrianClinics\Actions\BuildOpenSlotDemandHeatmapAction;
use Capell\EquestrianClinics\Actions\BuildSlotBookingCheckoutSessionDataAction;
use Capell\EquestrianClinics\Actions\BuildStaffCareWorklistAction;
use Capell\EquestrianClinics\Actions\CancelSlotBookingAction;
use Capell\EquestrianClinics\Actions\ClaimWaitlistOfferAction;
use Capell\EquestrianClinics\Actions\CompleteHorseCareTaskAction;
use Capell\EquestrianClinics\Actions\ConfirmSlotBookingPaymentAction;
use Capell\EquestrianClinics\Actions\CreateBillingEntryAction;
use Capell\EquestrianClinics\Actions\CreateCommercialProductAction;
use Capell\EquestrianClinics\Actions\CreateHorseCareTaskAction;
use Capell\EquestrianClinics\Actions\CreateHorseHealthRecordAction;
use Capell\EquestrianClinics\Actions\ExpireSlotBookingHoldsAction;
use Capell\EquestrianClinics\Actions\ExpireWaitlistOffersAction;
use Capell\EquestrianClinics\Actions\GenerateTourDaySlotsAction;
use Capell\EquestrianClinics\Actions\JoinSlotWaitlistAction;
use Capell\EquestrianClinics\Actions\MarkBillingEntryExportedAction;
use Capell\EquestrianClinics\Actions\PromoteWaitlistEntryAction;
use Capell\EquestrianClinics\Actions\QuoteTourDaySlotBookingAction;
use Capell\EquestrianClinics\Actions\RecordCoachBroadcastAction;
use Capell\EquestrianClinics\Actions\RecordCompetitionResultAction;
use Capell\EquestrianClinics\Actions\RecordHostRequestAction;
use Capell\EquestrianClinics\Actions\RequestSlotBookingAction;
use Capell\EquestrianClinics\Actions\ReserveFacilityResourceAction;
use Capell\EquestrianClinics\Actions\ValidateRiderHorseEligibilityAction;
use Capell\EquestrianClinics\Data\EquestrianSlotTemplateData;
use Capell\EquestrianClinics\Enums\EquestrianBillingEntryStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianCareTaskTypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianCommercialProductTypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianCommunicationChannelEnum;
use Capell\EquestrianClinics\Enums\EquestrianFacilityResourceTypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianHorseHealthRecordTypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianPaymentFeeModeEnum;
use Capell\EquestrianClinics\Enums\EquestrianPaymentStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotArchetypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianTourDayStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianWaitlistStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianFacilityResource;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Capell\EquestrianClinics\Models\EquestrianStaffMember;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianVenue;
use Capell\EquestrianClinics\Providers\EquestrianClinicsServiceProvider;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

it('generates tour day slots from a template and quotes legal-safe payment fees', function (): void {
    config()->set('capell-equestrian-clinics.universal_booking_fee_pence', 150);

    $tourDay = createTourDay();

    $slots = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Private lesson',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 15,
        capacityMin: 1,
        capacityMax: 1,
        pricePence: 4500,
        skillTier: 'novice',
    ));

    $slot = $slots->firstOrFail();

    $quote = QuoteTourDaySlotBookingAction::run(
        slot: $slot,
        provider: PaymentProvider::Stripe,
        travelFeePence: 500,
        addOnsPence: 2000,
    );

    expect($slots)->toHaveCount(9)
        ->and($slot->archetype)->toBe(EquestrianSlotArchetypeEnum::Private)
        ->and($quote->basePricePence)->toBe(4500)
        ->and($quote->universalBookingFeePence)->toBe(150)
        ->and($quote->totalPence)->toBe(7150);
});

it('guards method-specific Stripe and PayPal fees behind legal acknowledgement', function (): void {
    $slot = GenerateTourDaySlotsAction::run(createTourDay(), new EquestrianSlotTemplateData(
        title: 'Group clinic',
        archetype: EquestrianSlotArchetypeEnum::GroupClinic,
        durationMinutes: 60,
        gapMinutes: 0,
        capacityMin: 3,
        capacityMax: 6,
        pricePence: 3500,
    ))->firstOrFail();

    expect(fn (): mixed => QuoteTourDaySlotBookingAction::run(
        slot: $slot,
        provider: PaymentProvider::PayPal,
        feeMode: EquestrianPaymentFeeModeEnum::MethodSpecific,
        methodFeePence: 125,
    ))->toThrow(ValidationException::class);

    $quote = QuoteTourDaySlotBookingAction::run(
        slot: $slot,
        provider: PaymentProvider::PayPal,
        feeMode: EquestrianPaymentFeeModeEnum::MethodSpecific,
        methodFeePence: 125,
        methodFeeLegalAcknowledged: true,
    );

    expect($quote->methodFeePence)->toBe(125)
        ->and($quote->totalPence)->toBe(3625)
        ->and($quote->methodFeeLegalAcknowledgementRequired)->toBeTrue();
});

it('enforces rider skill tiers, horse suitability, workload limits, and facility conflicts', function (): void {
    $slot = GenerateTourDaySlotsAction::run(createTourDay(), new EquestrianSlotTemplateData(
        title: 'Advanced gridwork',
        archetype: EquestrianSlotArchetypeEnum::SemiPrivate,
        durationMinutes: 90,
        gapMinutes: 0,
        capacityMin: 2,
        capacityMax: 2,
        pricePence: 6500,
        skillTier: 'advanced',
    ))->firstOrFail();

    $rider = EquestrianRiderProfile::query()->create([
        'name' => 'Ada Rider',
        'skill_tiers' => ['advanced'],
    ]);
    $horse = EquestrianHorseProfile::query()->create([
        'name' => 'Blue',
        'daily_workload_limit_minutes' => 90,
        'suitable_skill_tiers' => ['advanced'],
    ]);

    ValidateRiderHorseEligibilityAction::run($slot, $rider, $horse);
    $allocatedSlot = AllocateHorseToSlotAction::run($slot, $horse);

    $facilityResource = EquestrianFacilityResource::query()->create([
        'venue_id' => $slot->tourDay->venue_id,
        'name' => 'Indoor arena',
        'type' => EquestrianFacilityResourceTypeEnum::Arena,
        'capacity' => 1,
        'price_pence' => 4000,
    ]);

    ReserveFacilityResourceAction::run($facilityResource, $slot);
    $secondSlot = $slot->replicate();
    $secondSlot->forceFill([
        'tour_day_id' => $slot->tour_day_id,
        'starts_at' => $slot->ends_at,
        'ends_at' => $slot->ends_at->addMinutes(45),
    ])->save();

    expect($allocatedSlot->meta['horse_profile_id'] ?? null)->toBe($horse->id)
        ->and(fn (): mixed => AllocateHorseToSlotAction::run($secondSlot, $horse))->toThrow(ValidationException::class)
        ->and(fn (): mixed => ReserveFacilityResourceAction::run($facilityResource, $slot))->toThrow(ValidationException::class);
});

it('builds host-safe facility reports and open-slot demand heatmaps', function (): void {
    $tourDay = createTourDay();
    $slot = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Polework clinic',
        archetype: EquestrianSlotArchetypeEnum::GroupClinic,
        durationMinutes: 60,
        gapMinutes: 0,
        capacityMin: 3,
        capacityMax: 6,
        pricePence: 3500,
        skillTier: 'novice',
    ))->firstOrFail();
    $slot->forceFill(['booked_count' => 4])->save();

    $stable = EquestrianFacilityResource::query()->create([
        'venue_id' => $tourDay->venue_id,
        'name' => 'Day stable',
        'type' => EquestrianFacilityResourceTypeEnum::Stable,
        'capacity' => 12,
        'price_pence' => 2000,
    ]);
    ReserveFacilityResourceAction::run($stable, $slot, 4);

    RecordHostRequestAction::run([
        'requester_name' => 'Venue Owner',
        'requester_email' => 'venue@example.com',
        'preferred_region' => 'North Yorkshire',
        'lesson_type' => 'Polework',
        'skill_tier' => 'novice',
        'expected_riders' => 8,
    ]);
    RecordHostRequestAction::run([
        'requester_name' => 'Second Owner',
        'requester_email' => 'second@example.com',
        'preferred_region' => 'North Yorkshire',
        'expected_riders' => 5,
    ]);

    $report = BuildFacilityReportAction::run($tourDay->refresh());
    $heatmap = BuildOpenSlotDemandHeatmapAction::run();

    expect($report->venueName)->toBe('Willow Farm Arena')
        ->and($report->expectedRiders)->toBe(4)
        ->and($report->resources)->toBe(['Day stable' => 4])
        ->and($report->slots[0])->not->toHaveKey('medical_disclosures')
        ->and($heatmap->first())->toBe([
            'region' => 'North Yorkshire',
            'requests' => 2,
            'expected_riders' => 13,
        ]);
});

it('holds online checkout for Stripe or PayPal and confirms only from provider state', function (): void {
    config()->set('capell-equestrian-clinics.checkout_hold_minutes', 10);

    $tourDay = createTourDay();
    $slot = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Private lesson',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 1,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();
    $rider = createRider('Checkout Rider');
    $horse = createHorse('Checkout Horse');
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');

    $booking = RequestSlotBookingAction::run(
        slot: $slot,
        riderProfile: $rider,
        horseProfile: $horse,
        provider: PaymentProvider::Stripe,
        quotedTotalPence: 4650,
        now: $now,
    );

    expect($booking->status)->toBe(EquestrianSlotBookingStatusEnum::Held)
        ->and($booking->payment_status)->toBe(EquestrianPaymentStatusEnum::Pending)
        ->and($booking->payment_provider)->toBe(PaymentProvider::Stripe)
        ->and($booking->hold_expires_at?->equalTo($now->addMinutes(10)))->toBeTrue()
        ->and($slot->refresh()->booked_count)->toBe(0)
        ->and(fn (): mixed => RequestSlotBookingAction::run(
            slot: $slot->refresh(),
            riderProfile: createRider('Second Rider'),
            horseProfile: createHorse('Second Horse'),
            provider: PaymentProvider::PayPal,
            now: $now,
        ))->toThrow(ValidationException::class);

    $confirmed = ConfirmSlotBookingPaymentAction::run($booking, $now->addMinutes(4));

    expect($confirmed->status)->toBe(EquestrianSlotBookingStatusEnum::Confirmed)
        ->and($confirmed->payment_status)->toBe(EquestrianPaymentStatusEnum::Paid)
        ->and($confirmed->hold_expires_at)->toBeNull()
        ->and($slot->refresh()->booked_count)->toBe(1);
});

it('builds a Payments checkout handoff from an active online slot hold', function (): void {
    config()->set('capell-equestrian-clinics.checkout_hold_minutes', 10);

    $tourDay = createTourDay();
    $tourDay->forceFill(['site_id' => 12])->save();
    $slot = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Semi-private clinic',
        archetype: EquestrianSlotArchetypeEnum::SemiPrivate,
        durationMinutes: 60,
        gapMinutes: 0,
        capacityMin: 2,
        capacityMax: 2,
        pricePence: 5500,
        skillTier: 'novice',
    ))->firstOrFail();
    $rider = createRider('Checkout Rider');
    $horse = createHorse('Checkout Horse');
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');
    $booking = RequestSlotBookingAction::run(
        slot: $slot,
        riderProfile: $rider,
        horseProfile: $horse,
        provider: PaymentProvider::Stripe,
        quotedTotalPence: 5750,
        now: $now,
    );

    $checkoutData = BuildSlotBookingCheckoutSessionDataAction::run(
        booking: $booking,
        successUrl: 'https://example.test/clinics/checkout/success',
        cancelUrl: 'https://example.test/clinics/checkout/cancel',
        now: $now,
    );

    expect($checkoutData->provider)->toBe(PaymentProvider::Stripe)
        ->and($checkoutData->purpose)->toBe(PaymentPurpose::OneOff)
        ->and($checkoutData->mode)->toBe(CheckoutMode::Payment)
        ->and($checkoutData->siteId)->toBe(12)
        ->and($checkoutData->customerEmail)->toBe('checkout-rider@example.com')
        ->and($checkoutData->customerName)->toBe('Checkout Rider')
        ->and($checkoutData->payableType)->toBe(EquestrianSlotBooking::class)
        ->and($checkoutData->payableId)->toBe((string) $booking->getKey())
        ->and($checkoutData->referenceId)->toBe('equestrian-slot-booking-' . $booking->getKey())
        ->and($checkoutData->lineItems)->toHaveCount(1)
        ->and($checkoutData->lineItems[0]->name)->toBe('Semi-private clinic - Willow Farm Tour Day')
        ->and($checkoutData->lineItems[0]->amount)->toBe(5750)
        ->and($checkoutData->lineItems[0]->currency)->toBe('gbp')
        ->and($checkoutData->lineItems[0]->description)->toBe('Checkout Rider with Checkout Horse')
        ->and($checkoutData->metadata['equestrian_slot_booking_id'] ?? null)->toBe($booking->getKey())
        ->and($checkoutData->metadata['hold_expires_at'] ?? null)->toBe($now->addMinutes(10)->toIso8601String());
});

it('rejects checkout handoff for expired, cash, or already confirmed bookings', function (): void {
    $slot = GenerateTourDaySlotsAction::run(createTourDay(), new EquestrianSlotTemplateData(
        title: 'Private checkout',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 3,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');
    $expiredHold = RequestSlotBookingAction::run($slot, createRider('Expired Rider'), createHorse('Expired Horse'), PaymentProvider::PayPal, false, 4500, $now);
    $confirmedHold = RequestSlotBookingAction::run($slot, createRider('Confirmed Rider'), createHorse('Confirmed Horse'), PaymentProvider::Stripe, false, 4500, $now);
    $cashBooking = RequestSlotBookingAction::run($slot, createRider('Cash Rider', $now), createHorse('Cash Horse'), null, true, 4500, $now);

    ConfirmSlotBookingPaymentAction::run($confirmedHold, $now->addMinute());

    foreach ([$expiredHold, $confirmedHold, $cashBooking] as $booking) {
        expect(fn (): mixed => BuildSlotBookingCheckoutSessionDataAction::run(
            booking: $booking->refresh(),
            successUrl: 'https://example.test/success',
            cancelUrl: 'https://example.test/cancel',
            now: $now->addMinutes(11),
        ))->toThrow(ValidationException::class);
    }
});

it('contributes rider horse and booking surfaces to the customer portal without private care details', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-18 08:00:00'));

    $siteId = (int) DB::table('sites')->insertGetId([]);
    $portalAccount = PortalAccount::query()->create([
        'site_id' => $siteId,
        'email' => 'portal-rider@example.com',
        'display_name' => 'Portal Rider',
        'status' => PortalAccountStatus::Active,
    ]);
    $otherPortalAccount = PortalAccount::query()->create([
        'site_id' => $siteId,
        'email' => 'other-rider@example.com',
        'display_name' => 'Other Rider',
        'status' => PortalAccountStatus::Active,
    ]);
    $rider = EquestrianRiderProfile::query()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'name' => 'Jordan Rider',
        'email' => 'portal-rider@example.com',
        'emergency_contact_name' => 'Private Guardian',
        'emergency_contact_phone' => '07111111111',
        'medical_disclosures' => 'Private medical notes',
        'skill_tiers' => ['novice'],
        'cash_approved_at' => CarbonImmutable::now(),
        'active' => true,
    ]);
    $horse = EquestrianHorseProfile::query()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'name' => 'Quiet Cob',
        'fitness_status' => 'fit',
        'notes' => 'Private horse notes',
        'daily_workload_limit_minutes' => 240,
        'suitable_skill_tiers' => ['novice'],
        'active' => true,
    ]);
    EquestrianRiderProfile::query()->create([
        'site_id' => $siteId,
        'portal_account_id' => $otherPortalAccount->getKey(),
        'name' => 'Other Private Rider',
        'email' => 'other-rider@example.com',
        'active' => true,
    ]);
    $tourDay = createTourDay();
    $tourDay->forceFill(['site_id' => $siteId])->save();
    $slot = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Portal clinic',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 1,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();

    RequestSlotBookingAction::run(
        slot: $slot,
        riderProfile: $rider,
        horseProfile: $horse,
        cashPayment: true,
        quotedTotalPence: 4500,
        now: CarbonImmutable::now(),
    );

    $profile = ResolvePortalProfileAction::run($portalAccount);
    $dashboardItems = ResolvePortalDashboardItemsAction::run($portalAccount);
    $selfServiceItems = ResolvePortalSelfServiceItemsAction::run($portalAccount);
    $surfaceJson = json_encode([
        'profile' => $profile->profile,
        'dashboard' => $dashboardItems,
        'self_service' => $selfServiceItems,
    ], JSON_THROW_ON_ERROR);

    expect($profile->profile['equestrian']['riders'][0]['name'] ?? null)->toBe('Jordan Rider')
        ->and($profile->profile['equestrian']['horses'][0]['name'] ?? null)->toBe('Quiet Cob')
        ->and($dashboardItems)->toHaveCount(1)
        ->and($dashboardItems[0]->key)->toBe('equestrian-clinics.profile')
        ->and($dashboardItems[0]->count)->toBe(1)
        ->and(collect($selfServiceItems)->pluck('key')->all())->toContain(
            'equestrian-clinics.rider.' . $rider->getKey(),
            'equestrian-clinics.horse.' . $horse->getKey(),
        )
        ->and($surfaceJson)->toContain('Portal clinic')
        ->and($surfaceJson)->not->toContain(
            'Private Guardian',
            '07111111111',
            'Private medical notes',
            'Private horse notes',
            'Other Private Rider',
            'other-rider@example.com',
        );

    CarbonImmutable::setTestNow();
});

it('expires stale checkout holds and permits approved cash customers', function (): void {
    $slot = GenerateTourDaySlotsAction::run(createTourDay(), new EquestrianSlotTemplateData(
        title: 'Private checkout',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 1,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');

    $hold = RequestSlotBookingAction::run(
        slot: $slot,
        riderProfile: createRider('Holding Rider'),
        horseProfile: createHorse('Holding Horse'),
        provider: PaymentProvider::PayPal,
        now: $now,
    );

    $expiredCount = ExpireSlotBookingHoldsAction::run($now->addMinutes(11));

    expect($expiredCount)->toBe(1)
        ->and($hold->refresh()->status)->toBe(EquestrianSlotBookingStatusEnum::Expired)
        ->and($hold->payment_status)->toBe(EquestrianPaymentStatusEnum::Failed)
        ->and(fn (): mixed => RequestSlotBookingAction::run(
            slot: $slot->refresh(),
            riderProfile: createRider('Unapproved Cash Rider'),
            horseProfile: createHorse('Cash Horse'),
            cashPayment: true,
            now: $now->addMinutes(12),
        ))->toThrow(ValidationException::class);

    $cashBooking = RequestSlotBookingAction::run(
        slot: $slot->refresh(),
        riderProfile: createRider('Approved Cash Rider', cashApprovedAt: $now),
        horseProfile: createHorse('Approved Cash Horse'),
        cashPayment: true,
        now: $now->addMinutes(12),
    );

    expect($cashBooking->status)->toBe(EquestrianSlotBookingStatusEnum::Confirmed)
        ->and($cashBooking->payment_status)->toBe(EquestrianPaymentStatusEnum::CashApproved)
        ->and($slot->refresh()->booked_count)->toBe(1);
});

it('applies booking cutoffs and refund windows when customers cancel', function (): void {
    $tourDay = createTourDay();
    $slot = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Cancellation lesson',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 1,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();

    $booking = RequestSlotBookingAction::run(
        slot: $slot,
        riderProfile: createRider('Refund Rider', cashApprovedAt: CarbonImmutable::parse('2026-06-01')),
        horseProfile: createHorse('Refund Horse'),
        cashPayment: true,
        now: CarbonImmutable::parse('2026-06-17 07:00:00'),
    );

    $refundAllowed = CancelSlotBookingAction::run($booking, CarbonImmutable::parse('2026-06-17 08:00:00'));

    expect($refundAllowed)->toBeTrue()
        ->and($booking->refresh()->status)->toBe(EquestrianSlotBookingStatusEnum::Cancelled)
        ->and($booking->meta['refund_allowed'] ?? null)->toBeTrue()
        ->and($slot->refresh()->booked_count)->toBe(0)
        ->and(fn (): mixed => RequestSlotBookingAction::run(
            slot: $slot->refresh(),
            riderProfile: createRider('Late Rider', cashApprovedAt: CarbonImmutable::parse('2026-06-01')),
            horseProfile: createHorse('Late Horse'),
            cashPayment: true,
            now: CarbonImmutable::parse('2026-06-19 09:00:00'),
        ))->toThrow(ValidationException::class);
});

it('promotes waitlisted riders into private claim windows', function (): void {
    config()->set('capell-equestrian-clinics.waitlist_claim_minutes', 120);

    $tourDay = createTourDay();
    $slot = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Waitlist private',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 1,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');

    $confirmedBooking = RequestSlotBookingAction::run(
        slot: $slot,
        riderProfile: createRider('Original Rider', cashApprovedAt: CarbonImmutable::parse('2026-06-01')),
        horseProfile: createHorse('Original Horse'),
        cashPayment: true,
        now: $now,
    );
    $waitlistEntry = JoinSlotWaitlistAction::run(
        slot: $slot->refresh(),
        riderProfile: createRider('Waiting Rider'),
        horseProfile: createHorse('Waiting Horse'),
        quotedTotalPence: 4500,
        now: $now,
    );

    expect($waitlistEntry->status)->toBe(EquestrianWaitlistStatusEnum::Waiting)
        ->and($slot->refresh()->waitlist_count)->toBe(1);

    CancelSlotBookingAction::run($confirmedBooking, $now->addHour());
    $offered = PromoteWaitlistEntryAction::run($slot->refresh(), $now->addHour());
    $claimedBooking = ClaimWaitlistOfferAction::run(
        entry: $offered,
        provider: PaymentProvider::PayPal,
        now: $now->addMinutes(90),
    );

    expect($offered->refresh()->status)->toBe(EquestrianWaitlistStatusEnum::Claimed)
        ->and($claimedBooking->status)->toBe(EquestrianSlotBookingStatusEnum::Held)
        ->and($claimedBooking->payment_provider)->toBe(PaymentProvider::PayPal)
        ->and($slot->refresh()->waitlist_count)->toBe(0);

    $expiringEntry = JoinSlotWaitlistAction::run(
        slot: $slot->refresh(),
        riderProfile: createRider('Second Waiting Rider'),
        horseProfile: createHorse('Second Waiting Horse'),
        now: $now->addMinutes(91),
    );
    ExpireSlotBookingHoldsAction::run($now->addMinutes(200));
    $secondOffer = PromoteWaitlistEntryAction::run($slot->refresh(), $now->addMinutes(201));
    $expiredOffers = ExpireWaitlistOffersAction::run($now->addMinutes(322));

    expect($expiringEntry->refresh()->status)->toBe(EquestrianWaitlistStatusEnum::Expired)
        ->and($secondOffer->refresh()->status)->toBe(EquestrianWaitlistStatusEnum::Expired)
        ->and($expiredOffers)->toBeGreaterThanOrEqual(1);
});

it('registers expiry commands and schedules them every five minutes', function (): void {
    $schedule = new Schedule;
    app()->instance(Schedule::class, $schedule);

    (new EquestrianClinicsServiceProvider(app()))->packageBooted();

    $holdEvent = collect($schedule->events())
        ->first(fn (mixed $scheduledEvent): bool => str_contains((string) $scheduledEvent->command, 'capell:equestrian-clinics-expire-holds'));
    $waitlistEvent = collect($schedule->events())
        ->first(fn (mixed $scheduledEvent): bool => str_contains((string) $scheduledEvent->command, 'capell:equestrian-clinics-expire-waitlist-offers'));

    throw_unless($holdEvent instanceof ScheduledEvent, RuntimeException::class, 'Expected stale hold expiry schedule to be registered.');
    throw_unless($waitlistEvent instanceof ScheduledEvent, RuntimeException::class, 'Expected waitlist offer expiry schedule to be registered.');

    expect($holdEvent->expression)->toBe('*/5 * * * *')
        ->and($holdEvent->withoutOverlapping)->toBeTrue()
        ->and($holdEvent->onOneServer)->toBeTrue()
        ->and($waitlistEvent->expression)->toBe('*/5 * * * *')
        ->and($waitlistEvent->withoutOverlapping)->toBeTrue()
        ->and($waitlistEvent->onOneServer)->toBeTrue();
});

it('expires stale holds and waitlist offers through console commands', function (): void {
    $slot = GenerateTourDaySlotsAction::run(createTourDay(), new EquestrianSlotTemplateData(
        title: 'Command expiry private',
        archetype: EquestrianSlotArchetypeEnum::Private,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 1,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');

    EquestrianSlotBooking::query()->create([
        'tour_day_slot_id' => $slot->getKey(),
        'rider_profile_id' => createRider('Command Held Rider')->getKey(),
        'horse_profile_id' => createHorse('Command Held Horse')->getKey(),
        'status' => EquestrianSlotBookingStatusEnum::Held,
        'payment_provider' => PaymentProvider::PayPal,
        'payment_status' => EquestrianPaymentStatusEnum::Pending,
        'cash_payment' => false,
        'quoted_total_pence' => 4500,
        'hold_expires_at' => $now->subMinute(),
    ]);
    EquestrianSlotWaitlistEntry::query()->create([
        'tour_day_slot_id' => $slot->getKey(),
        'rider_profile_id' => createRider('Command Waiting Rider')->getKey(),
        'horse_profile_id' => createHorse('Command Waiting Horse')->getKey(),
        'status' => EquestrianWaitlistStatusEnum::Offered,
        'quoted_total_pence' => 4500,
        'offered_at' => $now->subHours(3),
        'offer_expires_at' => $now->subMinute(),
    ]);

    CarbonImmutable::setTestNow($now);

    try {
        $this->artisan('capell:equestrian-clinics-expire-holds', ['--json' => true])
            ->expectsOutput('{"expired_holds":1}')
            ->assertSuccessful();
        $this->artisan('capell:equestrian-clinics-expire-waitlist-offers', ['--json' => true])
            ->expectsOutput('{"expired_offers":1}')
            ->assertSuccessful();
    } finally {
        CarbonImmutable::setTestNow();
    }

    expect(EquestrianSlotBooking::query()->firstOrFail()->status)->toBe(EquestrianSlotBookingStatusEnum::Expired)
        ->and(EquestrianSlotWaitlistEntry::query()->firstOrFail()->status)->toBe(EquestrianWaitlistStatusEnum::Expired);
});

it('tracks staff care tasks, commercial products, and broadcast recipients', function (): void {
    $tourDay = createTourDay();
    $slot = GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Broadcast lesson',
        archetype: EquestrianSlotArchetypeEnum::SemiPrivate,
        durationMinutes: 45,
        gapMinutes: 0,
        capacityMin: 1,
        capacityMax: 2,
        pricePence: 4500,
        skillTier: 'novice',
    ))->firstOrFail();
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');
    $staffMember = EquestrianStaffMember::query()->create([
        'name' => 'Yard Lead',
        'roles' => ['yard', 'coach'],
    ]);
    $horse = createHorse('Care Horse');

    $task = CreateHorseCareTaskAction::run(
        horseProfile: $horse,
        type: EquestrianCareTaskTypeEnum::Medication,
        title: 'Morning supplement',
        dueAt: $now->addHour(),
        assignedStaffMember: $staffMember,
        billablePence: 750,
    );
    $worklist = BuildStaffCareWorklistAction::run($staffMember, $now->addDay());
    $completedTask = CompleteHorseCareTaskAction::run($task, $now->addHours(2), 'Given after feed.');
    $product = CreateCommercialProductAction::run(
        type: EquestrianCommercialProductTypeEnum::StableCard,
        name: 'Five clinic stable card',
        pricePence: 20000,
        creditQuantity: 5,
        eligibleArchetype: EquestrianSlotArchetypeEnum::Private->value,
        settings: ['transferable' => false],
    );

    RequestSlotBookingAction::run(
        slot: $slot,
        riderProfile: createRider('Broadcast Rider A', cashApprovedAt: CarbonImmutable::parse('2026-06-01')),
        horseProfile: $horse,
        cashPayment: true,
        now: $now,
    );
    RequestSlotBookingAction::run(
        slot: $slot->refresh(),
        riderProfile: createRider('Broadcast Rider B', cashApprovedAt: CarbonImmutable::parse('2026-06-01')),
        horseProfile: createHorse('Broadcast Horse B'),
        cashPayment: true,
        now: $now,
    );

    $broadcast = RecordCoachBroadcastAction::run(
        tourDay: $tourDay,
        channel: EquestrianCommunicationChannelEnum::Sms,
        message: 'Running 15 minutes late.',
        slot: $slot->refresh(),
        sentAt: $now->addHours(3),
    );

    expect($staffMember->hasRole('yard'))->toBeTrue()
        ->and($worklist)->toHaveCount(1)
        ->and($worklist->first()?->id)->toBe($task->id)
        ->and($completedTask->completed_at?->equalTo($now->addHours(2)))->toBeTrue()
        ->and($completedTask->meta['completion_note'] ?? null)->toBe('Given after feed.')
        ->and($product->type)->toBe(EquestrianCommercialProductTypeEnum::StableCard)
        ->and($product->credit_quantity)->toBe(5)
        ->and($broadcast->recipient_count)->toBe(2)
        ->and($broadcast->recipients[0]['name'] ?? null)->toBe('Broadcast Rider A');
});

it('records horse health history, competition scores, and invoice export state', function (): void {
    $tourDay = createTourDay();
    $rider = createRider('Competition Rider');
    $horse = createHorse('Competition Horse');
    $staffMember = EquestrianStaffMember::query()->create([
        'name' => 'Records Lead',
        'roles' => ['admin'],
    ]);
    $now = CarbonImmutable::parse('2026-06-18 08:00:00');

    $healthRecord = CreateHorseHealthRecordAction::run(
        horseProfile: $horse,
        type: EquestrianHorseHealthRecordTypeEnum::Vaccination,
        summary: 'Annual booster',
        occurredAt: $now,
        dueNextAt: $now->addYear(),
        recordedByStaffMember: $staffMember,
        providerName: 'York Equine Vets',
        billablePence: 8500,
        documents: [
            ['label' => 'Certificate', 'path' => 'media/vaccination-certificate.pdf'],
        ],
    );
    $result = RecordCompetitionResultAction::run(
        riderProfile: $rider,
        className: 'Novice polework',
        occurredAt: $now->addDays(2),
        tourDay: $tourDay,
        horseProfile: $horse,
        discipline: 'working hunter',
        score: '72.5%',
        placing: '2nd',
    );
    $billingEntry = CreateBillingEntryAction::run(
        label: 'Annual booster',
        amountPence: $healthRecord->billable_pence,
        source: $healthRecord,
        portalAccountId: 123,
    );
    $exportedEntry = MarkBillingEntryExportedAction::run(
        billingEntry: $billingEntry,
        invoiceReference: 'QB-1024',
        exportedAt: $now->addDay(),
    );

    expect($healthRecord->type)->toBe(EquestrianHorseHealthRecordTypeEnum::Vaccination)
        ->and($healthRecord->recordedByStaffMember?->is($staffMember))->toBeTrue()
        ->and($healthRecord->documents[0]['label'] ?? null)->toBe('Certificate')
        ->and($result->score)->toBe('72.5%')
        ->and($result->riderProfile->is($rider))->toBeTrue()
        ->and($exportedEntry->status)->toBe(EquestrianBillingEntryStatusEnum::Invoiced)
        ->and($exportedEntry->invoice_reference)->toBe('QB-1024')
        ->and($exportedEntry->source_id)->toBe($healthRecord->id);
});

function createTourDay(): EquestrianTourDay
{
    $venue = EquestrianVenue::query()->create([
        'name' => 'Willow Farm Arena',
        'postal_code' => 'YO1 1AA',
        'facility_notes' => 'Indoor arena available.',
        'contact_email' => 'host@example.com',
    ]);

    /** @var EquestrianTourDay $tourDay */
    $tourDay = EquestrianTourDay::query()->create([
        'venue_id' => $venue->getKey(),
        'title' => 'Willow Farm Tour Day',
        'status' => EquestrianTourDayStatusEnum::Published,
        'starts_at' => CarbonImmutable::parse('2026-06-20 08:00:00'),
        'ends_at' => CarbonImmutable::parse('2026-06-20 17:00:00'),
        'minimum_paid_attendees' => 3,
        'minimum_revenue_pence' => 12000,
        'is_public' => true,
    ]);

    return $tourDay;
}

function createRider(string $name, ?CarbonImmutable $cashApprovedAt = null): EquestrianRiderProfile
{
    return EquestrianRiderProfile::query()->create([
        'name' => $name,
        'email' => str($name)->slug() . '@example.com',
        'emergency_contact_phone' => '07123456789',
        'skill_tiers' => ['novice'],
        'cash_approved_at' => $cashApprovedAt,
    ]);
}

function createHorse(string $name): EquestrianHorseProfile
{
    return EquestrianHorseProfile::query()->create([
        'name' => $name,
        'daily_workload_limit_minutes' => 240,
        'suitable_skill_tiers' => ['novice'],
    ]);
}
