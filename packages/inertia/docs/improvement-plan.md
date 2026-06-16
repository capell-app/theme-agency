# Capell Inertia — Improvement & Growth Plan

> Package: capell-app/inertia · Kind: plugin · Tier: core · Product group: Capell Frontend · Bundle: frontend · Status: Active

## 1. Snapshot

Capell Inertia is the frontend runtime bridge that lets Capell render public pages and package-owned routes through Inertia without moving CMS structure into theme packages. It registers the `FrontendRuntime::Inertia` renderer, the `capell.inertia` route middleware, the root Blade view (`capell-inertia::app`), a package helper/facade for package routes, a small adapter registry, sanitized adapter/root-view/component config resolvers, and page-prop construction backed by the public API payload builder. It has no migrations, settings, admin resources, or package-owned public routes.

## Completed Improvement Slices

- **2026-06-14:** Added `ResolveInertiaAdapterKeyAction` and wired it into middleware shared props, public page props, and adapter registry lookup so invalid adapter config cannot leak arrays or empty values into public Inertia payloads. Updated docs to explain the runtime bridge and recorded this package-local plan.

## 2. Improvements

1. **Shipped 2026-06-14: sanitize the configured adapter key.** `capell-inertia.adapter` is public runtime state. It now resolves through one action with a `vue` fallback when config is non-string or blank, and tests cover middleware props, page props, registry lookup, trimming, and invalid config. — `src/Actions/ResolveInertiaAdapterKeyAction.php`, `src/Http/Middleware/HandleInertiaRequests.php`, `src/Actions/BuildInertiaPagePropsAction.php`, `src/Support/InertiaAdapterRegistry.php`, `tests/Feature/InertiaBridgeTest.php` — S

2. **Extract shared root-view rendering.** `CapellInertiaResponseRenderer` and `CapellInertiaManager` both set the root view, render an Inertia response, apply an optional status, and run public HTML safety inspection. A small internal response builder would remove duplication and keep package-route rendering aligned with page rendering. — `src/Rendering/CapellInertiaResponseRenderer.php`, `src/Support/CapellInertiaManager.php` — S

3. **Done/Shipped: validate configured component/root-view values.** `ResolveInertiaRootViewAction` and `ResolveInertiaComponentNameAction` now sanitize root-view and component names for middleware, public page rendering, and package-route helper rendering. Bad config falls back to `capell-inertia::app` and `Capell/Page` without PHP string-cast surprises. — `src/Actions/ResolveInertiaRootViewAction.php`, `src/Actions/ResolveInertiaComponentNameAction.php`, `src/Rendering/CapellInertiaResponseRenderer.php`, `src/Support/CapellInertiaManager.php`, `tests/Feature/InertiaBridgeTest.php` — S

4. **Health check should report adapter readiness.** Current health only verifies renderer and middleware registration. Add adapter registry/config diagnostics so operators can see whether the configured adapter is installed and what package registered it. — `src/Health/InertiaHealthCheck.php`, `src/Support/InertiaAdapterRegistry.php` — M

## 3. Missing Features

- **Adapter install guidance.** The bridge does not install Vue or React assets by itself. Docs should keep pointing developers to adapter/theme packages such as `inertia-vue-adapter` and `inertia-react-adapter`.
- **Visual workflow screenshots.** This bridge is mostly runtime infrastructure; screenshots should be owned by adapter/theme packages that render actual pages. A screenshot contract is not required here unless this package gains a visible setup/debug screen.
- **SSR/asset manifest diagnostics.** There is no health output for missing client manifests, SSR manifest mismatch, or missing component mapping. Those checks likely belong in the adapter packages but should be surfaced through this bridge.

## 4. Risks

1. **Public output safety is strong but narrow.** Both package-route and page-render helpers run `AssertPublicHtmlContainsNoAuthoringSurfaceAction`, and tests prove obvious authoring props fail initial HTML rendering. Continue testing any new shared props for authoring markers, model IDs, or signed URLs.

2. **Payload builder depends on prepared relations.** `BuildInertiaPagePropsAction` reads loaded `translation` and `pageUrl` relations only, which keeps public rendering query-safe. Preserve that boundary; do not add lazy relation access in the action or root view.

3. **Adapter registry is intentionally small.** It is an in-process registry, not an installer. Adapter packages should register themselves and own their assets, component maps, and screenshots.

## 5. Positioning

Audience: frontend/package developers. Position this package as the boring runtime contract that lets Capell pages render through Inertia while keeping CMS internals out of public payloads. It is not a theme, not an adapter, and not a page builder.

Suggested summary: "Connect Capell public rendering to Inertia adapters with one root view, middleware stack, safe runtime props, and an adapter registry for package-owned Vue or React frontends."

## 6. Roadmap

| Item                                                   | Bucket | Effort | Impact | Ref |
| ------------------------------------------------------ | ------ | ------ | ------ | --- |
| Sanitize configured adapter key                        | Done   | S      | Medium | §2  |
| Add package-local improvement plan                     | Done   | S      | Medium | §1  |
| Extract shared Inertia response builder                | Later  | S      | Low    | §2  |
| Validate root view and page component config           | Done   | S      | Medium | §2  |
| Add adapter readiness diagnostics to health check      | Later  | M      | Medium | §2  |
| Surface SSR/client manifest diagnostics through bridge | Later  | M      | Medium | §3  |
