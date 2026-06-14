# Capell Inertia React Adapter

<!-- prettier-ignore-start -->

## What This Plugin Adds

Capell Inertia React Adapter is an **Available**, **No schema impact** Capell plugin in the **Capell Frontend** product group. It ships as `capell-app/inertia-react-adapter` and extends these surfaces: frontend.

React asset adapter for Capell Inertia pages and package routes.

After install, React theme and package developers get registered React/Vite dependencies, a generic React entrypoint, and a bridge adapter key that Capell Inertia can resolve safely.

Status details:

- Status: Available
- Tier: core
- Bundle: frontend
- Composer package: `capell-app/inertia-react-adapter`
- Namespace: `Capell\InertiaReactAdapter`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers and a React client entrypoint instead of pushing adapter behaviour into core or application code.

**For teams:** React-based Capell frontends can share the same Inertia bridge while themes own the actual visual components.

## Screens And Workflow

No screenshot contract is required for this generic adapter. Visual screenshots belong to React theme/component packages that render actual public pages through it.

## Technical Shape

- Service providers: `Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider`.
- Health checks: `Capell\InertiaReactAdapter\Health\InertiaReactAdapterHealthCheck`.
- Cache tags: `inertia-react`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

It registers adapter metadata and vendor assets with `capell-app/inertia` and `capell-app/core`; it does not persist data.

## Install Impact

- Admin navigation: no admin surface declared.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `inertia-react`.
- Commands: none declared.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Set `CAPELL_INERTIA_ADAPTER=react` when this generic React build should be used. A richer React theme package may suppress this generic build and provide its own component pack.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/inertia-react-adapter`.
2. Set `CAPELL_INERTIA_ADAPTER=react` in the host app.
3. Verify the Capell Inertia bridge reports the React adapter and that the React build entrypoint is included in vendor assets.

## Next Steps

- [Package docs index](README.md)
- [Improvement plan](improvement-plan.md)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Inertia](../../inertia/README.md).
- Focused tests: `vendor/bin/pest packages/inertia-react-adapter/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
