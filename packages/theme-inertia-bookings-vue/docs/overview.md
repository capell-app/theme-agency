# Theme Inertia Bookings Vue

Status: **Preview, premium component pack** · Kind: **plugin** · Surface:
**frontend** · Runtime: **Inertia Vue**

This package provides the Vue implementation for Theme Inertia Bookings. When
installed with `capell-app/inertia-vue-adapter`, it registers a themed Inertia
app entrypoint and the generic Vue adapter build condition backs off.

## What This Package Adds

- `resources/js/app.js`, the themed Vue Inertia entrypoint.
- Vue implementations for `Capell/Page` and `Capell/Bookings/Request`.
- Vue widget components for title, content, and image blocks.
- Tailwind source registration and a package-owned build asset condition.

## Boundaries

The component pack does not own booking data, routes, or validation. It renders
the server props supplied by `capell-app/theme-inertia-bookings` and
`capell-app/bookings`.

## Screenshot Plan

`docs/screenshots.json` records the required component-pack captures. The
marketplace gallery keeps the extension card artwork and promotes route-backed
PNG captures from `docs/screenshots/` for the Vue request, services, loading,
validation, and mobile states.

## Verification

```bash
vendor/bin/pest packages/theme-inertia-bookings-vue/tests --configuration=phpunit.xml
```
