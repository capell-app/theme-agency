# Theme Restaurant - Improvement & Growth Plan

> Package: capell-app/theme-restaurant · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Active

## 1. Snapshot

Theme Restaurant is a premium Blade child theme for hospitality sites built around menu discovery, reservation intent, private dining, events, opening hours, location confidence, chef story, proof, and editorial listing sections. It registers theme key `restaurant`, runtime inheritance `extends: default`, one preset, 14 section renderers, a demo command, optional integration flags for Bookings/Form Builder/Events/Blog, and a critical health check for package files and manifest wiring. The theme stays thin: no migrations, models, routes, permissions, settings, or admin resources. Public Blade has source-level tests for authoring metadata and database query safety. Marketplace media still promotes static SVG preview art; `docs/screenshots.json` points at route-backed PNG captures under `docs/screenshots/`, but those files are not committed and the entries are marked optional.

## 2. Improvements (existing functionality)

1. **Require Foundation Theme explicitly.** The demo Action imports `Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller`, and the theme extends the default renderer, but `composer.json` and `capell.json` only require Core and Frontend. Add `capell-app/foundation-theme` to Composer/manifest dependencies and update manifest tests/docs to prevent install-order drift. Evidence: `InstallRestaurantThemeDemoAction`, `capell.json dependencies.requires`, `composer.json require`. - **S**

2. **Fix the skip link target.** `page.blade.php` renders a skip link to `#main-content`, but the package shell does not render that target itself. Add a deterministic semantic target or ensure the renderer contract always emits one, then test rendered output. Evidence: `resources/views/page.blade.php`, `rg main-content packages/theme-restaurant`. - **S**

3. **Replace fake reservation and listing fallbacks with real or non-submitting states.** `reservation-panel.blade.php` posts a public GET form to `#` unless `form_action` is supplied; Bookings/Form Builder availability only changes status copy. `content-listing.blade.php`, footer, and navigation can also emit `href="#"` fallback links. Connected states should use safe URLs/actions supplied by render data; disconnected states should render CTAs/copy that do not pretend to submit. Evidence: `sections/reservation-panel.blade.php`, `sections/content-listing.blade.php`, `sections/navigation.blade.php`, `sections/footer.blade.php`. - **M**

4. **Promote only route-backed marketplace screenshots.** The screenshot contract expects five PNG captures under `docs/screenshots/`, but no screenshot directory/files are present and `capell.json` promotes static SVGs. Make homepage, menu, reservation, private dining, and events captures required, commit real runner PNGs, and update manifest media only after browser verification. Evidence: `docs/screenshots.json`, `capell.json marketplace.screenshots`, `docs/assets/marketplace/*.svg`. - **M**

5. **Strengthen health checks and failure coverage.** `ThemeRestaurantHealthCheck` has useful internal checks, but tests only assert `passed()` is true. Add failure tests for missing section/view files, missing stylesheet, manifest drift, missing marketplace media, and Foundation dependency. Also verify included sections match registered renderers. Evidence: `ThemeRestaurantHealthCheck`, `ThemeRestaurantHealthCheckTest`, `RestaurantThemeServiceProvider::sectionRenderers()`. - **S**

6. **Align the demo command with package Action patterns.** `DemoCommand` instantiates `new InstallRestaurantThemeDemoAction` directly, which bypasses the container and makes command behavior harder to fake than Liquid Glass. Resolve the Action through the container or use `InstallRestaurantThemeDemoAction::run()`, then add focused command tests for option parsing and base URL fallback. Evidence: `src/Console/Commands/DemoCommand.php`, `packages/theme-liquid-glass/src/Console/Commands/DemoCommand.php`. - **S**

7. **Expand public-output safety coverage.** Current public-output tests check authoring metadata and DB-query strings, but not inline scripts, dead form actions, or package-owned public links. Add tests proving public Blade remains script-free and does not ship `action="#"` or avoidable `href="#"` defaults in premium conversion sections. Evidence: `PublicOutputSafetyTest`, public Blade views. - **S**

8. **Clarify cacheability and integration docs.** Manifest public-output safety says cache-safe, but `performance.cacheSafety.cacheable` is `false`; docs explain optional integrations at a high level without defining the render-data contract for actions/URLs. Decide cache metadata, then document how Bookings/Form Builder/Events/Blog data reaches the views without package-owned queries. Evidence: `capell.json performance.cacheSafety`, `docs/overview.md`, `README.md`. - **S**

## 3. Missing Features (gaps)

Capabilities declared: `theme-restaurant`, `theme-restaurant-frontend`.

- **No real reservation workflow.** The theme can display a form, but it does not own or resolve a booking/enquiry action when Bookings or Form Builder is installed.
- **No events or blog data contract.** Optional Events/Blog availability changes labels, but the theme does not define a hydrated item contract or source loader.
- **No route-backed screenshot proof.** Static SVGs are the only committed media even though premium theme captures are declared.
- **No demo command tests.** Restaurant lacks the focused command option/base URL tests present in newer theme packages.
- **No dark/mobile visual proof.** Premium hospitality sites need mobile reservation/menu proof; current tests are source/render-string checks only.

## 4. Issues / Risks

1. **Important gap: package dependencies are incomplete.** Standalone package installs can miss Foundation Theme even though demo installation requires it. Recommended fix: add dependency to Composer/manifest overlays and assert it in tests. - **P2**

2. **Important gap: reservation fallback submits to `#`.** A premium conversion surface should not render a fake public booking workflow. Recommended fix: use real connected actions or a non-submitting fallback. - **P2**

3. **Important gap: skip target can be missing.** Keyboard users may not get a valid skip destination unless content happens to provide it. Recommended fix: make the package shell or renderer contract own the target. - **P2**

4. **Important gap: marketplace media is placeholder-only.** SVG previews do not prove the real renderer, mobile state, or optional integration fallbacks. Recommended fix: make route-backed PNG captures required before promotion. - **P2**

5. **Improvement: diagnostics are under-tested.** The health check can drift without failing tests because only the happy path is covered. Recommended fix: add targeted failure tests. - **P3**

## 5. Marketplace & Positioning

Restaurant has a strong premium lane separate from Commerce and Local Services. The theme sells a venue experience: menu rhythm, table intent, private dining capacity, seasonal events, service windows, and practical location confidence. For site owners, the outcome is a polished hospitality site that turns appetite into a reservation or enquiry. For developers, the value is a theme-owned presentation layer that stays data-light and integrates with companion packages through hydrated render data.

**Current summary:** "A premium hospitality theme for menu-led restaurants, bars, private dining venues, and event-led dining businesses."

**Improved summary:** "A premium hospitality theme for menu browsing, reservations, private dining, events, opening hours, and venue confidence."

**Improved description:** "Theme Restaurant gives hospitality teams a premium Capell renderer for the public journeys that matter before a guest books: reading the menu, checking service windows, finding the venue, exploring private dining, and discovering seasonal events. It keeps restaurant records and enquiries in companion packages or Capell content, while package-owned Blade views provide the visual rhythm and safe static fallbacks. Pair it with Bookings or Form Builder for reservations, Events for ticketed dining, Blog for venue stories, and SEO Suite for local discovery."

**Media status:** Placeholder SVGs are useful planning assets only. Completion requires real route-backed screenshots for homepage, menu, reservation, private dining, and events states, with mobile reservation/menu proof before marketplace promotion.

**Cross-sell:** Bookings or Form Builder should own reservations and private dining capture. Events should own event records. Blog should own dining guides and venue stories. SEO Suite should own local hospitality metadata.

**Keywords/tags:** `restaurant`, `hospitality`, `reservations`, `menus`, `private-dining`, `events`, `opening-hours`, `venue`, `premium-theme`, `bookings`, `form-builder`.

## 6. Prioritized Roadmap

| Item                                                                                           | Bucket | Effort | Impact | Section ref |
| ---------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add explicit Foundation Theme dependency in Composer/manifest/tests                            | Now    | S      | High   | §2.1, §4.1  |
| Fix skip link target and add rendered shell coverage                                           | Now    | S      | High   | §2.2, §4.3  |
| Replace `action="#"` reservation fallback with real action or non-submitting state             | Now    | M      | High   | §2.3, §4.2  |
| Add health check failure tests and renderer alignment assertions                               | Now    | S      | Medium | §2.5, §4.5  |
| Align demo command with Action/container pattern and add command tests                         | Now    | S      | Medium | §2.6        |
| Rewrite docs around dependencies, integration render-data contracts, and verification commands | Now    | S      | Medium | §2.8, §5    |
| Convert static SVG marketplace media to required route-backed PNG captures                     | Next   | M      | High   | §2.4, §4.4  |
| Add public-output tests for scripts and dead premium conversion links                          | Next   | S      | Medium | §2.7        |
| Clarify cacheability metadata and invalidation expectations                                    | Next   | S      | Medium | §2.8        |
| Add mobile/dark visual proof for reservation and menu pages                                    | Later  | M      | Medium | §3, §5      |

## 7. Verification

Plan-writing review only; no commands were run for this package yet. First implementation slice should start with:

```bash
vendor/bin/pest packages/theme-restaurant/tests --configuration=phpunit.xml
```

For renderer or dependency changes, include:

```bash
vendor/bin/pest packages/foundation-theme/tests packages/layout-builder/tests --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, views, and tests.
- [x] Comprehensive local review pass completed for theme definition, public views, accessibility, optional integrations, screenshots, docs, health checks, and tests.
- [x] Capell audience pass completed for restaurant owners, package developers, and frontend/theme developers.
- [ ] Approved implementation slices shipped.
- [ ] Focused Theme Restaurant verification passed.
- [ ] Package tests passed.
- [ ] Repo preflight passed for changed files.
