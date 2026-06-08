# Capell Inertia Overview

Status: **Available, runtime bridge** · Kind: **plugin** · Tier: **core** · Bundle: **frontend** · Contexts: **frontend** · Product group: **Capell Frontend**

Capell Inertia lets Capell public pages and package-owned routes render through Inertia while still using Capell's page payload, frontend middleware, theme data, render hooks, and asset/runtime manifests.

## Non-Technical Overview

Use Capell Inertia when a site or extension needs a public frontend that feels more app-like than traditional Blade output. It is infrastructure: visitors do not install or configure it directly, but themes and feature packages can depend on it to render React or Vue experiences through the same Capell routing and safety checks.

## What This Package Adds

- Inertia response renderer for Capell frontend pages.
- Inertia root Blade view that keeps Capell head/body components and render hooks.
- Middleware alias and frontend middleware registration.
- `CapellInertia::render()` facade for package-owned Inertia routes.
- `BuildInertiaPagePropsAction` for public page payloads.
- `InertiaAdapterRegistry` for framework adapter packages.

## Developer Deep Dive

Use `CapellInertia::render()` from feature-package controllers when the route is not a normal page render:

```php
return CapellInertia::render('Capell/Bookings/Request', $props);
```

Use `FrontendRuntime::Inertia` in a theme definition when normal Capell pages should render through the shared Inertia page component.

The package reads `CAPELL_INERTIA_ADAPTER` through `config('capell-inertia.adapter')`. Adapter packages register their framework-specific entrypoints and dependencies against the adapter registry.

## Boundaries

- This package does not ship React or Vue dependencies. Install one adapter package for the target runtime.
- This package does not own booking, commerce, or portal components. Feature/theme packages own their component source and server props.
- Public responses are checked by `AssertPublicHtmlContainsNoAuthoringSurfaceAction`; keep authoring state behind authenticated admin/editor flows.

## Verification

```bash
vendor/bin/pest packages/inertia/tests --configuration=phpunit.xml
```
