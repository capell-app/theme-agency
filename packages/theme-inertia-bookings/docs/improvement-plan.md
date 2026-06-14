# Theme Inertia Bookings - Improvement & Growth Plan

> Package: capell-app/theme-inertia-bookings · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Active

## 1. Snapshot

Theme Inertia Bookings is the base premium Inertia theme for appointment-led service businesses: services, clinics, consultants, classes, locations, FAQs, and public booking requests. It registers theme key `inertia-bookings`, runtime `FrontendRuntime::Inertia`, CSS vendor assets, a Theme management page contribution, and a binding that replaces Bookings' public request renderer with `InertiaPublicBookingRequestRenderer`. The booking request renderer is real: it calls `CapellInertia::render('Capell/Bookings/Request', BuildPublicBookingRequestPropsAction::run($request, lazySlots: true))`. The package also ships runner-backed PNG screenshots for homepage, services, locations, booking request, and mobile request flows. The critical defect is that `InertiaBookingsThemeRenderer::render()` returns an empty string, so the theme itself does not render public Inertia pages even though screenshots and docs describe homepage/services/location sections.

## 2. Improvements (existing functionality)

1. **Implement the Inertia theme page renderer.** `InertiaBookingsThemeRenderer::render()` currently returns `''`. It should render a real Inertia page/component with hydrated public-safe page props, or delegate through the shared Inertia runtime contract used by `capell-app/inertia`. Add tests proving homepage/services/location page props render without authoring metadata. Evidence: `src/Rendering/InertiaBookingsThemeRenderer.php`, screenshot contract entries for homepage/services/locations. - **M**

2. **Make adapter prerequisites explicit in health and docs.** The base package requires `capell-app/inertia` and supports Vue/React adapters, but it does not require either adapter. If the generic Inertia adapter is enough, document that. If a concrete adapter is required for public components, health checks should report missing adapter/component registration before the theme is selected. Evidence: `capell.json dependencies`, adapter docs, provider registers missing `resources/js/**/*.vue` and `resources/js/**/*.jsx` globs. - **S**

3. **Fix vendor asset source registration.** The provider registers Tailwind source globs under `resources/js/**/*.vue` and `resources/js/**/*.jsx`, but the base package has no `resources/js` directory. Either remove those globs from the base package and let React/Vue adapters own them, or add real shared components. Evidence: `InertiaBookingsThemeServiceProvider::registerVendorAssets()`, package file tree. - **S**

4. **Promote runner-backed screenshots consistently.** The package has `docs/screenshots/*.png`, but `capell.json` promotes `docs/assets/marketplace/*.png` copies. Prefer promoting runner-backed `docs/screenshots/` files, or document why curated marketplace crops are intentionally separate and keep both in sync with tests. Evidence: `capell.json marketplace.screenshots`, `docs/screenshots.json`, tests. - **S**

5. **Rewrite docs around the actual Inertia contract.** README/overview are generic and do not explain the split between base theme, Bookings renderer override, shared Inertia runtime, and React/Vue adapter packages. Add a concise architecture and install path so package adopters know which packages are required for a working page. - **S**

6. **Add public-output and prop-safety coverage.** Current tests validate manifest/screenshot/registration, but do not inspect rendered Inertia props for authoring metadata, package names, signed URLs, model IDs, or private booking internals. Add renderer-level tests for `InertiaPublicBookingRequestRenderer` and the theme renderer once implemented. - **M**

## 3. Missing Features (gaps)

Capabilities declared: theme-inertia-bookings and Inertia bookings frontend.

- **Theme page renderer is missing.** This is the central functional gap for a theme package.
- **No demo command.** Premium themes commonly ship a demo install path; this package has screenshots but no `capell:theme-inertia-bookings-demo` command.
- **No required page-set contract.** Homepage/services/locations/request screenshots exist, but there is no test proving the theme covers search/list/contact/detail equivalents for theme-scale requirements.
- **No fallback when Bookings is not installed.** Bookings is a hard dependency, which is reasonable for this theme, but docs should explain that it is not a generic service theme.
- **No adapter-specific visual health.** React/Vue adapter packages own components, but the base theme health check cannot tell whether the selected adapter has the booking request component available.

## 4. Issues / Risks

1. **Critical risk: the registered theme renderer outputs an empty response body.** The package can be installed and selected while public theme pages render nothing. Recommended fix: implement renderer and add a failing test before any other product depth work. - **P1**

2. **Important gap: adapter requirements are ambiguous.** A Capell site may install the base theme without a concrete React/Vue adapter and get missing components or blank output. Recommended fix: health diagnostics should verify adapter/component registration or docs should require a supported adapter. - **P2**

3. **Important gap: asset globs point to files that do not exist.** This can mislead the Tailwind asset pipeline and package authors. Recommended fix: move framework component source ownership to adapter packages. - **P2**

4. **Important gap: docs are too generic for a cross-runtime package.** The split between base theme, Inertia runtime, Bookings public route, and adapter component packs is the product boundary; it needs to be explicit. - **P2**

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

| Item                                                                                        | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Implement `InertiaBookingsThemeRenderer::render()` with public-safe Inertia page props      | Now    | M      | High   | §2.1, §4.1  |
| Add renderer/prop safety tests for theme pages and booking request output                   | Now    | M      | High   | §2.6        |
| Make adapter prerequisites explicit in health/docs                                          | Now    | S      | Medium | §2.2, §4.2  |
| Remove or relocate nonexistent JS vendor asset globs                                        | Now    | S      | Medium | §2.3, §4.3  |
| Rewrite README/overview around base theme, Inertia runtime, Bookings renderer, and adapters | Now    | S      | Medium | §2.5        |
| Promote runner-backed screenshots or assert marketplace crop parity                         | Next   | S      | Medium | §2.4        |
| Add demo command/fixture coverage for homepage, services, locations, and request journey    | Next   | M      | Medium | §3          |
| Add adapter-specific health checks for booking request component availability               | Next   | M      | Medium | §3          |
| Add layout graph consumption for richer Inertia page composition                            | Later  | L      | Medium | §3, §5      |

## 7. Verification

Plan-writing review only; no commands were run for this package yet. First implementation slice should start with:

```bash
vendor/bin/pest packages/theme-inertia-bookings/tests --configuration=phpunit.xml
```

When renderer behavior changes, include Inertia and Bookings focused suites:

```bash
vendor/bin/pest packages/inertia/tests packages/bookings/tests/Feature/PublicBookingRequestTest.php --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, renderer, Bookings override, health check, screenshots, docs, and adapter boundaries.
- [x] Capell audience pass completed for service owners, frontend developers, and package adopters.
- [ ] Approved implementation slices shipped.
- [ ] Focused Theme Inertia Bookings verification passed.
- [ ] Package tests passed.
- [ ] Repo preflight passed for changed files.
