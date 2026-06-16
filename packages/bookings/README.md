# Bookings

<!-- prettier-ignore-start -->

## What This Plugin Adds

Bookings is an **Available**, **Schema-owning** Capell plugin in the **Capell Operations** product group. It ships as `capell-app/bookings` and extends these surfaces: admin, console, frontend.

Bookings turns public appointment requests into a managed Capell operations workflow with availability, reminders, reviews, waitlists, travel planning, reporting, and optional advanced automation.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/bookings`
- Namespace: `Capell\Bookings`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Bookings starts with a branded request form and admin queue, then adds the operational depth teams need: availability, reminders, reviews, waitlists, travel planning, reporting, and secure customer links.

## Start Simple, Add Depth Later

Most teams should launch with the default request form, service setup, staff availability, and admin queue before enabling reminders, reviews, waitlists, travel planning, payments, or advanced automation. Use `docs/adoption-guide.md` as the rollout path for owners, operators, agencies, and developers.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Public booking request form (frontend, required).
- Appointment request admin queue (admin, required).
- Equi Dynamics public booking form on desktop (frontend, required).
- Equi Dynamics public booking form on mobile (frontend, required).
- Equi Dynamics successful booking submission (frontend, required).

## Technical Shape

- Service providers: `Capell\Bookings\Providers\BookingsServiceProvider`.
- Config files: `packages/bookings/config/capell-bookings.php`.
- Migrations: `packages/bookings/database/migrations/2026_05_31_130000_01_create_booking_services_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_02_create_booking_staff_members_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_03_create_booking_locations_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_04_create_booking_availability_windows_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_05_create_appointment_requests_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_06_create_booking_availability_exceptions_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_07_create_appointment_audit_logs_table.php`, `packages/bookings/database/migrations/2026_06_13_000001_create_lesson_series_table.php`, `packages/bookings/database/migrations/2026_06_13_000002_add_adaptive_foundations_to_appointment_requests_table.php`, `packages/bookings/database/migrations/2026_06_13_000003_add_travel_and_payment_fields_to_bookings_tables.php`, `packages/bookings/database/migrations/2026_06_13_000004_create_booking_lesson_notes_table.php`, `packages/bookings/database/migrations/2026_06_13_000005_create_booking_messaging_tables.php`, `packages/bookings/database/migrations/2026_06_13_000006_create_booking_travel_tables.php`, `packages/bookings/database/migrations/2026_06_13_000007_create_booking_change_proposals_table.php`, `packages/bookings/database/migrations/2026_06_13_000008_create_booking_group_sessions_table.php`, `packages/bookings/database/migrations/2026_06_13_000009_add_group_session_fields_to_appointment_requests_table.php`, `packages/bookings/database/migrations/2026_06_13_000010_create_booking_review_requests_table.php`, `packages/bookings/database/migrations/2026_06_13_000011_create_booking_owner_prompts_table.php`, `packages/bookings/database/migrations/2026_06_13_000012_create_booking_webhook_events_table.php`, `packages/bookings/database/migrations/2026_06_13_000013_create_booking_waitlist_entries_table.php`, `packages/bookings/database/migrations/2026_06_13_000014_create_booking_lesson_skill_assessments_table.php`, `packages/bookings/database/migrations/2026_06_13_000015_create_booking_lesson_bundles_table.php`, `packages/bookings/database/migrations/2026_06_13_000016_create_booking_review_participants_table.php`, `packages/bookings/database/migrations/2026_06_13_000017_add_token_fields_to_booking_review_requests_table.php`.
- Settings migrations: `packages/bookings/database/settings/2026_06_13_000001_create_bookings_settings.php`.
- Settings classes: `BookingsSettings`.
- Models: `AppointmentAuditLog`, `AppointmentRequest`, `BookingAvailabilityException`, `BookingAvailabilityWindow`, `BookingChangeProposal`, `BookingChangeProposalParty`, `BookingGroupSession`, `BookingLessonBundle`, `BookingLessonSkillAssessment`, `BookingLocation`, `BookingMessageLog`, `BookingOwnerPrompt`, `BookingReviewParticipant`, `BookingReviewRequest`, `BookingService`, `BookingStaffMember`, `BookingTravelAdjustment`, `BookingTravelObservation`, `BookingWaitlistEntry`, `BookingWebhookEvent`, `BookingWorkZone`, `LessonNote`, `LessonSeries`, `MessagingConsent`.
- Filament classes: `AppointmentRequestResource`, `EditAppointmentRequest`, `ListAppointmentRequests`, `AppointmentAuditLogsRelationManager`, `LessonNotesRelationManager`, `BookingAvailabilityExceptionResource`, `CreateBookingAvailabilityException`, `EditBookingAvailabilityException`, `ListBookingAvailabilityExceptions`, `BookingAvailabilityWindowResource`, `CreateBookingAvailabilityWindow`, `EditBookingAvailabilityWindow`, `and 45 more`.
- Route files: `packages/bookings/routes/web.php`.
- Actions: `AcknowledgeFuelAllowanceAction`, `AddReviewParticipantAction`, `ApplyBookingChangeAction`, `ApplyLessonBundleCreditAction`, `AssignGroupSlotTimesAction`, `AttachLessonPhotoAction`, `BuildAvailableBookingSlotsAction`, `BuildDayPlanAction`, `BuildInstructorFuelReportAction`, `BuildOwnerDigestAction`, `BuildPortalLessonRowsAction`, `BuildPublicBookingRequestOptionsAction`, `and 74 more`.
- Data objects: `AppointmentRequestData`, `AvailabilityExceptionData`, `AvailabilityWindowData`, `BookingMessageData`, `BookingMessageResultData`, `DayPlanData`, `DayPlanStopData`, `PortalLessonRowData`, `TravelEstimateData`.
- Console command classes: `ExpireBookingWorkflowStateCommand`, `PruneBookingRetentionDataCommand`, `ScheduleBookingReviewRequestsCommand`, `SendDueAppointmentRemindersCommand`.
- Manifest contributions: `admin-resource: Capell\Bookings\Manifest\AppointmentRequestResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingAvailabilityExceptionResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingAvailabilityWindowResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingChangeProposalResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingDayPlannerResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingGroupSessionResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingLocationResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingMessageLogResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingOwnerPromptResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingReviewRequestResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingServiceResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingStaffMemberResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingTravelObservationResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingWaitlistEntryResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingWorkZoneResourceContribution`, `admin-resource: Capell\Bookings\Manifest\LessonSeriesResourceContribution`, `model: Capell\Bookings\Manifest\BookingsModelsContribution`, `route: Capell\Bookings\Manifest\BookingsFrontendRoutesContribution`, `scheduled-job: Capell\Bookings\Manifest\BookingsReminderScheduleContribution`.
- Health checks: `Capell\Bookings\Health\BookingsHealthCheck`.
- Blade views: `packages/bookings/resources/views/portal/consent.blade.php`, `packages/bookings/resources/views/portal/lessons.blade.php`, `packages/bookings/resources/views/portal/proposal.blade.php`, `packages/bookings/resources/views/portal/review-participant.blade.php`, `packages/bookings/resources/views/portal/review.blade.php`, `packages/bookings/resources/views/request.blade.php`.
- Cache tags: `bookings`.

## Data Model

- Required tables: `booking_services`, `booking_staff_members`, `booking_locations`, `booking_availability_windows`, `booking_availability_exceptions`, `lesson_series`, `appointment_requests`, `appointment_audit_logs`, `booking_lesson_notes`, `booking_messaging_consents`, `booking_message_logs`, `booking_travel_observations`, `booking_travel_adjustments`, `booking_work_zones`, `booking_change_proposals`, `booking_change_proposal_parties`, `booking_group_sessions`, `booking_review_requests`, `booking_review_participants`, `booking_owner_prompts`, `booking_webhook_events`, `booking_waitlist_entries`, `booking_lesson_skill_assessments`, `booking_lesson_bundles`.
- Models: `AppointmentAuditLog`, `AppointmentRequest`, `BookingAvailabilityException`, `BookingAvailabilityWindow`, `BookingChangeProposal`, `BookingChangeProposalParty`, `BookingGroupSession`, `BookingLessonBundle`, `BookingLessonSkillAssessment`, `BookingLocation`, `BookingMessageLog`, `BookingOwnerPrompt`, `BookingReviewParticipant`, `BookingReviewRequest`, `BookingService`, `BookingStaffMember`, `BookingTravelAdjustment`, `BookingTravelObservation`, `BookingWaitlistEntry`, `BookingWebhookEvent`, `BookingWorkZone`, `LessonNote`, `LessonSeries`, `MessagingConsent`.
- Migration files: `2026_05_31_130000_01_create_booking_services_table.php`, `2026_05_31_130000_02_create_booking_staff_members_table.php`, `2026_05_31_130000_03_create_booking_locations_table.php`, `2026_05_31_130000_04_create_booking_availability_windows_table.php`, `2026_05_31_130000_05_create_appointment_requests_table.php`, `2026_05_31_130000_06_create_booking_availability_exceptions_table.php`, `2026_05_31_130000_07_create_appointment_audit_logs_table.php`, `2026_06_13_000001_create_lesson_series_table.php`, `2026_06_13_000002_add_adaptive_foundations_to_appointment_requests_table.php`, `2026_06_13_000003_add_travel_and_payment_fields_to_bookings_tables.php`, `2026_06_13_000004_create_booking_lesson_notes_table.php`, `2026_06_13_000005_create_booking_messaging_tables.php`, `2026_06_13_000006_create_booking_travel_tables.php`, `2026_06_13_000007_create_booking_change_proposals_table.php`, `2026_06_13_000008_create_booking_group_sessions_table.php`, `2026_06_13_000009_add_group_session_fields_to_appointment_requests_table.php`, `2026_06_13_000010_create_booking_review_requests_table.php`, `2026_06_13_000011_create_booking_owner_prompts_table.php`, `2026_06_13_000012_create_booking_webhook_events_table.php`, `2026_06_13_000013_create_booking_waitlist_entries_table.php`, `2026_06_13_000014_create_booking_lesson_skill_assessments_table.php`, `2026_06_13_000015_create_booking_lesson_bundles_table.php`, `2026_06_13_000016_create_booking_review_participants_table.php`, `2026_06_13_000017_add_token_fields_to_booking_review_requests_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:BookingService`, `Create:BookingService`, `Update:BookingService`, `Delete:BookingService`, `View:BookingStaffMember`, `Create:BookingStaffMember`, `Update:BookingStaffMember`, `Delete:BookingStaffMember`, `View:BookingLocation`, `Create:BookingLocation`, `Update:BookingLocation`, `Delete:BookingLocation`, `View:BookingAvailabilityWindow`, `Create:BookingAvailabilityWindow`, `Update:BookingAvailabilityWindow`, `Delete:BookingAvailabilityWindow`, `View:BookingAvailabilityException`, `Create:BookingAvailabilityException`, `Update:BookingAvailabilityException`, `Delete:BookingAvailabilityException`, `View:LessonSeries`, `Create:LessonSeries`, `Update:LessonSeries`, `Delete:LessonSeries`, `View:AppointmentRequest`, `Update:AppointmentRequest`, `View:BookingDayPlanner`, `View:BookingGroupSession`, `Create:BookingGroupSession`, `Update:BookingGroupSession`, `Delete:BookingGroupSession`, `View:BookingMessageLog`, `View:BookingReviewRequest`, `View:BookingTravelObservation`, `View:BookingWorkZone`, `Create:BookingWorkZone`, `Update:BookingWorkZone`, `Delete:BookingWorkZone`, `View:BookingOwnerPrompt`, `Update:BookingOwnerPrompt`, `View:BookingChangeProposal`, `Update:BookingChangeProposal`, `View:BookingWaitlistEntry`, `Create:BookingWaitlistEntry`, `Update:BookingWaitlistEntry`, `Delete:BookingWaitlistEntry`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: `Capell\Bookings\Settings\BookingsSettings`.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `bookings`.
- Commands: console command classes detected: `InstallBookingsDemoCommand`, `ExpireBookingWorkflowStateCommand`, `PruneBookingRetentionDataCommand`, `ScheduleBookingReviewRequestsCommand`, `SendDueAppointmentRemindersCommand`.

## Demo Fixtures

Run `capell:bookings-demo` from the host app to install an idempotent demo consultation service, staff member, location, weekly availability window, and appointment request. The fixture is intentionally small so operators can verify the public request form and admin queue without importing a full business calendar.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
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

1. Install the package: `composer require capell-app/bookings`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Bookings appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Customer Portal](../customer-portal/README.md), [Address](../address/README.md), [Ai Orchestrator](../ai-orchestrator/README.md), [Events](../events/README.md), [Form Builder](../form-builder/README.md), [Media Library](../media-library/README.md), [Payments](../payments/README.md), [Seo Suite](../seo-suite/README.md).
- Focused tests: `vendor/bin/pest packages/bookings/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
