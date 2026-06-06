# Payments Overview

Payments gives Capell a general payment layer beyond Shopify Commerce. It stores provider-neutral records while shipping native Stripe Checkout support for:

- One-off payments
- Subscriptions
- Donations
- Paid downloads
- Paid gated access
- Form Builder payment fields

## Integrations

- Form Builder submissions can resolve payment fields into signed Checkout URLs. Custom success and cancel URLs must use the app host or a host listed in `capell-payments.form_builder.allowed_return_hosts`; unsafe external return URLs are rejected before Stripe Checkout is created.
- Access Gate registrations can be approved by the paid gated-access fulfillment handler.
- Customer Portal can show billing, subscriptions, completed payments, and paid downloads.
- Stripe webhooks keep checkout, intent, subscription, refund, and dispute records synchronized. Intake verifies the signature and stores a `received` event before queueing `ProcessStripeWebhookEventJob`; the job locks the stored event row before processing so duplicate deliveries cannot run fulfillment concurrently. Use `CAPELL_PAYMENTS_WEBHOOK_QUEUE` or `capell-payments.webhooks.queue` to route webhook work to a dedicated queue; it defaults to `payments`.
- Paid download fulfillment is replay-safe: redelivered checkout-complete webhooks reuse the existing entitlement without extending the original expiry window.

## Traceability

The package manifest declares admin resources, payment models, frontend/payment routes, required tables, public actions, and capabilities. `contributionTraceability.deferredContributions` is intentionally empty because the current native payment scope is implemented by package-owned code or guarded optional-package integrations.

## Screenshot Coverage

The committed Capell runner-backed screenshot gallery covers checkout session monitoring, Stripe webhook event health, Payments settings, customer portal billing, and a Form Builder payment checkout handoff.
