# Access Gate - Improvement & Growth Plan

> Package: capell-app/access-gate · Kind: package · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Active

## 1. Snapshot

Access Gate is a schema-owning operations package for page, download, member-area, guest-link, schedule, and paid-access gating. It owns admin resources for areas, registrations, grants, browser tokens, claim tokens, and events; public request, claim, logout, and optional status routes; customer-portal and payments bridges; diagnostics; announcement-bar hooks; and screenshot coverage for admin and public workflows. The paid-access checkout slice is now explicit: Access Gate creates Payments `gated_access` checkout sessions for registrations, and the Payments fulfillment handler approves the registration after completion. The current operations wave has also added retention pruning, duplicate public-request idempotency, broader diagnostics, documented public cache/privacy boundaries, CSV audit export for support evidence, Customer Portal visibility for active browser access, and approval-limit regression coverage.

## 2. Improvements (existing functionality)

1. **Add lifecycle pruning for stale access records.** Browser tokens, claim tokens, expired registrations, and audit events have status/expiry concepts, but there is no obvious package command for retention pruning. Add `PruneAccessGateRecordsAction`, a console command, config retention windows, and tests proving active grants are preserved. Evidence: `src/Models/BrowserToken.php`, `src/Models/ClaimToken.php`, `src/Models/Event.php`, `src/Actions/ExpireRegistrationAction.php`, `capell.json commands`. - **M**

2. **Harden duplicate public registration submission.** Public requests validate area/email and record metadata, but `SubmitAccessGatePublicAction` delegates creation without an explicit duplicate-pending policy at the public boundary. Add tests for repeated email+area submissions and either return the existing pending registration or produce a translated safe response. Evidence: `src/Actions/SubmitAccessGatePublicAction.php`, `src/Actions/CreateRegistrationAction.php`, `routes/web.php`. - **M**

3. **Deepen diagnostics beyond route/table checks.** `AccessGateHealthCheck` delegates to `AccessGateDiagnosticsService`, but health should explicitly cover configured identity methods, registration fields, public route throttles, payment fulfillment handler registration, customer portal provider registration, and announcement hook cache safety. Evidence: `src/Health/AccessGateHealthCheck.php`, `src/Providers/AccessGateServiceProvider.php`. - **M**

4. **Document public caching and privacy boundaries.** Public Blade views and announcement hooks are cache-sensitive. README/overview should state that anonymous gated responses may be cacheable only when no registration, grant, browser token, or admin metadata leaks into HTML. Evidence: `resources/views/request.blade.php`, `resources/views/message.blade.php`, `src/Support/RenderHooks/RegisterAnnouncementBarHook.php`. - **S**

5. **Done/Shipped: paid-access checkout creation manifest gap is closed.** `CreatePaidAccessCheckoutForRegistrationAction` creates a Payments `gated_access` checkout session from a concrete registration with stable idempotency, registration metadata, and input validation; `PaidAccessCheckoutCreationContribution` is declared in manifest actions for traceability; `contributionTraceability.deferredContributions` is empty. Evidence: `src/Actions/CreatePaidAccessCheckoutForRegistrationAction.php`, `src/Data/CreatePaidAccessCheckoutData.php`, `src/Manifest/PaidAccessCheckoutCreationContribution.php`, `capell.json`, `tests/Unit/Payments/AccessGatePaymentFulfillmentHandlerTest.php`, `tests/Unit/ManifestRequirementsTest.php`. - **S**

## 3. Missing Features (gaps)

Capabilities declared: `access-gating`, `registration-approval`, `claim-token-management`, `paid-gated-access-checkout-creation`, `paid-gated-access-fulfillment`, and `access-gate-customer-portal-gated-resource-feed`.

- **No retention automation.** Expired registrations and tokens can be marked unusable, but package-owned cleanup is not exposed as a command/schedule.
- **Done/Shipped: audit CSV export support.** `capell:access-gate-audit-export` exports events with area, registration, grant, token, actor, subject, payload, and metadata columns; operators can filter by area key, event type, date range, and limit, then write to a path or stdout.
- **Done/Shipped: Customer Portal active access visibility.** Customer Portal self-service items now include active grants, pending registrations, and active browser-token sessions scoped to the portal account email and site; revoked and expired browser tokens stay out of the feed.
- **Done/Shipped: approval-limit batch coverage.** Repeated approval batches and claimed registrations are now covered so approval limits cannot silently over-approve queued registrations.

## 4. Issues / Risks

1. **Important risk: stale tokens and events can accumulate indefinitely.** Access Gate stores security-relevant records, so retention should be explicit and test-backed. Recommended fix: add pruning Action/command plus config defaults. - **P2**

2. **Important risk: duplicate public requests can create noisy queues.** The public route is throttled, but product behavior should be deterministic for repeated email/area submissions. Recommended fix: idempotent pending-registration handling. - **P2**

3. **Important risk: cache and authoring boundaries are spread across code, docs, and tests.** Public output must not expose grants, model IDs, signed claim links, or editor surfaces. Recommended fix: add a dedicated public-output safety test matrix and document caching rules. - **P2**

4. **Improvement: paid-access fulfillment deserves more operator visibility.** The Payments bridge now has explicit checkout creation and fulfillment tests, but admins still need richer reporting from checkout to grant. Recommended fix: add operator-facing lifecycle reporting once Payments exposes the desired reporting seam. - **P3**

## 5. Marketplace & Positioning

Access Gate should be positioned as a serious access-control layer for Capell operators, not just a "password page" feature. For teams, emphasize controlled content launches, member areas, private downloads, paid access through Payments, and auditability. For developers, emphasize Actions, policy-backed admin resources, public-safe routes, and extension points for registration fields, access methods, and Payments-backed checkout handoff.

**Current summary:** "Gate any Capell page, download, or member area behind login, email approval, guest links, schedules, or paid checkout - with full request, grant, and audit management in the admin."

**Improved summary:** "Controlled access workflows for Capell pages, downloads, and member areas, with approvals, claim links, paid fulfillment, browser tokens, and audit history."

**Media status:** Marketplace media is strong and route-backed. Keep it current when lifecycle pruning or self-service flows are added.

**Cross-sell:** Payments for paid access, Customer Portal for user self-service, HTML Cache for safe gated-output behavior, Public Actions for embedded request forms.

## 6. Prioritized Roadmap

| Item                                                                        | Bucket | Effort | Impact | Section ref |
| --------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add retention pruning Action, command, config, and tests                    | Done   | M      | High   | §2.1, §4.1  |
| Make public registration submission idempotent for duplicate pending users  | Done   | M      | High   | §2.2, §4.2  |
| Expand health diagnostics for methods, fields, throttles, bridges, and hook | Done   | M      | Medium | §2.3        |
| Document cache/privacy boundaries for public gated output                   | Done   | S      | Medium | §2.4, §4.3  |
| Add audit CSV/export support                                                | Done   | M      | Medium | §3, §4.4    |
| Expand Customer Portal self-service for active grants and browser tokens    | Done   | M      | Medium | §3          |
| Add approval-limit concurrency coverage                                     | Done   | M      | High   | §3          |
| Add richer paid-access lifecycle reporting                                  | Later  | L      | Medium | §4.4, §5    |
| Close paid-access checkout creation manifest gap                            | Done   | S      | High   | §2.5        |
| Add segmented launch/waitlist campaign templates                            | Later  | M      | Medium | §5          |

## 7. Verification

Implementation slice 1 exposed the already-shipped models, public routes, console commands, dashboard widget, and health check as manifest contributions, and hardened provider model registration for Core discovery. Verify with:

```bash
vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml
```

For pruning or public-route changes, include:

```bash
vendor/bin/pest packages/access-gate/tests/Feature/AccessGateMiddlewareTest.php packages/access-gate/tests/Unit/Actions --configuration=phpunit.xml
```

For audit export changes, include:

```bash
vendor/bin/pest packages/access-gate/tests/Feature/AccessGateDoctorCommandTest.php --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for routes, provider, Actions, health, docs, and public surfaces.
- [x] Capell audience pass completed for operators, developers, and buyers.
- [x] Approved implementation slice 1 shipped: manifest contribution metadata and Core model registration.
- [x] Focused Access Gate verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
