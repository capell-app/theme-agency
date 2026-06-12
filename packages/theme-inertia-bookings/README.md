# Theme Inertia Bookings

Theme Inertia Bookings is a booking-first Capell theme for services, clinics, consultants, classes, and appointment-led organizations.

## At A Glance

- Package: `capell-app/theme-inertia-bookings`
- Namespace: `Capell\ThemeStudio\InertiaBookings\`
- Theme key: `inertia-bookings`
- Runtime: Inertia
- Surfaces: public frontend
- Service provider: `Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider`
- Capell dependencies: `capell-app/core`, `capell-app/frontend`, `capell-app/inertia`, `capell-app/bookings`

## Why It Helps Your Capell Workflow

For teams, the theme provides a booking-led public site direction where service information, trust proof, locations, FAQs, and the appointment request flow fit together.

For developers, it registers an Inertia theme definition and replaces the public Bookings request renderer with an Inertia route while keeping appointment data and validation in `capell-app/bookings`.

## What It Adds

- The `inertia-bookings` theme definition and preset.
- Theme CSS for Inertia booking pages and request forms.
- A Bookings public request renderer that calls `CapellInertia::render('Capell/Bookings/Request', ...)`.
- Vendor asset registrations for theme CSS and Inertia component source scanning.
- Marketplace management contribution and committed route-backed screenshots.

## Best Used With

- [Capell Inertia](../inertia/README.md)
- [Bookings](../bookings/README.md)
- [Theme Inertia Bookings React](../theme-inertia-bookings-react/README.md)
- [Theme Inertia Bookings Vue](../theme-inertia-bookings-vue/README.md)

## Boundaries

- This package owns the theme definition, CSS, and server renderer binding.
- Bookings owns services, staff, locations, availability, appointment requests, reminders, and calendar feeds.
- React and Vue component implementations live in optional component packs.
- The theme owns no migrations, settings, or package-owned admin resources.

## Runtime Surface

- Provider: `src/Providers/InertiaBookingsThemeServiceProvider.php`
- Theme renderer: `src/Rendering/InertiaBookingsThemeRenderer.php`
- Booking request renderer: `src/Rendering/InertiaPublicBookingRequestRenderer.php`
- Marketplace/admin contribution: `src/Manifest/ThemeManagementPageContribution.php`
- Theme assets: `resources/`
- Tests: `packages/theme-inertia-bookings/tests`

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [screenshots.json](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/theme-inertia-bookings/tests --configuration=phpunit.xml
```
