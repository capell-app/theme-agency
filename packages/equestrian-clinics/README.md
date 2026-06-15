# Equestrian Clinics

<!-- prettier-ignore-start -->

## What This Plugin Adds

Equestrian Clinics is an **Available**, **Schema-owning** Capell plugin in the **Capell Operations** product group. It ships as `capell-app/equestrian-clinics` and extends these surfaces: admin, frontend, console.

Equestrian Clinics turns Capell into a full equestrian operations platform for travelling coaches, riding schools, clinics, venues, riders, horses, waivers, payments, credits, resources, and mobile day-of delivery.

After install, the package contributes admin-facing extension points and may affect public output or routes. Docs gap: no concrete Filament resource or page was detected.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/equestrian-clinics`
- Namespace: `Capell\EquestrianClinics`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Tour days, riders, horses, waivers, payments, facilities, credits, waitlists, and coach-ready operations for equestrian businesses inside Capell.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Equestrian Clinics public discovery (frontend, optional).
- Equestrian Clinics coach timetable (frontend, optional).
- Equestrian Clinics marketplace card (marketplace, required).

## Technical Shape

- Service providers: `Capell\EquestrianClinics\Providers\EquestrianClinicsServiceProvider`.
- Config files: `packages/equestrian-clinics/config/capell-equestrian-clinics.php`.
- Migrations: `packages/equestrian-clinics/database/migrations/2026_06_13_000001_create_equestrian_clinics_tables.php`.
- Models: `EquestrianBillingEntry`, `EquestrianClinicCredit`, `EquestrianCommercialProduct`, `EquestrianCommunicationLog`, `EquestrianCompetitionResult`, `EquestrianFacilityBooking`, `EquestrianFacilityResource`, `EquestrianHorseCareTask`, `EquestrianHorseHealthRecord`, `EquestrianHorseProfile`, `EquestrianHostRequest`, `EquestrianRiderProfile`, `EquestrianSlotBooking`, `EquestrianSlotWaitlistEntry`, `EquestrianStaffMember`, `EquestrianTourDay`, `EquestrianTourDaySlot`, `EquestrianVenue`, `EquestrianWaiverSignature`.
- Route files: `packages/equestrian-clinics/routes/web.php`.
- Actions: `AllocateHorseToSlotAction`, `BuildClinicDiscoveryAction`, `BuildCoachTimetableAction`, `BuildFacilityReportAction`, `BuildOpenSlotDemandHeatmapAction`, `BuildStaffCareWorklistAction`, `CancelSlotBookingAction`, `ClaimWaitlistOfferAction`, `CompleteHorseCareTaskAction`, `ConfirmSlotBookingPaymentAction`, `CreateBillingEntryAction`, `CreateCommercialProductAction`, `and 15 more`.
- Data objects: `EquestrianBookingQuoteData`, `EquestrianFacilityReportData`, `EquestrianSlotTemplateData`.
- Manifest contributions: `model: Capell\EquestrianClinics\Manifest\EquestrianClinicsModelsContribution`.
- Health checks: `Capell\EquestrianClinics\Health\EquestrianClinicsHealthCheck`.
- Blade views: `packages/equestrian-clinics/resources/views/coach-timetable.blade.php`, `packages/equestrian-clinics/resources/views/discovery.blade.php`.
- Cache tags: `equestrian-clinics`.

## Data Model

- Required tables: `equestrian_venues`, `equestrian_tour_days`, `equestrian_tour_day_slots`, `equestrian_staff_members`, `equestrian_rider_profiles`, `equestrian_horse_profiles`, `equestrian_slot_bookings`, `equestrian_slot_waitlist_entries`, `equestrian_horse_care_tasks`, `equestrian_horse_health_records`, `equestrian_competition_results`, `equestrian_facility_resources`, `equestrian_facility_bookings`, `equestrian_waiver_signatures`, `equestrian_clinic_credits`, `equestrian_commercial_products`, `equestrian_billing_entries`, `equestrian_communication_logs`, `equestrian_host_requests`.
- Models: `EquestrianBillingEntry`, `EquestrianClinicCredit`, `EquestrianCommercialProduct`, `EquestrianCommunicationLog`, `EquestrianCompetitionResult`, `EquestrianFacilityBooking`, `EquestrianFacilityResource`, `EquestrianHorseCareTask`, `EquestrianHorseHealthRecord`, `EquestrianHorseProfile`, `EquestrianHostRequest`, `EquestrianRiderProfile`, `EquestrianSlotBooking`, `EquestrianSlotWaitlistEntry`, `EquestrianStaffMember`, `EquestrianTourDay`, `EquestrianTourDaySlot`, `EquestrianVenue`, `EquestrianWaiverSignature`.
- Migration files: `2026_06_13_000001_create_equestrian_clinics_tables.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: admin-facing extension points are declared, but no concrete Filament class was detected.
- Permissions: `ViewAny:EquestrianTourDay`, `View:EquestrianTourDay`, `Create:EquestrianTourDay`, `Update:EquestrianTourDay`, `Delete:EquestrianTourDay`, `ViewAny:EquestrianRiderProfile`, `ViewAny:EquestrianHorseProfile`, `ViewAny:EquestrianVenue`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `equestrian-clinics`.
- Commands: none declared.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/equestrian-clinics`.
2. Run the required setup: `php artisan migrate`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Address](../address/README.md), [Bookings](../bookings/README.md), [Customer Portal](../customer-portal/README.md), [Events](../events/README.md), [Media Library](../media-library/README.md), [Payments](../payments/README.md), [Ai Orchestrator](../ai-orchestrator/README.md), [Email Studio](../email-studio/README.md), [Social Feeds](../social-feeds/README.md).
- Focused tests: `vendor/bin/pest packages/equestrian-clinics/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
