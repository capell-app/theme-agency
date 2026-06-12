# Capell Bookings

Bookings adds appointment request workflows to Capell sites: services, staff, locations, availability, public request forms, confirmations, reminders, and calendar feeds.

## At A Glance

| Field             | Value                                                   |
| ----------------- | ------------------------------------------------------- |
| Composer package  | `capell-app/bookings`                                   |
| Namespace         | `Capell\Bookings`                                       |
| Product group     | Capell Operations, premium operations bundle            |
| Surfaces          | Admin, frontend, console                                |
| Provider          | `Capell\Bookings\Providers\BookingsServiceProvider`     |
| Requires          | `capell-app/admin`, `capell-app/core`                   |
| Supports          | Address, Events, Form Builder, Notifications, SEO Suite |
| Public routes     | `calendar.staff`, `request`, `request.store`            |
| Scheduled command | `capell:bookings:send-due-reminders` every five minutes |

## Why It Helps Your Capell Workflow

Owners can sell or coordinate appointment-led services without bolting on a separate booking system. Editors manage the service catalogue, staff availability, blackout dates, locations, and appointment request queues from Capell admin.

Visitors get public booking request forms and staff calendar feeds that expose only public-safe appointment data. Developers get Actions and DTOs for request creation, slot building, confirmation, cancellation, reminders, and calendar export without wiring business rules into controllers or views.

## What It Adds

- Filament resources for booking services, staff members, locations, availability windows, availability exceptions, and appointment requests.
- Public booking request controllers backed by `BuildPublicBookingRequestPropsAction`, `BuildAvailableBookingSlotsAction`, and `CreateAppointmentRequestAction`.
- Appointment workflow Actions for confirmation, cancellation, audit logging, notification queueing, and reminder scheduling.
- Staff calendar feed URLs and feed output through `CreateStaffCalendarFeedUrlAction` and `BuildStaffCalendarFeedAction`.
- Package-owned models and tables for services, staff, locations, availability, requests, and audit logs.

## Boundaries

Bookings owns appointment setup, request handling, appointment audit history, reminders, and calendar exports. Public routes must stay `no-store` and must not expose Filament resources, package internals, authoring metadata, permission names, or admin-only identifiers.

External notification delivery belongs to the notification/mail layer. Themes and consuming packages should render public request data from package Actions/DTOs rather than querying booking models in Blade.

## Runtime Surface

- Provider: `src/Providers/BookingsServiceProvider.php`
- Controllers: `src/Http/Controllers/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Models: `src/Models/`
- Command: `src/Console/SendDueAppointmentRemindersCommand.php`
- Manifest contributions: `src/Manifest/`
- Tests: `packages/bookings/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/bookings/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                                              | Likely cause                                                                                   | Check                                                                                                      | Fix                                                                                   |
| ---------------------------------------------------- | ---------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------- |
| Public request form shows no slots                   | Service, staff, availability window, lead time, or exception rules leave no available interval | Review booking service/staff resources and run the package tests around `BuildAvailableBookingSlotsAction` | Add active availability windows, reduce constraints, or remove conflicting exceptions |
| Confirmed appointments do not appear in a staff feed | Calendar feed token or appointment status is not valid for export                              | Inspect `booking_staff_members.calendar_feed_token` and the appointment request status                     | Regenerate the staff feed URL or confirm the appointment through the package Action   |
| Reminders do not send                                | Scheduler is not running the package command                                                   | In a host app, run `php artisan schedule:list` and check `capell:bookings:send-due-reminders`              | Enable the scheduler/queue worker and rerun the command in the host app               |
