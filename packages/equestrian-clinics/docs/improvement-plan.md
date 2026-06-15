# Equestrian Clinics - Improvement & Growth Plan

> Package: capell-app/equestrian-clinics · Kind: plugin · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Active

## 1. Snapshot

Equestrian Clinics is a domain-rich operations package for travelling coaches, riding schools, venues, riders, horses, waivers, payments, credits, facilities, waitlists, coach timetables, and public clinic discovery. It owns a large schema in one migration, public discovery/host-request/signed-coach timetable routes, Actions for booking/payment/waitlist/facility/horse-care workflows, model registration, rate limiting, screenshots, and focused tests. The package declares admin/frontend/console surfaces but currently contributes only model metadata, so the major gap is turning the domain engine into real admin and operational surfaces.

## 2. Improvements (existing functionality)

1. **Add admin resources or correct manifest/admin claims.** The package declares an admin surface but `capell.json contributes` only a model contribution and README notes no concrete Filament resource/page. Either add first-class resources for tour days, venues, riders, horses, bookings, waitlist, facility resources, and care tasks, or downgrade the manifest until admin exists. Evidence: `capell.json`, `src/Manifest/EquestrianClinicsModelsContribution.php`, `README.md`. - **L**

2. **Expand health diagnostics to routes, morph map, and payment dependencies.** Health checks tables/actions, but should also cover morph aliases, public routes, signed coach timetable route, Payments/Address/Bookings dependencies, rate limiter, and protected table registration. Evidence: `src/Health/EquestrianClinicsHealthCheck.php`, `src/Providers/EquestrianClinicsServiceProvider.php`, `routes/web.php`. - **M**

3. **Add lifecycle commands for holds and waitlists.** Actions exist for expiring slot holds and waitlist offers, but no console command/schedule is surfaced in `capell.json`. Add commands and tests so checkout holds and offers cannot linger. Evidence: `ExpireSlotBookingHoldsAction`, `ExpireWaitlistOffersAction`, `capell.json commands`. - **M**

4. **Verify public discovery privacy and query behavior.** Discovery returns venue details, facility notes, maps URLs, open slots, and heatmap data. Add public-output/query-budget tests proving only intended published tour-day data is exposed and no private rider/horse/waiver/payment data leaks. Evidence: `BuildClinicDiscoveryAction`, `resources/views/discovery.blade.php`, `tests/Feature/EquestrianClinicsPublicWorkflowTest.php`. - **M**

## 3. Missing Features (gaps)

Capabilities declared cover tour days, slot generation, public calendar discovery, payments, holds, waitlists, rider/family profiles, horse profiles, care tasks, health records, competitions, staff schedules, facilities, waivers, credits, products, billing export, coach mobile dashboards, host portal, marketing, analytics, and AI prompts.

- **No concrete admin resources.** This is the biggest product gap for a schema-heavy operations plugin.
- **No scheduled expiry commands.** Holds/waitlists require maintenance.
- **No payment checkout bridge visible in routes.** Actions model payment status, but user-facing checkout handoff needs clearer implementation and docs.
- **Marketplace media is card-only despite public PNG screenshots.** Promote route-backed PNGs only after visual verification.

## 4. Issues / Risks

1. **Critical gap: admin surface is declared but not implemented.** Operators need admin resources to manage the domain model. Recommended fix: add focused Filament resources for the core workflow or update manifest claims. - **P1**

2. **Important risk: operational holds/offers can linger without commands.** Recommended fix: package commands and optional schedule metadata. - **P2**

3. **Important risk: public discovery can leak sensitive domain details if data mapping expands.** Recommended fix: public-output and query-budget tests. - **P2**

4. **Improvement: marketplace proof is conservative but thin.** Recommended fix: verify and promote real public screenshots after public safety tests pass. - **P3**

## 5. Marketplace & Positioning

Equestrian Clinics should be positioned as a specialized vertical operations suite, not a generic booking theme. For operators, emphasize tour-day setup, rider/horse profiles, waivers, payments, waitlists, coach timetables, facilities, and day-of workflows. For developers, emphasize it builds on Bookings, Payments, Address, Events, Media, and Customer Portal.

**Current summary:** "Equestrian Clinics turns Capell into a full equestrian operations platform for travelling coaches, riding schools, clinics, venues, riders, horses, waivers, payments, credits, resources, and mobile day-of delivery."

**Improved summary:** "A vertical Capell operations suite for equestrian clinics, combining tour days, riders, horses, waivers, payments, waitlists, facilities, coach timetables, and public clinic discovery."

**Media status:** Keep Marketplace card-only until public discovery and coach timetable screenshots are verified against real route output and promoted intentionally.

**Cross-sell:** Bookings, Payments, Address, Customer Portal, Events, Media Library, Automation Studio, Theme Inertia Bookings.

## 6. Prioritized Roadmap

| Item                                                              | Bucket | Effort | Impact | Section ref |
| ----------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add core admin resources or correct manifest/admin claims         | Done   | L      | High   | §2.1, §4.1  |
| Expand health checks for routes, morph map, deps, and rate limits | Done   | M      | High   | §2.2        |
| Add expiry commands/schedule metadata for holds and waitlists     | Done   | M      | High   | §2.3, §4.2  |
| Add public discovery privacy and query-budget tests               | Done   | M      | High   | §2.4, §4.3  |
| Add payment checkout handoff docs/tests                           | Next   | M      | High   | §3          |
| Add customer portal rider/horse profile surfaces                  | Next   | L      | High   | §3, §5      |
| Promote verified public screenshots                               | Next   | S      | Medium | §3, §4.4    |
| Add coach mobile dashboard beyond signed timetable                | Later  | L      | Medium | §3, §5      |
| Add analytics/BI export and AI assistant workflows                | Later  | L      | Medium | §5          |

## 7. Verification

Implementation slices shipped the current Now rows. Re-run the package verification with:

```bash
vendor/bin/pest packages/equestrian-clinics/tests --configuration=phpunit.xml
```

For public and lifecycle changes, include:

```bash
vendor/bin/pest packages/equestrian-clinics/tests/Feature/EquestrianClinicsPublicWorkflowTest.php packages/equestrian-clinics/tests/Unit/EquestrianClinicsOperationsTest.php --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, routes, health, Actions, public views, manifest, docs, screenshots, and tests.
- [x] Capell audience pass completed for equestrian operators, developers, and buyers.
- [x] Approved implementation slices shipped.
- [x] Focused Equestrian Clinics verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
