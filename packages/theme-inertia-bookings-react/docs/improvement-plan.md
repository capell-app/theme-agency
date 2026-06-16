# Theme Inertia Bookings React - Improvement & Growth Plan

> Package: capell-app/theme-inertia-bookings-react · Kind: plugin · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Active

## 1. Snapshot

Theme Inertia Bookings React is the React component pack for the base Theme Inertia Bookings package. It requires `capell-app/theme-inertia-bookings` and `capell-app/inertia-react-adapter`, registers React Tailwind/build assets behind a vendor asset condition, contributes the `Capell/Bookings/Request` frontend component, and ships React implementations for `Capell/Page`, the public booking request form, and three basic widget components. Tests prove provider asset registration, component file existence, manifest contribution shape, explicit form labels, and the accessible validation/loading selectors required by the screenshot contract. Required screenshot PNG files exist under `docs/screenshots/` and are now promoted in marketplace media after a successful local runner recapture.

## 2. Improvements (existing functionality)

1. **Shipped: real validation and loading states matching the screenshot contract.** `Request.jsx` now reads `useForm().errors`, renders accessible `.error` messages, and wraps the deferred slot select in a stable `.slots` region with loading copy. Focused tests lock the selectors and accessibility attributes against drift. Evidence: `resources/js/Pages/Capell/Bookings/Request.jsx`, `tests/Unit/ManifestRequirementsTest.php`, `docs/screenshots.json`. - **S**

2. **Add component-map drift coverage.** `app.jsx` manually maps `Capell/Page` and `Capell/Bookings/Request`; manifest contribution declares only `Capell/Bookings/Request`. Add a test that every declared provider/component route has a matching source file and that the shared `Capell/Page` fallback stays present. Evidence: `resources/js/app.jsx`, `capell.json contributes`, `InertiaBookingsReactServiceProvider`. - **S**

3. **Document and test the generic React adapter suppression boundary.** The generic `inertia-react-adapter` disables its build when this package is installed. This package should explain that it owns the full React component pack for bookings and should test the condition handoff from both sides where practical. Evidence: `packages/inertia-react-adapter/docs/improvement-plan.md`, provider condition. - **S**

4. **Shipped: guard public HTML injection points.** `Page.jsx` and `Content.jsx` now pass page/widget content through `sanitizePublicHtml()` before `dangerouslySetInnerHTML`, while the base renderer remains responsible for server-side portable HTML projection. Tests pin blocked authoring/package metadata, admin/signed URL, and unsafe-scheme markers. Evidence: `resources/js/Support/publicHtml.js`, `resources/js/Pages/Capell/Page.jsx`, `resources/js/Components/Capell/Widgets/Content.jsx`, `tests/Unit/ManifestRequirementsTest.php`. - **M**

5. **Shipped: promote screenshot media after recapture.** The local screenshot runner captured React request, services, loading, validation, and mobile states through the adapter package; `capell.json` now promotes those required docs screenshots alongside the extension card. - **S**

6. **Improve docs from generated copy to adapter architecture.** README/overview say only "React components for Theme Inertia Bookings." Add the actual package split: base theme registers Inertia/Bookings renderer, React adapter supplies components/build entrypoint, generic adapter is suppressed, no schema/routes are owned here. - **S**

## 3. Missing Features (gaps)

Capabilities declared: `theme-inertia-bookings-react`.

- **Shipped: validation UI is browser-recaptured.** The runner captured validation and loading states with the React adapter installed.
- **No success/confirmation state.** The form submits through Inertia but does not document or display a successful appointment request state.
- **Widget support is minimal.** Only Content, Image, and Title widgets are implemented; richer Layout Builder widgets fall back to Content.
- **No browser-level React test.** Current tests inspect files and provider registration, not actual rendered component behavior.
- **Shipped: marketplace-ready media after runner recapture.** Required docs screenshots are promoted in the package manifest.

## 4. Issues / Risks

1. **Closed: screenshot contract selectors needed recapture.** React adapter screenshots were recaptured through the local package runner and promoted from `docs/screenshots/`. - **P2**

2. **Resolved gap: validation errors are visible to users.** Failed booking submissions now render accessible inline messages and a summary through Inertia form errors. Keep package tests around this public state. - **P2**

3. **Important gap: component-map drift can still hide missing components.** Recommended fix: compare declared component contributions with committed React component files. - **P2**

4. **Improvement: docs under-explain the adapter split.** The package is technical and should be clear for frontend developers deciding which adapter to install. - **P3**

## 5. Marketplace & Positioning

The React adapter should be sold as part of the Inertia Bookings theme family, not as a standalone visual theme. For site owners, the value is that the booking theme can run on a React Inertia frontend. For frontend developers, the value is a ready component pack that matches Capell's server-side page and booking request prop contracts.

**Current summary:** "React components for Theme Inertia Bookings."

**Improved summary:** "React component pack for the Inertia Bookings theme, including the booking request form, page renderer, and booking-focused widgets."

**Improved description:** "Theme Inertia Bookings React supplies the React components and build entrypoint for Capell's Inertia Bookings theme. It renders the shared `Capell/Page` and `Capell/Bookings/Request` contracts, registers package build assets only when the React adapter is active, and lets the base theme own booking renderer binding. Install it when the host Capell/Inertia frontend uses React and needs the booking request flow to render with first-party components."

**Media status:** Runner-backed PNGs are promoted for React request components, services, slot loading, validation, and mobile request states.

**Cross-sell:** Requires Theme Inertia Bookings and Inertia React Adapter. Complements Bookings and the base Inertia package.

**Keywords/tags:** `react`, `inertia`, `bookings`, `adapter`, `component-pack`, `appointments`, `frontend`, `theme`.

## 6. Prioritized Roadmap

| Item                                                                                       | Bucket | Effort | Impact | Section ref      |
| ------------------------------------------------------------------------------------------ | ------ | ------ | ------ | ---------------- |
| Add accessible validation errors and `.error` screenshot state                             | Done   | S      | High   | §2.1, §4.1, §4.2 |
| Add `.slots` loading region and stable deferred slot state                                 | Done   | S      | High   | §2.1, §4.1       |
| Rewrite README/overview around base theme, React adapter, and generic adapter suppression  | Done   | S      | Medium | §2.6             |
| Add component-map drift tests for source files and manifest component declarations         | Done   | S      | Medium | §2.2             |
| Document/test sanitized prop boundary for `dangerouslySetInnerHTML` usage                  | Done   | M      | High   | §2.4, §4.3       |
| Coordinate screenshot runner recapture and keep marketplace media card-only until verified | Done   | M      | Medium | §2.5             |
| Add browser-level component smoke coverage for booking request interactions                | Later  | M      | Medium | §3               |
| Expand widget component coverage beyond Content/Image/Title                                | Later  | M      | Medium | §3               |
| Add success/confirmation UI contract                                                       | Later  | M      | Medium | §3               |

## 7. Verification

Focused verification for the current slice: `vendor/bin/pest packages/theme-inertia-bookings-react/tests --configuration=phpunit.xml` passed, and changed-file preflight passed. For broader adapter handoff changes, include `vendor/bin/pest packages/inertia-react-adapter/tests packages/theme-inertia-bookings/tests --configuration=phpunit.xml`.

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, health check, React components, screenshot contract, docs, and adapter boundary.
- [x] Capell audience pass completed for frontend developers and package adopters.
- [x] Accessible validation/loading implementation slice shipped.
- [x] Focused Theme Inertia Bookings React verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
