# Events — Improvement & Growth Plan

> Package: capell-app/events · Kind: plugin · Tier: premium · Product group: Capell Content · Bundle: content-product · Status: Draft

## 1. Snapshot

Events adds event records, reusable venues, recurring-occurrence expansion (`rlanvin/php-rrule`), native RSVP/registration with capacity + waitlist, public `.ics` calendar feeds (`spatie/icalendar-generator`), schema.org `Event` JSON-LD render hooks, an admin calendar page/widget, and frontend Livewire listing + calendar pages. Surfaces: `admin`, `frontend`, `console`. Core Actions: `ExpandEventRecurrenceAction` / `SyncEventOccurrencesAction` (occurrence materialization), `RegisterForEventOccurrenceAction` (locked RSVP), `BuildCalendarFeedAction`, `BuildEventSchemaAction`, `ProcessDueEventNotificationLogsAction` (scheduled reminders). Tables: `event_venues`, `events`, `event_occurrences`, `event_registrations`, `event_notification_logs`. Requires `admin`, `frontend`, `navigation`, `publishing-studio`; supports `address`, `form-builder`, `seo-suite`, `tags`, and integrates with `customer-portal` (registration self-service feed) and `site-discovery` (public URL contributor).

Current marketplace summary (verbatim): _"Events adds listings, recurring events, venues, RSVPs, calendar feeds, and Event schema."_ Manifest declares **1** screenshot (`docs/assets/marketplace/extension-card.jpg`). `docs/screenshots.json` defines ~7 capture targets (index, create, edit, venues, occurrences, registrations, calendar, frontend listing/calendar, feed) but only the extension card is committed — `docs/screenshots/*.png` do not exist yet. So the _capture contract_ exceeds the manifest's listed media, while _committed_ media is just the single card.

## Completed Improvement Slices

- **2026-06-03:** Replaced the stubbed `EventsHealthCheck` with real diagnostics and refreshed marketplace copy.
- **2026-06-04:** Wired confirm/cancel row actions into `EventRegistrationResource`, so staff can change registration status from the shipped admin surface and cancellations trigger the existing waitlist-promotion workflow. Declared the event resource permissions in `capell.json`.

## 2. Improvements (existing functionality)

1. **Move RSVP confirmation mail out of the DB transaction** — why: `RegisterForEventOccurrenceAction::handle()` runs inside `DB::transaction()` after `lockForUpdate()` on the occurrence row, then calls `ScheduleEventNotificationsAction::run()` → `SendEventNotificationAction` which sends mail _synchronously_ (`Notification::route('mail',…)->notify(...)`). Mail-driver latency holds the row lock, serializing concurrent registrations for the same occurrence and risking lock-wait timeouts. Schedule notifications _after_ commit (or dispatch a queued job). — `src/Actions/RegisterForEventOccurrenceAction.php`, `src/Actions/SendEventNotificationAction.php` — M
2. **Make `EventRegistrationNotification` implement `ShouldQueue`** — why: confirmation and waitlist-promotion mail are sent inline in request/Action context; a slow SMTP call blocks the web request and (per #1) a DB lock. Queue it. — `src/Notifications/EventRegistrationNotification.php` — S
3. **Bound recurrence generation explicitly** — why: `SyncEventOccurrencesAction` silently caps materialization at `now()+1 year`; an unbounded `RRULE` (`FREQ=DAILY` with no `UNTIL`/`COUNT`) produces a rolling 1-year window with no operator feedback, and re-running sync never trims past occurrences. Add an explicit horizon setting + surface "generated through {date}" in the edit form. — `src/Actions/SyncEventOccurrencesAction.php`, `src/Actions/ExpandEventRecurrenceAction.php` — M
4. **Pass hydrated view data into public Blade instead of Eloquent models** — why: `events-listing.blade.php` iterates raw `EventOccurrence` models and calls `$occurrence->starts_at->setTimezone($occurrence->timezone)`; `event-calendar.blade.php` reaches `$occurrence->event->translation->title`. Relationships are eager-loaded in `QueryPublicEventOccurrencesAction` so there is no live N+1, but handing models to public templates is fragile (one stray accessor = lazy load / leak) and breaks the Capell "public Blade must not touch the DB; pass hydrated render data" rule. Map to a typed `EventOccurrenceViewData` first. — `resources/views/livewire/page/events-listing.blade.php`, `src/Livewire/Page/EventsListingPage.php`, `src/Livewire/EventCalendar.php` — M
5. **Stop sharing the admin-labelled calendar partial with the public page** — why: `resources/views/livewire/event-calendar.blade.php` is rendered on the public `EventsCalendarPage` but its `aria-label` uses `__('capell-events::generic.admin_calendar')`. Minor, but it leaks an admin-oriented label into anonymous output and conflates two surfaces. Split or parameterize the label. — `resources/views/livewire/event-calendar.blade.php`, `src/Livewire/EventCalendar.php` — S
6. **Denormalized `registration_count` is recomputed by aggregate query anyway** — why: `PromoteWaitlistAction` and `refreshRegistrationCount` call `confirmedRegistrationQuantity()` (a `SUM(quantity)` query) then write it back to `registration_count`. The column exists to avoid that query on read, but `remainingCapacity()` recomputes the SUM live rather than reading the column — so the denormalization buys nothing on the hot path. Either trust the column on read or drop it. — `src/Models/EventOccurrence.php` — S
7. **`occurrenceUrl()` builds the public URL by string concatenation** (`rtrim($pageUrl,'/').'/'.date`) — why: bypasses the URL registry/route layer; brittle if listing-page URL structure changes and not locale-aware for the date segment. Resolve through the page-URL contract used elsewhere. — `src/Models/EventOccurrence.php` — M
8. **Add a per-listing-page feed scope** — why: `routes/web.php` defines `events/{listingPage}/feed.ics` but `BuildCalendarFeedAction` ignores any listing-page filter and always returns the whole site's occurrences, so both feed routes emit identical content. Honor the listing page (category/tag/venue filter). — `src/Http/Controllers/CalendarFeedController.php`, `src/Actions/BuildCalendarFeedAction.php` — M

## 3. Missing Features (gaps)

Declared `capabilities[]`: `events`, `events-admin`, `events-console`, `events-frontend`, `events-registration-created-event`, `events-customer-portal-registration-feed`. All six are genuinely reachable (registration event is dispatched and consumed by `contacts`; portal feed provider is registered). Gaps against events-category norms:

- **Customer-portal self-service registration cancellation** _(table-stakes)_ — staff can now confirm/cancel registrations from the admin resource and cancellation reaches `PromoteWaitlistAction`, but the customer-portal feed still lists registrations without a buyer cancel action.
- **Timezone-aware display controls** _(table-stakes)_ — occurrences store a `timezone` and Blade calls `setTimezone($occurrence->timezone)`, but there is no viewer-timezone handling, no "in your local time" toggle, and no per-site default-tz setting (there are **no** settings at all — `database/settings/` is empty, manifest `settings:[]`).
- **iCal per-attendee / VALARM reminders** _(differentiator)_ — feed emits `VEVENT`s but no `VALARM` reminders and no personalized `?token` feed per attendee. Reminder emails exist server-side but aren't reflected in the calendar subscription.
- **Capacity/waitlist UI + automatic promotion** _(table-stakes)_ — capacity & waitlist logic is correct in `RegisterForEventOccurrenceAction`/`PromoteWaitlistAction`/`EventOccurrence`, but promotion is never triggered automatically (no listener on cancellation, no scheduled sweep). Wire `PromoteWaitlistAction` to a cancellation event and/or a scheduled reconcile.
- **Ticketing / paid registration** _(differentiator)_ — `booking_mode`/`booking_url` support external booking links only; no integration with `capell-app/payments` for paid tickets despite `payments` being in customer-portal's support graph.
- **Recurring-event exception editing in the UI** _(differentiator)_ — the data model supports per-occurrence overrides (`is_override`, `override_data`, `CancelOccurrenceAction`, `RescheduleOccurrenceAction`) but there is no evidence these reschedule/cancel Actions are reachable from `ManageEventOccurrences` (verify; likely the same dead-wiring class as registrations).
- **Reminder cadence configuration** _(table-stakes)_ — reminder is hard-coded to `starts_at->subDay()` in `ScheduleEventNotificationsAction`; no multi-reminder schedule (e.g. 1 week + 1 day + 1 hour) and no per-event opt-out.

## 4. Issues / Risks

- **Admin registration management shipped; portal cancellation remains.** `EventRegistrationResource` now exposes confirm/cancel row actions backed by `UpdateRegistrationStatusAction`; cancellation triggers `PromoteWaitlistAction` and refreshes `registration_count`. Remaining gap: customer-portal self-service cancellation and any scheduled reconcile for stale waitlists. — `src/Actions/UpdateRegistrationStatusAction.php`, `src/Actions/PromoteWaitlistAction.php`, `src/Filament/Resources/Registrations/`
- **Health checks shipped.** `EventsHealthCheck` now runs recurrence, registration-capacity, calendar-feed, and Event schema diagnostics with package tests. Remaining gap: `commands.doctor` is still `null`, so diagnostics are discoverable through the manifest/check class rather than a package-specific doctor command. — `src/Health/EventsHealthCheck.php`, `capell.json`
- **Manifest-vs-reality mismatches narrowed.** Four Shield-backed policy subjects are now declared in `permissions[]`. Remaining mismatch: manifest lists 1 marketplace screenshot while `docs/screenshots.json` defines ~7 capture targets and **none of the `docs/screenshots/*.png` are committed** (only `extension-card.jpg`). — `capell.json`, `src/Providers/EventsServiceProvider.php`, `docs/screenshots.json`
- **Mail inside a locked transaction (perf + reliability).** See §2.1/§2.2 — synchronous `Notification` send under `lockForUpdate()`; a mail-provider stall blocks both the HTTP request and the occurrence row lock. Against `performance.adminQueryBudget: 40` / `frontendRenderBudgetMs: 20`, registration write latency is unbounded. — `src/Actions/RegisterForEventOccurrenceAction.php`
- **Timezone/DST correctness unproven.** `ExpandEventRecurrenceAction` builds `new RRULE($rule, $event->starts_at->toDateTimeImmutable())` then `CarbonImmutable::instance($occurrenceStart)->setTimezone($event->timezone)`. RRULE expands from the absolute start instant; wall-clock-anchored recurrences ("every Mon 09:00") can drift by an hour across DST boundaries. A test asserts "expands practical RRULE occurrences inside a range" but **no test crosses a DST transition**. — `src/Actions/ExpandEventRecurrenceAction.php`
- **Public-output safety: models in Blade.** As §2.4 — anonymous templates receive Eloquent models; safe today only because the upstream query eager-loads. No regression test proves the listing/calendar Blade never lazy-loads or emits admin internals. Capell convention requires anonymous-safety tests for rendering changes. — `resources/views/livewire/page/`, `resources/views/livewire/event-calendar.blade.php`
- **`cacheable: false` on every public surface.** Manifest `performance.cacheSafety.cacheable: false`. Listing, calendar, and `.ics` feed run live DB queries on every anonymous hit (`QueryPublicEventOccurrencesAction` with 4 eager loads). Feed controller reportedly sets freshness/ETag headers (test: "serves calendar feeds with freshness headers and conditional etag support") — good — but the HTML surfaces have no caching story and no declared `invalidationSources`. — `capell.json`, `src/Livewire/Page/EventsListingPage.php`
- **Test gaps.** Strong coverage on capacity/waitlist creation, feed, schema, recurrence range, editorial-calendar, portal feed, navigation (47 named tests). Missing: registration cancellation/status transition, automatic waitlist promotion on cancel, health-check behavior, DST recurrence, public-Blade anonymous-leak assertions, per-listing-page feed filtering. — `tests/`
- **i18n.** Lang files exist (`enum/form/generic/notification/package/table/validation`), but `occurrenceUrl()` date segment and some calendar formatting use server locale/timezone, not viewer locale. — `src/Models/EventOccurrence.php`

## 5. Marketplace & Selling

**Critique.** Manifest `summary` and composer `description` diverge: composer says only _"Events for Capell"_ (uninformative); the manifest summary is a flat feature list with no buyer outcome. Neither conveys _who it's for_ or _why it beats a hand-rolled events CPT_. "RSVPs" is over-claimed given the registration-management chain is currently unwired (§4).

**Improved summary (1 sentence):**

> Publish recurring events with venues, capacity-managed RSVPs, subscribable iCal feeds, and Google-ready Event schema — all inside your Capell admin.

**Improved description (3–4 sentences):**

> Events turns Capell into a full event platform: editors create one event with an RRULE recurrence and the package materializes every occurrence, each with its own page, venue, schedule, and capacity. Visitors RSVP with automatic waitlisting and confirmation/reminder emails, while a public `.ics` feed lets them subscribe in Apple/Google/Outlook calendars. Every occurrence emits schema.org `Event` JSON-LD for rich results, and an admin calendar plus dashboard widget keep the programme visible. Built on `php-rrule` and `spatie/icalendar-generator`, with first-class hooks into Publishing Studio, Site Discovery, and the Customer Portal.

**Screenshot/media gaps.** Capture and commit the 7 targets already specified in `docs/screenshots.json` (admin index/create/edit, venues, occurrences, registrations, admin calendar) plus the public listing, public calendar, and a rendered `.ics`/Google rich-result preview; then sync `capell.json` `marketplace.screenshots` to match (currently only the card is listed). A short GIF of "create recurring event → occurrences appear → public calendar updates" would carry the value prop better than any static shot.

**Positioning.** Premium / `content-product` bundle is right. Cross-sell paths via existing deps: **Address** (venue geocoding/maps), **Tags** (event categories/filtered feeds), **SEO Suite** (event sitemaps + schema validation), **Customer Portal** ("my registrations" + future cancel), **Site Discovery** (event URLs in canonical registry), **Payments** (paid ticketing — net-new, see §3). Bundle as a "Programming & Events" extension suite with Address + Tags + Customer Portal.

**Differentiators / value props / target buyer.** Differentiators: RRULE recurrence with per-occurrence overrides, capacity+waitlist, native iCal subscription feeds, schema.org rich results — without leaving the CMS. Target buyer: marketing/comms teams at venues, conferences, education, nonprofits, and local-services sites already on Capell who run a recurring programme and want SEO + calendar reach without a separate ticketing SaaS.

**Keywords/tags (8–12):** events, event calendar, recurring events, RRULE, iCalendar, ICS feed, RSVP, waitlist, venues, event schema, JSON-LD, Filament.

## 6. Prioritized Roadmap

| Item                                                                                            | Bucket | Effort | Impact | Section ref    |
| ----------------------------------------------------------------------------------------------- | ------ | ------ | ------ | -------------- |
| Wire `UpdateRegistrationStatusAction` into `EventRegistrationResource` (confirm/cancel actions) | Done   | M      | High   | §4, §3         |
| Auto-trigger `PromoteWaitlistAction` on cancellation (listener) + scheduled reconcile           | Now    | M      | High   | §4, §3         |
| Add package-specific `doctor` command wiring for Events diagnostics                              | Now    | S      | Med    | §4             |
| Move RSVP mail out of locked transaction; queue `EventRegistrationNotification`                 | Now    | M      | High   | §2.1, §2.2, §4 |
| Capture + commit the 7 `screenshots.json` targets; sync manifest `screenshots`                  | Now    | S      | Med    | §1, §5         |
| Fix composer `description`; adopt improved summary/description                                  | Now    | S      | Med    | §5             |
| Declare `permissions[]` (and any settings) in `capell.json` to match registered policies        | Done   | S      | Med    | §4             |
| Pass typed view data to public Blade; add anonymous-leak rendering test                         | Next   | M      | Med    | §2.4, §4       |
| Add DST-crossing recurrence test; bound recurrence horizon explicitly                           | Next   | M      | Med    | §2.3, §4       |
| Per-listing-page filtered `.ics` feed (honor `{listingPage}`)                                   | Next   | M      | Med    | §2.8, §3       |
| Viewer-timezone display + per-site default-tz setting                                           | Next   | M      | Med    | §3, §4         |
| Configurable multi-reminder cadence + per-event opt-out                                         | Next   | M      | Low    | §3             |
| Verify/expose `CancelOccurrenceAction`/`RescheduleOccurrenceAction` in occurrence UI            | Next   | S      | Med    | §3             |
| Paid ticketing via `capell-app/payments` integration                                            | Later  | L      | High   | §3             |
| Customer-portal self-service RSVP cancellation                                                  | Later  | M      | Med    | §3             |
| Personalized per-attendee iCal feed + VALARM reminders                                          | Later  | M      | Low    | §3             |
| Resolve `occurrenceUrl()` through URL registry; drop or trust `registration_count`              | Later  | M      | Low    | §2.6, §2.7     |
