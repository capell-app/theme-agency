# Changelog

All notable changes to `capell-app/url-manager` will be documented in this file.

## Unreleased

### 2026-06-04

- Added URL Manager configuration for redirect status codes, absolute target host allowlists, regex limits, hit retention, 404 capture, and canonical URL policy.
- Normalized redirect match paths by lowercasing and stripping query strings while preserving query reattachment at response time.
- Added open-redirect host protection, regex source bounds, exact-loop detection, priority ordering, deferred hit recording, parent-move prefix redirects, canonical URL building, and redirect-hit pruning.
- Registered frontend 404 capture middleware through the Capell frontend middleware registry.
- Documented the new runtime behavior, config, canonical URL policy, 404 capture, and retention command.

### 2026-06-03

- Rewrote the package and marketplace descriptions around recovering broken-link traffic, preserving moved page URLs, and editor-friendly redirect hygiene.
- Promoted the existing URL Manager PNG screenshots into `capell.json` marketplace media.
- Replaced the API-version-only health check with diagnostics for redirect tables, manifest-declared action classes, and provider/table metadata.
- Added tests for the health diagnostics and updated manifest copy/media expectations.
