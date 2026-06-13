<?php

declare(strict_types=1);

use Capell\EquestrianClinics\Actions\AllocateHorseToSlotAction;
use Capell\EquestrianClinics\Actions\BuildFacilityReportAction;
use Capell\EquestrianClinics\Actions\BuildOpenSlotDemandHeatmapAction;
use Capell\EquestrianClinics\Actions\GenerateTourDaySlotsAction;
use Capell\EquestrianClinics\Actions\QuoteTourDaySlotBookingAction;
use Capell\EquestrianClinics\Actions\RecordHostRequestAction;
use Capell\EquestrianClinics\Actions\ReserveFacilityResourceAction;
use Capell\EquestrianClinics\Actions\ValidateRiderHorseEligibilityAction;
use Capell\EquestrianClinics\Data\EquestrianSlotTemplateData;
use Capell\EquestrianClinics\Enums\EquestrianFacilityResourceTypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianPaymentFeeModeEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotArchetypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianTourDayStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianFacilityResource;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianVenue;
use Capell\Payments\Enums\PaymentProvider;
use Carbon\CarbonImmutable;
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
