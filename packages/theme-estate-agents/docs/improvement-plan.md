# Theme Estate Agents - Improvement & Growth Plan

> Package: capell-app/theme-estate-agents · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Active

## 1. Snapshot

Theme Estate Agents is a premium Blade child theme for property agencies, lettings teams, valuations, area guides, agent proof, and viewing-request journeys. It registers theme key `estate-agents`, runtime inheritance `extends: default`, one preset, a package page wrapper, 14 section renderers, a demo command, opt-in screenshot fixture routes, and a critical health check that verifies definition/manifest/files. The theme stays thin: no migrations, models, permissions, settings, or admin resources. Optional integrations are passed into sections for Search, Address, and Form Builder, but current views mostly use static form/search markup with connected labels. The package has solid public-output and no-query source tests. Marketplace media now includes five route-backed PNG captures for homepage, property search, valuation, local-guide, and viewing-request journeys.

## 2. Improvements (existing functionality)

1. **Require Foundation Theme explicitly.** Theme scale says child themes should require `capell-app/foundation-theme`; this package extends `default` but `composer.json` and `capell.json` only require Core and Frontend. Add Foundation Theme to Composer/manifest dependencies and update tests/docs to prevent install-order drift. Evidence: `capell.json dependencies.requires`, `composer.json require`, `EstateAgentsThemeServiceProvider::definition()`. - **S** - **Done 2026-06-14:** `composer.json` and `capell.json` now explicitly require `capell-app/foundation-theme`, with manifest and health coverage updated.

2. **Fix the broken skip link target.** `page.blade.php` renders a skip link to `#main-content`, but no element in the theme views has that id. Add `id="main-content"` to the main public wrapper or render a semantic `<main>` so keyboard users have a working skip target. Evidence: `resources/views/page.blade.php`, `rg main-content`. - **S** - **Done 2026-06-14:** the page wrapper is now a semantic `<main id="main-content">` and public-output tests lock the skip target.

3. **Replace static marketplace SVGs with route-backed captures.** Premium themes need at least five real frontend screenshots. This package promotes five route-backed PNG captures for homepage, property search/listing, valuation, local guide, and viewing-request fixtures and marks the runner entries required. Evidence: `capell.json marketplace.screenshots`, `docs/screenshots.json`, `docs/screenshots/*.png`, `routes/screenshot-fixtures.php`. - **M** - **Done 2026-06-15:** opt-in screenshot fixture routes render the real Estate Agents Blade sections with safe static data, and Capell runner captures are committed as required marketplace PNGs.

4. **Wire or clarify optional integration forms/search.** Search/Form Builder availability changes labels, but property search, valuation, and viewing sections still render plain GET forms to `#` unless `form_action` is supplied. For a premium theme, connected Search/Form Builder states should use real public-safe URLs/actions from render data, or the fallback should be explicitly non-submitting CTA markup. Evidence: `sections/property-search.blade.php`, `valuation-cta.blade.php`, `viewing-request.blade.php`. - **M** - **Done 2026-06-16:** property search, valuation, and viewing sections now sanitize public action URLs and render non-submitting setup panels when no safe action is provided.

5. **Add required page-set coverage.** Theme scale expects homepage, landing/conversion page, list page with pagination, search results, contact/conversion form, and a detail/resource page. Current tests cover individual section rendering and source safety, but not the required page set or screenshot fixture contract. Add tests around demo install output or fixture route metadata. - **M** - **Done 2026-06-16:** screenshot fixture metadata now declares every required page-set role, docs explain the coverage contract, and tests assert the route-backed required fixtures cover the full theme set.

6. **Strengthen token/dark-mode coverage.** CSS uses theme variables for primary/accent/surface but still hardcodes several whites, mists, and dark gradients. Add tests or visual fixtures proving the preset tokens recolor key buttons, panels, and dark cards, plus define dark-mode expectations if marketplace captures include dark variants later. Evidence: `resources/css/theme-estate-agents.css`. - **M** - **Done 2026-06-16:** CSS surfaces now route paper, mist, buttons, focus rings, and dark cards through theme variables, mobile layout guards are defined, and screenshot metadata/tests pin desktop-light, mobile-light, and dark token-readiness profiles.

## 3. Missing Features (gaps)

Capabilities declared: estate-agents theme and frontend renderer.

- **No real property data/search package integration.** The theme intentionally does not own property records, but there is no companion property-listings package or Search contract for structured property filters.
- **Valuation and viewing capture are static by default.** Form Builder availability is detected, but the theme does not render a package-owned form component or documented form action contract.
- **Demo install remains generic.** Demo install delegates to Foundation's generic `ThemeDemoPageInstaller`; estate-specific screenshot routes now cover marketplace proof, but a deeper installed-demo page set is still a Next-row improvement.
- **No dark/mobile screenshot proof.** Premium property sites are sold visually; current committed proof covers desktop light-mode routes only.

## 4. Issues / Risks

1. **Important gap: child theme dependency is incomplete.** A theme extending `default` should explicitly require Foundation Theme so install order and split package metadata are clear. Recommended fix: add `capell-app/foundation-theme` to manifests/Composer overlays and tests. - **P2**

2. **Important gap: skip link is broken.** This is a real accessibility defect in every rendered page. Recommended fix: add the target and test it. - **P2**

3. **Resolved: premium screenshots are now route-backed.** Static SVG planning art has been replaced by five required Capell runner PNG captures for the real Estate Agents Blade sections. Remaining visual proof depth is dark/mobile coverage. - **P2**

4. **Important gap: connected forms/search are labels, not workflows.** Visitors can submit forms to `#` unless host content supplies actions. Recommended fix: either wire safe optional package actions or render CTA links/fallback copy when disconnected. - **P2**

5. **Improvement: theme cache metadata is conservative.** Public output is marked safe but `performance.cacheSafety.cacheable` is false. Recommended fix: decide whether this child theme can inherit Foundation cacheability and declare invalidation sources or document why theme output is intentionally non-cacheable. - **P3**

## 5. Marketplace & Positioning

Estate Agents has a distinct premium lane: buyer search, vendor valuation, local expertise, agent credibility, and viewing requests. That separation is strong and should stay separate from Local Services, Portfolio, and Commerce. For owners, the outcome is a site that serves buyers and sellers in one premium journey. For developers, the value is property-specific presentation without theme-owned listing models.

**Current summary:** "A premium property theme for estate agencies, lettings teams, valuations, local guides, and viewing-led enquiry journeys."

**Improved summary:** "A premium estate-agency theme for buyer search, vendor valuation, local guides, agent proof, and viewing-request journeys."

**Improved description:** "Theme Estate Agents gives property teams a polished public renderer for the two journeys every agency site needs: buyers searching for homes and vendors deciding who to trust with a valuation. It provides search bands, featured-property cards, area-guide sections, market proof, agent credibility, valuation CTAs, and viewing-request layouts while keeping property records and enquiries in companion packages or Capell content. Optional Search, Address, SEO Suite, Blog, and Form Builder integrations have safe static fallbacks. Built for agencies that want a premium property brochure without hardcoding listing data into theme views."

**Media status:** Marketplace media now uses route-backed PNG captures for homepage, search/listing, valuation, local guide, and viewing request. The extension-card SVG remains a compact marketplace card preview.

**Cross-sell:** Search should power property discovery. Address should support branch/area context. Form Builder should own valuation/viewing capture. Blog and SEO Suite should support market reports and area guides. A future Property Listings package would be the natural structured data companion.

**Keywords/tags:** `estate-agents`, `property`, `listings`, `valuation`, `viewings`, `local-guides`, `agents`, `search`, `form-builder`, `premium-theme`.

## 6. Prioritized Roadmap

| Item                                                                              | Bucket | Effort | Impact | Section ref |
| --------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add explicit Foundation Theme dependency in Composer/manifest/tests               | Done   | S      | High   | §2.1, §4.1  |
| Fix skip link target and add source/render test coverage                          | Done   | S      | High   | §2.2, §4.2  |
| Convert static SVG screenshot contract to five required route-backed PNG captures | Done   | M      | High   | §2.3, §4.3  |
| Rewrite README/overview with current theme-scale install/screenshot expectations  | Done   | S      | Medium | §5          |
| Wire connected Search/Form Builder actions or render non-submitting CTA fallbacks | Done   | M      | High   | §2.4, §4.4  |
| Add required page-set/demo fixture coverage                                       | Done   | M      | Medium | §2.5        |
| Add token/dark/mobile visual proof                                                | Done   | M      | Medium | §2.6        |
| Define property-listings companion package boundary or documented Search contract | Later  | L      | High   | §3, §5      |

## 7. Verification

Focused package verification passed:

```bash
vendor/bin/pest packages/theme-estate-agents/tests --configuration=phpunit.xml
```

Result: 9 tests, 95 assertions passed.

For renderer contract changes, include:

```bash
vendor/bin/pest packages/foundation-theme/tests packages/layout-builder/tests --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for theme definition, public views, accessibility, optional integrations, screenshots, docs, and tests.
- [x] Capell audience pass completed for site owners, agency implementers, and frontend/theme developers.
- [x] Approved implementation slices shipped.
- [x] Focused Theme Estate Agents verification passed.
- [x] Package tests passed.
- [ ] Repo preflight passed for changed files.
