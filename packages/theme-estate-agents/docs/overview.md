# Theme Estate Agents Overview

Theme Estate Agents is an **Available**, **No schema impact** Capell theme.

Product group:
**Capell Themes**

It ships as `capell-app/theme-estate-agents`, uses theme key `estate-agents`, and runtime inheritance uses `extends: default`. It requires `capell-app/frontend` for the built-in default fallback and does not own migrations, models, routes, permissions, or settings tables.

## Buyer Value

Estate agency sites have two conversion paths running at once: buyers want search, featured homes, local confidence, and viewing requests; vendors want valuation confidence, market proof, and agent credibility. Theme Estate Agents puts those workflows into one premium public renderer without creating property models or admin resources inside the theme.

The lane is intentionally separate from Local Services, Portfolio, SaaS, and Commerce. It is not a quote-led service page, a case-study portfolio, a product-led subscription page, or a retail catalogue. It is a property brochure and enquiry theme for search, valuation, local guidance, and viewing intent.

## Surfaces

- Frontend Blade renderer.
- Console demo command: `capell:theme-estate-agents-demo`.
- Shared Theme management page contribution through the Marketplace manifest.
- Diagnostics health check for package files, manifest wiring, and marketplace assets.

## Optional Integrations

- `capell-app/search` can power public listing discovery.
- `capell-app/address` can support area and branch context.
- `capell-app/form-builder` can power valuation and viewing capture.
- `capell-app/blog` and `capell-app/seo-suite` can support area guides and market reports.

Every integration has a safe static fallback. Public Blade performs no database queries and does not expose authoring, admin, permission, signed URL, field path, or model details.

## Screenshots

The package ships committed SVG marketplace previews. Real route-backed captures should be generated through the deployment screenshot runner once the demo harness has the theme installed and seeded.

## Tests

From the repository root, run `vendor/bin/pest packages/theme-estate-agents/tests`.
