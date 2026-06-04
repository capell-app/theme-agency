# Changelog

All notable changes to `capell-app/theme-nonprofit` will be documented in this file.

## Unreleased

### 2026-06-04

- Added the missing `main-content` target so the public skip link has a real destination.
- Moved the events section eyebrow into translations and covered it in render tests.
- Removed public Blade package introspection from the campaigns section; optional package state now comes from the provider/renderer layer only.
- Extended public-output tests to guard against future package checks inside Blade.

### 2026-06-03

- Rewrote marketplace summary and description to lead with the charity, NGO, and civic supporter journey, and aligned the Composer description with that buyer-facing copy.
- Replaced the stub critical health check with real diagnostics for the Theme Studio definition, required package files, manifest/provider wiring, and declared screenshot asset paths.
- Documented the intentional `default` runtime inheritance key in the service provider while keeping the manifest dependency on Foundation Theme.
