# Theme Commerce — Improvement & Growth Plan

> Package: capell-app/theme-commerce · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

Theme Commerce ("Editorial Commerce") registers theme key `commerce`, extends `default` (manifest says `capell-app/foundation-theme`), and ships a Blade renderer (`CommerceThemeServiceProvider` → `BladeThemeRenderer` with `page.blade.php` wrapper). It registers **16 section renderers** (`src/CommerceThemeServiceProvider.php` `sectionRenderers()`): navigation, hero, features→product-grid, content-listing→collections, product-finder, collections, product-grid, comparison, catalog, lookbook, promotion, buying-guide, proof, blog-teaser, cta, footer — backed by 17 Blade views under `resources/views`. Demo command `capell:theme-commerce-demo` delegates to `InstallCommerceThemeDemoAction` → Foundation `ThemeDemoPageInstaller` (seeds ≥7 pages). It is a **styling theme, not a commerce integration**: it renders content/section data only and never queries products itself. Integration with `shopify-commerce` is one boolean (`$shopifyAvailable`) toggling copy in `catalog.blade.php`; `blog` availability does the same in `blog-teaser.blade.php`; `campaign-studio` and `media-library` availability feed promotion/lookbook views. Marketplace copy is buyer-facing, `capell.json` declares committed marketplace JPGs only, and `docs/screenshots.json` still declares 13 capture entries with no committed `docs/screenshots/` PNG set.

## Completed Improvement Slices

- **2026-06-03:** Replaced marketplace plumbing copy with premium commerce positioning, limited marketplace screenshots to committed preview assets, declared optional package supports, documented the dual `extends` meaning, and replaced the stub health check with registration/view/vendor-asset diagnostics.
- **2026-06-04:** Moved hero and catalog hard-coded English into translations, made hero trust badges data-driven with translated fallbacks, replaced the catalog carousel's theme-specific public selector with a generic selector, and added tests for those contracts.

## 2. Improvements (existing functionality)

Prioritized.

1. **Route theme colours through tokens instead of hard-coded hex** — 106 arbitrary-hex utilities (`bg-[#17211c]`, `text-[#e86f5c]`, `bg-[#fffaf3]`, `bg-[#1f5f4a]`) across the views mean the theme presets and per-brand tokens cannot recolour the UI; the `--retail-*`/`--theme-*` token chain in CSS is mostly bypassed. Replace with token-backed classes (`bg-[var(--retail-ink)]`, `text-[var(--retail-accent)]`) so the declared `commerce` preset and brand colours actually apply. — `resources/views/sections/*.blade.php`, `resources/css/theme-commerce.css` — **L**
2. **Add dark mode** — zero `dark:` variants in any view (sibling Foundation hero ships `dark:` pairs). A premium-tier theme should support dark rendering or explicitly document its absence. Add `dark:` variants keyed off the shell, or invert via tokens in `theme-commerce.css`. — `resources/views/**`, `resources/css/theme-commerce.css` — **L**
3. **Hero/catalog copy translated.** — Hero trust pill fallbacks and the catalog highlights panel now use `capell-theme-commerce::generic.*` translation keys, with regression tests covering the rendered strings. — `resources/views/sections/hero.blade.php`, `resources/views/sections/catalog.blade.php`, `resources/lang/en/generic.php`, `tests/Unit/CommerceThemeDefinitionTest.php` — **S**
4. **Hero trust badges are data-driven.** — The hero reads `$section->badges` when provided and falls back to translated retail badges, so custom page data can replace the stock trust copy without editing Blade. — `resources/views/sections/hero.blade.php` — **M**
5. **Fix the empty `<details>` mobile menu spacing/`marker:hidden`** — mobile nav uses `<details>`/`<summary>` with `marker:hidden` (`navigation.blade.php:38,41`); `marker:hidden` is not a real utility (use `[&::-webkit-details-marker]:hidden` + `list-none`, which is already partly present). Verify the disclosure renders without a duplicate marker across browsers. — `resources/views/sections/navigation.blade.php` — **S**
6. **Comparison section is a stacked list, not a comparison** — `comparison.blade.php` renders a 2-column title/description list; it does not compare options side by side, which is what "comparison" implies for retail (spec/price columns). Either rename the section intent or build a real multi-column comparison grid. — `resources/views/sections/comparison.blade.php` — **M**
7. **Catalog "Highlights" panel duplicates the connected/ready panel** — the right `retail-frame` panel and the inner dark panel both restate merchandising messaging with overlapping dark blocks; tighten to one panel or differentiate (e.g. live stock vs. value prop). — `resources/views/sections/catalog.blade.php:55-85` — **S**
8. **Image `loading`/`decoding`/`sizes` hints for LCP** — hero (`hero.blade.php:61`) and product grid imagery have no `loading`/`fetchpriority`/`sizes`; the hero image is the LCP element. Add `fetchpriority="high"` to hero media and `loading="lazy"` to below-fold product images. — `hero.blade.php`, `product-grid.blade.php` — **S**

## 3. Missing Features (gaps)

Manifest `capabilities[]` is only `["theme-commerce","theme-commerce-frontend"]` (generic), so gaps are measured against what a retail site needs and against the package's own marketed promises.

- **Promised layouts have no renderers or views.** `docs/screenshots.json` and the committed SVGs advertise **search**, **store-event**, **newsletter**, and **campaign** layouts (requiring `capell-app/search`, `capell-app/events`, `capell-app/newsletter`, `capell-app/campaign-studio`). There are **no** `search`/`event`/`newsletter` section renderers or Blade views, and only `promotion`/`lookbook` partially cover campaign/lookbook. The marketing surface over-promises vs. the code. Either build these sections or remove the SVGs/entries. — table-stakes for the claims made.
- **No product-detail (PDP) layout.** `screenshots.json` promises "Product detail with gallery, price, variants, stock proof, recommendations, basket CTA" but there is no product-detail section/view — only a `product-grid`. A commerce theme's highest-value surface is the PDP. — **differentiator + table-stakes.**
- **No cart / checkout / mini-basket styling.** Navigation has a generic CTA but no basket affordance; there is no cart drawer, line-item, or checkout styling. For a theme bundled with `shopify-commerce` this is the core conversion surface. — **table-stakes.**
- **No promo/sale banner or countdown primitives.** `promotion.blade.php` exists but `screenshots.json` promises "offer hero, countdown, segmented CTAs"; no countdown, no sale-badge/price-strike, no announcement bar. — **table-stakes for retail.**
- **No trust/reviews/ratings components.** `proof` exists as a generic ledger, but no star ratings, review cards, or trust-badge row (returns, secure-checkout, shipping). — **differentiator.**
- **Shopify integration is cosmetic only.** `CatalogSectionRenderer`/`BlogTeaserSectionRenderer` receive one boolean each; the theme renders no real product, price, variant, or stock data from `shopify-commerce`. Deeper integration (price formatting, availability states, variant chips driven by hydrated render data) is the natural bundle value. — **differentiator vs. sibling content themes.**
- **Empty/zero-state coverage is thin.** Sections fall back to placeholder skeletons (hero) or generic copy; no consistent empty-state for collections/search/product grid. — **table-stakes.**

## 4. Issues / Risks

- **Health check shipped.** `ThemeCommerceHealthCheck` now probes Theme Studio registration, required views, and vendor assets, with unit coverage for passing and failing registration states. Remaining opportunity: add a package-specific doctor command if direct console diagnostics become necessary. — `src/Health/ThemeCommerceHealthCheck.php`, `tests/Unit/ThemeCommerceHealthCheckTest.php`.
- **Optional dependency metadata aligned.** `capell.json` now lists `blog`, `campaign-studio`, `media-library`, and `shopify-commerce` in `dependencies.supports`, matching the package availability checks in `sectionRenderers()`. — `src/CommerceThemeServiceProvider.php`, `capell.json`.
- **Marketplace screenshot triple-mismatch.** `capell.json` declares 6 media items, `docs/screenshots.json` declares 13 entries, and **0** PNGs exist (`docs/screenshots/` absent). A premium theme sold on visuals currently ships only placeholder SVGs/stock jpgs. — `capell.json`, `docs/screenshots.json`.
- **Dual `extends` meaning documented.** Manifest `extends: "capell-app/foundation-theme"` records package inheritance; `ThemeDefinitionData.extends = 'default'` records runtime theme-key inheritance, and the provider comment documents that distinction. — `src/CommerceThemeServiceProvider.php`, `capell.json`.
- **Cache safety contradiction.** Manifest `performance.cacheSafety` sets `cacheable: false` with empty `invalidationSources: []` but `queueInvalidation: true`. A non-cacheable theme queuing invalidation with no sources is contradictory; reconcile (themes are normally cacheable per-site/locale). — `capell.json`.
- **`frontendRenderBudgetMs: 20` is unverified.** No performance/render test asserts the 16-section render stays under 20ms; the budget is declared but untested. — `capell.json`, `tests/`.
- **Renderers swallow exceptions when not `failLoudly`.** `CatalogSectionRenderer`/`BlogTeaserSectionRenderer` `catch (Throwable)` and return `''` unless `failLoudly`. They are registered with `failLoudly: true` (good), but the default path silently blanks a section — a section vanishing in production with no log. Add logging on the swallow path. — `src/Rendering/*.php`.
- **Public-output safety: covered.** `tests/Unit/PublicOutputSafetyTest.php` asserts no DB calls (`DB::`, `::query(`, `loadMissing(`, `Frontend::`, etc.) and no authoring/leak tokens (`wire:`, `data-field`, `model_id`, `signed`, `permission`, package name) in views+lang. `page.blade.php` renders pre-built `$content` and brand tokens only. This is solid; keep new sections inside the same test.
- **WCAG gaps.** Hero/catalog dark panels use `text-stone-200`/`text-white/40` on dark — verify contrast ratios; `:focus-visible` outline exists (good, `theme-commerce.css:78`), skip-link present (good). Carousel buttons (`catalog.blade.php`) are `hidden` with no keyboard/visible affordance on desktop — scroll-snap region needs a focusable control. — `catalog.blade.php`, `product-grid.blade.php`.
- **Test gap: only 4 view-rendering paths asserted.** Tests cover registration, definition keys, demo command, output safety, and render of catalog/blog-teaser/lookbook/promotion/buying-guide + feature/content-listing fallbacks — but hero, navigation, comparison, collections, product-finder, proof, cta, footer have no per-section render assertion. — `tests/`.

## 5. Marketplace & Selling

**Original `summary` critique:** _"Retail buying-path theme screenshots from route-backed demo layouts."_ — this screenshot-pipeline copy has been replaced with the buyer-facing summary below. The package composer description remains short, but the manifest description and marketplace description now carry the stronger positioning.

**Improved 1-sentence summary:**

> A premium, conversion-focused retail theme for Capell — image-led catalog, product discovery, social proof, and buying-guide layouts that turn browsing into baskets.

**Improved 3–4 sentence description:**

> Editorial Commerce is a premium Capell theme built for retail and e-commerce storefronts that need to feel like a buying journey, not a styled brochure. It ships an image-led hero, product finder, collection and product grids, comparison and proof sections, buying-guide editorial, and conversion CTAs — all driven by hydrated render data with zero database access in public Blade. Pair it with Capell Shopify Commerce to light up connected-catalog merchandising panels, and with Blog for buying-guide content that supports purchase decisions. Warm editorial direction (deep ink, forest-green merchandising, coral action accents) and a token-driven design system keep every store on-brand while staying fast and accessible.

**Screenshot/media gaps (must fix before sale):** ship the **13** real PNGs promised by `docs/screenshots.json` (currently 0), and reconcile `capell.json`'s 6 declared media to match. Replace placeholder SVG "layout" cards with rendered captures (homepage, collection, product, lookbook, buying-guide). Add `hero-desktop`/`hero-mobile` real frames (the jpgs exist but aren't wired into `capell.json` marketplace list). Do **not** advertise search/event/newsletter/campaign layouts until the sections exist (Section 3).

**Differentiation & bundling:** vs. sibling content themes (Agency, Corporate) this is the only retail/buying-path theme — lean into PDP, cart, promo/countdown, and reviews to make that real. The natural commercial bundle is **theme-commerce + shopify-commerce (+ payments)** sold as a "Capell Storefront" pack; today the Shopify tie is one boolean, so the bundle story is weak until deeper integration lands (Section 3).

**Target buyer:** retail/DTC store owners and agencies launching a Capell storefront who want a polished, conversion-oriented design without bespoke front-end work.

**Keywords/tags:** ecommerce theme, retail theme, storefront, product grid, catalog, conversion, shopify, buying guide, lookbook, merchandising, collections, premium Capell theme.

## 6. Prioritized Roadmap

| Item                                                                                             | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------------------------ | ------ | ------ | ------ | ----------- |
| Implement real `ThemeCommerceHealthCheck` (or downgrade severity)                                | Done   | S      | High   | 4           |
| Capture & commit the 13 real PNG screenshots; reconcile `capell.json` 6 vs `screenshots.json` 13 | Now    | M      | High   | 4, 5        |
| Translate hard-coded hero/catalog English strings                                                | Done   | S      | Med    | 2           |
| Rewrite `summary` + manifest description                                                         | Done   | S      | High   | 5           |
| Declare `campaign-studio` + `media-library` in `capell.json` supports                            | Done   | S      | Med    | 4           |
| Resolve `extends` (`foundation-theme` vs `default`)                                              | Done   | S      | Med    | 4           |
| Reconcile cacheSafety contradiction                                                              | Done   | S      | Med    | 4 — closed 2026-06-05: the manifest now keeps non-cacheable theme output from queueing invalidation when no invalidation sources are declared, with regression coverage. |
| Replace 106 hard-coded hex utilities with theme tokens                                           | Next   | L      | High   | 2           |
| Build product-detail (PDP) section + view                                                        | Next   | L      | High   | 3           |
| Add cart/mini-basket + promo/countdown + reviews/ratings components                              | Next   | L      | High   | 3           |
| Add per-section render tests (hero, nav, comparison, proof, cta, footer) + render-budget test    | Next   | M      | Med    | 4           |
| Make hero trust badges data-driven                                                               | Done   | M      | Med    | 2           |
| Add dark mode variants/tokens                                                                    | Later  | L      | Med    | 2           |
| Build search/event/newsletter/campaign sections to match advertised SVGs (or remove SVGs)        | Later  | L      | Med    | 3           |
| Deepen `shopify-commerce` integration (price/variant/stock via hydrated data)                    | Later  | L      | High   | 3           |
| Real multi-column comparison grid + carousel keyboard a11y                                       | Later  | M      | Med    | 2, 4        |
