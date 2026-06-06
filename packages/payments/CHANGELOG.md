# Changelog

All notable changes to `capell-app/payments` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
- Locked stored Stripe webhook events before processing so duplicate deliveries cannot concurrently run fulfillment for the same provider event.
- Added host allow-list validation for Form Builder payment checkout success/cancel return URLs via `capell-payments.form_builder.allowed_return_hosts`.
- Made paid-download fulfillment replay-safe by preserving the original entitlement expiry and fulfilled timestamp on duplicate checkout-complete deliveries.
- Generated deterministic Stripe idempotency keys for checkout sessions and billing portal sessions when callers do not supply one.
- Added a money-formatting helper for decimal and zero-decimal currencies, and applied it to payment admin tables plus customer portal payment descriptions.
- Added `capell:payments:webhooks:reprocess` and `capell:payments:webhooks:reconcile` console commands for stored webhook recovery and stale-event diagnostics.
- Added Stripe refund issuance through `IssuePaymentRefundAction` and a guarded Payment Intent admin row action.

## 2026-06-03

- Added explicit `integer` casts for the money-bearing minor-unit columns (`amount` on `PaymentRefund`, `PaymentIntent`, and `PaymentDispute`; `amount_subtotal` and `amount_total` on `CheckoutSession`) so runtime values match their `int` docblocks instead of arriving as numeric strings, while preserving the nullable checkout totals. Covered by a new `MoneyModelAmountCastTest`.
- Rewrote the marketplace summary and the manifest/composer descriptions to lead with the buyer outcome (charge customers on any Capell site, no external store) instead of the internal "provider-neutral payment records" phrasing.
