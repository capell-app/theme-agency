# Payments Overview

Payments gives Capell a general payment layer beyond Shopify Commerce. It stores provider-neutral records while shipping native Stripe Checkout support for:

- One-off payments
- Subscriptions
- Donations
- Paid downloads
- Paid gated access
- Form Builder payment fields

## Integrations

- Form Builder submissions can resolve payment fields into signed Checkout URLs.
- Access Gate registrations can be approved by the paid gated-access fulfillment handler.
- Customer Portal can show billing, subscriptions, completed payments, and paid downloads.
- Stripe webhooks keep checkout, intent, subscription, refund, and dispute records synchronized.

## Traceability

The package manifest declares admin resources, payment models, frontend/payment routes, required tables, public actions, and capabilities. `contributionTraceability.deferredContributions` is intentionally empty because the current native payment scope is implemented by package-owned code or guarded optional-package integrations.
