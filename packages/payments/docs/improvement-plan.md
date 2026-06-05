# Payments — Improvement & Growth Plan

> Package: capell-app/payments · Kind: package · Tier: premium · Product group: Capell Commerce · Bundle: commerce · Status: Draft

## 1. Snapshot

Payments is a provider-neutral payment record layer with a native Stripe Checkout foundation, surfacing on `admin`, `frontend`, and `console`. It ships eight Eloquent models / tables (`payment_customers`, `payment_checkout_sessions`, `payment_intents`, `payment_subscriptions`, `payment_webhook_events`, `payment_refunds`, `payment_disputes`, `payment_download_entitlements`) and seven read-only Filament resources, all driven through `lorisleiva/laravel-actions` and `spatie/laravel-data` boundary objects. Core flows: `CreateCheckoutSessionAction` → `StripePaymentGateway` (`src/Support/Gateways/StripePaymentGateway.php`), `HandleStripeWebhookAction` → `VerifyStripeWebhookSignatureAction` → record actions → `FulfillCompletedCheckoutSessionAction` dispatching tagged `PaymentFulfillmentHandler`s (`src/Actions/`, `src/Support/Fulfillment/PaidDownloadFulfillmentHandler.php`). Manifest declares `requires: [admin, core]`, `supports: [access-gate, customer-portal, form-builder]`, one critical health check (`Capell\Payments\Health\PaymentsHealthCheck`), `cacheSafety.cacheable: false / sensitiveOutput: true`, and `adminQueryBudget: 40`. Current marketplace summary verbatim: _"Provider-neutral payment records with native Stripe Checkout, subscriptions, form payments, paid downloads, and paid gated access fulfillment."_ Screenshots: **0** (`marketplace.screenshots: []`).

## 2. Improvements (existing functionality)

1. **Process webhooks on a queue, not in-request.** — `HandleStripeWebhookAction::handle` verifies the signature, opens a `DB::transaction`, writes N record rows, then runs every tagged fulfillment handler (which performs `Storage` I/O for paid downloads) — all synchronously inside the HTTP request before returning 2xx to Stripe. Stripe times out webhook deliveries at ~20s and retries on slow responses, amplifying load. Split into: persist `PaymentWebhookEvent` (status `Received`) + return 200 immediately, then dispatch a queued job to `processEvent`. — `src/Actions/HandleStripeWebhookAction.php`, `src/Http/Controllers/StripeWebhookController.php` — **M**

2. **Done/Shipped: Lock the webhook event row before processing to close the duplicate race.** `ProcessStripeWebhookEventAction` now performs the status read/guard inside the `lockForUpdate()` transaction and refreshes the row after rollback before recording failures. Coverage proves terminal `processed`/`ignored` events do not replay checkout recording or fulfillment side effects. — `src/Actions/ProcessStripeWebhookEventAction.php`, `tests/Unit/HandleStripeWebhookActionTest.php` — **S**

3. **Promote refund issuance and billing-portal creation into the `PaymentGateway` contract.** — The contract only declares `createCheckoutSession`. `CreateBillingPortalSessionAction` calls `Http::…->post('/v1/billing_portal/sessions')` directly, bypassing the gateway abstraction the package is built around. Anything provider-specific that isn't checkout leaks raw `Http::` calls into Actions, undermining the "provider-neutral" promise. Add `createBillingPortalSession()` (and future `issueRefund()`, `capturePaymentIntent()`) to the interface and route through the bound gateway. — `src/Contracts/PaymentGateway.php`, `src/Actions/CreateBillingPortalSessionAction.php` — **M**

4. **Done/Shipped: Cast `amount` to `integer` on every money-bearing model.** `PaymentRefund`, `PaymentIntent`, and `PaymentDispute` cast `amount` to `integer`; `CheckoutSession` casts `amount_subtotal` and `amount_total` to `integer`; subscriptions have no money columns, and paid-download entitlements only track non-money `download_count`. Evidence: `MoneyModelAmountCastTest` proves numeric database values hydrate as ints and nullable checkout amounts remain null. — `src/Models/PaymentRefund.php`, `src/Models/PaymentIntent.php`, `src/Models/PaymentDispute.php`, `src/Models/CheckoutSession.php`, `tests/Unit/MoneyModelAmountCastTest.php` — **S**

5. **Validate `success_url` / `cancel_url` against an allow-list before redirect.** — `CreateFormPaymentCheckoutUrlAction` carries caller-supplied `success_url`/`cancel_url` query params into a signed route; they become the Stripe redirect targets. Even behind a signed URL, an operator-generated link could be crafted to redirect post-payment to an attacker host (open-redirect / phishing). Constrain to the site's own host(s) or a configured allow-list. — `src/Actions/CreateFormPaymentCheckoutUrlAction.php`, `src/Http/Controllers/CreateFormPaymentCheckoutController.php` — **S**

6. **Done/Shipped: Make paid-download fulfillment fully re-delivery-safe (don't refresh expiry on replays).** Evidence: `GrantPaidDownloadAccessAction` only fills `expires_at` and `fulfilled_at` when creating a new `(checkout_session_id, download_key)` entitlement, while the webhook regression test proves repeated checkout fulfillment updates descriptors without extending access or resetting first fulfillment time. — `src/Actions/GrantPaidDownloadAccessAction.php`, `tests/Unit/HandleStripeWebhookActionTest.php` — **S**

7. **Record fulfillment results.** — `FulfillCompletedCheckoutSessionAction` returns a `list<PaymentFulfillmentResultData>` that is discarded by the webhook handler. Failures (`fulfilled: false, 'missing_download_reference'`) vanish silently — no audit trail, no admin visibility, no retry. Persist results (e.g. onto the checkout session or a fulfillment-attempt row) and surface failures in the health report. — `src/Actions/FulfillCompletedCheckoutSessionAction.php`, `src/Actions/HandleStripeWebhookAction.php` — **M**

8. **Add a webhook-replay / backfill console command.** — `surfaces` includes `console`, yet `src/Console/` does not exist. A `payments:webhooks:reprocess` command (re-run `processEvent` for `Failed` events) and a `payments:reconcile` command would make operational recovery possible without DB surgery. — new `src/Console/` — **M**

## 3. Missing Features (gaps)

Mapped against declared `capabilities[]` and payment-platform norms. Capabilities present: `stripe-checkout`, `one-off-payments`, `subscriptions`, `donations`, `paid-downloads`, `paid-gated-access`, `form-payment-fields`, `stripe-webhooks`, `refund-records`, `dispute-records`, `payments-admin`, `payment-fulfillment-handlers`, `stripe-billing-portal`, `subscription-entitlements`, `customer-portal-billing-dashboard`, `customer-portal-payments-feed`.

**Table-stakes gaps:**

- **Refund issuance (not just records).** `refund-records` only ingests `charge.refunded`/`refund.*` webhooks into `PaymentRefund`. There is no action or admin button to _initiate_ a refund (`POST /v1/refunds`). For a "premium" commerce package this is expected day-one functionality.
- **Partial captures / authorize-then-capture.** `CheckoutMode` supports `payment|subscription|setup` but there is no `capture_method=manual` path and no `capturePaymentIntent` action, despite `PaymentIntentStatus::RequiresCapture` being a modelled state.
- **Idempotency keys on outbound calls.** `StripePaymentGateway` forwards `Idempotency-Key` only when the caller supplies `CreateCheckoutSessionData::idempotencyKey` (often null). `CreateBillingPortalSessionAction` sends none. Generate deterministic keys per logical operation.
- **Reconciliation.** No mechanism to pull authoritative state from Stripe and detect drift (missed webhooks, stuck `Received`/`Open` rows). The health check counts events but cannot self-heal.
- **3DS / SCA surfacing.** Stripe Checkout handles SCA, but `PaymentIntentStatus::RequiresAction` is recorded with no follow-up signalling to the customer portal — a stalled payment is invisible to the buyer.
- **Multi-currency presentation.** Amounts are stored as integer minor units with a `currency` column (correct), but there is no money-formatting/zero-decimal-currency helper; Filament renders `amount` as a raw `->numeric()` integer (e.g. `1099` not `£10.99`), and JPY/zero-decimal currencies will display wrong.

**Differentiators (beyond table-stakes):**

- **Second gateway** (PayPal / Mollie / GoCardless direct debit). The provider-neutral records and `PaymentProvider` enum are explicitly designed for this, but only `Stripe` exists. A second driver is the single biggest proof of the "provider-neutral" claim.
- **Invoices / receipts.** No PDF receipt or invoice generation on completed payment — high-value for donations and paid downloads.
- **Tax (Stripe Tax / VAT).** No tax collection or recording; `amount_subtotal` vs `amount_total` are stored but tax breakdown is not modelled.
- **Dunning / subscription recovery.** `PastDue` is modelled but there is no retry/notify workflow.

## 4. Issues / Risks

- **Done/Shipped: Webhook duplicate-processing guard now runs under a row lock.** `ProcessStripeWebhookEventAction` locks the webhook row before checking terminal status, so duplicate deliveries cannot both pass the `Received` guard before processing. SQLite package tests cover the terminal-state contract; true blocking semantics still depend on the production database engine's `lockForUpdate()` support. — `src/Actions/ProcessStripeWebhookEventAction.php`, `tests/Unit/HandleStripeWebhookActionTest.php`
- **Failed webhook is recorded `Failed` and rethrown.** On `processEvent` exception the handler sets status `Failed` then re-throws, so the controller returns 200 only on success and Stripe retries — correct behaviour, but combined with synchronous processing (§2.1) a persistently failing handler will hammer the endpoint. — `src/Actions/HandleStripeWebhookAction.php`
- **Done/Shipped: Feature and Arch test suites now cover the public-route and package-boundary invariants.** `PaidDownloadRouteTest` covers signed paid-download success, unsigned rejection, expired-entitlement 410 responses, and missing-file 404 responses; `PaymentsPackageBoundaryTest` covers thin controllers, sensitive/non-cacheable frontend manifest settings, public-output authoring-marker absence, and strict-equality usage. The idempotency test (`HandleStripeWebhookActionTest.php:69`) still asserts _sequential_ redelivery only, not the concurrent race. — `tests/Feature/PaidDownloadRouteTest.php`, `tests/Arch/PaymentsPackageBoundaryTest.php`
- **Done/Shipped: Money amount casts match runtime arithmetic expectations.** `PaymentRefund`, `PaymentIntent`, `PaymentDispute`, and `CheckoutSession` explicitly cast amount fields to `integer`; `MoneyModelAmountCastTest` hydrates string-persisted numeric values and asserts strict integer values. — `src/Models/PaymentRefund.php`, `src/Models/PaymentIntent.php`, `src/Models/PaymentDispute.php`, `src/Models/CheckoutSession.php`, `tests/Unit/MoneyModelAmountCastTest.php`
- **Open-redirect via form-checkout return URLs** — see §2.5. — `src/Actions/CreateFormPaymentCheckoutUrlAction.php`
- **Money handling is otherwise correct.** Amounts are integer minor units end-to-end (`unsignedBigInteger`, `int`), Stripe `unit_amount` is sent as integer, no float arithmetic observed. Stripe `amount_subtotal`/`amount_total` mapped via `is_int`/`integerValue` guards. No rounding logic to audit (no division). Good.
- **Secrets & PII handling is strong.** Webhook signature uses `hash_hmac` + `hash_equals` with a 300s timestamp tolerance (`VerifyStripeWebhookSignatureAction`); secrets resolved via settings/env, never hard-coded; `provider_payload`/`payload` are `encrypted:array` on all models and `PaymentCustomer.email` / `PaymentDownloadEntitlement.email` are `encrypted`; `signature_header_hash` stores a SHA-256 of the header, not the header. Tables are registered via `CapellCore::registerProtectedTable`.
- **Public-output safety holds.** All Filament resources expose only an `index` List page (no Create/Edit/View pages); policies extend `AbstractPaymentsResourcePolicy`; manifest grants only `View:*` permissions. Customer-portal providers return hydrated `PortalDashboardItemData`/self-service items and resolve via Actions; controllers redirect to provider URLs rather than rendering internals. No public Blade in the package. Consistent with `cacheSafety.cacheable: false`.
- **Performance budget caveat.** Manifest sets `frontendRenderBudgetMs: 0` (no frontend render) and `adminQueryBudget: 40`. The customer-portal dashboard provider issues 2 `subscriptions()` aggregate queries per account (`description()` + `activeSubscriptionCount()`) — fine for one account, but if a portal renders many providers this should be consolidated. Cite `src/Support/CustomerPortal/PaymentsPortalDashboardItemProvider.php`.
- **i18n.** Enums and Filament labels are translated (`capell-payments::generic.*`), `resources/lang/en/{generic,settings}.php` present. Health-report `$issues[]` strings (`'Stripe secret key is not configured.'`) are hard-coded English — `src/Actions/BuildPaymentsHealthReportAction.php`.

## 5. Marketplace & Selling

**Critique.** The marketplace `summary` is an accurate feature list but reads as a spec line, not a sell — it leads with "Provider-neutral payment records," an internal-architecture phrase that means nothing to a buyer, and never states the benefit (accept money on a Capell site without bolting on Shopify). The composer `description` ("Payments and Stripe checkout foundation for Capell CMS.") is weaker still and contradicts the manifest's "provider-neutral" framing by naming only Stripe.

**Improved summary (1 sentence):**

> Take one-off payments, donations, subscriptions, paid downloads, and gated access on any Capell site through Stripe Checkout — with a provider-neutral record layer built to add more gateways later.

**Improved description (3–4 sentences):**

> Payments gives Capell a first-party way to charge customers without depending on an external store. It ships native Stripe Checkout for one-off purchases, recurring subscriptions, donations, paid file downloads, and paid gated access, plus signed webhook intake that keeps payments, subscriptions, refunds, and disputes in sync automatically. Every record is stored behind a provider-neutral model layer, so admins get read-only audit resources, customers get a self-service billing portal, and Form Builder fields can collect payment inline. Designed for revenue-generating sites that want checkout, fulfilment, and reconciliation handled inside the CMS.

**Screenshot / media gaps (critical — currently 0):** add at minimum (1) the admin Payments/Monitoring resource list (checkout sessions or payment intents) showing statuses; (2) the webhook events resource with a healthy/freshness state; (3) the Settings → Payments Stripe configuration screen; (4) the Customer Portal billing dashboard item; (5) a Form Builder form with a payment field → Checkout redirect. A short GIF of the checkout-to-fulfilment flow would carry the listing.

**Pricing / tier / bundle positioning.** `tier: premium`, `bundle: commerce`, license `paid`, certification `first-party`, support `priority` — appropriate for a money-touching package. Position as the entry point to **Capell Commerce** for sites that don't need full Shopify: cheaper/simpler than the `shopify-commerce` path, upgradeable to it.

**Cross-sell (via `supports[]` + Extension Suites):**

- **form-builder** — payment fields turn any form into a paid form/donation (already wired via `CreateFormPaymentCheckoutUrlAction`); headline this in the form-builder listing.
- **access-gate** — "pay to unlock" gated content via the gated-access fulfilment handler.
- **customer-portal** — billing/subscription/payment-history self-service (dashboard + self-service item providers already registered).
- **shopify-commerce** — position as the lightweight alternative and the natural upgrade target; cross-link both ways.

**Differentiators / value props / target buyer.** Value: native checkout + automatic webhook reconciliation + read-only audit trail, all inside Capell, no separate storefront. Differentiator vs `shopify-commerce`: no external platform, no product-catalog overhead, donations and paid-downloads first-class. Target buyer: charities/non-profits (donations), creators/publishers (paid downloads, gated access), and SMB sites taking occasional payments or subscriptions.

**Keywords/tags:** `stripe`, `checkout`, `payments`, `subscriptions`, `donations`, `paid-downloads`, `gated-access`, `webhooks`, `billing-portal`, `recurring-billing`, `form-payments`, `commerce`.

## 6. Prioritized Roadmap

| Item                                                                                          | Bucket | Effort | Impact | Section ref |
| --------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Lock webhook event row before processing (close duplicate race)                               | Done | S | High | §2.2, §4 — closed by locked processing guard and terminal-event no-replay coverage |
| Done/Shipped: Add Feature + Arch test suites (signed download and public-output safety). Evidence: `PaidDownloadRouteTest` covers signed download paths and `PaymentsPackageBoundaryTest` covers controller/package-boundary guardrails. | Done | M | High | §4 |
| Add marketplace screenshots/GIF (currently 0)                                                 | Now    | S      | High   | §5          |
| Rewrite marketplace summary + composer description                                            | Now    | S      | Medium | §5          |
| Done/Shipped: Cast `amount` to integer across money models. Evidence: model casts cover refunds, intents, disputes, and checkout subtotal/total fields; `MoneyModelAmountCastTest` proves numeric DB values hydrate as ints. | Done | S | Medium | §2.4, §4 |
| Done/Shipped: Validate form-checkout `success_url`/`cancel_url` (open-redirect). Evidence: `ValidateFormPaymentReturnUrlAction` allows local paths, requires absolute URLs to use trusted hosts, and rejects foreign/private/internal redirects before signed checkout URLs reach Stripe. | Done | S | High | §2.5, §4 |
| Done/Shipped: Make paid-download fulfilment expiry replay-safe. Evidence: `HandleStripeWebhookActionTest` replays paid-download checkout fulfilment for the same provider session and proves the existing entitlement keeps its original `expires_at` and `fulfilled_at` while new entitlements still receive expiry. | Done | S | Medium | §2.6 |
| Move webhook processing to a queued job                                                       | Next   | M      | High   | §2.1        |
| Implement refund issuance (action + admin button)                                             | Next   | M      | High   | §3          |
| Persist fulfilment results + surface failures in health report                                | Next   | M      | Medium | §2.7, §3    |
| Generate idempotency keys for all outbound gateway calls                                      | Next   | S      | Medium | §3          |
| Money-formatting helper (zero-decimal currencies) for admin/portal                            | Next   | S      | Medium | §3, §4      |
| Webhook reprocess / reconcile console commands                                                | Next   | M      | Medium | §2.8, §3    |
| Promote billing-portal/refund/capture into `PaymentGateway` contract                          | Later  | M      | Medium | §2.3, §3    |
| Add a second gateway driver (proves provider-neutral claim)                                   | Later  | L      | High   | §3, §5      |
| Invoices/receipts + Stripe Tax recording                                                      | Later  | L      | Medium | §3          |
