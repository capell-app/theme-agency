# Theme Inertia Bookings Vue - Improvement & Growth Plan

> Package: capell-app/theme-inertia-bookings-vue · Kind: plugin · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Active

## 1. Snapshot

Theme Inertia Bookings Vue is the Vue component pack for the base Theme Inertia Bookings package. It requires `capell-app/theme-inertia-bookings` and `capell-app/inertia-vue-adapter`, registers Vue Tailwind/build assets behind a vendor asset condition, contributes the `Capell/Bookings/Request` frontend component, and ships Vue implementations for `Capell/Page`, the public booking request form, and three basic widget components. Tests prove provider asset registration, component file existence, manifest contribution shape, and required screenshot files. Required screenshot PNG files exist under `docs/screenshots/` and are now promoted in marketplace media after a successful local runner recapture.

## 2. Improvements (existing functionality)

1. **Done: Add real validation and loading states matching the screenshot contract.** `Request.vue` now renders `useForm().errors` through accessible inline `.error` messages, a validation summary, and a stable `.slots` region with deferred loading copy for screenshot capture. Evidence: `resources/js/Pages/Capell/Bookings/Request.vue`, `tests/Feature/InertiaBookingsVueServiceProviderTest.php`, `docs/screenshots.json`. - **S**

2. **Add component-map drift coverage.** `app.js` manually maps `Capell/Page` and `Capell/Bookings/Request`; manifest contribution declares only `Capell/Bookings/Request`. Add a test that every declared provider/component route has a matching source file and that the shared `Capell/Page` fallback stays present. Evidence: `resources/js/app.js`, `capell.json contributes`, `InertiaBookingsVueServiceProvider`. - **S**

3. **Document and test the generic Vue adapter suppression boundary.** The generic `inertia-vue-adapter` should not compete with this package's booking-specific component pack. This package should explain that it owns the full Vue component pack for bookings and should test the condition handoff from both sides where practical. Evidence: provider asset condition, `capell.json` dependency on `capell-app/inertia-vue-adapter`. - **S**

4. **Shipped: guard public HTML injection points.** `Page.vue` and `Content.vue` now pass page/widget content through `sanitizePublicHtml()` before `v-html`, while the base renderer remains responsible for server-side portable HTML projection. Tests pin blocked authoring/package metadata, admin/signed URL, and unsafe-scheme markers. Evidence: `resources/js/Support/publicHtml.js`, `resources/js/Pages/Capell/Page.vue`, `resources/js/Components/Capell/Widgets/Content.vue`, `tests/Feature/InertiaBookingsVueServiceProviderTest.php`. - **M**

5. **Shipped: promote screenshot media after recapture.** The local screenshot runner captured Vue request, services, loading, validation, and mobile states through the adapter package; `capell.json` now promotes those required docs screenshots alongside the extension card. - **S**

6. **Improve docs from generated copy to adapter architecture.** README/overview say only "Vue components for Theme Inertia Bookings." Add the actual package split: base theme registers Inertia/Bookings renderer, Vue adapter supplies components/build entrypoint, generic adapter is suppressed, no schema/routes are owned here. - **S**

7. **Strengthen health checks beyond file presence.** `InertiaBookingsVueHealthCheck` only confirms two component files exist. It should also prove the package can resolve its build entrypoint, component contribution class, and vendor asset condition name so installer health catches broken packaging before runtime. Evidence: `src/Health/InertiaBookingsVueHealthCheck.php`. - **S**

## 3. Missing Features (gaps)

Capabilities declared: `theme-inertia-bookings-vue`.

- **Component-map coverage is still shallow.** The adapter now renders validation/loading states, but source-file to manifest contribution drift still needs a focused test.
- **No success/confirmation state.** The form submits through Inertia but does not document or display a successful appointment request state.
- **Widget support is minimal.** Only Content, Image, and Title widgets are implemented; richer Layout Builder widgets fall back to Content.
- **No browser-level Vue test.** Current tests inspect files and provider registration, not actual rendered component behavior.
- **Shipped: marketplace-ready media after runner recapture.** Required docs screenshots are promoted in the package manifest.

## 4. Issues / Risks

1. **Closed: screenshot contract waited on runner recapture.** Vue adapter screenshots were recaptured through the local package runner and promoted from `docs/screenshots/`. - **P2**

2. **Important gap: health checks remain mostly file-presence based.** Installer health should catch broken build entrypoints and contribution metadata before runtime. Recommended fix: assert build entrypoint, component contribution class, and vendor asset condition. - **P2**

3. **Important gap: component-map drift can still hide missing components.** Recommended fix: compare declared component contributions with committed Vue component files. - **P2**

4. **Improvement: browser-level component smoke coverage is absent.** Recommended fix: add a runner-backed Vue interaction smoke once the screenshot app can install the adapter. - **P3**

## 5. Marketplace & Positioning

The Vue adapter should be sold as part of the Inertia Bookings theme family, not as a standalone visual theme. For site owners, the value is that the booking theme can run on a Vue Inertia frontend. For frontend developers, the value is a ready component pack that matches Capell's server-side page and booking request prop contracts.

**Current summary:** "Vue components for Theme Inertia Bookings."

**Improved summary:** "Vue component pack for the Inertia Bookings theme, including the booking request form, page renderer, and booking-focused widgets."

**Improved description:** "Theme Inertia Bookings Vue supplies the Vue components and build entrypoint for Capell's Inertia Bookings theme. It renders the shared `Capell/Page` and `Capell/Bookings/Request` contracts, registers package build assets only when the Vue adapter is active, and lets the base theme own booking renderer binding. Install it when the host Capell/Inertia frontend uses Vue and needs the booking request flow to render with first-party components."

**Media status:** Runner-backed PNGs are promoted for Vue request components, services, slot loading, validation, and mobile request states.

**Cross-sell:** Requires Theme Inertia Bookings and Inertia Vue Adapter. Complements Bookings and the base Inertia package.

**Keywords/tags:** `vue`, `inertia`, `bookings`, `adapter`, `component-pack`, `appointments`, `frontend`, `theme`.

## 6. Prioritized Roadmap

| Item                                                                                       | Bucket | Effort | Impact | Section ref      |
| ------------------------------------------------------------------------------------------ | ------ | ------ | ------ | ---------------- |
| Add accessible validation errors and `.error` screenshot state                             | Done   | S      | High   | §2.1, §4.1, §4.2 |
| Add `.slots` loading region and stable deferred slot state                                 | Done   | S      | High   | §2.1, §4.1       |
| Rewrite README/overview around base theme, Vue adapter, and generic adapter suppression    | Done   | S      | Medium | §2.6             |
| Add component-map drift tests for source files and manifest component declarations         | Done   | S      | Medium | §2.2             |
| Strengthen health check coverage beyond file presence                                      | Done   | S      | Medium | §2.7             |
| Document/test sanitized prop boundary for `v-html` usage                                   | Done   | M      | High   | §2.4, §4.3       |
| Coordinate screenshot runner recapture and keep marketplace media card-only until verified | Done   | M      | Medium | §2.5             |
| Add browser-level component smoke coverage for booking request interactions                | Later  | M      | Medium | §3               |
| Expand widget component coverage beyond Content/Image/Title                                | Later  | M      | Medium | §3               |
| Add success/confirmation UI contract                                                       | Later  | M      | Medium | §3               |

## 7. Verification

Focused verification for the current slice: `vendor/bin/pest packages/theme-inertia-bookings-vue/tests --configuration=phpunit.xml` passed, and changed-file preflight passed. For broader adapter handoff changes, include `vendor/bin/pest packages/inertia-vue-adapter/tests packages/theme-inertia-bookings/tests --configuration=phpunit.xml`.

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, health check, Vue components, screenshot contract, docs, and adapter boundary.
- [x] Capell audience pass completed for frontend developers and package adopters.
- [x] Validation/loading state implementation slice shipped.
- [x] Sanitized raw HTML prop contract documented and covered with focused tests.
- [x] Focused Theme Inertia Bookings Vue verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
