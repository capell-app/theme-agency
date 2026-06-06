# Theme Inertia Bookings

Status: **Preview, premium theme** · Kind: **theme** · Theme key:
**inertia-bookings** · Surface: **frontend** · Runtime: **Inertia**

Theme Inertia Bookings is a booking-first public theme for services, clinics,
consultants, classes, and appointment-led organizations. It registers an
Inertia runtime theme definition and replaces the public Bookings request
renderer with a Capell Inertia route.

## What This Package Adds

- The `inertia-bookings` theme definition, preset, and Marketplace management contribution.
- A Bookings public request renderer that calls `CapellInertia::render('Capell/Bookings/Request', ...)`.
- Theme CSS for the Inertia page shell, booking request form, and shared booking widgets.
- Runtime vendor asset registrations for the theme CSS and component source scanning.

## Boundaries

The package owns no database tables or settings. Appointment data, availability,
capacity, reminders, and calendar feeds remain in `capell-app/bookings`.
React/Vue implementations live in the optional component packs.

## Screenshot Plan

`docs/screenshots.json` describes the required route-backed captures for the
theme homepage, service sections, locations/FAQ, and public booking request
flow. The marketplace gallery keeps the extension card artwork and promotes
the committed PNG captures in `docs/screenshots/`.

## Verification

```bash
vendor/bin/pest packages/theme-inertia-bookings/tests --configuration=phpunit.xml
```
