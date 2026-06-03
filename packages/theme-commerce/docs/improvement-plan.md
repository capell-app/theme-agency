# Theme Commerce — Improvement & Growth Plan

> Package: capell-app/theme-commerce · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

Theme Commerce ("Editorial Commerce") registers theme key `commerce`, extends `default` (manifest says `capell-app/foundation-theme`), and ships a Blade renderer (`CommerceThemeServiceProvider` → `BladeThemeRenderer` with `page.blade.php` wrapper). It registers **16 section renderers** (`src/CommerceThemeServiceProvider.php` `sectionRenderers()`): navigation, hero, features→product-grid, content-listing→collections, product-finder, collections, product-grid, comparison, catalog, lookbook, promotion, buying-guide, proof, blog-teaser, cta, footer — backed by 17 Blade views under `resources/views`. Demo command `capell:theme-commerce-demo` delegates to `InstallCommerceThemeDemoAction` → Foundation `ThemeDemoPageInstaller` (seeds ≥7 pages). It is a **styling theme, not a commerce integration**: it renders content/section data only and never queries products itself. Integration with `shopify-commerce` is one boolean (`$shopifyAvailable`) toggling copy in `catalog.blade.php`; `blog` availability does the same in `blog-teaser.blade.php`. Marketplace summary verbatim: _"Retail buying-path theme screenshots from route-backed demo layouts."_ Screenshots: `capell.json` declares **6** media items (1 jpg + 5 placeholder SVGs); `docs/screenshots.json` declares **13** capture entries; **0** real `.png` screenshots are committed and `docs/screenshots/` does not exist — every shipped marketplace image is a placeholder SVG or stock jpg.

## 2. Improvements (existing functionality)

Prioritized.

1. **Route theme colours through tokens instead of hard-coded hex** — 106 arbitrary-hex utilities (`bg-[#17211c]`, `text-[#e86f5c]`, `bg-[#fffaf3]`, `bg-[#1f5f4a]`) across the views mean the theme presets and per-brand tokens cannot recolour the UI; the `--retail-*`/`--theme-*` token chain in CSS is mostly bypassed. Replace with token-backed classes (`bg-[var(--retail-ink)]`, `text-[var(--retail-accent)]`) so the declared `commerce` preset and brand colours actually apply. — `resources/views/sections/*.blade.php`, `resources/css/theme-commerce.css` — **L**
2. **Add dark mode** — zero `dark:` variants in any view (sibling Foundation hero ships `dark:` pairs). A premium-tier theme should support dark rendering or explicitly document its absence. Add `dark:` variants keyed off the shell, or invert via tokens in `theme-commerce.css`. — `resources/views/**`, `resources/css/theme-commerce.css` — **L**
3. **Translate hard-coded hero/catalog copy** — hero trust pills "Premium stock visuals" / "Fast checkout" / "Built for conversion" (`hero.blade.php:44,49,54`) and the catalog highlights panel "Highlights" / "Conversion-ready merchandising" / "Discover products by behavior, seasonality, and intent signals for stronger margin." (`catalog.blade.php:76,79,82`) are literal English. Move to `resources/lang/en/generic.php` (`__()`); the rest of the theme already uses translation keys. — `hero.blade.php`, `catalog.blade.php` — **S**
4. **Make hero trust badges data-driven, not fixed** — the three hero pills are hard-coded markup, so every site shows identical badges. Drive from `$section` (e.g. `$section->badges`) with the current strings as translated fallback. — `resources/views/sections/hero.blade.php` — **M**
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

- **Health check is a stub.** `src/Health/ThemeCommerceHealthCheck.php` implements only `compatibleCapellApiVersion(): '^4.0'` — no health/`check()` logic — yet `capell.json` lists it with `"severity": "critical"`. Diagnostics will treat a no-op as a critical surface. Implement real checks (theme registered, views resolvable, CSS asset present) or drop the severity. — `src/Health/ThemeCommerceHealthCheck.php`, `capell.json`.
- **Manifest dependency / availability mismatch.** `sectionRenderers()` reads `capell-app/campaign-studio` and `capell-app/media-library` availability, but `capell.json` `dependencies.supports` lists only `blog` + `shopify-commerce`. The `lookbook`/`promotion` integrations are undeclared, so the marketplace won't surface them and installs won't hint at the optional packages. — `src/CommerceThemeServiceProvider.php`, `capell.json`.
- **Marketplace screenshot triple-mismatch.** `capell.json` declares 6 media items, `docs/screenshots.json` declares 13 entries, and **0** PNGs exist (`docs/screenshots/` absent). A premium theme sold on visuals currently ships only placeholder SVGs/stock jpgs. — `capell.json`, `docs/screenshots.json`.
- **Theme `extends` inconsistency.** Manifest `extends: "capell-app/foundation-theme"`, but `ThemeDefinitionData.extends` is `'default'` (`CommerceThemeServiceProvider::definition()`). Confirm which the renderer honours; mismatched parent could change inherited section fallbacks. — `src/CommerceThemeServiceProvider.php`.
- **Cache safety contradiction.** Manifest `performance.cacheSafety` sets `cacheable: false` with empty `invalidationSources: []` but `queueInvalidation: true`. A non-cacheable theme queuing invalidation with no sources is contradictory; reconcile (themes are normally cacheable per-site/locale). — `capell.json`.
- **`frontendRenderBudgetMs: 20` is unverified.** No performance/render test asserts the 16-section render stays under 20ms; the budget is declared but untested. — `capell.json`, `tests/`.
- **Renderers swallow exceptions when not `failLoudly`.** `CatalogSectionRenderer`/`BlogTeaserSectionRenderer` `catch (Throwable)` and return `''` unless `failLoudly`. They are registered with `failLoudly: true` (good), but the default path silently blanks a section — a section vanishing in production with no log. Add logging on the swallow path. — `src/Rendering/*.php`.
- **Public-output safety: covered.** `tests/Unit/PublicOutputSafetyTest.php` asserts no DB calls (`DB::`, `::query(`, `loadMissing(`, `Frontend::`, etc.) and no authoring/leak tokens (`wire:`, `data-field`, `model_id`, `signed`, `permission`, package name) in views+lang. `page.blade.php` renders pre-built `$content` and brand tokens only. This is solid; keep new sections inside the same test.
- **WCAG gaps.** Hero/catalog dark panels use `text-stone-200`/`text-white/40` on dark — verify contrast ratios; `:focus-visible` outline exists (good, `theme-commerce.css:78`), skip-link present (good). Carousel buttons (`catalog.blade.php`) are `hidden` with no keyboard/visible affordance on desktop — scroll-snap region needs a focusable control. — `catalog.blade.php`, `product-grid.blade.php`.
- **Test gap: only 4 view-rendering paths asserted.** Tests cover registration, definition keys, demo command, output safety, and render of catalog/blog-teaser/lookbook/promotion/buying-guide + feature/content-listing fallbacks — but hero, navigation, comparison, collections, product-finder, proof, cta, footer have no per-section render assertion. — `tests/`.

## 5. Marketplace & Selling

**Current `summary` critique:** _"Retail buying-path theme screenshots from route-backed demo layouts."_ — describes the screenshot tooling, not the product; reads like an internal QA note, names no benefit, no buyer. **composer `description` critique:** _"Editorial commerce theme for Capell"_ — generic and flat; no audience, no differentiator, no outcome.

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
| Implement real `ThemeCommerceHealthCheck` (or downgrade severity)                                | Now    | S      | High   | 4           |
| Capture & commit the 13 real PNG screenshots; reconcile `capell.json` 6 vs `screenshots.json` 13 | Now    | M      | High   | 4, 5        |
| Translate hard-coded hero/catalog English strings                                                | Now    | S      | Med    | 2           |
| Rewrite `summary` + composer `description` (verbatim copy in §5)                                 | Now    | S      | High   | 5           |
| Declare `campaign-studio` + `media-library` in `capell.json` supports                            | Now    | S      | Med    | 4           |
| Resolve `extends` (`foundation-theme` vs `default`) + cacheSafety contradiction                  | Now    | S      | Med    | 4           |
| Replace 106 hard-coded hex utilities with theme tokens                                           | Next   | L      | High   | 2           |
| Build product-detail (PDP) section + view                                                        | Next   | L      | High   | 3           |
| Add cart/mini-basket + promo/countdown + reviews/ratings components                              | Next   | L      | High   | 3           |
| Add per-section render tests (hero, nav, comparison, proof, cta, footer) + render-budget test    | Next   | M      | Med    | 4           |
| Make hero trust badges data-driven                                                               | Next   | M      | Med    | 2           |
| Add dark mode variants/tokens                                                                    | Later  | L      | Med    | 2           |
| Build search/event/newsletter/campaign sections to match advertised SVGs (or remove SVGs)        | Later  | L      | Med    | 3           |
| Deepen `shopify-commerce` integration (price/variant/stock via hydrated data)                    | Later  | L      | High   | 3           |
| Real multi-column comparison grid + carousel keyboard a11y                                       | Later  | M      | Med    | 2, 4        |
