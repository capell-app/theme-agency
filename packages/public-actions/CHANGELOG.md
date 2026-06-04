# Changelog

All notable changes to `capell-app/public-actions` will be documented in this file.

## Unreleased

- Hardened HTTP webhook dispatch against DNS rebinding and unchecked redirects by resolving destination hosts once, blocking unresolved or private addresses by default, pinning cURL to the validated address, disabling redirect following, and covering private/link-local/internal redirect targets.
- Reused the canonical encoded webhook payload for request hashes, signatures, and non-GET request bodies.
- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Replaced the stub `PublicActionsHealthCheck` with real diagnostics covering the four advertised health keys: webhook dispatch storage and adapter availability, webhook SSRF guard configuration, provider preset normalisation (Zapier, Pipedream, n8n, Make, generic), and the Form Builder bridge listener.
- Sharpened the marketplace summary, manifest description, and composer description to lead with signed, retried, fully-audited webhooks to Zapier, Make, n8n, and Pipedream.
