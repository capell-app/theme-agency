# Capell Inertia React Adapter

The React adapter registers React dependencies and the Capell Inertia React application entrypoint for Inertia-powered Capell pages and package routes.

## At A Glance

- Package: `capell-app/inertia-react-adapter`
- Namespace: `Capell\InertiaReactAdapter\`
- Surfaces: public frontend
- Service provider: `Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider`
- Capell dependencies: `capell-app/core`, `capell-app/inertia`
- Framework dependencies: `@inertiajs/react`, `@vitejs/plugin-react`, `react`, `react-dom`

## Why It Helps Your Capell Workflow

For teams, the adapter is the installable switch that lets a Capell Inertia theme use React components.

For developers, it registers the `react` adapter key, NPM dependencies, build asset entrypoint, and component names through Capell's vendor asset system instead of requiring each app to hand-wire React bootstrapping.

## What It Adds

- React adapter metadata in `InertiaAdapterRegistry`.
- NPM dependency declarations for React and the Inertia React adapter.
- Build asset `vendor/capell/inertia-react` with entrypoint `resources/js/app.jsx`.
- Default component map for `Capell/Page` and `Capell/Bookings/Request`.
- A vendor asset condition that defers to the bookings-specific React component pack when that package is installed.

## Boundaries

- This package owns generic React bootstrapping only.
- Feature-specific React components can live in theme/component packages such as `capell-app/theme-inertia-bookings-react`.
- It owns no migrations, settings, routes, admin resources, or public Blade views.

## Runtime Surface

- Provider: `src/Providers/InertiaReactAdapterServiceProvider.php`
- Health check: `src/Health/InertiaReactAdapterHealthCheck.php`
- Entry point: `resources/js/app.jsx`
- Component source: `resources/js/`
- Tests: `packages/inertia-react-adapter/tests`

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [Capell Inertia](../inertia/README.md)
- [Theme Inertia Bookings React](../theme-inertia-bookings-react/README.md)

## Testing

```bash
vendor/bin/pest packages/inertia-react-adapter/tests --configuration=phpunit.xml
```
