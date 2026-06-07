# Changelog

All notable changes to `capell-app/frontend-authoring` will be documented in this file.

## Unreleased

- Documented and covered the admin-only content HTML policy: frontend authoring stores trusted admin `content` HTML exactly as submitted.
- Replaced editable-region field/type/surface/status string comparisons with backed enums while preserving the public payload values used by signed editor URLs.
- Extracted beacon response assembly into `BuildBeaconResponseAction` so the controller delegates admin manifest, page resolution, origin checks, and script rendering to an Action.
- Added translated Diagnostics messages and focused failure-mode coverage for the real `FrontendAuthoringHealthCheck` probes.
- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Replaced the stub `FrontendAuthoringHealthCheck` with real probes (beacon route registration, registry/signer resolvability, configuration readability, and signing-secret availability) so the `critical` diagnostics severity reflects actual surface health.
- Documented the signed-payload editor flow: HMAC-signed region payloads (`EditableRegionSigner`) drive the `auth`+`signed` edit route and are re-validated against the live manifest on load and save.
- Documented beacon origin hardening: the beacon short-circuits to a bare CSRF token for anonymous users, non-admins, cross-origin requests, and posted-URL/origin mismatches before any database work.
- Documented the optional approval-workspace save flow used when inline edits are routed through publishing for review.
- Sharpened marketplace and composer copy to outcome-led messaging and promoted real desktop and mobile authoring captures into the marketplace screenshots.
- Added a focused `EditableRegionSigner` unit test covering tampered and malformed payload rejection.
- Removed a stray duplicate screenshot asset nested under `packages/frontend-authoring/` inside the package.
