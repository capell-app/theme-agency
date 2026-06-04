# Capell Payments

Payments is the Capell payment layer for provider-neutral payment records and native Stripe Checkout flows.

## Included Capabilities

- Stripe Checkout sessions for one-off payments, donations, subscriptions, paid downloads, paid gated access, and Form Builder payment fields.
- Stripe webhook intake for checkout sessions, payment intents, subscriptions, refunds, and disputes.
- Read-only admin resources for payment customers, checkout sessions, payment intents, subscriptions, webhook events, refunds, and disputes.
- Paid download entitlements with signed download URLs.
- Access Gate fulfillment through payment completion.
- Customer Portal billing, payment history, subscription, and paid download self-service items.

## Package Boundaries

Payments owns payment records, Stripe gateway calls, webhook processing, and fulfillment dispatch. Owning packages contribute fulfillment handlers for package-specific outcomes, such as Access Gate granting paid access after a completed gated-access checkout session.

Public routes are signed or webhook-only and must not expose Filament, package internals, or authoring metadata.

Form Builder checkout return URLs are host allow-listed. The app host is accepted by default; add extra trusted hosts through `capell-payments.form_builder.allowed_return_hosts`.

Stripe webhook requests verify the signature, persist a `received` webhook event, and queue `ProcessStripeWebhookEventJob` after commit so Stripe can receive a fast 2xx response. Processing locks the stored event row before mutating payment records or running fulfillment, and paid-download redeliveries do not extend existing entitlement expiry windows. Route this work with `CAPELL_PAYMENTS_WEBHOOK_QUEUE` or `capell-payments.webhooks.queue`; the default queue name is `payments`.
