# Theme Inertia Bookings React

Status: **Preview, premium component pack** · Kind: **plugin** · Surface:
**frontend** · Runtime: **Inertia React**

This package provides the React implementation for Theme Inertia Bookings. When
installed with `capell-app/inertia-react-adapter`, it registers a themed Inertia
app entrypoint and the generic React adapter build condition backs off.

## What This Package Adds

- `resources/js/app.jsx`, the themed React Inertia entrypoint.
- React implementations for `Capell/Page` and `Capell/Bookings/Request`.
- React widget components for title, content, and image blocks.
- Tailwind source registration and a package-owned build asset condition.

## Boundaries

The component pack does not own booking data, routes, or validation. It renders
the server props supplied by `capell-app/theme-inertia-bookings` and
`capell-app/bookings`.

## Screenshot Plan

`docs/screenshots.json` records the required component-pack capture. The
committed SVG assets are interim Marketplace previews until the seeded harness
can compile and capture the React runtime.

## Verification

```bash
vendor/bin/pest packages/theme-inertia-bookings-react/tests --configuration=phpunit.xml
```
