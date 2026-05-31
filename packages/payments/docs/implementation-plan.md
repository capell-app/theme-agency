# Payments Package Implementation Plan

## Current Foundation

This package owns the general Capell payment layer. Shopify Commerce remains a catalog sync integration and should not become the payment abstraction.

Implemented in this first slice:

- Provider-neutral payment records for customers, checkout sessions, payment intents, and subscriptions.
- Typed Data objects and backed enums for checkout creation, session recording, intents, subscriptions, providers, statuses, and payment purpose.
- `PaymentGateway` contract and a Stripe Checkout implementation using Laravel HTTP, not a new Stripe SDK dependency.
- Actions for creating and recording checkout sessions and recording provider-returned payment intents/subscriptions.
- Generic references for paid downloads, gated access, donation flows, and form submissions through `payable_*`, `source_*`, `billable_*`, and `reference_id` columns.

## Next Slices

1. Stripe webhooks:
    - Add signed webhook route and controller.
    - Verify Stripe signatures with `STRIPE_WEBHOOK_SECRET`.
    - Record `checkout.session.completed`, `payment_intent.*`, `customer.subscription.*`, and refund/dispute events.
    - Add idempotent event storage before mutating domain records.

2. Access fulfilment:
    - Define package-local fulfilment contracts for paid downloads and gated access.
    - Consume completed checkout sessions, then call package extension points rather than importing Access Gate internals.
    - Add tests proving public/cached output does not expose payment or admin internals.

3. Form Builder integration:
    - Add an optional form payment field contribution from this package.
    - Keep Form Builder unchanged until the payment package has a stable extension contract.
    - Store only portable payment field state in form definitions.

4. Admin surface:
    - Add read-only payment/customer/session resources.
    - Add settings schema for Stripe keys and webhook health.
    - Use translations for all labels.

5. Subscription management:
    - Add Stripe Customer Portal session action for self-service billing changes.
    - Add subscription entitlement resolver for active/trialing states.

6. Installation and diagnostics:
    - Add install/doctor actions.
    - Register health checks for key configuration, webhook freshness, and failed payment events.
