# Changelog

All notable changes to `capell-app/hero` will be documented in this file.

## Unreleased

### 2026-06-04

- Declared Hero's admin dependency and admin surface in Composer and `capell.json` to match the shipped Filament schema extenders.
- Populated Hero marketplace capabilities for the widget, video backgrounds, overlay backgrounds, carousel, and theme inheritance.
- Removed the empty installed-check branch from `HeroServiceProvider::packageBooted()`.
- Updated docs and manifest tests for the admin/extender metadata.

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Rewrote the marketplace summary, top-level manifest description, and composer description to highlight responsive video/overlay backgrounds, carousel slides, and inheritable theme styling.
- Replaced the stubbed `HeroHealthCheck` with a real Diagnostics probe that verifies the `capell::widget.hero` Blade component is registered and the `capell-hero` view namespace resolves the hero widget view.
- Added `fetchpriority="high"` to the hero media poster image to improve Largest Contentful Paint for above-the-fold media heroes.
