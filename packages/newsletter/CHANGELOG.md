# Changelog

All notable changes to `capell-app/newsletter` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Implemented real diagnostics in `NewsletterHealthCheck` for the four advertised manifest health checks (form subscription capture, provider sync retry storage, provider webhook idempotency, and segment evaluation), replacing the previous no-op stub.
- Bound the Form Builder `FormSubmitted` listener directly instead of building the event class name from a string, so subscription capture no longer fails silently if Form Builder internals change.
- Rewrote the marketplace summary and package descriptions (`capell.json`, `composer.json`) to lead with the capture-confirm-segment-sync value proposition and aligned the keyword set.
