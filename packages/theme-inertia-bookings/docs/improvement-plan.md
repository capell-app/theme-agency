# Theme Inertia Bookings - Improvement & Growth Plan

> Package: capell-app/theme-inertia-bookings · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Active

## 1. Snapshot

Theme Inertia Bookings is the base premium Inertia theme for appointment-led service businesses: services, clinics, consultants, classes, locations, FAQs, and public booking requests. It registers theme key `inertia-bookings`, runtime `FrontendRuntime::Inertia`, CSS vendor assets, a Theme management page contribution, and a binding that replaces Bookings' public request renderer with `InertiaPublicBookingRequestRenderer`. The booking request renderer is real: it calls `CapellInertia::render('Capell/Bookings/Request', BuildPublicBookingRequestPropsAction::run($request, lazySlots: true))`. The package also ships runner-backed PNG screenshots for homepage, services, locations, booking request, and mobile request flows. The current implementation slice now gives `InertiaBookingsThemeRenderer` a real `Capell/Page` payload path, makes adapter prerequisites explicit in health/docs, and leaves framework-specific component assets in the React/Vue adapter packages.

## 2. Improvements (existing functionality)

1. **Shipped: implement the Inertia theme page renderer.** `InertiaBookingsThemeRenderer::render()` now renders the configured `capell-inertia.page_component` with hydrated public-safe page props derived from `ThemePageData`. Tests prove homepage/services props are not blank and do not include authoring metadata, signed editor fields, package names, or model IDs. Evidence: `src/Rendering/InertiaBookingsThemeRenderer.php`, `tests/Unit/ThemeInertiaBookingsPackageTest.php`. - **M**

2. **Shipped: make adapter prerequisites explicit in health and docs.** The base package requires `capell-app/inertia` and `capell-app/bookings`; health now also requires the configured `CAPELL_INERTIA_ADAPTER` package (`capell-app/inertia-vue-adapter` or `capell-app/inertia-react-adapter`) to be installed. README/overview explain that theme-specific React/Vue packages provide component implementations. Evidence: `ThemeInertiaBookingsHealthCheck`, README, docs overview. - **S**

3. **Shipped: fix vendor asset source registration.** The base provider now registers only `resources/css/theme-inertia-bookings.css`; adapter packages own Vue/JSX source globs and build assets. Evidence: `InertiaBookingsThemeServiceProvider::registerVendorAssets()`, registration tests. - **S**

4. **Promote runner-backed screenshots consistently.** The package has `docs/screenshots/*.png`, but `capell.json` promotes `docs/assets/marketplace/*.png` copies. Prefer promoting runner-backed `docs/screenshots/` files, or document why curated marketplace crops are intentionally separate and keep both in sync with tests. Evidence: `capell.json marketplace.screenshots`, `docs/screenshots.json`, tests. - **S**

5. **Shipped: rewrite docs around the actual Inertia contract.** README/overview now explain the split between base theme, Bookings renderer override, shared Inertia runtime, and React/Vue adapter packages, including the required install path. - **S**

6. **Shipped: add public-output and prop-safety coverage.** Tests now inspect theme renderer props and the booking request renderer facade call for public-only keys, avoiding authoring metadata, package names, signed editor URLs, model IDs, and admin-only fields. - **M**

## 3. Missing Features (gaps)

Capabilities declared: theme-inertia-bookings and Inertia bookings frontend.

- **Theme page renderer is missing.** This is the central functional gap for a theme package.
- **No demo command.** Premium themes commonly ship a demo install path; this package has screenshots but no `capell:theme-inertia-bookings-demo` command.
- **No required page-set contract.** Homepage/services/locations/request screenshots exist, but there is no test proving the theme covers search/list/contact/detail equivalents for theme-scale requirements.
- **No fallback when Bookings is not installed.** Bookings is a hard dependency, which is reasonable for this theme, but docs should explain that it is not a generic service theme.
- **No adapter-specific visual health.** React/Vue adapter packages own components, but the base theme health check cannot tell whether the selected adapter has the booking request component available.

## 4. Issues / Risks

1. **Closed: the registered theme renderer previously output an empty response body.** The renderer now delegates through Capell Inertia and returns the response body for the configured page component. - **P1**

2. **Closed for the base package: adapter requirements were ambiguous.** Health now fails when the configured generic Inertia adapter package is missing, and docs explain when to install the theme-specific React/Vue component packages. Adapter component-depth checks remain in the adapter package plans. - **P2**

3. **Closed: asset globs pointed to files that did not exist.** Framework source ownership is now explicit in adapter packages. - **P2**

4. **Closed: docs were too generic for a cross-runtime package.** README and overview now document the base/runtime/adapter boundary. - **P2**

5. **Improvement: screenshot promotion uses duplicate marketplace assets.** The committed runner files are stronger evidence. Recommended fix: promote `docs/screenshots/` or add tests that assert marketplace crops correspond to runner captures. - **P3**

## 5. Marketplace & Positioning

Theme Inertia Bookings is a premium lane for appointment-led businesses using Capell Inertia. For owners, the outcome is a fast service and booking journey with a real appointment request flow. For developers, the differentiator is that the theme uses Capell's Inertia bridge and Bookings package renderer instead of a Blade-only public path.

**Current summary:** "Premium Inertia booking-business theme for services, clinics, consultants, classes, and appointments."

**Improved summary:** "A premium Inertia theme for appointment-led businesses, pairing service pages, locations, proof, and the real Bookings request flow."

**Improved description:** "Theme Inertia Bookings gives service businesses a Capell/Inertia public journey built around services, trust proof, locations, FAQs, and appointment requests. The base package registers the `inertia-bookings` theme and swaps Bookings' public request route onto an Inertia renderer with lazy slot loading. React and Vue adapter packages provide framework-specific components, while the Bookings package remains the source of appointment data and submission behavior. Built for teams that want a modern Inertia frontend without moving booking logic into the theme."

**Media status:** The package has useful committed PNG evidence. Once the renderer is implemented, recapture or visually verify homepage/services/location screenshots so they prove real output rather than stale fixture output.

**Cross-sell:** Bookings is required. Inertia is required. React/Vue adapters provide component implementation. Layout Builder can provide public composition if the Inertia renderer consumes layout graph data in the future.

**Keywords/tags:** `inertia`, `bookings`, `appointments`, `services`, `clinics`, `locations`, `react`, `vue`, `premium-theme`, `booking-request`.

## 6. Prioritized Roadmap

| Item                                                                                     | Bucket | Effort | Impact | Section ref |
| ---------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Promote runner-backed screenshots or assert marketplace crop parity                      | Next   | S      | Medium | §2.4        |
| Add demo command/fixture coverage for homepage, services, locations, and request journey | Next   | M      | Medium | §3          |
| Add adapter-specific health checks for booking request component availability            | Next   | M      | Medium | §3          |
| Add layout graph consumption for richer Inertia page composition                         | Later  | L      | Medium | §3, §5      |

## 7. Verification

Current implementation slice verification:

```bash
vendor/bin/pest packages/theme-inertia-bookings/tests --configuration=phpunit.xml
```

Passed on 2026-06-14: 8 tests, 81 assertions.

When renderer behavior changes, include Inertia and Bookings focused suites:

```bash
vendor/bin/pest packages/inertia/tests packages/bookings/tests/Feature/PublicBookingRequestTest.php --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, renderer, Bookings override, health check, screenshots, docs, and adapter boundaries.
- [x] Capell audience pass completed for service owners, frontend developers, and package adopters.
- [x] Approved implementation slices shipped.
- [x] Focused Theme Inertia Bookings verification passed.
- [x] Package tests passed.
- [ ] Repo preflight passed for changed files.
