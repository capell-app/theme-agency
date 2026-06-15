<?php

declare(strict_types=1);

use Capell\EquestrianClinics\Actions\GenerateTourDaySlotsAction;
use Capell\EquestrianClinics\Data\EquestrianSlotTemplateData;
use Capell\EquestrianClinics\Enums\EquestrianFacilityResourceTypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianPaymentStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotArchetypeEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianTourDayStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianFacilityResource;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianVenue;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

it('renders public clinic discovery with venue and postcode filtering without authoring leakage', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-13 10:00:00'));

    createPublishedClinicTourDay(
        venueName: 'Willow Farm Arena',
        postalCode: 'YO1 1AA',
        title: 'Willow Farm Tour Day',
    );
    createPublishedClinicTourDay(
        venueName: 'Coastal Yard',
        postalCode: 'NE1 1AA',
        title: 'Coastal Gridwork Day',
    );

    $response = $this->get(route('capell-equestrian-clinics.discovery', [
        'postcode' => 'YO1',
    ]));

    $response
        ->assertOk()
        ->assertSee('Willow Farm Tour Day')
        ->assertSee('Willow Farm Arena')
        ->assertDontSee('Coastal Gridwork Day')
        ->assertDontSee('filament')
        ->assertDontSee('signed editor')
        ->assertDontSee('wire:id');

    CarbonImmutable::setTestNow();
});

it('sorts public clinic discovery by distance when coordinates are supplied', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-13 10:00:00'));

    createPublishedClinicTourDay(
        venueName: 'Distant Yard',
        postalCode: 'NE1 1AA',
        title: 'Distant Gridwork Day',
        latitude: 54.9783,
        longitude: -1.6178,
    );
    createPublishedClinicTourDay(
        venueName: 'Nearby Arena',
        postalCode: 'YO1 1AA',
        title: 'Nearby Polework Day',
        latitude: 53.9590,
        longitude: -1.0815,
    );

    $response = $this->get(route('capell-equestrian-clinics.discovery', [
        'latitude' => '53.9583',
        'longitude' => '-1.0803',
    ]));

    $response
        ->assertOk()
        ->assertSeeInOrder(['Nearby Polework Day', 'Distant Gridwork Day'])
        ->assertSee('miles');

    CarbonImmutable::setTestNow();
});

it('keeps discovery output public-safe and query bounded when private bookings exist', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-13 10:00:00'));

    $tourDay = createPublishedClinicTourDay(
        venueName: 'Willow Farm Arena',
        postalCode: 'YO1 1AA',
        title: 'Willow Farm Tour Day',
    );
    $slot = $tourDay->slots()->firstOrFail();
    $rider = EquestrianRiderProfile::query()->create([
        'name' => 'Private Rider',
        'email' => 'private-rider@example.com',
        'date_of_birth' => CarbonImmutable::parse('2014-04-12'),
        'emergency_contact_name' => 'Private Guardian',
        'emergency_contact_phone' => '07700 900111',
        'medical_disclosures' => 'Private medical disclosure',
        'guardian_email' => 'guardian@example.com',
    ]);
    $horse = EquestrianHorseProfile::query()->create([
        'name' => 'Private Horse',
        'notes' => 'Private horse care notes',
    ]);

    EquestrianSlotBooking::query()->create([
        'tour_day_slot_id' => $slot->getKey(),
        'rider_profile_id' => $rider->getKey(),
        'horse_profile_id' => $horse->getKey(),
        'status' => EquestrianSlotBookingStatusEnum::Confirmed,
        'payment_status' => EquestrianPaymentStatusEnum::Paid,
        'quoted_total_pence' => 3500,
        'confirmed_at' => CarbonImmutable::now(),
        'notes' => 'Private booking note',
        'meta' => ['gateway_reference' => 'pi_private_secret'],
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->get(route('capell-equestrian-clinics.discovery'));
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response
        ->assertOk()
        ->assertSee('Willow Farm Tour Day')
        ->assertSee('Willow Farm Arena')
        ->assertDontSee('Private Rider')
        ->assertDontSee('private-rider@example.com')
        ->assertDontSee('Private Guardian')
        ->assertDontSee('07700 900111')
        ->assertDontSee('Private medical disclosure')
        ->assertDontSee('guardian@example.com')
        ->assertDontSee('Private Horse')
        ->assertDontSee('Private horse care notes')
        ->assertDontSee('Private booking note')
        ->assertDontSee('pi_private_secret')
        ->assertDontSee('filament')
        ->assertDontSee('wire:id')
        ->assertDontSee('signed editor');

    expect($queryCount)->toBeLessThanOrEqual(6);

    CarbonImmutable::setTestNow();
});

it('records host requests from the public event page', function (): void {
    $response = $this->from(route('capell-equestrian-clinics.discovery'))->post(route('capell-equestrian-clinics.host-request.store'), [
        'requester_name' => 'Venue Owner',
        'requester_email' => 'venue@example.com',
        'venue_name' => 'Upper Meadow EC',
        'preferred_region' => 'North Yorkshire',
        'expected_riders' => 9,
        'message' => 'We can offer an arena and day stables.',
    ]);

    $response
        ->assertRedirect(route('capell-equestrian-clinics.discovery'))
        ->assertSessionHas('equestrian_host_request_status');

    $this->assertDatabaseHas('equestrian_host_requests', [
        'requester_email' => 'venue@example.com',
        'venue_name' => 'Upper Meadow EC',
        'preferred_region' => 'North Yorkshire',
        'expected_riders' => 9,
    ]);
});

it('requires a signed URL for the coach mobile timetable', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-13 10:00:00'));

    $tourDay = createPublishedClinicTourDay(
        venueName: 'Willow Farm Arena',
        postalCode: 'YO1 1AA',
        title: 'Willow Farm Tour Day',
    );
    $slot = $tourDay->slots()->firstOrFail();
    $facilityResource = EquestrianFacilityResource::query()->create([
        'venue_id' => $tourDay->venue_id,
        'name' => 'Indoor arena',
        'type' => EquestrianFacilityResourceTypeEnum::Arena,
        'capacity' => 1,
        'price_pence' => 4000,
    ]);
    $slot->facilityBookings()->create([
        'facility_resource_id' => $facilityResource->getKey(),
        'starts_at' => $slot->starts_at,
        'ends_at' => $slot->ends_at,
        'quantity' => 1,
    ]);

    $this->get(route('capell-equestrian-clinics.coach.timetable', $tourDay))->assertForbidden();

    $response = $this->get(URL::signedRoute('capell-equestrian-clinics.coach.timetable', $tourDay));

    $response
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Willow Farm Tour Day')
        ->assertSee('Indoor arena x1')
        ->assertSee('Minimum viable clinic')
        ->assertDontSee('medical_disclosures')
        ->assertDontSee('filament');

    CarbonImmutable::setTestNow();
});

function createPublishedClinicTourDay(string $venueName, string $postalCode, string $title, ?float $latitude = null, ?float $longitude = null): EquestrianTourDay
{
    $venue = EquestrianVenue::query()->create([
        'name' => $venueName,
        'address_line' => '1 Main Yard',
        'postal_code' => $postalCode,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'facility_notes' => 'Indoor arena available.',
        'parking_notes' => 'Park in the lower field.',
        'contact_email' => 'host@example.com',
    ]);

    /** @var EquestrianTourDay $tourDay */
    $tourDay = EquestrianTourDay::query()->create([
        'venue_id' => $venue->getKey(),
        'title' => $title,
        'status' => EquestrianTourDayStatusEnum::Published,
        'starts_at' => CarbonImmutable::parse('2026-06-20 08:00:00'),
        'ends_at' => CarbonImmutable::parse('2026-06-20 12:00:00'),
        'minimum_paid_attendees' => 3,
        'minimum_revenue_pence' => 12000,
        'is_public' => true,
    ]);

    GenerateTourDaySlotsAction::run($tourDay, new EquestrianSlotTemplateData(
        title: 'Polework clinic',
        archetype: EquestrianSlotArchetypeEnum::GroupClinic,
        durationMinutes: 60,
        gapMinutes: 15,
        capacityMin: 3,
        capacityMax: 6,
        pricePence: 3500,
        skillTier: 'novice',
    ));

    return $tourDay->refresh();
}
