# Changelog

All notable changes to `capell-app/theme-portfolio` will be documented in this file.

## Unreleased

### 2026-06-04

- Moved newsletter form chrome into translations.
- Stopped rendering an inert `action="#"` newsletter form when no capture action is provided.
- Added hydrated newsletter form rendering for real `formAction`/`formMethod` section data.
- Covered static and hydrated newsletter render paths in package tests.

### 2026-06-03

- Rewrote marketplace summary and description to lead with creator and consultant case-study outcomes, and aligned the Composer description with that buyer-facing copy.
- Replaced the stub critical health check with real diagnostics for the Theme Studio definition, required package files, manifest/provider wiring, and declared screenshot asset paths.
- Documented the intentional `default` runtime inheritance key in the service provider while keeping the manifest dependency on Foundation Theme.
