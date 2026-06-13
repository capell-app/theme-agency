# Theme Restaurant Overview

Theme Restaurant is an **Available**, **No schema impact** Capell theme.

Product group:
**Capell Themes**

It ships as `capell-app/theme-restaurant`, uses theme key `restaurant`, and runtime inheritance uses `extends: default`. It requires `capell-app/frontend` for the built-in default fallback and does not own migrations, models, routes, permissions, or settings tables.

## Buyer Value

Restaurants need a public site that gets guests from appetite to action quickly. Theme Restaurant gives them menu-first browsing, reservation capture, private dining promotion, event discovery, opening hours, location confidence, chef story, proof, and editorial listing sections without storing designed markup in content fields.

The lane is intentionally separate from Commerce and Local Services. Commerce sells product discovery and retail conversion. Local Services sells quote-led operational service work. Theme Restaurant sells a venue: service windows, menu rhythm, room capacity, events, and the practical confidence needed before booking.

## Surfaces

- Frontend Blade renderer.
- Console demo command: `capell:theme-restaurant-demo`.
- Shared Theme management page contribution through the Marketplace manifest.
- Diagnostics health check for package files, manifest wiring, and marketplace assets.

## Optional Integrations

- `capell-app/bookings` and `capell-app/form-builder` can power reservation capture.
- `capell-app/events` can power seasonal dinners and ticketed occasions.
- `capell-app/blog` can power guides, reviews, and menu stories.
- `capell-app/seo-suite` can support hospitality metadata.

Every integration has a safe static fallback. Public Blade performs no database queries and does not expose authoring, admin, permission, signed URL, field path, or model details.

## Screenshots

The package ships committed SVG marketplace previews. Real route-backed captures should be generated through the deployment screenshot runner once the demo harness has the theme installed and seeded.

## Tests

From the repository root, run `vendor/bin/pest packages/theme-restaurant/tests`.
