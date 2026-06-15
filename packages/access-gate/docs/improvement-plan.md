# Access Gate - Improvement & Growth Plan

> Package: capell-app/access-gate · Kind: package · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Active

## 1. Snapshot

Access Gate is a schema-owning operations package for page, download, member-area, guest-link, schedule, and paid-access gating. It owns admin resources for areas, registrations, grants, browser tokens, claim tokens, and events; public request, claim, logout, and optional status routes; customer-portal and payments bridges; diagnostics; announcement-bar hooks; and screenshot coverage for admin and public workflows. The code is already Action-heavy and has broad focused tests, but the remaining risk is operational depth: stale tokens/registrations/events can accumulate, duplicate public submissions need stronger idempotency guarantees, and the public cache/privacy boundaries should be documented as first-class product behavior.

## 2. Improvements (existing functionality)

1. **Add lifecycle pruning for stale access records.** Browser tokens, claim tokens, expired registrations, and audit events have status/expiry concepts, but there is no obvious package command for retention pruning. Add `PruneAccessGateRecordsAction`, a console command, config retention windows, and tests proving active grants are preserved. Evidence: `src/Models/BrowserToken.php`, `src/Models/ClaimToken.php`, `src/Models/Event.php`, `src/Actions/ExpireRegistrationAction.php`, `capell.json commands`. - **M**

2. **Harden duplicate public registration submission.** Public requests validate area/email and record metadata, but `SubmitAccessGatePublicAction` delegates creation without an explicit duplicate-pending policy at the public boundary. Add tests for repeated email+area submissions and either return the existing pending registration or produce a translated safe response. Evidence: `src/Actions/SubmitAccessGatePublicAction.php`, `src/Actions/CreateRegistrationAction.php`, `routes/web.php`. - **M**

3. **Deepen diagnostics beyond route/table checks.** `AccessGateHealthCheck` delegates to `AccessGateDiagnosticsService`, but health should explicitly cover configured identity methods, registration fields, public route throttles, payment fulfillment handler registration, customer portal provider registration, and announcement hook cache safety. Evidence: `src/Health/AccessGateHealthCheck.php`, `src/Providers/AccessGateServiceProvider.php`. - **M**

4. **Document public caching and privacy boundaries.** Public Blade views and announcement hooks are cache-sensitive. README/overview should state that anonymous gated responses may be cacheable only when no registration, grant, browser token, or admin metadata leaks into HTML. Evidence: `resources/views/request.blade.php`, `resources/views/message.blade.php`, `src/Support/RenderHooks/RegisterAnnouncementBarHook.php`. - **S**

## 3. Missing Features (gaps)

Capabilities declared: `access-gating`, `registration-approval`, `claim-token-management`, `paid-gated-access-fulfillment`, and `access-gate-customer-portal-gated-resource-feed`.

- **No retention automation.** Expired registrations and tokens can be marked unusable, but package-owned cleanup is not exposed as a command/schedule.
- **No export/report workflow for access audits.** Events are visible in admin, but support teams often need CSV evidence of access grants, denials, and revocations.
- **Limited user self-service.** Customer Portal integration exists, but browser-token revocation and active-grant visibility should be expanded for end users.
- **No plan-backed load/concurrency tests.** Approval limits and queued registrations are sensitive to race conditions; tests should cover concurrent approval batches.

## 4. Issues / Risks

1. **Important risk: stale tokens and events can accumulate indefinitely.** Access Gate stores security-relevant records, so retention should be explicit and test-backed. Recommended fix: add pruning Action/command plus config defaults. - **P2**

2. **Important risk: duplicate public requests can create noisy queues.** The public route is throttled, but product behavior should be deterministic for repeated email/area submissions. Recommended fix: idempotent pending-registration handling. - **P2**

3. **Important risk: cache and authoring boundaries are spread across code, docs, and tests.** Public output must not expose grants, model IDs, signed claim links, or editor surfaces. Recommended fix: add a dedicated public-output safety test matrix and document caching rules. - **P2**

4. **Improvement: paid-access fulfillment deserves more operator visibility.** The Payments bridge is valuable, but admins need a clear audit trail from checkout to grant. Recommended fix: add docs and tests linking fulfillment events to grant/event records. - **P3**

## 5. Marketplace & Positioning

Access Gate should be positioned as a serious access-control layer for Capell operators, not just a "password page" feature. For teams, emphasize controlled content launches, member areas, private downloads, paid access, and auditability. For developers, emphasize Actions, policy-backed admin resources, public-safe routes, and extension points for registration fields and access methods.

**Current summary:** "Gate any Capell page, download, or member area behind login, email approval, guest links, schedules, or paid checkout - with full request, grant, and audit management in the admin."

**Improved summary:** "Controlled access workflows for Capell pages, downloads, and member areas, with approvals, claim links, paid fulfillment, browser tokens, and audit history."

**Media status:** Marketplace media is strong and route-backed. Keep it current when lifecycle pruning or self-service flows are added.

**Cross-sell:** Payments for paid access, Customer Portal for user self-service, HTML Cache for safe gated-output behavior, Public Actions for embedded request forms.

## 6. Prioritized Roadmap

| Item                                                                        | Bucket | Effort | Impact | Section ref |
| --------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add retention pruning Action, command, config, and tests                    | Now    | M      | High   | §2.1, §4.1  |
| Make public registration submission idempotent for duplicate pending users  | Now    | M      | High   | §2.2, §4.2  |
| Expand health diagnostics for methods, fields, throttles, bridges, and hook | Now    | M      | Medium | §2.3        |
| Document cache/privacy boundaries for public gated output                   | Now    | S      | Medium | §2.4, §4.3  |
| Add audit CSV/export support                                                | Next   | M      | Medium | §3, §4.4    |
| Expand Customer Portal self-service for active grants and browser tokens    | Next   | M      | Medium | §3          |
| Add approval-limit concurrency coverage                                     | Next   | M      | High   | §3          |
| Add richer paid-access lifecycle reporting                                  | Later  | L      | Medium | §4.4, §5    |
| Add segmented launch/waitlist campaign templates                            | Later  | M      | Medium | §5          |

## 7. Verification

Plan-writing review only; no commands were run for this package in this pass. First implementation slice should start with:

```bash
vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml
```

For pruning or public-route changes, include:

```bash
vendor/bin/pest packages/access-gate/tests/Feature/AccessGateMiddlewareTest.php packages/access-gate/tests/Unit/Actions --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for routes, provider, Actions, health, docs, and public surfaces.
- [x] Capell audience pass completed for operators, developers, and buyers.
- [ ] Approved implementation slices shipped.
- [ ] Focused Access Gate verification passed.
- [ ] Package tests passed.
- [ ] Repo preflight passed for changed files.
