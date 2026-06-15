# Capell Inertia React Adapter — Improvement & Growth Plan

> Package: capell-app/inertia-react-adapter · Kind: plugin · Tier: core · Product group: Capell Frontend · Bundle: frontend · Status: Active

## 1. Snapshot

The React adapter registers the `react` adapter with the shared Capell Inertia bridge, contributes React/Vite NPM dependencies, and exposes a generic React application entrypoint at `resources/js/app.jsx`. It deliberately owns no database tables, admin resources, settings, commands, or routes. When a richer React theme package such as `theme-inertia-bookings-react` is installed, this generic adapter build condition turns off so the theme can provide the concrete component pack.

## Completed Improvement Slices

- **2026-06-14:** Routed the vendor asset condition through the shared `ResolveInertiaAdapterKeyAction`, so trimmed adapter config and invalid config behave consistently across public props and asset registration. Tightened health checks to verify the registered adapter belongs to this package and points at the expected build entrypoint. Added this package-local plan and refreshed generated docs placeholders.

## 2. Improvements

1. **Shipped 2026-06-14: align asset condition with sanitized adapter config.** The build condition now uses the bridge resolver instead of raw config, so `CAPELL_INERTIA_ADAPTER=" react "` enables the React build and invalid config falls back away from it. — `src/Providers/InertiaReactAdapterServiceProvider.php`, `tests/Feature/InertiaReactAdapterServiceProviderTest.php` — S

2. **Shipped 2026-06-14: strengthen health readiness.** Health now verifies the registered adapter's package name, build path, and entrypoint, not only that a `react` key exists. — `src/Health/InertiaReactAdapterHealthCheck.php`, `tests/Feature/InertiaReactAdapterServiceProviderTest.php` — S

3. **Add component-map drift tests.** The provider declares `Capell/Page` and `Capell/Bookings/Request`; `resources/js/app.jsx` imports matching files manually. Add a small file-existence or manifest test so a component map change cannot silently break the generic build. — `src/Providers/InertiaReactAdapterServiceProvider.php`, `resources/js/app.jsx` — S

4. **Surface adapter diagnostics through the bridge.** Once the Inertia bridge exposes adapter readiness details, report React package/version/build metadata there too. — `src/Health/InertiaReactAdapterHealthCheck.php`, `packages/inertia/src/Health/InertiaHealthCheck.php` — M

## 3. Missing Features

- **No SSR entrypoint.** The package only ships a browser entrypoint. SSR support should be explicit before claiming server-rendered Inertia pages.
- **No visual screenshot contract.** This generic adapter has no user-facing UI by itself. Screenshots should come from React theme/component packages that render actual pages through it.
- **No generated component map.** Components are registered manually in `app.jsx`; a generated map could remove drift once more page/component surfaces exist.

## 4. Risks

1. **Generic adapter suppression is package-name based.** The condition disables the generic build when `capell-app/theme-inertia-bookings-react` is installed. If another full React theme becomes available, either register a broader suppressor contract or add explicit package keys.

2. **React version is a public build contract.** NPM dependency versions are registered as vendor assets. Keep adapter package tests aligned with any React/Vite major bump.

## 5. Positioning

Audience: frontend/package developers. Position this package as the included React client adapter for Capell Inertia, not as a theme. It gives developers a working React entrypoint and dependency contract; themes and component packs own the actual visual experience.

## 6. Roadmap

| Item                                           | Bucket | Effort | Impact | Ref |
| ---------------------------------------------- | ------ | ------ | ------ | --- |
| Align asset condition with sanitized config    | Done   | S      | Medium | §2  |
| Strengthen adapter health readiness            | Done   | S      | Medium | §2  |
| Add package-local improvement plan             | Done   | S      | Medium | §1  |
| Add component-map drift tests                  | Later  | S      | Medium | §2  |
| Add bridge-visible adapter diagnostics         | Later  | M      | Medium | §2  |
| Add explicit SSR entrypoint when SSR is needed | Later  | M      | Medium | §3  |
