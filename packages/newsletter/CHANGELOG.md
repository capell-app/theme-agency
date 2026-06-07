# Changelog

All notable changes to `capell-app/newsletter` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
- Added expiry to unsubscribe and preference-center public tokens via `capell-newsletter.public_tokens.token_expiry_hours`.
- Added RFC 8058 List-Unsubscribe headers and a one-click unsubscribe POST route backed by the existing token burn, consent ledger, provider sync, Contacts sync, and lifecycle event flow.
- Enforced global suppression for suppressed, bounced, and complained subscribers so provider webhook outcomes block later re-subscribe attempts and provider sync fan-out.
- Added `SubscriberConfirmed` and `SubscriberUnsubscribed` domain events so automation packages can react to public newsletter lifecycle changes.
- Hardened the Fake provider adapter so webhook verification is only accepted in local/testing environments unless `capell-newsletter.webhooks.allow_fake_provider` is explicitly enabled.

## 2026-06-03

- Implemented real diagnostics in `NewsletterHealthCheck` for the four advertised manifest health checks (form subscription capture, provider sync retry storage, provider webhook idempotency, and segment evaluation), replacing the previous no-op stub.
- Bound the Form Builder `FormSubmitted` listener directly instead of building the event class name from a string, so subscription capture no longer fails silently if Form Builder internals change.
- Rewrote the marketplace summary and package descriptions (`capell.json`, `composer.json`) to lead with the capture-confirm-segment-sync value proposition and aligned the keyword set.
