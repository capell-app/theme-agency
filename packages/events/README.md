# Events

<!-- prettier-ignore-start -->

## What This Plugin Adds

Events is an **Available**, **Schema-owning** Capell plugin in the **Capell Content** product group. It ships as `capell-app/events` and extends these surfaces: admin, frontend, console.

Events turns Capell into a full event platform: editors create one event with an RRULE recurrence and the package materializes every occurrence, each with its own page, venue, schedule, and capacity. Visitors RSVP with automatic waitlisting and confirmation/reminder emails, while a public `.ics` feed lets them subscribe in Apple/Google/Outlook calendars. Every occurrence emits schema.org `Event` JSON-LD for rich results, and an admin calendar plus dashboard widget keep the programme visible. Built on `php-rrule` and `spatie/icalendar-generator`, with first-class hooks into Publishing Studio, Site Discovery, and the Customer Portal.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: content-product
- Composer package: `capell-app/events`
- Namespace: `Capell\Events`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Publish recurring events with venues, capacity-managed RSVPs, subscribable iCal feeds, and Google-ready Event schema - all inside your Capell admin.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Events index (admin, required).
- Create event form (admin, required).
- Edit event form (admin, required).
- Event venues management (admin, required).
- Event occurrences management (admin, required).
- Event registrations management (admin, required).
- Admin event calendar page (admin, required).
- Event calendar widget (admin, required).
- Frontend events listing page (frontend, required).
- Frontend events calendar page (frontend, required).
- iCalendar feed route (frontend, required).

## Technical Shape

- Service providers: `Capell\Events\Providers\EventsServiceProvider`.
- Config files: `packages/events/config/capell-events.php`.
- Migrations: `packages/events/database/migrations/2026_05_10_190848_01_create_event_venues_table.php`, `packages/events/database/migrations/2026_05_10_190848_02_create_events_table.php`, `packages/events/database/migrations/2026_05_10_190848_03_create_event_occurrences_table.php`, `packages/events/database/migrations/2026_05_10_190848_04_create_event_registrations_table.php`, `packages/events/database/migrations/2026_05_10_190848_05_create_event_notification_logs_table.php`, `packages/events/database/migrations/2026_05_31_070000_06_add_unique_event_notification_logs_identity_index.php`, `packages/events/database/migrations/2026_06_07_000000_07_add_notification_keys_to_event_notification_logs_table.php`.
- Models: `Event`, `EventNotificationLog`, `EventOccurrence`, `EventRegistration`, `EventVenue`.
- Filament classes: `EventCalendarPage`, `EventResource`, `CreateEvent`, `EditEvent`, `ListEvents`, `EventForm`, `EventsTable`, `EventOccurrenceResource`, `ManageEventOccurrences`, `EventRegistrationResource`, `ManageEventRegistrations`, `EventVenueResource`, `and 2 more`.
- Livewire components: `EventCalendar`, `EventsCalendarPage`, `EventsListingPage`.
- Route files: `packages/events/routes/web.php`.
- Policies: `AbstractEventResourcePolicy`, `EventOccurrencePolicy`, `EventPolicy`, `EventRegistrationPolicy`, `EventVenuePolicy`.
- Events: `EventRegistrationCancelled`, `EventRegistrationCreated`.
- Listeners: `PromoteWaitlistAfterRegistrationCancelled`.
- Actions: `BuildCalendarFeedAction`, `BuildEventOccurrenceUrlAction`, `BuildEventOccurrenceViewDataAction`, `BuildEventSchemaAction`, `CancelOccurrenceAction`, `EnsureEventPublishingDefaultsAction`, `EnsureEventPublishingSurfaceAction`, `ExpandEventRecurrenceAction`, `InstallEventsPackageAction`, `InstallPackageAction`, `ProcessDueEventNotificationLogsAction`, `PromoteWaitlistAction`, `and 9 more`.
- Data objects: `EventOccurrenceData`, `EventOccurrenceViewData`, `EventRegistrationData`.
- Command signatures: `capell:events-doctor`, `capell:events-install`.
- Console command classes: `EventsDoctorCommand`, `InstallCommand`.
- Manifest contributions: `admin-page: Capell\Events\Manifest\EventsAdminPageContribution`, `admin-resource: Capell\Events\Manifest\EventsAdminResourcesContribution`, `console-command: Capell\Events\Manifest\EventsConsoleCommandsContribution`, `dashboard-widget: Capell\Events\Manifest\EventsDashboardWidgetsContribution`, `frontend-component: Capell\Events\Manifest\EventsFrontendComponentsContribution`, `health-check: Capell\Events\Health\EventsHealthCheck`, `migration: Capell\Events\Manifest\EventsMigrationsContribution`, `model: Capell\Events\Manifest\EventsModelsContribution`, `page-type: Capell\Events\Manifest\EventsPageTypesContribution`, `page-variation: Capell\Events\Manifest\EventsPageTypesContribution`, `render-hook: Capell\Events\Manifest\EventsRenderHookContribution`, `route: Capell\Events\Manifest\EventsRoutesContribution`, `scheduled-job: Capell\Events\Manifest\EventsScheduleContribution`.
- Health checks: `Capell\Events\Health\EventsHealthCheck`.
- Blade views: `packages/events/resources/views/filament/widgets/event-calendar.blade.php`, `packages/events/resources/views/livewire/event-calendar.blade.php`, `packages/events/resources/views/livewire/page/events-calendar.blade.php`, `packages/events/resources/views/livewire/page/events-listing.blade.php`.
- Cache tags: `events`.

## Data Model

- Required tables: `event_venues`, `events`, `event_occurrences`, `event_registrations`, `event_notification_logs`.
- Models: `Event`, `EventNotificationLog`, `EventOccurrence`, `EventRegistration`, `EventVenue`.
- Migration files: `2026_05_10_190848_01_create_event_venues_table.php`, `2026_05_10_190848_02_create_events_table.php`, `2026_05_10_190848_03_create_event_occurrences_table.php`, `2026_05_10_190848_04_create_event_registrations_table.php`, `2026_05_10_190848_05_create_event_notification_logs_table.php`, `2026_05_31_070000_06_add_unique_event_notification_logs_identity_index.php`, `2026_06_07_000000_07_add_notification_keys_to_event_notification_logs_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `ViewAny:Event`, `View:Event`, `Create:Event`, `Update:Event`, `Delete:Event`, `DeleteAny:Event`, `Restore:Event`, `RestoreAny:Event`, `ForceDelete:Event`, `ForceDeleteAny:Event`, `Replicate:Event`, `Reorder:Event`, `ViewAny:EventVenue`, `View:EventVenue`, `Create:EventVenue`, `Update:EventVenue`, `Delete:EventVenue`, `DeleteAny:EventVenue`, `Restore:EventVenue`, `RestoreAny:EventVenue`, `ForceDelete:EventVenue`, `ForceDeleteAny:EventVenue`, `Replicate:EventVenue`, `Reorder:EventVenue`, `ViewAny:EventOccurrence`, `View:EventOccurrence`, `Create:EventOccurrence`, `Update:EventOccurrence`, `Delete:EventOccurrence`, `DeleteAny:EventOccurrence`, `Restore:EventOccurrence`, `RestoreAny:EventOccurrence`, `ForceDelete:EventOccurrence`, `ForceDeleteAny:EventOccurrence`, `Replicate:EventOccurrence`, `Reorder:EventOccurrence`, `ViewAny:EventRegistration`, `View:EventRegistration`, `Create:EventRegistration`, `Update:EventRegistration`, `Delete:EventRegistration`, `DeleteAny:EventRegistration`, `Restore:EventRegistration`, `RestoreAny:EventRegistration`, `ForceDelete:EventRegistration`, `ForceDeleteAny:EventRegistration`, `Replicate:EventRegistration`, `Reorder:EventRegistration`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `events`.
- Commands: `capell:events-doctor`, `capell:events-install`.

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
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/events`.
2. Run the required setup: `php artisan capell:events-install`.
3. Open the related Capell admin surface and verify Events appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Navigation](../navigation/README.md), [Publishing Studio](../publishing-studio/README.md), [Address](../address/README.md), [Customer Portal](../customer-portal/README.md), [Form Builder](../form-builder/README.md), [Seo Suite](../seo-suite/README.md), [Site Discovery](../site-discovery/README.md), [Tags](../tags/README.md).
- Focused tests: `vendor/bin/pest packages/events/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
