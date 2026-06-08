# Capell Inertia

Capell Inertia is the shared runtime bridge that lets Capell public pages and package-owned frontend routes render through Inertia instead of Blade-only theme views.

## At A Glance

- Package: `capell-app/inertia`
- Namespace: `Capell\Inertia\`
- Surfaces: public frontend
- Service provider: `Capell\Inertia\Providers\InertiaServiceProvider`
- Capell dependencies: `capell-app/core`, `capell-app/frontend`, `capell-app/api`
- Third-party dependency: `inertiajs/inertia-laravel`

## Why It Helps Your Capell Workflow

For teams, Capell Inertia makes it possible to ship richer app-like public experiences while keeping normal Capell page, language, layout, theme, asset, and render-hook data in the request.

For developers, it provides one renderer, middleware, root view, facade, adapter registry, and page-prop builder so package routes do not each invent their own Inertia bootstrapping.

## What It Adds

- Registers the `FrontendRuntime::Inertia` response renderer.
- Adds the `capell.inertia` middleware alias and inserts Inertia middleware into frontend routes.
- Provides the `CapellInertia::render()` facade for package-owned routes.
- Builds public page props through `BuildInertiaPagePropsAction`.
- Registers an `InertiaAdapterRegistry` for React/Vue adapter packages.
- Uses `capell-inertia::app` as the default root view.

## Configuration

`config/capell-inertia.php` supports:

| Key | Default | Purpose |
| --- | --- | --- |
| `adapter` | `env('CAPELL_INERTIA_ADAPTER', 'vue')` | Active frontend adapter key. |
| `root_view` | `capell-inertia::app` | Inertia root Blade view. |
| `page_component` | `Capell/Page` | Default public page component. |

## Developer Deep Dive

Use the facade for package-owned routes:

```php
use Capell\Inertia\Facades\CapellInertia;

return CapellInertia::render('Capell/Bookings/Request', [
    'service' => $serviceData,
    'slots' => $slotData,
]);
```

Use the frontend runtime renderer for normal Capell pages by selecting the Inertia runtime in the theme/package definition. The renderer builds public page payloads with `BuildPublicPagePayloadAction`, includes layout widget component data when a layout is present, and runs the public authoring-surface assertion before returning the response.

## Boundaries

- Capell Inertia owns the server-side bridge, root view, renderer, middleware, and adapter registry.
- Adapter packages own framework dependencies and application entrypoints.
- Theme/component packages own actual React or Vue components.
- Public Inertia responses must not expose authoring markers, signed editor URLs, admin selectors, model IDs, field paths, or package internals.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [React adapter](../inertia-react-adapter/README.md)
- [Vue adapter](../inertia-vue-adapter/README.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/inertia/tests --configuration=phpunit.xml
```
