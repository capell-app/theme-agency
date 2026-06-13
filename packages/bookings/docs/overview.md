# Bookings

<!-- prettier-ignore-start -->

## What This Plugin Adds

Bookings is an **Available**, **Schema-owning** Capell plugin in the **Capell Operations** product group. It ships as `capell-app/bookings` and extends these surfaces: admin, console, frontend.

Bookings adds services, staff, locations, availability, appointment requests, confirmations, reminders, and calendar feeds.

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

**For teams:** Bookings adds appointment request workflows, availability, confirmations, reminders, and calendar feeds for healthcare, services, education, consulting, nonprofit, and portfolio sites.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Public booking request form (frontend, required).
- Appointment request admin queue (admin, required).

## Technical Shape

- Service providers: `Capell\Bookings\Providers\BookingsServiceProvider`.
- Config files: `packages/bookings/config/capell-bookings.php`.
- Migrations: `packages/bookings/database/migrations/2026_05_31_130000_01_create_booking_services_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_02_create_booking_staff_members_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_03_create_booking_locations_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_04_create_booking_availability_windows_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_05_create_appointment_requests_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_06_create_booking_availability_exceptions_table.php`, `packages/bookings/database/migrations/2026_05_31_130000_07_create_appointment_audit_logs_table.php`.
- Models: `AppointmentAuditLog`, `AppointmentRequest`, `BookingAvailabilityException`, `BookingAvailabilityWindow`, `BookingLocation`, `BookingService`, `BookingStaffMember`.
- Filament classes: `AppointmentRequestResource`, `EditAppointmentRequest`, `ListAppointmentRequests`, `AppointmentAuditLogsRelationManager`, `BookingAvailabilityExceptionResource`, `CreateBookingAvailabilityException`, `EditBookingAvailabilityException`, `ListBookingAvailabilityExceptions`, `BookingAvailabilityWindowResource`, `CreateBookingAvailabilityWindow`, `EditBookingAvailabilityWindow`, `ListBookingAvailabilityWindows`, `and 12 more`.
- Route files: `packages/bookings/routes/web.php`.
- Actions: `BuildAvailableBookingSlotsAction`, `BuildPublicBookingRequestOptionsAction`, `BuildPublicBookingRequestPropsAction`, `BuildStaffCalendarFeedAction`, `CancelAppointmentRequestAction`, `ConfirmAppointmentRequestAction`, `CreateAppointmentRequestAction`, `CreateAvailabilityExceptionAction`, `CreateStaffCalendarFeedUrlAction`, `QueueAppointmentNotificationAction`, `QueueAppointmentReminderAction`, `RecordAppointmentAuditLogAction`.
- Data objects: `AppointmentRequestData`, `AvailabilityExceptionData`, `AvailabilityWindowData`.
- Console command classes: `SendDueAppointmentRemindersCommand`.
- Manifest contributions: `admin-resource: Capell\Bookings\Manifest\AppointmentRequestResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingAvailabilityExceptionResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingAvailabilityWindowResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingLocationResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingServiceResourceContribution`, `admin-resource: Capell\Bookings\Manifest\BookingStaffMemberResourceContribution`, `model: Capell\Bookings\Manifest\BookingsModelsContribution`, `route: Capell\Bookings\Manifest\BookingsFrontendRoutesContribution`, `scheduled-job: Capell\Bookings\Manifest\BookingsReminderScheduleContribution`.
- Health checks: `Capell\Bookings\Health\BookingsHealthCheck`.
- Blade views: `packages/bookings/resources/views/request.blade.php`.
- Cache tags: `bookings`.

## Data Model

- Required tables: `booking_services`, `booking_staff_members`, `booking_locations`, `booking_availability_windows`, `booking_availability_exceptions`, `appointment_requests`, `appointment_audit_logs`.
- Models: `AppointmentAuditLog`, `AppointmentRequest`, `BookingAvailabilityException`, `BookingAvailabilityWindow`, `BookingLocation`, `BookingService`, `BookingStaffMember`.
- Migration files: `2026_05_31_130000_01_create_booking_services_table.php`, `2026_05_31_130000_02_create_booking_staff_members_table.php`, `2026_05_31_130000_03_create_booking_locations_table.php`, `2026_05_31_130000_04_create_booking_availability_windows_table.php`, `2026_05_31_130000_05_create_appointment_requests_table.php`, `2026_05_31_130000_06_create_booking_availability_exceptions_table.php`, `2026_05_31_130000_07_create_appointment_audit_logs_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:BookingService`, `Create:BookingService`, `Update:BookingService`, `Delete:BookingService`, `View:BookingStaffMember`, `Create:BookingStaffMember`, `Update:BookingStaffMember`, `Delete:BookingStaffMember`, `View:BookingLocation`, `Create:BookingLocation`, `Update:BookingLocation`, `Delete:BookingLocation`, `View:BookingAvailabilityWindow`, `Create:BookingAvailabilityWindow`, `Update:BookingAvailabilityWindow`, `Delete:BookingAvailabilityWindow`, `View:BookingAvailabilityException`, `Create:BookingAvailabilityException`, `Update:BookingAvailabilityException`, `Delete:BookingAvailabilityException`, `View:AppointmentRequest`, `Update:AppointmentRequest`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `bookings`.
- Commands: console command classes detected: `SendDueAppointmentRemindersCommand`.

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

1. Install the package: `composer require capell-app/bookings`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Bookings appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Address](../../address/README.md), [Events](../../events/README.md), [Form Builder](../../form-builder/README.md), [Seo Suite](../../seo-suite/README.md).
- Focused tests: `vendor/bin/pest packages/bookings/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
