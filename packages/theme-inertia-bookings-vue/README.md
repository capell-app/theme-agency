# Theme Inertia Bookings Vue

Theme Inertia Bookings Vue provides the Vue component implementation for the booking-first Inertia theme.

## At A Glance

- Package: `capell-app/theme-inertia-bookings-vue`
- Namespace: `Capell\ThemeStudio\InertiaBookingsVue\`
- Runtime: Inertia Vue
- Surfaces: public frontend
- Service provider: `Capell\ThemeStudio\InertiaBookingsVue\Providers\InertiaBookingsVueServiceProvider`
- Capell dependencies: `capell-app/theme-inertia-bookings`, `capell-app/inertia-vue-adapter`

## Why It Helps Your Capell Workflow

For teams, this package turns the booking theme into a Vue-powered public experience with the same server-side booking contract.

For developers, it registers the themed Vue entrypoint and component contribution for `Capell/Bookings/Request`; the generic Vue adapter build backs off when this component pack is installed.

## What It Adds

- `resources/js/app.js`, the themed Vue Inertia entrypoint.
- Vue implementations for `Capell/Page` and `Capell/Bookings/Request`.
- Vue widget components for title, content, and image blocks.
- Tailwind source registration and package-owned build asset conditions.
- Marketplace screenshot contract for booking request, services, loading, validation, and mobile states.

## Boundaries

- The package renders server props supplied by Theme Inertia Bookings and Bookings.
- It owns no booking data, routes, validation, database tables, settings, or admin resources.
- Do not duplicate booking availability or appointment request logic in Vue components.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [screenshots.json](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/theme-inertia-bookings-vue/tests --configuration=phpunit.xml
```
