# Theme Local Services — Improvement & Growth Plan

> Package: capell-app/theme-local-services · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

`LocalServicesThemeServiceProvider` registers theme key `local-services` (`src/LocalServicesThemeServiceProvider.php`), with the layout view `capell-theme-local-services::page` (`resources/views/page.blade.php` — a token-bound shell + skip link that echoes pre-rendered `$content`) and 14 section views under `resources/views/sections/` (hero, features, services, service-packages, service-areas, locality-proof, proof, content-listing, quote-form, quote-estimator, case-studies, resources, contact, cta — `navigation` + `footer` are deliberately inherited from foundation, returned as `null` by `isFoundationSection()`). The demo command `capell:theme-local-services-demo` exists and delegates to `InstallLocalServicesThemeDemoAction` → `ThemeDemoPageInstaller`; `DemoCommandTest` proves it installs ≥7 demo pages idempotently. Optional `quote-form`/`quote-estimator` (Form Builder) and `resources` (Blog) sections are correctly guarded via `ViewSectionRenderer` extra view data with static fallbacks. The marketplace summary/Composer description now use buyer-facing quote-request, service-area, and click-to-call positioning. Health checks now verify provider, package file, manifest, and screenshot boundaries. Screenshots: capell.json `marketplace.screenshots` declares **6** (3 `.jpg` + 3 `.svg`), all 6 committed — but 3 are **SVG placeholder diagrams**, not real captures; separately `docs/screenshots.json` declares **9 PNG** captures in `docs/screenshots/`, a directory that **does not exist** (0 committed).

## Completed Improvement Slices

- **2026-06-03:** Rewrote marketplace/Composer copy, replaced the critical health-check stub with real diagnostics, and documented the intentional split between runtime `extends: default` and manifest dependency `extends: capell-app/foundation-theme`.
- **2026-06-04:** Moved service-area defaults into translations, made service-area cards data-driven from section render data, added the default contact anchor, removed dead `href="#"` links, added fallback/custom render coverage, and hardened manifest tests.

## 2. Improvements (existing functionality)

1. **Replace decorative quote-form with a real fallback form** — the `quote-form` "static" path renders four label cards over inert `<span>` bars (`resources/views/sections/quote-form.blade.php`); a quote-led theme's headline conversion surface produces zero leads when Form Builder is absent. Ship a real `<form>` with name/phone/postcode/service/message inputs (POST target configurable) so the static path still captures enquiries. — converts the primary CTA from theatre to function — `resources/views/sections/quote-form.blade.php` — M
2. **Shipped 2026-06-04: Fix dead service-area links + hardcoded districts** — `service-areas.blade.php` now renders translated default areas or `$section->items` entries with `label`/`title`/`name`, `url`, and optional `postcode`/`postcodePrefix`. Empty data renders a translated empty state, real URLs render anchors, default areas point to the contact section anchor, and no-URL entries render static labels. — fixes broken links + i18n on a core local-SEO section — `resources/views/sections/service-areas.blade.php` — S
3. **Add real dark-mode token support or remove the stray variant** — only `services.blade.php` carries `dark:` variants (`dark:text-white`, `dark:text-stone-300`); the other 13 sections are light-only and the shell CSS (`resources/css/theme-local-services.css`) defines no dark palette. Either commit to a dark theme across all sections driven by `--theme-*` tokens, or strip the orphan `dark:` classes so output is consistent. — removes half-implemented styling that looks broken in dark UA — `resources/views/sections/services.blade.php`, `resources/css/theme-local-services.css` — M
4. **Decide and standardise the Tailwind `tw:` prefix** — repo convention (`.ai/frontend-tailwind`) mandates the `tw:` prefix on store frontends to avoid Bootstrap collisions; this theme uses bare utilities throughout (0 `tw:` occurrences across `resources/`). Confirm whether themes are exempt (they import via `foundation-theme.css`); if not, this is a systemic correctness bug across all 16 views. — prevents class collisions / aligns with house rule — all `resources/views/**/*.blade.php` — L
5. **Hydrate hero image as the LCP element** — hero `<img>` (`hero.blade.php`) has no `loading`/`fetchpriority`/width/height and `$imageAlt` defaults to `''`. As the largest above-fold element it should be `fetchpriority="high"`, explicitly sized to avoid CLS, and alt-required. — improves LCP/CLS and a11y on the money page — `resources/views/sections/hero.blade.php` — S
6. **Replace literal English defaults in section PHP blocks** — `services.blade.php` emits literal `Services`/`Service`, `case-studies.blade.php` defaults `metric => 'Result'` and hardcoded study copy, `proof.blade.php` hardcodes `'Satisfaction'`/`'Response'`/`'Coverage'` fallback labels. Move all to `resources/lang/en/generic.php` (which already holds ~80 keys). — completes translation coverage already started elsewhere — `services.blade.php`, `case-studies.blade.php`, `proof.blade.php`, `resources.blade.php` — S
7. **Unify card radius/border tokens** — sections mix `rounded-xl`/`rounded-2xl`/square `border` arbitrarily (services `rounded-xl`, service-areas `rounded-2xl`, contact/quote square). The preset declares `radius => 'md'` but views ignore it. Map a single `--ls-radius` token into the shell CSS and apply consistently. — tightens visual polish expected at premium tier — `resources/css/theme-local-services.css` + section views — M

## 3. Missing Features (gaps)

`capabilities[]` is only `["theme-local-services","theme-local-services-frontend"]` — generic theme registration, no local-business capability surfaced. For a local-services site the following are **table stakes** and currently absent:

- **LocalBusiness structured data (JSON-LD)** — no `application/ld+json` anywhere in the package (grep: 0). A local-services theme without `LocalBusiness`/`Service`/`AggregateRating`/`OpeningHoursSpecification` schema forfeits rich results and AI-answer eligibility. This is the single biggest differentiator gap vs siblings (none of the sibling themes ship it either). **Differentiator.**
- **Click-to-call / contact actions** — the `contact` section renders four static descriptive cards (`contact.blade.php`) with no `tel:`, `mailto:`, address, or map. Mobile local-services traffic is call-driven. **Table stakes.**
- **Opening hours section** — no section or lang keys for business hours / "open now" state. **Table stakes** for trades/salon/clinic.
- **Reviews / testimonials** — `proof` is metric-tiles only; there is no quote/star/source testimonial section. **Table stakes** (trust).
- **Trust / accreditation badges** — no Gas Safe / NICEIC / DBS / insurance / guarantee badge strip. **Differentiator** for trades verticals.
- **Before/after gallery** — `case-studies` is text cards; no image pair / project gallery. **Differentiator.**
- **FAQ section** — no FAQ section or `FAQPage` schema (pairs with AI-SEO). **Table stakes.**
- **Cross-sell hooks**: Form Builder integration is structural-only (it just swaps copy strings) — it does not actually embed a Form Builder form. Real `bookings`/`form-builder` embedding in `quote-form` is the natural paid cross-sell. Blog `resources` integration is similarly copy-only. Tie these to `supports[]` (already lists `layout-builder`, `frontend-authoring`, `publishing-studio`, `seo-suite`) and add `bookings`/`form-builder` as first-class embeds.
- **Local SEO**: beyond JSON-LD, there is no per-area landing pattern, NAP block, or `seo-suite` hook usage despite `seo-suite` being in `supports[]`.

## 4. Issues / Risks

- **Dual `extends` meaning is documented, but should stay tested** — capell.json declares the package dependency (`"extends": "capell-app/foundation-theme"`) while `definition()` uses the Theme Studio runtime inheritance key (`extends: 'default'`). The provider now documents the distinction; keep tests around both boundaries so this does not regress. — `src/LocalServicesThemeServiceProvider.php`, `capell.json`, `tests/Unit/LocalServicesThemeDefinitionTest.php`
- **Health check shipped, keep expanding diagnostics as the package grows** — `ThemeLocalServicesHealthCheck` now verifies provider, package file, manifest, and screenshot boundaries. Add any future sections/assets to those diagnostics instead of letting the critical check drift back into a shallow compatibility-only assertion. — `src/Health/ThemeLocalServicesHealthCheck.php`, `capell.json`
- **Surfaces mismatch (manifest vs docs)** — capell.json `surfaces: ["frontend"]` but README "At A Glance" says `Surfaces: frontend, console` (the demo command is a console surface). Pick one and align. — `capell.json`, `README.md`
- **screenshots.json is entirely unfulfilled** — 9 declared PNG capture paths point at `docs/screenshots/`, which does not exist; 0 PNGs committed. Either generate the captures or prune the manifest. — `docs/screenshots.json`
- **Marketplace media is placeholder-grade** — 3 of 6 marketplace assets are hand-drawn `.svg` layout diagrams (`local-services-*-layout.svg`), not screenshots, on a `tier: premium` product sold on visuals. — `capell.json`, `docs/assets/marketplace/`
- **WCAG**: hero/quote/cta/proof contain many `aria-hidden` decorative bar-charts (acceptable), service-area cards now avoid dead anchors, the carousel prev/next buttons are `hidden` by default with no documented JS to reveal them (`data-carousel-*` hooks present, no shipped controller in package), and `$imageAlt` defaults to empty. — `service-areas.blade.php`, `proof.blade.php`, `services.blade.php`, `case-studies.blade.php`, `resources.blade.php`
- **Carousel JS provenance** — every carousel relies on `data-carousel`/`data-carousel-track`/`data-carousel-prev/next` and `.theme-carousel-button` but the package ships no JS; it assumes foundation-theme provides it. If foundation drops it, every carousel silently degrades to an overflow-scroll with invisible buttons. Document the dependency and add a render/no-JS test. — `resources/views/sections/*` (all carousels)
- **Public-output safety: good.** `PublicOutputSafetyTest` asserts no DB calls / no admin metadata in Blade + lang, and views echo pre-rendered `$content` with no queries — compliant with the public-output rule. Keep this guard and extend it to any new section.
- **Performance budget** — manifest sets `frontendRenderBudgetMs: 20`, `adminQueryBudget: 0`, `cacheSafety.cacheable: false` (varies by site+locale). No test asserts the 20ms budget or the zero-query claim; add a render-timing/`assertQueryCount(0)` guard so the budget is enforced, not aspirational. — `capell.json`, `tests/`
- **No anonymous render snapshot test per section** — `LocalServicesThemeDefinitionTest` now covers the service-area fallback/custom paths and absence of the package slug, but contact/quote-form/case-studies/navigation/footer need fuller render assertions.

## 5. Marketplace & Selling

The manifest and Composer description now use this buyer-facing 1-sentence summary:

> A conversion-first Capell theme for local trades, clinics, and service businesses — built around quote requests, service-area coverage, and click-to-call trust.

The manifest marketplace description now uses this buyer-facing product story:

> Theme Local Services turns visitors into booked jobs. It ships hero, services, service-area, locality-proof, quote-estimator, case-study, and contact sections tuned for plumbers, electricians, salons, cleaners, and clinics, with a teal/amber palette and a quote desk front-and-centre. Optional Form Builder and Blog integrations upgrade the enquiry form and resources feed when those packages are installed, and the theme inherits foundation navigation, footer, and SEO. Drop in your services and coverage areas and launch a credible local-business site in minutes.

**Media gaps:** Replace the 3 SVG placeholder diagrams with real desktop+mobile screenshots; fulfil or delete the 9-entry `docs/screenshots.json` capture plan; add a hero light/dark pair and at least one quote-form + one service-area capture (both are key selling sections currently unshot). Premium tier demands real, retina screenshots — not diagrams.

**Differentiation / target buyer:** Among the 10 sibling themes (`theme-agency`, `-commerce`, `-corporate`, `-education`, `-healthcare`, `-knowledge`, `-nonprofit`, `-portfolio`, `-saas`), this is the only **lead-capture / quote-led** theme. Target buyer: a solo-operator or small local trade/services business (or the agency building for them) that needs phone calls and quote forms, not a brochure. Lean into the quote-desk + coverage + trust story to separate from `theme-corporate`/`theme-agency`.

**Keywords/tags:** local business, trades, plumber, electrician, salon, cleaner, quote form, service area, click to call, lead generation, local SEO, LocalBusiness schema.

## 6. Prioritized Roadmap

| Item                                                                             | Bucket | Effort | Impact | Section ref |
| -------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Replace decorative quote-form with a real fallback `<form>`                      | Now    | M      | High   | §2.1        |
| Add click-to-call / address / map to contact section                             | Now    | M      | High   | §3          |
| Fix surfaces mismatch (manifest vs README)                                       | Now    | S      | Low    | §4          |
| Add LocalBusiness + Service + FAQPage JSON-LD (render-data driven)               | Next   | M      | High   | §3          |
| Add reviews/testimonials section + lang keys                                     | Next   | M      | High   | §3          |
| Add opening-hours section ("open now")                                           | Next   | M      | Med    | §3          |
| Move remaining literal English to `generic.php` lang file                        | Next   | S      | Med    | §2.6        |
| Hero image LCP/CLS + required alt                                                | Next   | S      | Med    | §2.5        |
| Resolve `tw:` prefix convention question across all views                        | Next   | L      | Med    | §2.4        |
| Replace SVG placeholders with real screenshots; fulfil/prune screenshots.json    | Next   | M      | High   | §4, §5      |
| Add anonymous render test per section + 0-query/20ms budget guard                | Next   | M      | Med    | §4          |
| Add trust/accreditation badge strip + before/after gallery sections              | Later  | M      | Med    | §3          |
| Real Form Builder / Bookings embed in quote-form (cross-sell) + dark-mode system | Later  | L      | High   | §3, §2.3    |
| Keep dual `extends` semantics explicitly covered by tests                        | Later  | S      | Med    | §4          |
