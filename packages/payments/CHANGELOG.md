# Changelog

All notable changes to `capell-app/payments` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Added explicit `integer` casts for the money-bearing minor-unit columns (`amount` on `PaymentRefund`, `PaymentIntent`, and `PaymentDispute`; `amount_subtotal` and `amount_total` on `CheckoutSession`) so runtime values match their `int` docblocks instead of arriving as numeric strings, while preserving the nullable checkout totals. Covered by a new `MoneyModelAmountCastTest`.
- Rewrote the marketplace summary and the manifest/composer descriptions to lead with the buyer outcome (charge customers on any Capell site, no external store) instead of the internal "provider-neutral payment records" phrasing.
