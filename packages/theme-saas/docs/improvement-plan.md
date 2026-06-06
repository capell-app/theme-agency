# Theme SaaS — Improvement & Growth Plan

> Package: capell-app/theme-saas · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

Theme SaaS registers the `saas` theme key (`SaasThemeServiceProvider::THEME_KEY = 'saas'`) and a Blade renderer that records package inheritance as `extends: "capell-app/foundation-theme"` in the manifest while using runtime `extends: 'default'` in Theme Studio, reusing Foundation Theme's runtime data/section contracts while shipping its own page wrapper and 14 section views. It registers a `SaasThemePageAdapter` (renderer-side, reads `Frontend::page()` + `meta.theme_demo.render_data`) and 13 section renderers (`navigation, hero, features, proof, content-listing, comparison, calculator, pricing, docs-onboarding, demo-request, cta, footer, blog`); 12 use `ViewSectionRenderer`, `blog` uses a dedicated `BlogSectionRenderer`. The demo command `capell:theme-saas-demo` exists (`src/Console/Commands/DemoCommand.php`) and delegates to `InstallSaasThemeDemoAction` → `ThemeDemoPageInstaller::run($data, 'saas', 'SaaS')`. Theme key registration, demo command, and all 18 Blade views verified present and wired. Marketplace and Composer copy now use buyer-facing software/subscription positioning, and the marketplace manifest points at committed PNG captures. Screenshots: `docs/screenshots/` now contains 12 committed PNG captures, and `capell.json.marketplace.screenshots` promotes the strongest route-backed frontend/admin surfaces.

## Completed Improvement Slices

- **2026-06-03:** Rewrote marketplace/Composer copy, promoted committed PNG captures into the marketplace manifest, and replaced the critical health-check stub with real diagnostics.
- **2026-06-04:** Wired Content Sections, Document Lifecycle, and Form Builder availability into pricing, docs onboarding, and demo request sections with translated connected/static guidance and branch coverage.
- **2026-06-05:** Reconciled manifest cache safety by keeping theme output non-cacheable and disabling queued invalidation while no invalidation sources are declared.
- **2026-06-05:** Aligned package README and overview verification commands to the repo-root Pest invocation and documented that the package does not ship its own PHPUnit config.
- **2026-06-06:** Reconciled the screenshot contract to actual seeded Capell routes, captured six route-backed frontend PNGs, promoted the strongest homepage/contact/CTA captures, and added package CSS fallbacks for navigation and CTA contrast in the runner build.

## 2. Improvements (existing functionality)

Prioritized.

1. **Shipped 2026-06-04: Consume the availability flags already plumbed into views** — `pricing`, `docs-onboarding`, and `demo-request` now render translated connected/static guidance from `$contentSectionsAvailable`, `$documentLifecycleAvailable`, and `$formBuilderAvailable`, with tests covering both states. Full embeds/forms remain a follow-up. — why: removes dead data plumbing and makes the templates honest about their dependency on sibling packages — `src/SaasThemeServiceProvider.php`, `resources/views/sections/pricing.blade.php`, `resources/views/sections/docs-onboarding.blade.php`, `resources/views/sections/demo-request.blade.php` — M

2. **Add dark-mode rendering** — there are **zero `dark:` variants in the views** and **no `prefers-color-scheme`/`.dark` rules in CSS**, yet the package ships a full `*-dark.png` screenshot set (`frontend-page-rendered-with-saas-theme-dark.png`, `theme-preset-selection-showing-saas-dark.png`, `theme-preview-url-output-dark.png`). The "dark" screenshots are OS/browser chrome around a light-only theme. SaaS buyers expect a dark theme. Either add genuine dark token variants (the `page.blade.php` already injects `$brand->tokens()` as CSS custom properties — extend with a dark token set) or stop implying dark support in media. — why: the dark screenshots over-promise; a real dark mode is a top SaaS differentiator — `resources/css/theme-saas.css`, `resources/views/page.blade.php`, all `resources/views/sections/*.blade.php` — L

3. **Tokenize hard-coded slate/cyan/blue palette** — sections hard-code raw Tailwind colors (`bg-slate-950`, `text-cyan-200`, `text-slate-600`, `bg-slate-50`) across `hero`, `navigation`, `demo-request`, `docs-onboarding`, `comparison`, etc. Navigation/links already use `var(--saas-primary)`, proving the token path exists. Preset values (`primaryColor #2563eb`, `accentColor #06b6d4`, `neutralColor #0f172a`) only reach a few elements; most chrome ignores the preset. Route fixed brand colors through the brand tokens so the preset actually re-skins the theme. — why: today changing the preset barely changes the page; tokenization is what makes a premium theme feel configurable — `resources/views/sections/*.blade.php`, `resources/css/theme-saas.css` — L

4. **Add responsive coverage at common breakpoints beyond `md`/`lg`** — sections jump from single column to `md:grid-cols-[0.8fr_1.2fr]` / `lg:grid-cols-[0.9fr_1.1fr]` with no `sm:` step and no `xl:`/`2xl:` max-width discipline beyond `max-w-6xl`. Tablet portrait and ultra-wide both get awkward gaps. Audit `hero`, `demo-request`, `docs-onboarding`, `calculator`, `comparison` for an intermediate `sm:` layout and a constrained ultra-wide container. — why: SaaS landing pages are judged on tablet/desktop polish — `resources/views/sections/hero.blade.php`, `demo-request.blade.php`, `docs-onboarding.blade.php`, `calculator.blade.php` — M

5. **Shipped 2026-06-06: replaced jargon placeholder copy** — visible labels such as `conversion_command_label`, `activation_map_label`, `aha_step_label`, `pipeline_signal`, and `premium_layout_ready` now resolve to plain SaaS buyer language like "Demo pipeline", "Customer journey", "First value", "Pipeline", and "Landing page ready". — why: visible jargon undercuts the "premium, sold on visuals" positioning — `resources/lang/en/generic.php`, `resources/views/sections/demo-request.blade.php`, `docs-onboarding.blade.php` — S

6. **Make the calculator/comparison sections actually interactive (or rename)** — `sections/calculator.blade.php` is fully static markup (no `x-data`, no `wire:`, no `<script>`; grep confirms no Alpine/Livewire/JS in any view). A "calculator" that cannot compute is a labelling problem. Either add Alpine-driven ROI math (client-side, cache-safe) or rename the section to "value summary" so the demo isn't selling a feature that doesn't function. — why: the section name sets an expectation the markup doesn't meet — `resources/views/sections/calculator.blade.php` — M

7. **Harden the carousel against missing JS** — `content-listing`/`proof` use `data-carousel`, `data-carousel-track`, `data-carousel-prev/next` attributes (foundation-theme presumably binds JS). If Foundation's carousel script is absent or fails, the prev/next buttons are inert with no graceful fallback. Confirm the dependency and add a no-JS fallback (scroll-snap already present via `snap-mandatory` / `overflow-x-auto`, so hide the buttons when JS is off). — why: avoids dead controls on the public page — `resources/views/sections/content-listing.blade.php`, `proof.blade.php` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` is only `["theme-saas", "theme-saas-frontend"]` — i.e. capabilities are generic surface markers, not feature claims, so gaps must be judged against what a SaaS site needs.

**Table-stakes for a SaaS theme that this package is missing or only stubs:**

- **Working pricing/plan comparison table** — `pricing` and `comparison` views exist but render only generic card lists (`$item['title']` / `$item['summary']`); there is no real plan matrix (tiers × features, monthly/annual toggle, "most popular" highlight, per-seat pricing). This is the single most expected SaaS surface. (`resources/views/sections/pricing.blade.php`, `comparison.blade.php`)
- **Functional demo-request / signup CTA** — `demo-request.blade.php` renders static "qualification" cards and ignores `$formBuilderAvailable`. There is no actual form, no trial-signup CTA wiring. `screenshots.json` itself notes the demo-request layout "Requires capell-app/form-builder" — so the **cross-sell with `form-builder` is designed but not implemented**. (`resources/views/sections/demo-request.blade.php`)
- **Integrations / logos strip** — no integrations grid or customer-logo wall section. `proof.blade.php` carries metrics but not a logo cloud. Logos are standard SaaS social proof.
- **Testimonials with attribution** — `proof` shows numeric metrics only; no quote/testimonial card with name/role/company/photo.
- **FAQ section** — none. SaaS pricing/feature pages almost always pair with an FAQ accordion.
- **Changelog / "what's new" surface** — SaaS vertical specifically values a changelog; not present (the `blog` section is the only content surface, and it degrades to generic "resources" when `capell-app/blog` is absent).
- **Docs surface depends on an uninstalled-by-default sibling** — `docs-onboarding` is plumbed with `$documentLifecycleAvailable` but ignores it; `screenshots.json` notes the docs layout "Requires capell-app/document-lifecycle". Designed cross-sell, not wired.

**Cross-sell / differentiator vs table-stakes:**

- Cross-sell with **form-builder** (demo-request/trial form) and **document-lifecycle** (docs) is _declared in screenshots.json and plumbed via availability flags_ but _not implemented in templates_. Finishing this is the highest-leverage gap and the clearest differentiator from sibling themes.
- A **payments** cross-sell (pricing CTA → checkout/subscription) is the obvious SaaS-vertical hook and is entirely absent — pricing CTAs are static `url` links.

**Vs siblings:** the package leans on Foundation Theme for everything except section presentation; it does not register page types, settings, or its own routes (`database.migrations: false`, `settings: false`, no `providers.admin/frontend`). That keeps it light but means every differentiating SaaS feature (pricing toggle, FAQ, logos) must live in these section views — none of which exist yet beyond generic card loops.

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
> (Note: pricing-comparison, calculator interactivity, logo/testimonial proof, and the Form Builder / Document Lifecycle integrations described above are still partially placeholder today — see §3.)

**Screenshot/media gaps.** Marketplace now uses committed runner PNGs for the admin preset list, signed preview URL, frontend render, and seeded homepage/contact/CTA routes. Dark alternates remain available for the original admin/frontend evidence, but real dark-mode layout captures should wait until genuine dark mode exists (§2.2), rather than duplicating light-only theme output.

**Differentiation & target buyer.** Target buyer: a SaaS/software founder or product-marketing owner running their marketing site on Capell who wants a conversion-ready, product-led look without bespoke design. Differentiate from sibling Capell themes on: (a) genuine SaaS surfaces (pricing matrix, calculator, demo-request) vs generic content sections; (b) Form Builder + Document Lifecycle + Payments integrations; (c) a configurable brand-token palette and real dark mode. Right now the _promise_ differentiates but the _implementation_ is close to a generic card theme — closing §3 is what earns the premium tier.

**Keywords/tags (8–12):** `saas-theme`, `software-website`, `subscription`, `product-led-growth`, `landing-page`, `pricing-page`, `conversion`, `activation`, `demo-request`, `dark-mode`, `tailwind-theme`, `capell-theme`.

## 6. Prioritized Roadmap

| Item                                                                                                                | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Shipped 2026-06-03: Implement real health-check logic (views/theme-registered/assets present), drop stub            | Done   | S      | High   | §4          |
| Done/Shipped: Rendered-output anonymous/non-admin leak coverage across all sections + adapter query-count assertion | Done   | M      | High   | §4          |
| Shipped 2026-06-05: Translate hard-coded English in `SaasThemePageAdapter` fallback sections                        | Done   | S      | Med    | §4          |
| Shipped 2026-06-06: Build a real pricing/plan-comparison matrix (tiers × features, popular flag, CTA/period fields) | Done   | M      | High   | §3          |
| Implement functional demo-request/trial form via Form Builder (with static fallback)                                | Next   | M      | High   | §3          |
| Deepen connected pricing/docs/demo-request states beyond guidance copy into real embeds/data                        | Next   | M      | High   | §2.1, §3    |
| Done/Shipped: Rewrite marketplace `summary` + composer `description`; capture & commit the 12 PNG screenshots      | Done   | M      | High   | §5          |
| Replace jargon placeholder copy in `generic.php` and visible eyebrows                                               | Done   | S      | Med    | §2.5        |
| Tokenize hard-coded palette so the `saas` preset actually re-skins the theme                                        | Next   | L      | Med    | §2.3        |
| Add genuine dark mode (token set + `dark:` variants) and a true dark screenshot                                     | Next   | L      | High   | §2.2        |
| Add logos/integrations strip + testimonial cards + FAQ accordion sections                                           | Later  | M      | Med    | §3          |
| Make calculator interactive (Alpine, cache-safe) or rename to value-summary                                         | Later  | M      | Med    | §2.6        |
| Done/Shipped: Verify demo-seeded slugs match `screenshots.json` capture routes (or fix the routes)                 | Done   | S      | Med    | §4          |
