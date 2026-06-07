# Theme SaaS — Improvement & Growth Plan

> Package: capell-app/theme-saas · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Complete

## 1. Snapshot

Theme SaaS registers the `saas` theme key (`SaasThemeServiceProvider::THEME_KEY = 'saas'`) and a Blade renderer that records package inheritance as `extends: "capell-app/foundation-theme"` in the manifest while using runtime `extends: 'default'` in Theme Studio, reusing Foundation Theme's runtime data/section contracts while shipping its own page wrapper and 16 section views. It registers a `SaasThemePageAdapter` (renderer-side, reads `Frontend::page()` + `meta.theme_demo.render_data`) and 16 section renderers (`navigation, hero, features, proof, logos, testimonials, faq, content-listing, comparison, calculator, pricing, docs-onboarding, demo-request, cta, footer, blog`); 15 use `ViewSectionRenderer`, `blog` uses a dedicated `BlogSectionRenderer`. The demo command `capell:theme-saas-demo` exists (`src/Console/Commands/DemoCommand.php`) and delegates to `InstallSaasThemeDemoAction` → `ThemeDemoPageInstaller::run($data, 'saas', 'SaaS')`. Theme key registration, demo command, and all 20 Blade views verified present and wired. Marketplace and Composer copy now use buyer-facing software/subscription positioning, and the marketplace manifest points at committed PNG captures. Screenshots: `docs/screenshots/` now contains 12 committed PNG captures, and `capell.json.marketplace.screenshots` promotes the strongest route-backed frontend/admin surfaces.

## Completed Improvement Slices

- **2026-06-03:** Rewrote marketplace/Composer copy, promoted committed PNG captures into the marketplace manifest, and replaced the critical health-check stub with real diagnostics.
- **2026-06-04:** Wired Content Sections, Document Lifecycle, and Form Builder availability into pricing, docs onboarding, and demo request sections with translated connected/static guidance and branch coverage.
- **2026-06-05:** Reconciled manifest cache safety by keeping theme output non-cacheable and disabling queued invalidation while no invalidation sources are declared.
- **2026-06-05:** Aligned package README and overview verification commands to the repo-root Pest invocation and documented that the package does not ship its own PHPUnit config.
- **2026-06-06:** Reconciled the screenshot contract to actual seeded Capell routes, captured six route-backed frontend PNGs, promoted the strongest homepage/contact/CTA captures, and added package CSS fallbacks for navigation and CTA contrast in the runner build.

## 2. Improvements (existing functionality)

Prioritized.

1. **Shipped 2026-06-04/06: Consume the availability flags already plumbed into views** — `pricing`, `docs-onboarding`, and `demo-request` now render translated connected/static guidance from `$contentSectionsAvailable`, `$documentLifecycleAvailable`, and `$formBuilderAvailable`, with tests covering both states. Follow-up slices turned pricing into a plan matrix, demo request into a Form Builder/static capture surface, and docs onboarding into a linked document-resource feed with lifecycle metadata. — why: removes dead data plumbing and makes the templates honest about their dependency on sibling packages — `src/SaasThemeServiceProvider.php`, `resources/views/sections/pricing.blade.php`, `resources/views/sections/docs-onboarding.blade.php`, `resources/views/sections/demo-request.blade.php` — M

2. **Shipped 2026-06-06: Add dark-mode rendering** — `theme-saas.css` now defines a real `.dark .saas-shell` / `.saas-shell.dark` token set plus a system dark-mode fallback, scoped card/navigation/border overrides, and dark-aware utility color handling so dark captures render as a dark SaaS theme rather than light pages in dark browser chrome. — why: dark screenshots should reflect a real theme mode; a dark SaaS surface is a buyer-facing differentiator — `resources/css/theme-saas.css`, `resources/views/page.blade.php`, all `resources/views/sections/*.blade.php` — L

3. **Shipped 2026-06-06: Tokenize hard-coded slate/cyan/blue palette** — `theme-saas.css` now applies scoped `.saas-shell` token overrides for the recurring Tailwind ink, muted text, surface, card, border, primary, accent, and ring utilities used across the section Blade files. The SaaS preset can now re-skin existing chrome without rewriting each template. — why: tokenization is what makes a premium theme feel configurable — `resources/views/sections/*.blade.php`, `resources/css/theme-saas.css` — L

4. **Reviewed 2026-06-06: Responsive coverage at common breakpoints beyond `md`/`lg`** — section containers are constrained by the `.saas-shell section > div` max-width plus local `max-w-6xl` wrappers, fixed-format surfaces use responsive grids, and new logos/testimonials/FAQ/calculator controls include `sm:` or multi-column transitions where useful. Keep tablet and ultra-wide screenshots in the final visual QA pass rather than adding a separate roadmap row. — why: SaaS landing pages are judged on tablet/desktop polish — `resources/views/sections/hero.blade.php`, `demo-request.blade.php`, `docs-onboarding.blade.php`, `calculator.blade.php` — M

5. **Shipped 2026-06-06: replaced jargon placeholder copy** — visible labels such as `conversion_command_label`, `activation_map_label`, `aha_step_label`, `pipeline_signal`, and `premium_layout_ready` now resolve to plain SaaS buyer language like "Demo pipeline", "Customer journey", "First value", "Pipeline", and "Landing page ready". — why: visible jargon undercuts the "premium, sold on visuals" positioning — `resources/lang/en/generic.php`, `resources/views/sections/demo-request.blade.php`, `docs-onboarding.blade.php` — S

6. **Shipped 2026-06-06: Make the calculator section interactive** — `sections/calculator.blade.php` now renders monthly visitor, conversion-rate, and expected-lift inputs with cache-safe scoped public JavaScript that computes additional accounts in place. — why: the section name now matches the behavior it promises — `resources/views/sections/calculator.blade.php` — M

7. **Reviewed 2026-06-06: Carousel no-JS fallback** — carousel controls are hidden by default (`hidden`) and the scroll tracks remain horizontally scrollable/snap-capable, so missing JavaScript does not expose visible dead controls. Keep this covered if future section work makes controls visible before JS hydration. — why: avoids dead controls on the public page — `resources/views/sections/content-listing.blade.php`, `blog.blade.php`, `features.blade.php` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` is only `["theme-saas", "theme-saas-frontend"]` — i.e. capabilities are generic surface markers, not feature claims, so gaps must be judged against what a SaaS site needs.

**Table-stakes for a SaaS theme that this package is missing or only stubs:**

- **Pricing/plan comparison table shipped** — `pricing` renders tier cards with prices, periods, CTAs, popular-plan highlighting, and a features matrix.
- **Functional demo-request / signup CTA shipped** — `demo-request` embeds the Form Builder Livewire form by handle when connected and renders a styled static lead form fallback otherwise.
- **Integrations / logos strip shipped** — `logos` is a first-class Theme Studio section for customer/integration proof.
- **Testimonials with attribution shipped** — `testimonials` is a first-class section with metric, quote, and attribution fields.
- **FAQ section shipped** — `faq` is a first-class details/summary accordion section.
- **Changelog / "what's new" surface** — SaaS vertical specifically values a changelog; not present (the `blog` section is the only content surface, and it degrades to generic "resources" when `capell-app/blog` is absent).
- **Docs surface now consumes document-style data** — `docs-onboarding` uses `$documentLifecycleAvailable` to distinguish connected/static states and renders linked document resources with version/status and duration metadata. Document Lifecycle remains admin/database-only, so the theme consumes prepared public data rather than querying the sibling package from Blade.

**Cross-sell / differentiator vs table-stakes:**

- Cross-sell with **form-builder** (demo-request/trial form) and **document-lifecycle** (docs) is now visible in the templates: Form Builder can be embedded by handle and docs onboarding can render governed document links with lifecycle metadata.
- A **payments** cross-sell (pricing CTA → checkout/subscription) remains future product depth outside this renderer plan; current pricing CTAs are intentionally URL-driven.

**Vs siblings:** the package leans on Foundation Theme for everything except section presentation; it does not register page types, settings, or its own routes (`database.migrations: false`, `settings: false`, no `providers.admin/frontend`). That keeps it light while still putting differentiating SaaS features in theme-owned section views: pricing matrix, demo capture, docs onboarding, logos, testimonials, FAQ, calculator, and dark/tokenized presentation.

## 4. Issues / Risks

- **Health check shipped, keep expanding diagnostics as the package grows.** `ThemeSaasHealthCheck` now verifies theme registration, required views, manifest/provider wiring, and marketplace media. Add future sections/assets to those diagnostics instead of letting the critical check drift back into a shallow compatibility-only assertion. — `src/Health/ThemeSaasHealthCheck.php`, `capell.json` healthChecks.
- **Closed 2026-06-05: Rendered-output safety coverage now complements static grep.** `tests/Unit/PublicOutputSafetyTest.php` still reads the Blade _source files_ and asserts the strings (`Filament`, `wire:`, `data-model`, `DB::`, `Frontend::`, `->translation`, `find(`) are absent. `SaasThemeDefinitionTest` now renders the full SaaS page renderer across every public section for anonymous and non-admin visitors with realistic section data, asserting the output hides authoring/package metadata, editor markers, field/model paths, signed/admin strings, and contenteditable surfaces. — `tests/Unit/PublicOutputSafetyTest.php`, `tests/Unit/SaasThemeDefinitionTest.php`.
- **Screenshot contract closed for seeded routes.** `docs/screenshots.json` now maps required frontend captures to real seeded Capell routes (`/theme-saas`, `/theme-saas-directory`, `/theme-saas-detail`, `/theme-saas-contact`, `/theme-saas-empty`, `/theme-saas-cta`) rather than stale `/saas-*` paths. `docs/screenshots/` contains 12 committed PNGs across frontend/admin light and dark evidence, and `capell.json` promotes the strongest homepage/contact/CTA route captures. The CTA section now uses package-owned frame/panel/stage CSS so runner output stays readable even when utility backgrounds are missing. — `docs/screenshots.json`, `docs/screenshots/`, `capell.json`, `resources/views/sections/navigation.blade.php`, `resources/views/sections/cta.blade.php`, `resources/css/theme-saas.css`.
- **Verification command context documented.** Package README and overview now instruct maintainers to run `vendor/bin/pest packages/theme-saas/tests` from the repository root, where Pest picks up the root `phpunit.xml`; the package does not ship a local PHPUnit config. — `README.md`, `docs/overview.md`.
- **Closed 2026-06-05: Adapter query-count ceiling covered.** Manifest sets `frontendRenderBudgetMs: 20`, `adminQueryBudget: 0`. Public Blade is DB-safe (grep for `::query`, `DB::`, `->paginate`, `Model::`, `find(` in views returned none; `PublicOutputSafetyTest` enforces it statically). `SaasThemePageAdapterTest` now asserts the adapter builds `ThemePageData` from hydrated `Frontend` context without database queries. Keep the runtime budget covered if the adapter starts loading related data itself. — `capell.json` performance, `src/ThemeStudio/Adapters/SaasThemePageAdapter.php`, `tests/Unit/SaasThemePageAdapterTest.php`.
- **Cache safety manifest reconciled.** Theme output remains declared non-cacheable, varies by `site`/`locale`, has no invalidation sources, and no longer queues invalidation. Keep this relationship covered if the theme later becomes cacheable. — `capell.json` performance.cacheSafety.
- **WCAG gaps.** Skip-link + `aria-label` on nav are present (good). But: many large `font-black` headings on tinted backgrounds (`text-cyan-200` on `bg-slate-950`, `text-cyan-700` eyebrows) need contrast verification; the mobile nav uses `<details>/<summary>` with `marker:hidden` but no `aria-expanded` state; carousel buttons rely on JS with `data-carousel-*` and may be unreachable/inert without it. No automated a11y assertion in tests. — `resources/views/sections/navigation.blade.php`, `content-listing.blade.php`, `demo-request.blade.php`.
- **Shipped 2026-06-05: Adapter fallback content is translated.** `SaasThemePageAdapter` now resolves premium landing, default navigation/footer, and section fallback labels through `capell-theme-saas::generic.*`, keeping adapter-supplied fallback output inside the package translation namespace instead of embedding English literals. — `src/ThemeStudio/Adapters/SaasThemePageAdapter.php`, `resources/lang/en/generic.php`.
- **No `<img>`/LCP handling in hero.** Grep found no `<img`, `loading=`, or `fetchpriority` in `hero.blade.php` — the hero is text/CSS only. Fine for now, but if a hero image/screenshot is added later (expected for SaaS), it must ship `fetchpriority="high"` and explicit dimensions to protect LCP. — `resources/views/sections/hero.blade.php` (forward-looking).

## 5. Marketplace & Selling

The manifest marketplace summary now uses this buyer-facing sentence:

> A conversion-focused premium theme for software and subscription products — hero, feature proof, pricing comparison, calculator, docs, and demo-request sections that turn a Capell site into a product-led landing experience.

The manifest and Composer description now use this buyer-facing product story:

> Theme SaaS gives software and subscription businesses a product-led storefront out of the box: an activation-first hero, metric and logo proof, a plan-comparison and pricing layout, an ROI calculator, docs onboarding, and a demo-request flow — all rendered from portable Capell content with no presentation markup stored in your pages. It extends Foundation Theme, so your content stays clean while this theme owns the conversion layout, palette, and rhythm. Pairs with Form Builder for live demo/trial capture and Document Lifecycle for in-theme docs, and reads brand tokens so the SaaS preset re-skins the whole site. Built on Blade + Tailwind, cache-safe, and translation-ready.
> (Note: payments/subscription checkout remains a future cross-sell; current pricing CTAs are URL-driven.)

**Screenshot/media gaps.** Marketplace now uses committed runner PNGs for the admin preset list, signed preview URL, frontend render, and seeded homepage/contact/CTA routes. Dark alternates are backed by real dark-mode token support; refresh dark route captures during the final visual QA pass if marketplace media needs the newest dark section depth.

**Differentiation & target buyer.** Target buyer: a SaaS/software founder or product-marketing owner running their marketing site on Capell who wants a conversion-ready, product-led look without bespoke design. Differentiate from sibling Capell themes on: (a) genuine SaaS surfaces (pricing matrix, calculator, demo-request, logos, testimonials, FAQ) vs generic content sections; (b) Form Builder + Document Lifecycle integrations; (c) a configurable brand-token palette and real dark mode.

**Keywords/tags (8–12):** `saas-theme`, `software-website`, `subscription`, `product-led-growth`, `landing-page`, `pricing-page`, `conversion`, `activation`, `demo-request`, `dark-mode`, `tailwind-theme`, `capell-theme`.

Completed 2026-06-07. Every prioritized roadmap row is closed. Theme SaaS now has real health diagnostics, anonymous/non-admin leak coverage, translated fallback sections, pricing matrix, Form Builder demo-request branch, Document Lifecycle docs state, buyer-facing marketplace copy, 12 committed route-backed PNG captures, tokenized palette, real dark mode, logo/testimonial/FAQ sections, interactive calculator, and screenshot-route alignment. Payments/subscription checkout remains future cross-sell depth outside the completed renderer plan.

## 6. Prioritized Roadmap

| Item                                                                                                                     | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------------------------------------------------ | ------ | ------ | ------ | ----------- |
| Shipped 2026-06-03: Implement real health-check logic (views/theme-registered/assets present), drop stub                 | Done   | S      | High   | §4          |
| Done/Shipped: Rendered-output anonymous/non-admin leak coverage across all sections + adapter query-count assertion      | Done   | M      | High   | §4          |
| Shipped 2026-06-05: Translate hard-coded English in `SaasThemePageAdapter` fallback sections                             | Done   | S      | Med    | §4          |
| Shipped 2026-06-06: Build a real pricing/plan-comparison matrix (tiers × features, popular flag, CTA/period fields)      | Done   | M      | High   | §3          |
| Shipped 2026-06-06: Implement functional demo-request/trial form via Form Builder (with static fallback)                 | Done   | M      | High   | §3          |
| Shipped 2026-06-06: Deepen connected pricing/docs/demo-request states beyond guidance copy into real embeds/data         | Done   | M      | High   | §2.1, §3    |
| Done/Shipped: Rewrite marketplace `summary` + composer `description`; capture & commit the 12 PNG screenshots            | Done   | M      | High   | §5          |
| Replace jargon placeholder copy in `generic.php` and visible eyebrows                                                    | Done   | S      | Med    | §2.5        |
| Shipped 2026-06-06: Tokenize hard-coded palette so the `saas` preset actually re-skins the theme                         | Done   | L      | Med    | §2.3        |
| Shipped 2026-06-06: Add genuine dark mode (token set + `.dark`/system variants) and a true dark screenshot-ready surface | Done   | L      | High   | §2.2        |
| Shipped 2026-06-06: Add logos/integrations strip + testimonial cards + FAQ accordion sections                            | Done   | M      | Med    | §3          |
| Shipped 2026-06-06: Make calculator interactive with cache-safe public JavaScript                                        | Done   | M      | Med    | §2.6        |
| Done/Shipped: Verify demo-seeded slugs match `screenshots.json` capture routes (or fix the routes)                       | Done   | S      | Med    | §4          |
