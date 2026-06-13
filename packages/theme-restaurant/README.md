# Theme Restaurant

Theme Restaurant is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-restaurant` and extends these surfaces: frontend, console.

Theme Restaurant gives hospitality teams a premium venue website built around menu discovery, reservation intent, private dining, events, opening hours, and location confidence. It keeps restaurant content portable while the package owns the visual rhythm: menu panels, reservation paths, private-room selling, event previews, and location proof.

## Package

- Composer package: `capell-app/theme-restaurant`
- Product group: `Capell Themes`
- Manifest extends: `default`
- Runtime extends: `default`
- Theme key: `restaurant`
- Tier: `premium`
- Bundle: `themes`
- Schema impact: none
- Cache tags: `theme-restaurant`
- Commands: `capell:theme-restaurant-demo`

## Best Fit

- Restaurants and brasseries that need a menu-first public site.
- Bars and hospitality venues with events, hours, and location-led planning.
- Private dining teams that need premium enquiry surfaces without custom records.

## Optional Integrations

- Bookings or Form Builder for reservation and private dining capture.
- Events for ticketed dinners, seasonal launches, and chef nights.
- Blog for dining guides, reviews, menu notes, and venue stories.
- SEO Suite for hospitality metadata and local discovery.

## Development

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-restaurant/tests
```

Use `COMPOSER=composer.local.json composer preflight` before committing package wiring changes.
