# Equestrian Clinics

Equestrian Clinics is the Capell Operations package for travelling coaches, riding schools, clinic days, and venue-hosted equestrian activity. It turns generic bookings into an equestrian workflow: Tour Days, venue-linked slots, riders, horses, waivers, payments, credits, facility resources, host requests, and coach-ready day-of sheets.

It ships as `capell-app/equestrian-clinics`, lives inside the Laravel application like any Capell package, and keeps equestrian rules out of the generic Bookings and Events packages.

## The Short Version

- **For site owners:** riders can find nearby clinics, request suitable areas, and book into a workflow that protects payment, safety, and venue costs.
- **For operators:** Tour Days become the main planning object: pick a venue, define hours, generate slots, check capacity, monitor minimum viable clinic thresholds, and run the day from a mobile timetable.
- **For coaches:** the day-of view surfaces venue notes, slot times, resources, capacity, waiver/payment reminders, and minimum clinic status without exposing private medical details.
- **For developers:** equestrian logic stays in package Actions, Data objects, models, migrations, translations, route contracts, and manifest metadata.

## What This Plugin Adds

| Area              | What the package gives you                                                                                                                                                                                                            |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Tour days         | Venue-linked clinic days, operating hours, auto-generated slot templates, private/semi-private/group archetypes, capacity, and minimum viable clinic thresholds.                                                                      |
| Public discovery  | Public clinic search, venue filter, postcode/address filtering, coordinate-based nearest-event sorting, open-slot demand heatmap, and host/request-an-event form.                                                                     |
| Payments          | Stripe and PayPal provider support through Payments, legal-safe universal booking fee quoting, guarded method-specific fees, deposits, add-ons, cash approval, checkout holds, expiry, confirmation, and cancellation/refund cutoffs. |
| Riders and horses | Family-account-ready rider profiles, skill tiers, emergency and medical fields, horse profiles, vaccinations, suitability tiers, workload limits, care tasks, health/service records, staff worklists, and billable care notes.       |
| Facilities        | Arenas, fields, paddocks, stables, horseboxes, hookups, equipment, capacity conflict checks, add-on pricing, and host-safe facility reports.                                                                                          |
| Compliance        | Waiver signature records, versioned consent snapshots, guardian details, sensitive data protection boundaries, and GDPR export/erasure scope.                                                                                         |
| Commerce          | Clinic credits, lesson packs, memberships, stable cards, gift cards, services, venue add-ons, billable records, invoice references, and export-ready billing entries.                                                                 |
| Competitions      | Rider/horse competition result records with class, discipline, score, placing, date, and Tour Day linkage.                                                                                                                            |
| Coach workflow    | Signed mobile timetable, minimum clinic signal, venue/parking notes, slot quick-view, resource reservations, broadcast recipient logging, and waiver/payment status reminder copy.                                                    |

## Why It Matters

Generic booking tools can sell a time slot, but they do not understand whether the rider is safe for the group, whether the horse is suitable, whether the arena is double-booked, or whether the clinic has enough paid attendees to cover the venue. This package keeps those decisions inside Capell so an equestrian business can grow without bolting a separate SaaS booking portal onto the site.

The package is intentionally progressive. A solo travelling coach can start with public discovery, Tour Days, slots, and host requests. A riding school or clinic venue can later enable horse allocation, resource capacity, credits, waivers, messaging, payments, and reporting.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Public `/equestrian-clinics` discovery page with search, venue filter, postcode filter, slot list, waitlist state, demand heatmap, and host request form.
- Signed coach timetable at `/equestrian-clinics/coach/tour-days/{tourDay}/timetable`.
- Marketplace extension card in `docs/assets/marketplace/extension-card.svg`.

Primary operator workflow:

1. Create or choose a venue with address, postcode, map link, facility notes, parking notes, and contact details.
2. Create a Tour Day with operating hours, booking/cancellation windows, minimum attendee/revenue thresholds, and public status.
3. Generate slots from a template or create custom private, semi-private, and group clinic blocks.
4. Reserve facility resources, assign horses, and create care tasks where the business uses yard operations.
5. Publish discovery, take booking requests through the Bookings/Payments integration, monitor waitlists, and promote riders into private claim windows.
6. Run the day from the signed coach timetable.

## Technical Shape

- Service provider: `Capell\EquestrianClinics\Providers\EquestrianClinicsServiceProvider`.
- Config: `packages/equestrian-clinics/config/capell-equestrian-clinics.php`.
- Public routes: discovery, host request submission, and signed coach timetable.
- Actions: slot generation, booking quote, booking holds, payment confirmation, hold expiry, cancellation, waitlist join/promotion/claim/expiry, rider/horse eligibility, horse allocation, horse care tasks, horse health records, competition results, staff worklists, facility reservation, facility report, commercial products, billing entries, broadcast logging, host request capture, demand heatmap, discovery payload, and coach timetable payload.
- Models: venues, Tour Days, slots, staff members, rider profiles, horse profiles, slot bookings, waitlist entries, horse care tasks, horse health records, competition results, facility resources, facility bookings, waiver signatures, clinic credits, commercial products, billing entries, communication logs, and host requests.
- Dependencies: Bookings, Events, Payments, Customer Portal, Address, and Media Library.
- Health: `Capell\EquestrianClinics\Health\EquestrianClinicsHealthCheck` verifies the core schema and action availability.

## Data Model

- `equestrian_venues`: venue profile, postcode, coordinates, maps URL, access/parking/facility notes, and host contact.
- `equestrian_tour_days`: venue-linked clinic day, status, time window, booking/cancellation cutoffs, minimum attendee/revenue thresholds, and public flag.
- `equestrian_tour_day_slots`: private/semi-private/group slot blocks, capacity, booking/waitlist counts, skill tier, price, deposit, and booking bridge IDs.
- `equestrian_staff_members`: staff identity, roles, availability, and care assignment scope.
- `equestrian_rider_profiles`: family-account-scoped rider identity, emergency contact, medical disclosure, skill tiers, guardian details, and cash approval timestamp.
- `equestrian_horse_profiles`: horse profile, age, fitness, vaccination, suitability tiers, workload limits, and notes.
- `equestrian_slot_bookings`: checkout holds, confirmed bookings, payment provider/status, cash approval, cancellation, and refund cutoff state.
- `equestrian_slot_waitlist_entries`: waiting riders, offer windows, claim state, and quoted totals.
- `equestrian_horse_care_tasks`: feed, medication, vet, farrier, exercise, vaccination, grooming, and billable care tasks.
- `equestrian_horse_health_records`: vet, farrier, vaccination, medication, dental, bodywork, document, reminder, and billable service history.
- `equestrian_competition_results`: rider/horse class results, discipline, score, placing, and Tour Day linkage.
- `equestrian_facility_resources` and `equestrian_facility_bookings`: venue resource inventory, capacity, pricing, reservations, and conflict checks.
- `equestrian_waiver_signatures`: rider waiver version, signer details, signed timestamp, and consent snapshot.
- `equestrian_clinic_credits`: account-scoped clinic credits, lesson packs, membership cards, and expiry.
- `equestrian_commercial_products`: lesson packs, memberships, stable cards, gift cards, services, add-ons, pricing, and eligibility.
- `equestrian_billing_entries`: invoice/export-ready billable lines for care, products, services, and integrations such as accounting exports.
- `equestrian_communication_logs`: broadcast channel, audience, message, recipients, and sent timestamp.
- `equestrian_host_requests`: public demand and host-area request capture.

## Install Impact

- Adds package migrations for nineteen equestrian tables.
- Registers protected tables and morph aliases for package-owned models.
- Adds public routes only after the package is marked installed.
- Adds a throttled host-request endpoint and a signed coach timetable endpoint.
- Requires dependent packages to be present before a full consuming-site install can run.
- Extends Payments with PayPal gateway support through the provider-neutral gateway binding.

## Common Pitfalls

- Do not treat every manifest capability as a finished admin screen. This package currently supplies the domain foundation, public discovery, signed coach timetable, and tested Actions; full Filament CRUD, checkout handoff screens, portal vault, outbound messaging jobs, and automation UI still need product UI layers.
- Do not expose rider medical data, waiver snapshots, signed media URLs, package internals, authoring metadata, or admin URLs in public Blade.
- Generate signed coach timetable URLs through Laravel's signed URL helpers.
- Method-specific Stripe or PayPal fees must stay disabled until a stored legal acknowledgement is implemented by the consuming admin settings surface.
- Equi Dynamics cannot install this package cleanly until its local `equidynamics/theme` Composer constraint is aligned with the locked path package branch.

## Quick Start

1. Install dependencies and this package: `composer require capell-app/equestrian-clinics`.
2. Run the host app package install/migration workflow.
3. Mark Equestrian Clinics installed through Capell extension install.
4. Create venues and Tour Days.
5. Generate slots with `GenerateTourDaySlotsAction`.
6. Open `/equestrian-clinics` and test discovery/search.
7. Generate a signed coach timetable URL for a Tour Day and test mobile display.

## Troubleshooting

- If `/equestrian-clinics` is missing from `route:list`, confirm the package is installed in Capell, not only present in Composer.
- If the consuming app install fails during Composer update, check path repository branch aliases and locked local package constraints.
- If the public page shows no clinics, confirm the Tour Day is published, public, future-dated, and has generated slots.
- If distance sorting is absent, make sure venue latitude and longitude are populated and the request includes `latitude` and `longitude` query parameters.
- If a full slot still shows as requestable, confirm `booked_count` is greater than or equal to `capacity_max`.
- If a paid checkout appears stuck, run hold expiry and confirm bookings only from verified provider state or webhook processing.

## Next Steps

- [Package docs](docs/README.md)
- [Technical overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- Focused tests: `vendor/bin/pest packages/equestrian-clinics/tests --configuration=phpunit.xml`.
