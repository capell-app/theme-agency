# Capell Inertia React Adapter Overview

Status: **Available, runtime adapter** · Kind: **plugin** · Tier: **core** · Bundle: **frontend** · Contexts: **frontend** · Product group: **Capell Frontend**

The React adapter supplies the framework-specific layer for Capell Inertia: React dependencies, an application entrypoint, and adapter metadata.

## Non-Technical Overview

Install this package when an Inertia-powered Capell frontend should use React. It does not create a visible admin screen or database table; it tells Capell's asset/runtime pipeline which framework assets to build and load.

## Developer Deep Dive

The provider registers:

- adapter key `react`;
- build path `vendor/capell/inertia-react`;
- entrypoint `resources/js/app.jsx`;
- components `Capell/Page` and `Capell/Bookings/Request`;
- NPM dependencies for React, React DOM, Vite React plugin, and `@inertiajs/react`.

The generic build asset loads only when the active runtime uses Inertia, `capell-inertia.adapter` is `react`, and the bookings-specific React component pack is not installed.

## Verification

```bash
vendor/bin/pest packages/inertia-react-adapter/tests --configuration=phpunit.xml
```
