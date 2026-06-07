# Events — Improvement & Growth Plan

> Package: capell-app/events · Kind: plugin · Tier: premium · Product group: Capell Content · Bundle: content-product · Status: Draft

## 1. Snapshot

Events adds event records, reusable venues, recurring-occurrence expansion (`rlanvin/php-rrule`), native RSVP/registration with capacity + waitlist, public `.ics` calendar feeds (`spatie/icalendar-generator`), schema.org `Event` JSON-LD render hooks, an admin calendar page/widget, and frontend Livewire listing + calendar pages. Surfaces: `admin`, `frontend`, `console`. Core Actions: `ExpandEventRecurrenceAction` / `SyncEventOccurrencesAction` (occurrence materialization), `RegisterForEventOccurrenceAction` (locked RSVP), `BuildCalendarFeedAction`, `BuildEventSchemaAction`, `ProcessDueEventNotificationLogsAction` (scheduled reminders). Tables: `event_venues`, `events`, `event_occurrences`, `event_registrations`, `event_notification_logs`. Requires `admin`, `frontend`, `navigation`, `publishing-studio`; supports `address`, `form-builder`, `seo-suite`, `tags`, and integrates with `customer-portal` (registration self-service feed) and `site-discovery` (public URL contributor).

Current marketplace summary (verbatim): _"Publish recurring events with venues, capacity-managed RSVPs, subscribable iCal feeds, and Google-ready Event schema — all inside your Capell admin."_ Manifest declares the extension card plus **10** committed runner-backed PNG captures covering event CRUD, venues, occurrences, registrations, admin calendar/widget, and public listing/calendar. The `.ics` feed capture remains committed runner evidence for the protocol route, but it is no longer promoted as buyer-facing marketplace media.

## Completed Improvement Slices

- **2026-06-03:** Replaced the stubbed `EventsHealthCheck` with real diagnostics and refreshed marketplace copy.
- **2026-06-04:** Wired confirm/cancel row actions into `EventRegistrationResource`, so staff can change registration status from the shipped admin surface and cancellations trigger the existing waitlist-promotion workflow. Declared the event resource permissions in `capell.json`.
- **2026-06-04:** Queued registration notifications after commit, added a package doctor command, wired cancellation-driven waitlist promotion through an event listener, and scheduled stale waitlist reconcile.
- **2026-06-06:** Captured and committed all 11 Events screenshot-runner targets, promoted the 10 styled admin/frontend captures into `capell.json` marketplace media, and retained the `.ics` feed output as runner evidence in `docs/screenshots.json`.

## 2. Improvements (existing functionality)

1. **Move RSVP confirmation mail out of the DB transaction** — shipped: `RegisterForEventOccurrenceAction` now defers registration notification scheduling until after commit, so confirmation/reminder log creation and notification dispatch do not run while the occurrence row is locked. — `src/Actions/RegisterForEventOccurrenceAction.php`, `src/Actions/ScheduleEventNotificationsAction.php` — M
2. **Make `EventRegistrationNotification` implement `ShouldQueue`** — shipped: `EventRegistrationNotification` implements `ShouldQueueAfterCommit`, so confirmation, reminder, and waitlist-promotion notifications queue after commit. — `src/Notifications/EventRegistrationNotification.php` — S
3. **Done/Shipped: Bound recurrence generation explicitly** — recurrence materialization now reads explicit `capell-events.recurrence.sync_past_days` and `sync_horizon_days` settings, preserving the existing 31-day/365-day default window while making the cap visible/configurable. DST-crossing weekly recurrence coverage asserts local wall-clock time stability across the Europe/London spring transition. — `config/capell-events.php`, `src/Actions/SyncEventOccurrencesAction.php`, `tests/Unit/Actions/ExpandEventRecurrenceActionTest.php` — M
4. **Done/Shipped: Pass hydrated view data into public Blade instead of Eloquent models** — public listing and calendar components now map occurrences to `EventOccurrenceViewData` before rendering; Blade templates only read pre-hydrated strings/dates/URLs/venue names, and focused render coverage asserts anonymous HTML avoids model/admin identifiers. — `src/Actions/BuildEventOccurrenceViewDataAction.php`, `src/Data/EventOccurrenceViewData.php`, `resources/views/livewire/page/events-listing.blade.php`, `resources/views/livewire/event-calendar.blade.php`, `tests/Feature/PublicEventViewDataTest.php` — M
5. **Done/Shipped: Stop sharing the admin-labelled calendar partial with the public page** — the public calendar now uses `capell-events::generic.event_calendar` instead of the admin-oriented label, with render coverage proving the public view does not emit the admin label. — `resources/views/livewire/event-calendar.blade.php`, `resources/lang/en/generic.php`, `tests/Feature/PublicEventViewDataTest.php` — S
6. **Denormalized `registration_count` is recomputed by aggregate query anyway** — why: `PromoteWaitlistAction` and `refreshRegistrationCount` call `confirmedRegistrationQuantity()` (a `SUM(quantity)` query) then write it back to `registration_count`. The column exists to avoid that query on read, but `remainingCapacity()` recomputes the SUM live rather than reading the column — so the denormalization buys nothing on the hot path. Either trust the column on read or drop it. — `src/Models/EventOccurrence.php` — S
7. **`occurrenceUrl()` builds the public URL by string concatenation** (`rtrim($pageUrl,'/').'/'.date`) — why: bypasses the URL registry/route layer; brittle if listing-page URL structure changes and not locale-aware for the date segment. Resolve through the page-URL contract used elsewhere. — `src/Models/EventOccurrence.php` — M
8. **Add a per-listing-page feed scope** — why: `routes/web.php` defines `events/{listingPage}/feed.ics` but `BuildCalendarFeedAction` ignores any listing-page filter and always returns the whole site's occurrences, so both feed routes emit identical content. Honor the listing page (category/tag/venue filter). — `src/Http/Controllers/CalendarFeedController.php`, `src/Actions/BuildCalendarFeedAction.php` — M

## 3. Missing Features (gaps)

Declared `capabilities[]`: `events`, `events-admin`, `events-console`, `events-frontend`, `events-registration-created-event`, `events-customer-portal-registration-feed`. All six are genuinely reachable (registration event is dispatched and consumed by `contacts`; portal feed provider is registered). Gaps against events-category norms:

- **Customer-portal self-service registration cancellation** _(table-stakes)_ — staff can now confirm/cancel registrations from the admin resource and cancellation reaches `PromoteWaitlistAction` through a package event listener, but the customer-portal feed still lists registrations without a buyer cancel action.
- **Timezone-aware display controls** _(table-stakes)_ — occurrences store a `timezone` and Blade calls `setTimezone($occurrence->timezone)`, but there is no viewer-timezone handling, no "in your local time" toggle, and no per-site default-tz setting (there are **no** settings at all — `database/settings/` is empty, manifest `settings:[]`).
- **iCal per-attendee / VALARM reminders** _(differentiator)_ — feed emits `VEVENT`s but no `VALARM` reminders and no personalized `?token` feed per attendee. Reminder emails exist server-side but aren't reflected in the calendar subscription.
- **Capacity/waitlist UI + automatic promotion** _(table-stakes)_ — capacity & waitlist logic is correct in `RegisterForEventOccurrenceAction`/`PromoteWaitlistAction`/`EventOccurrence`; cancellation now dispatches `EventRegistrationCancelled` and a scheduled reconcile promotes stale waitlisted registrations. Remaining gap: richer public/admin waitlist UI.
- **Ticketing / paid registration** _(differentiator)_ — `booking_mode`/`booking_url` support external booking links only; no integration with `capell-app/payments` for paid tickets despite `payments` being in customer-portal's support graph.
- **Shipped 2026-06-07: recurring-event exception editing is reachable in the occurrence UI.** `EventOccurrenceResource` now exposes cancel and reschedule row actions backed by `CancelOccurrenceAction` and `RescheduleOccurrenceAction`, with translated notifications and Livewire table-action coverage. — `src/Filament/Resources/Occurrences/EventOccurrenceResource.php`, `tests/Integration/EventRegistrationResourceWorkflowTest.php`
- **Reminder cadence configuration** _(table-stakes)_ — reminder is hard-coded to `starts_at->subDay()` in `ScheduleEventNotificationsAction`; no multi-reminder schedule (e.g. 1 week + 1 day + 1 hour) and no per-event opt-out.

## 4. Issues / Risks

- **Admin registration management shipped; portal cancellation remains.** `EventRegistrationResource` now exposes confirm/cancel row actions backed by `UpdateRegistrationStatusAction`; cancellation dispatches `EventRegistrationCancelled`, the registered listener triggers `PromoteWaitlistAction`, and a scheduled reconcile promotes stale waitlisted registrations. Remaining gap: customer-portal self-service cancellation. — `src/Actions/UpdateRegistrationStatusAction.php`, `src/Events/EventRegistrationCancelled.php`, `src/Listeners/PromoteWaitlistAfterRegistrationCancelled.php`, `src/Actions/ReconcileEventWaitlistsAction.php`
- **Health checks and package doctor command shipped.** `EventsHealthCheck` now runs recurrence, registration-capacity, calendar-feed, and Event schema diagnostics with package tests, and `capell:events-doctor` is registered in the package manifest. — `src/Health/EventsHealthCheck.php`, `src/Console/Commands/EventsDoctorCommand.php`, `capell.json`
- **Manifest-vs-reality mismatches narrowed.** Four Shield-backed policy subjects are now declared in `permissions[]`, and the marketplace screenshot gallery now lists the extension card plus the styled admin/frontend runner PNG captures from `docs/screenshots.json`; the `.ics` feed capture remains technical runner evidence. — `capell.json`, `src/Providers/EventsServiceProvider.php`, `docs/screenshots.json`
- **Mail dispatch moved out of the locked registration transaction.** `EventRegistrationNotification` queues after commit and `RegisterForEventOccurrenceAction` schedules confirmation/reminder notifications only after the registration transaction commits. — `src/Actions/RegisterForEventOccurrenceAction.php`, `src/Notifications/EventRegistrationNotification.php`
- **Timezone/DST correctness unproven.** `ExpandEventRecurrenceAction` builds `new RRULE($rule, $event->starts_at->toDateTimeImmutable())` then `CarbonImmutable::instance($occurrenceStart)->setTimezone($event->timezone)`. RRULE expands from the absolute start instant; wall-clock-anchored recurrences ("every Mon 09:00") can drift by an hour across DST boundaries. A test asserts "expands practical RRULE occurrences inside a range" but **no test crosses a DST transition**. — `src/Actions/ExpandEventRecurrenceAction.php`
- **Public-output safety: models in Blade.** As §2.4 — anonymous templates receive Eloquent models; safe today only because the upstream query eager-loads. No regression test proves the listing/calendar Blade never lazy-loads or emits admin internals. Capell convention requires anonymous-safety tests for rendering changes. — `resources/views/livewire/page/`, `resources/views/livewire/event-calendar.blade.php`
- **`cacheable: false` on every public surface.** Manifest `performance.cacheSafety.cacheable: false`. Listing, calendar, and `.ics` feed run live DB queries on every anonymous hit (`QueryPublicEventOccurrencesAction` with 4 eager loads). Feed controller reportedly sets freshness/ETag headers (test: "serves calendar feeds with freshness headers and conditional etag support") — good — but the HTML surfaces have no caching story and no declared `invalidationSources`. — `capell.json`, `src/Livewire/Page/EventsListingPage.php`
- **Test gaps.** Strong coverage on capacity/waitlist creation, feed, schema, recurrence range, editorial-calendar, portal feed, navigation (47 named tests). Missing: registration cancellation/status transition, automatic waitlist promotion on cancel, health-check behavior, DST recurrence, public-Blade anonymous-leak assertions, per-listing-page feed filtering. — `tests/`
- **i18n.** Lang files exist (`enum/form/generic/notification/package/table/validation`), but `occurrenceUrl()` date segment and some calendar formatting use server locale/timezone, not viewer locale. — `src/Models/EventOccurrence.php`

## 5. Marketplace & Selling

**Critique.** Manifest `summary`, package `description`, and composer `description` now use stronger buyer-facing copy. The UI media gap is closed for the static gallery: the marketplace manifest lists the extension card plus the 10 committed Capell runner PNG captures that prove admin and frontend workflows. The `.ics` feed PNG remains technical runner evidence rather than product media.

**Improved summary (1 sentence):**

> Publish recurring events with venues, capacity-managed RSVPs, subscribable iCal feeds, and Google-ready Event schema — all inside your Capell admin.

**Improved description (3–4 sentences):**

> Events turns Capell into a full event platform: editors create one event with an RRULE recurrence and the package materializes every occurrence, each with its own page, venue, schedule, and capacity. Visitors RSVP with automatic waitlisting and confirmation/reminder emails, while a public `.ics` feed lets them subscribe in Apple/Google/Outlook calendars. Every occurrence emits schema.org `Event` JSON-LD for rich results, and an admin calendar plus dashboard widget keep the programme visible. Built on `php-rrule` and `spatie/icalendar-generator`, with first-class hooks into Publishing Studio, Site Discovery, and the Customer Portal.

**Screenshot/media gaps.** Static UI screenshots are now reconciled: the promoted runner captures cover admin index/create/edit, venues, occurrences, registrations, admin calendar/widget, and public listing/calendar. The rendered `.ics` feed stays in `docs/screenshots.json` as route evidence. A short GIF of "create recurring event → occurrences appear → public calendar updates" would carry the value prop better than any static shot.

**Positioning.** Premium / `content-product` bundle is right. Cross-sell paths via existing deps: **Address** (venue geocoding/maps), **Tags** (event categories/filtered feeds), **SEO Suite** (event sitemaps + schema validation), **Customer Portal** ("my registrations" + future cancel), **Site Discovery** (event URLs in canonical registry), **Payments** (paid ticketing — net-new, see §3). Bundle as a "Programming & Events" extension suite with Address + Tags + Customer Portal.

**Differentiators / value props / target buyer.** Differentiators: RRULE recurrence with per-occurrence overrides, capacity+waitlist, native iCal subscription feeds, schema.org rich results — without leaving the CMS. Target buyer: marketing/comms teams at venues, conferences, education, nonprofits, and local-services sites already on Capell who run a recurring programme and want SEO + calendar reach without a separate ticketing SaaS.

**Keywords/tags (8–12):** events, event calendar, recurring events, RRULE, iCalendar, ICS feed, RSVP, waitlist, venues, event schema, JSON-LD, Filament.

## 6. Prioritized Roadmap

| Item                                                                                            | Bucket | Effort | Impact | Section ref    |
| ----------------------------------------------------------------------------------------------- | ------ | ------ | ------ | -------------- |
| Wire `UpdateRegistrationStatusAction` into `EventRegistrationResource` (confirm/cancel actions) | Done   | M      | High   | §4, §3         |
| Auto-trigger `PromoteWaitlistAction` on cancellation (listener) + scheduled reconcile           | Done   | M      | High   | §4, §3         |
| Add package-specific `doctor` command wiring for Events diagnostics                             | Done   | S      | Med    | §4             |
| Move RSVP mail out of locked transaction; queue `EventRegistrationNotification`                 | Done   | M      | High   | §2.1, §2.2, §4 |
| Capture + commit the 11 `screenshots.json` targets; sync manifest `screenshots`                 | Done   | S      | Med    | §1, §5         |
| Fix composer `description`; adopt improved summary/description                                  | Done   | S      | Med    | §5             |
| Declare `permissions[]` (and any settings) in `capell.json` to match registered policies        | Done   | S      | Med    | §4             |
| Done/Shipped: Pass typed view data to public Blade; add anonymous-leak rendering test           | Done   | M      | Med    | §2.4, §4       |
| Done/Shipped: Add DST-crossing recurrence test; bound recurrence horizon explicitly             | Done   | M      | Med    | §2.3, §4       |
| Per-listing-page filtered `.ics` feed (honor `{listingPage}`)                                   | Next   | M      | Med    | §2.8, §3       |
| Viewer-timezone display + per-site default-tz setting                                           | Next   | M      | Med    | §3, §4         |
| Configurable multi-reminder cadence + per-event opt-out                                         | Next   | M      | Low    | §3             |
| Shipped 2026-06-07: Verify/expose `CancelOccurrenceAction`/`RescheduleOccurrenceAction` in occurrence UI | Done   | S      | Med    | §3             |
| Paid ticketing via `capell-app/payments` integration                                            | Later  | L      | High   | §3             |
| Customer-portal self-service RSVP cancellation                                                  | Later  | M      | Med    | §3             |
| Personalized per-attendee iCal feed + VALARM reminders                                          | Later  | M      | Low    | §3             |
| Resolve `occurrenceUrl()` through URL registry; drop or trust `registration_count`              | Later  | M      | Low    | §2.6, §2.7     |
