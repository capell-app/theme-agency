# Equestrian Clinics

Equestrian Clinics is a premium Capell Operations package for tour-day, clinic, riding-school, and barn-adjacent workflows. It is deliberately more specific than generic Bookings: it understands riders, horses, skill tiers, waivers, venues, facility resources, clinic credits, host requests, and mobile day-of coaching.

## What This Plugin Adds

The package adds an equestrian operations layer around Capell's existing booking, event, payment, portal, address, and media capabilities.

| Feature family         | Current package coverage                                                                                                                          |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| Tour days and clinics  | Venue-linked Tour Days, operating hours, slot templates, auto-generation, slot archetypes, capacity counts, and minimum viable clinic thresholds. |
| Public discovery       | Search, venue filtering, postcode filtering, coordinate distance sorting, demand heatmap, and host/request form.                                  |
| Payments               | Quote rules for universal booking fees and guarded method-specific fees, plus PayPal support in Payments.                                         |
| Riders and horses      | Rider/horse profiles, skill tiers, cash approval timestamp, suitability checks, vaccination fields, and workload limits.                          |
| Facilities             | Resource inventory, capacity conflict checks, resource pricing, reservations, and host-safe reports.                                              |
| Waivers and compliance | Waiver signature records, version snapshots, guardian data, and sensitive-output boundaries.                                                      |
| Coach dashboard        | Signed mobile timetable with venue notes, minimum threshold status, slot capacity, waitlist count, and resources.                                 |

## Why It Matters

Equestrian businesses do not only schedule time. They manage rider ability, horse suitability, venue logistics, weather changes, safety paperwork, facility capacity, and payment risk. Keeping those rules in a Capell package lets the site stay owned by the Laravel application while the operation gains purpose-built equestrian workflows.

For a site owner, the value is fewer manual messages, clearer clinic demand, and a booking journey that feels native to the site. For a coach or riding-school operator, the value is a day sheet that answers operational questions quickly: where am I, who is booked, what resources are reserved, and is the clinic viable?

## Screens And Workflow

The public route is `/equestrian-clinics`. It renders a rider-facing discovery page with filters, upcoming Tour Days, slot capacity, waitlist state, venue notes, demand heatmap, and host request capture.

The coach route is `/equestrian-clinics/coach/tour-days/{tourDay}/timetable`. It is signed, no-indexed, and intended for mobile outdoor use. It displays the Tour Day window, venue details, minimum viable clinic status, slot list, waitlist counts, and reserved resources.

The package screenshot contract lives in `docs/screenshots.json`. Route screenshots require the package to be installed in a consuming app or package harness with fixture Tour Days.

## Technical Shape

The service provider registers config, views, translations, migrations, protected tables, morph aliases, a host-request rate limiter, and public routes after Capell marks the package installed.

Domain behavior is held in Actions:

- `GenerateTourDaySlotsAction`
- `QuoteTourDaySlotBookingAction`
- `ValidateRiderHorseEligibilityAction`
- `AllocateHorseToSlotAction`
- `ReserveFacilityResourceAction`
- `BuildFacilityReportAction`
- `RecordHostRequestAction`
- `BuildOpenSlotDemandHeatmapAction`
- `BuildClinicDiscoveryAction`
- `BuildCoachTimetableAction`

The package contributes model metadata and a health check through the manifest. The public views receive hydrated arrays from controllers and Actions; they should not query the database.

## Data Model

The migration creates these package-owned tables:

| Table                           | Purpose                                                                                                                                     |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `equestrian_venues`             | Venue profile, address, postcode, coordinates, Google Maps URL, facility/access/parking notes, and contact details.                         |
| `equestrian_tour_days`          | Clinic day connected to one venue, with status, time window, booking lock, refund cutoff, minimum paid attendees, and minimum revenue.      |
| `equestrian_tour_day_slots`     | Slot blocks with title, archetype, time window, capacity, booked/waitlist counts, skill tier, price, deposit, and booking bridge IDs.       |
| `equestrian_rider_profiles`     | Rider records scoped to a portal account, including emergency contact, medical disclosure, skill tiers, guardian fields, and cash approval. |
| `equestrian_horse_profiles`     | Horse records with fitness, vaccination, suitability tiers, workload limits, and notes.                                                     |
| `equestrian_facility_resources` | Venue-owned arenas, fields, stables, horseboxes, hookups, equipment, and priced resource capacity.                                          |
| `equestrian_facility_bookings`  | Resource reservations against slot time windows.                                                                                            |
| `equestrian_waiver_signatures`  | Versioned waiver signatures and consent snapshots.                                                                                          |
| `equestrian_clinic_credits`     | Account-scoped credits, packs, memberships, and expiry.                                                                                     |
| `equestrian_host_requests`      | Public request/host demand capture and heatmap source.                                                                                      |

## Install Impact

The package requires `capell-app/address`, `capell-app/bookings`, `capell-app/core`, `capell-app/customer-portal`, `capell-app/events`, `capell-app/media-library`, and `capell-app/payments`.

Install adds database tables and public routes, and it extends Payments with PayPal as a provider. It does not yet add full Filament CRUD resources or customer portal pages; those are the next implementation layer.

## Common Pitfalls

- Composer availability is not the same as Capell installation. Routes are registered only after the package is marked installed.
- The public discovery buttons are currently state labels, not checkout forms. Booking handoff still needs the Bookings/Payments UI layer.
- Waitlist counts render, but promotion, claim windows, and customer notifications are not yet implemented.
- The coach timetable deliberately avoids medical disclosure and waiver snapshot output.
- Method-specific payment fees are quoted only when legal acknowledgement is passed to the Action; a real admin setting must persist that acknowledgement before production use.

## Quick Start

1. Require and install package dependencies in the consuming Capell app.
2. Run migrations through the app's normal package install workflow.
3. Mark the package installed through Capell extension install.
4. Create a venue and Tour Day.
5. Generate slots using `GenerateTourDaySlotsAction`.
6. Create facility resources and reserve them with `ReserveFacilityResourceAction`.
7. Test `/equestrian-clinics` with venue, postcode, and coordinate filters.
8. Generate a signed timetable URL and test it at mobile width.

## Troubleshooting

- Missing routes: run `route:list` and confirm the package is installed, not merely composer-required.
- Empty discovery: use future published public Tour Days with at least one slot.
- No distance labels: add venue coordinates and query with `latitude` and `longitude`.
- Facility conflict errors: inspect overlapping bookings for the same resource and time window.
- Horse allocation errors: inspect workload already assigned through slot metadata for the same date.

## Next Steps

The next product layer should add:

- Filament resources for venues, Tour Days, slots, rider profiles, horse profiles, waivers, credits, resources, host requests, and reports.
- Public booking handoff into Bookings and Payments, including checkout holds, refunds, cash approval, and waitlist claim windows.
- Customer portal pages for riders, horses, waivers, credits, bookings, waitlist offers, coaching vault, and rebooking.
- Messaging jobs for reminders, cancellations, weather changes, waitlist offers, and broadcast history.
- Screenshot fixture generation in Equi Dynamics after its package install blocker is resolved.
