# Changelog

All notable changes to `capell-app/theme-local-services` will be documented in this file.

## Unreleased

### 2026-06-04

- Moved default service-area coverage cards into package translations.
- Made service-area cards data-driven with real URLs and postcode labels instead of hardcoded districts.
- Removed dead `href="#"` service-area links, added the contact anchor used by defaults, and covered the fallback/custom render paths in tests.
- Hardened manifest tests so marketplace and provider boundaries fail with clearer typed assertions.

### 2026-06-03

- Rewrote marketplace summary and description to lead with quote requests, service-area coverage, and click-to-call trust, and aligned the Composer description with that buyer-facing copy.
- Replaced the stub critical health check with real diagnostics for the Theme Studio definition, required package files, manifest/provider wiring, and declared screenshot asset paths.
- Documented the intentional `default` runtime inheritance key in the service provider while keeping the manifest dependency on Foundation Theme.
