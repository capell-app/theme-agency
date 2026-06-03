# Bookings — Improvement & Growth Plan

> Package: capell-app/bookings · Kind: plugin · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Draft

## 1. Snapshot

Bookings is a premium Capell Operations plugin that adds an appointment-request workflow: 7 models / tables (`booking_services`, `booking_staff_members`, `booking_locations`, `booking_availability_windows`, `booking_availability_exceptions`, `appointment_requests`, `appointment_audit_logs`), 6 Filament admin resources, a public request form route, and an opaque per-staff iCalendar feed. Surfaces declared in `capell.json` are `admin`, `console`, `frontend` — but no `console` artifact exists (`src/Console/` is absent; `src/Health/BookingsHealthCheck.php` is the only thing implying a CLI/health surface). Domain logic lives correctly in `src/Actions` (`CreateAppointmentRequestAction` is the 340-line core: lead-time, future-window, weekly-availability, date-exception, capacity, and buffer validation; `ConfirmAppointmentRequestAction`, `CancelAppointmentRequestAction`, `QueueAppointmentNotificationAction`, `QueueAppointmentReminderAction`, `BuildStaffCalendarFeedAction`, `BuildPublicBookingRequestOptionsAction`, `RecordAppointmentAuditLogAction`). Deps: `requires` `capell-app/admin` + `capell-app/core`; `supports` `address`, `events`, `form-builder`, `notifications`, `seo-suite`. Current marketplace summary, verbatim: _"Bookings adds appointment request workflows, availability, confirmations, reminders, and calendar feeds for healthcare, services, education, consulting, nonprofit, and portfolio sites."_ Screenshot count: **0** (`marketplace.screenshots: []`).

**Manifest-vs-reality mismatches (load-bearing):**

- `healthChecks[].label` claims it verifies "models, migrations, and appointment request actions are discoverable" but `BookingsHealthCheck` implements only `compatibleCapellApiVersion()` — it performs **no check** (§4).
- Surface `Update:AppointmentRequest` and overview text "Operators confirm or cancel requests in admin resources" are false: confirm/cancel Actions have **no UI caller** (§4).
- Capability `bookings-reminders` is declared and unit-tested, but `QueueAppointmentReminderAction` has **no scheduled/production caller** anywhere in the monorepo — reminders never fire (§4).

## 2. Improvements (existing functionality)

1. **Wire Confirm / Cancel into the Filament `AppointmentRequest` admin UI.** — The two Actions exist and are tested but `EditAppointmentRequest.php` and `ListAppointmentRequests.php` are empty `EditRecord`/`ListRecords` subclasses with no header/table Actions. Operators currently can only edit raw fields; there is no audited state transition button. — `src/Filament/Resources/AppointmentRequests/Pages/EditAppointmentRequest.php`, `.../Pages/ListAppointmentRequests.php`, calling `ConfirmAppointmentRequestAction`/`CancelAppointmentRequestAction`. Gate visibility with the `filament-authorisation` per-record pattern. — **M**

2. **Add the scheduled reminder dispatcher.** — `QueueAppointmentReminderAction` is only ever called from tests. Add a Console command (registered as the declared `console` surface) that finds confirmed appointments within a reminder lead window and calls the action, then schedule it. Without this, the advertised reminder feature is inert. — new `src/Console/SendDueAppointmentRemindersCommand.php` + schedule registration in `BookingsServiceProvider`. — **M**

3. **Implement the health check body.** — Make `BookingsHealthCheck` actually assert the 7 tables exist, models are morph-registered, and the core actions resolve, matching its manifest label. — `src/Health/BookingsHealthCheck.php`. — **S**

4. **Hold a row/advisory lock over the availability slot during creation.** — `CreateAppointmentRequestAction::handle()` wraps work in `DB::transaction` but the capacity guard is a `count()` with no `lockForUpdate` and no unique constraint, so two concurrent requests can both pass (§4). Add `lockForUpdate()` on the matched availability window/exception (or a slot advisory lock) so the count is serialized. — `src/Actions/CreateAppointmentRequestAction.php` (`validateCapacity`, `matchingAvailabilityWindow`). — **M**

5. **Eliminate the N+1 in `BuildStaffCalendarFeedAction`.** — Feed eager-loads `service`/`location` (good) but iterates with `->each` building strings; fine for small sets, but there is no pagination/chunking guard for staff with thousands of confirmed appointments, and no upper bound. Add `->chunkById()` and an optional date-range floor (e.g. only future + recent past). — `src/Actions/BuildStaffCalendarFeedAction.php`. — **S**

6. **Replace hand-rolled ICS with `spatie/icalendar-generator` (already in `suggest`).** — Current builder escapes text but does **not** fold lines to 75 octets (RFC 5545 §3.1), omits `SEQUENCE`, and hardcodes `PRODID`. Long names/notes produce technically-invalid feeds some clients reject. The suggested dependency is unused dead advice today. — `src/Actions/BuildStaffCalendarFeedAction.php`, `composer.json`. — **M**

7. **Make the public form timezone a select, not a free-text input defaulting to `Europe/London`.** — `resources/views/request.blade.php` renders `<input type="text" name="timezone" value="old('timezone','Europe/London')">`. A typo'd or hostile string flows into `CarbonImmutable::setTimezone()` in `matchingAvailabilityWindow` and the notification; invalid zones throw. Use a constrained `<select>` of `DateTimeZone::listIdentifiers()` and validate `in:`. — `resources/views/request.blade.php`, `src/Http/Controllers/StoreBookingRequestController.php`. — **S**

8. **Surface the audit log in admin.** — `appointment_audit_logs` is written on every transition but there is no relation manager or infolist exposing it; operators can't see history. Add a read-only relation manager / timeline entry on the AppointmentRequest resource. — `src/Filament/Resources/AppointmentRequests/...`. — **S**

## 3. Missing Features (gaps)

Tie to declared `capabilities[]`: `bookings`, `bookings-availability`, `bookings-availability-exceptions`, `bookings-holidays`, `bookings-capacity`, `bookings-lead-times`, `bookings-service-buffers`, `bookings-appointment-requests`, `bookings-notifications`, `bookings-reminders`, `bookings-calendar-feeds`.

**Table-stakes for the scheduling category that are missing:**

- **Reschedule / decline flow.** Status enum has `Declined`, `Completed`, `NoShow` cases but only `Confirmed`/`Cancelled` have Actions. No reschedule path (cancel + re-create loses the audit thread). Differentiator-adjacent; expected by any booking buyer.
- **Customer-facing confirm/cancel link.** `appointment_requests` has a `confirmation_token` column (in `$fillable`) but nothing generates or consumes it — no public route lets the requester confirm or cancel their own appointment. The column is dead.
- **`bookings-holidays` capability is unbacked.** No holiday/blackout-calendar model or seed; holidays must be hand-entered as individual `BookingAvailabilityException` rows. Either build a holiday source or drop the capability.
- **Real availability-slot endpoint.** The public form only lists services/staff/locations (`BuildPublicBookingRequestOptionsAction`); it offers no bookable time slots, so the visitor free-picks a `datetime-local` and gets rejected server-side. A slot-generation Action (windows minus exceptions minus booked capacity) is the single biggest UX gap.
- **Confirmation/reschedule emails to staff.** Notifications route only to `customer_email`; the staff member assigned is never notified of a new request.
- **Deposits / payments.** No payment capture on request or confirmation. This is the natural cross-sell (§5) and a clear differentiator vs. free booking widgets.
- **Calendar sync (inbound).** Feeds are export-only (ICS pull). No two-way sync / external-calendar busy import, so staff double-booking from outside the system is undetectable.
- **Timezone of record per resource.** Service/staff/location all have `timezone` columns but availability matching uses the _request's_ timezone only; staff working hours in their own zone aren't normalized.

**Differentiators worth building:** real slot-picker API, customer self-service confirm/reschedule via token, and deposit capture (payments suite). The rest are table-stakes.

## 4. Issues / Risks

- **[High] Confirm/Cancel/Reminder workflow unreachable in production.** `grep -rn 'ConfirmAppointmentRequestAction|CancelAppointmentRequestAction' src/` returns **only the class definitions**; `QueueAppointmentReminderAction` callers are tests only. The admin pages (`EditAppointmentRequest.php`, `ListAppointmentRequests.php`) declare no Actions. The audited state machine is dead code from the user's perspective, contradicting `surfaces`, the `Update:AppointmentRequest` capability, and `docs/overview.md`. — `src/Filament/Resources/AppointmentRequests/Pages/*`.
- **[High] Double-booking race condition.** `CreateAppointmentRequestAction::validateCapacity()` does `AppointmentRequest::...->count()` then `create()` inside a transaction with **no `lockForUpdate`** on the slot and **no DB unique constraint**. Concurrent requests for the last slot both read `count < capacity` and both insert. Note the contrast: `ConfirmAppointmentRequestAction`/`CancelAppointmentRequestAction` correctly use `lockForUpdate()`, so the omission in Create is the gap. — `src/Actions/CreateAppointmentRequestAction.php`.
- **[High] Health check is a stub.** `BookingsHealthCheck` implements only `compatibleCapellApiVersion(): '^4.0'` and no `check()` logic, yet `capell.json` marks it `severity: critical` with a label promising it verifies models/migrations/actions. A critical health check that always "passes" is worse than none. — `src/Health/BookingsHealthCheck.php`.
- **[Med] Dead `confirmation_token` column + dead `spatie/icalendar-generator` suggest.** `confirmation_token` is fillable but never written/read; the suggested iCal library is never imported (feed is hand-rolled). Both are manifest/composer noise that imply features that don't exist. — `database/migrations/...05_create_appointment_requests_table.php`, `composer.json`.
- **[Med] ICS not RFC-5545 line-folded.** `BuildStaffCalendarFeedAction::escapeText()` escapes `\ ; , \n` but lines are emitted unfolded; `SUMMARY`/`DESCRIPTION` over 75 octets violate the spec and break strict parsers. Public-output correctness issue. — `src/Actions/BuildStaffCalendarFeedAction.php`.
- **[Med] Free-text timezone from anonymous input.** Public `timezone` field is an unconstrained text input (`request.blade.php`); an invalid identifier throws inside availability matching / notification formatting. Validate against `DateTimeZone` identifiers. — `resources/views/request.blade.php`, `StoreBookingRequestController.php`.
- **[Low] Performance budget is unmeasurable as written.** `capell.json` sets `performance.budgets.performanceTargetMs: 0` and `cacheSafety.cacheable: false`; a 0ms target can never pass a real budget gate and the public form is rebuilt per request (`BuildPublicBookingRequestOptionsAction` runs 3 queries every GET with `Cache-Control: no-store`). Set a realistic target (e.g. 200ms) and consider short-TTL caching of the public options. — `capell.json`, `src/Http/Controllers/ShowBookingRequestController.php`.
- **[Low] No `SettingsSchemaRegistry` / `CacheInvalidationRegistry` usage** despite the operations bundle norm; reminder lead time, default timezone, and buffers are not admin-configurable settings. — package-wide.
- **Public-output safety: PASS (verified).** `PublicBookingRequestTest` asserts `Cache-Control: no-store, private`, `X-Robots-Tag: noindex, nofollow`, and `assertDontSee` for `capell-app/bookings`, `Filament`, `BookingServiceResource`, `admin`, `signed`. `BuildPublicBookingRequestOptionsAction` selects only safe columns and maps to scalars; Blade receives hydrated arrays (no DB queries in the view). The staff feed is gated by a 64-char opaque token (`Str::random(64)`) and 404s on miss. Good — keep these guarantees under test for any §2 change.
- **Test gaps:** no test for the double-booking race; no test that admin can confirm/cancel (because the UI doesn't); no feed-content test for line-folding or token-miss 404 assertions on content; no `BookingsAdminSurfaceTest` coverage of action buttons. — `tests/`.

## 5. Marketplace & Selling

**Critique.** The composer `description` ("Bookings and appointment requests for Capell") is generic and buries the value. The manifest `summary` is a feature list stuffed with six verticals ("healthcare, services, education, consulting, nonprofit, and portfolio") that dilutes positioning and overpromises — it advertises **reminders** (inert, §4) and implies operator confirm/cancel (no UI, §4). Zero screenshots on a premium-tier, paid, first-party-certification package is the single biggest conversion blocker.

**Improved 1-sentence summary:**

> Take appointment requests on any page, enforce real availability and buffers, then confirm, cancel, and remind from your admin — with private staff calendar feeds out of the box.

**Improved 3–4 sentence description:**

> Bookings turns a Capell site into an appointment-request engine. Visitors request times against per-service durations, lead times, weekly availability windows, date-specific exceptions, capacity, and before/after buffers — no double-bookings, no spreadsheet. Operators confirm or cancel each request with a full audit trail and automatic customer notifications, and every staff member gets a private, tokenised iCalendar feed that syncs confirmed appointments to Apple, Google, or Outlook. Built for clinics, studios, advisors, and any team that runs on bookable time.

_(Ship §2.1/§2.2/§2.3 before publishing this copy — it currently describes the intended state, not the shipped one.)_

**Screenshot/media gaps (fill all):** (1) public request form, (2) admin appointment list with status badges, (3) the confirm/cancel action modal (once built), (4) availability-window editor, (5) a calendar app showing a subscribed staff feed. Add a 30s GIF of request → confirm → calendar-sync.

**Pricing / tier / bundle.** Premium tier in the `operations` bundle is defensible _once_ the workflow is reachable and reminders fire; as shipped it's closer to a standard tier. Position as the operations-bundle anchor.

**Cross-sell (via deps + Extension Suites).** `supports` already lists the hooks: **payments suite** for deposits/prepayment at confirmation (highest-value attach, currently a §3 gap); `capell-app/notifications` for SMS/multi-channel reminders; `capell-app/form-builder` for custom intake fields on the request; `capell-app/address` for location addresses; `capell-app/seo-suite` for indexable service landing pages.

**Differentiators / value props / target buyer.** Value props: zero-double-booking guarantee (once §4 fixed), private calendar feeds with no admin leakage, full audit trail. Target buyer: small service/clinic/studio operators on Capell who need to take bookings without a separate SaaS.

**Keywords/tags (8–12):** appointment booking, scheduling, calendar feed, iCal/ICS, availability windows, booking buffers, appointment reminders, staff calendar, capacity limits, lead time, Filament admin, Laravel CMS.

## 6. Prioritized Roadmap

| Item                                                           | Bucket | Effort | Impact | Section ref |
| -------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Wire Confirm/Cancel into Filament admin (gated)                | Now    | M      | High   | §2.1, §4    |
| Implement `BookingsHealthCheck` body to match its label        | Now    | S      | High   | §2.3, §4    |
| Fix double-booking race (lock slot / unique constraint) + test | Now    | M      | High   | §2.4, §4    |
| Add scheduled reminder command (the `console` surface)         | Now    | M      | High   | §3, §4      |
| Validate public timezone against real identifiers              | Now    | S      | Med    | §2.7, §4    |
| Surface audit log timeline in admin                            | Next   | S      | Med    | §2.8        |
| Real availability slot-picker API for the public form          | Next   | L      | High   | §3          |
| Customer self-service confirm/cancel via `confirmation_token`  | Next   | M      | High   | §3, §4      |
| Notify assigned staff on new/confirmed requests                | Next   | S      | Med    | §3          |
| RFC-5545 line-folding via `spatie/icalendar-generator`         | Next   | M      | Med    | §2.6, §4    |
| Capture screenshots + rewrite summary/description              | Next   | S      | High   | §5          |
| Chunk/bound staff calendar feed query                          | Next   | S      | Low    | §2.5        |
| Reschedule + Decline + NoShow flows                            | Later  | M      | Med    | §3          |
| Deposits/payments at confirmation (payments suite cross-sell)  | Later  | L      | High   | §3, §5      |
| Admin-configurable settings (lead time, buffers, default tz)   | Later  | M      | Med    | §4          |
| Set realistic `performanceTargetMs` + cache public options     | Later  | S      | Low    | §4          |
