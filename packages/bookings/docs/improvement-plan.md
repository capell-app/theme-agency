# Bookings - Improvement & Growth Plan

> Package: capell-app/bookings · Kind: plugin · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Active

## 1. Snapshot

Bookings is a large operations package for appointment requests, availability, holds, reminders, reviews, waitlists, travel planning, payment-gated confirmation, group clinics, customer portal lesson records, signed proposal/review URLs, webhook ingestion, retention pruning, and an overridable public booking renderer. It owns many models, migrations, admin resources, scheduled commands, settings, route surfaces, and focused tests. The feature set is deep, but test coverage and manifest/docs need to catch up with the package's operational breadth: health has pass/fail internals but no diagnostic messages, manifest commands do not reflect registered/scheduled commands, and public booking/hold/payment workflows need stronger idempotency and abuse coverage.

## 2. Improvements (existing functionality)

1. **Add diagnostic health output.** `BookingsHealthCheck` has `passes()`, missing tables, morph aliases, and action resolution checks, but no diagnostic collection or remediation labels. Add `runDiagnostics()` for tables, morph map, actions, public route binding, renderer binding, schedules, settings migration, and command registration. Evidence: `src/Health/BookingsHealthCheck.php`, `src/Providers/BookingsServiceProvider.php`. - **M**

2. **Align manifest commands and schedules with provider behavior.** The provider registers four console commands and schedules them, while `capell.json commands` are null and scheduled-job contributions repeat the same class. Update manifest metadata and tests to trace reminders, workflow expiry, review scheduling, and retention pruning. Evidence: `BookingsServiceProvider::configurePackage()`, `registerReminderSchedule()`, `capell.json`, `tests/Unit/BookingsPackageTest.php`. - **M**

3. **Harden public booking request abuse/idempotency.** The route is throttled by email/IP and Action validation is strong, but repeated submissions for the same service/time/email should have deterministic behavior. Add tests for duplicate requests, provisional holds, expired holds, and payment-gated confirmation. Evidence: `routes/web.php`, `CreateAppointmentRequestAction`, `PlaceProvisionalHoldAction`, `ExpireStaleHoldsAction`. - **M**

4. **Add public prop/output safety coverage across renderers.** Blade public renderer and Inertia override consumers must not leak admin URLs, model internals, tokens, or private notes. Add tests around `BuildPublicBookingRequestPropsAction` and `BladePublicBookingRequestRenderer`. Evidence: `src/Rendering/BladePublicBookingRequestRenderer.php`, `BuildPublicBookingRequestPropsAction`, `resources/views/request.blade.php`. - **M**

## 3. Missing Features (gaps)

Capabilities declared span availability, requests, reminders, calendar feeds, portal lesson records, messaging, travel, waitlists, group clinics, reviews, AI prompts, GDPR, and retention.

- **Shipped 2026-06-16: demo command and fixtures.** `capell:bookings-demo` installs an idempotent demo service, staff member, location, availability window, and appointment request, and `capell.json` now advertises the demo path.
- **Shipped 2026-06-16: failed message retry and webhook replay Actions.** Failed message logs can be retried from the admin resource, and processed webhook events can be reset for replay without duplicating provider event records.
- **Shipped 2026-06-16: public success/confirmation component contract.** Public booking props now include a stable `success` state with submitted/status/message/reference fields for Blade and alternate renderers.
- **No single workflow test covering request -> hold -> payment -> confirmation -> reminder -> review.** Unit tests cover slices, but the end-to-end operations story needs a package smoke test.

## 4. Issues / Risks

1. **Important gap: health diagnostics are too terse for a complex package.** Recommended fix: diagnostic results with remediation for each runtime dependency. - **P2**

2. **Important issue: manifest commands/schedules under-report the package surface.** Recommended fix: align commands and scheduled-job contributions with provider registrations. - **P2**

3. **Important risk: public request and hold workflows are abuse-sensitive.** Recommended fix: duplicate/idempotency tests and deterministic handling. - **P2**

4. **Important risk: public renderer contracts can leak internals as more adapters consume them.** Recommended fix: prop/output safety tests for Blade and Inertia consumers. - **P2**

## 5. Marketplace & Positioning

Bookings should be positioned as a serious operations workflow, not just a form. For teams, emphasize request intake, availability, reminders, reviews, waitlists, travel, payment-gated confirmation, and customer portal records. For developers, emphasize Actions, renderer override, typed Data, provider contracts, and scheduled maintenance.

**Current summary:** "Bookings turns public appointment requests into a managed Capell operations workflow with availability, reminders, reviews, waitlists, travel planning, reporting, and optional advanced automation."

**Improved summary:** "A complete Capell booking operations suite: public appointment requests, availability, holds, reminders, reviews, waitlists, travel planning, customer portal links, and retention workflows."

**Media status:** Existing booking screenshots are useful. Recapture after success-state or public renderer changes.

**Cross-sell:** Theme Inertia Bookings, Customer Portal, Payments, Automation Studio, Agent Bridge, Equestrian Clinics, Social Feeds, Email Studio.

## 6. Prioritized Roadmap

| Item                                                           | Bucket | Effort | Impact | Section ref |
| -------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add diagnostic health output with remediation                  | Done   | M      | High   | §2.1, §4.1  |
| Align manifest commands and scheduled-job metadata             | Done   | M      | High   | §2.2, §4.2  |
| Add duplicate/hold/payment public request idempotency coverage | Done   | M      | High   | §2.3, §4.3  |
| Add public renderer prop/output safety coverage                | Done   | M      | High   | §2.4, §4.4  |
| Add demo/setup command and fixtures                            | Done   | M      | Medium | §3, §5      |
| Add failed message/webhook retry admin workflow                | Done   | L      | High   | §3          |
| Add public success/confirmation component contract             | Done   | M      | Medium | §3          |
| Add end-to-end booking workflow smoke test                     | Later  | L      | High   | §3          |
| Add richer analytics/reporting dashboard                       | Later  | L      | Medium | §5          |

## 7. Verification

Implementation slices shipped the current Now rows. Re-run the package verification with:

```bash
vendor/bin/pest packages/bookings/tests --configuration=phpunit.xml
```

For public booking workflow changes, include:

```bash
vendor/bin/pest packages/bookings/tests/Feature/PublicBookingRequestTest.php packages/bookings/tests/Unit/PublicBookingSlotActionsTest.php --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, health, public routes, Actions, renderer, docs, screenshots, and tests.
- [x] Capell audience pass completed for operators, service businesses, and developers.
- [x] Approved implementation slices shipped.
- [x] Focused Bookings verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
