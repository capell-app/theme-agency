# Theme Inertia Bookings React

Theme Inertia Bookings React provides the React component implementation for the booking-first Inertia theme.

## At A Glance

- Package: `capell-app/theme-inertia-bookings-react`
- Namespace: `Capell\ThemeStudio\InertiaBookingsReact\`
- Runtime: Inertia React
- Surfaces: public frontend
- Service provider: `Capell\ThemeStudio\InertiaBookingsReact\Providers\InertiaBookingsReactServiceProvider`
- Capell dependencies: `capell-app/theme-inertia-bookings`, `capell-app/inertia-react-adapter`

## Why It Helps Your Capell Workflow

For teams, this package turns the booking theme into a React-powered public experience with the same server-side booking contract.

For developers, it registers the themed React entrypoint and component contribution for `Capell/Bookings/Request`; the generic React adapter build backs off when this component pack is installed.

## What It Adds

- `resources/js/app.jsx`, the themed React Inertia entrypoint.
- React implementations for `Capell/Page` and `Capell/Bookings/Request`.
- React widget components for title, content, and image blocks.
- Tailwind source registration and package-owned build asset conditions.
- Marketplace screenshot contract for booking request, services, loading, validation, and mobile states.

## Boundaries

- The package renders server props supplied by Theme Inertia Bookings and Bookings.
- It owns no booking data, routes, validation, database tables, settings, or admin resources.
- Do not duplicate booking availability or appointment request logic in React components.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [screenshots.json](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/theme-inertia-bookings-react/tests --configuration=phpunit.xml
```
