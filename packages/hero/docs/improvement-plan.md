# Hero — Improvement & Growth Plan

> Package: capell-app/hero · Kind: package · Tier: free · Product group: Capell Foundation · Bundle: foundation · Status: Draft

## 1. Snapshot

Hero ships the single default home-page hero widget (`capell::widget.hero`) plus a layout/theme-level hero **background** and **media** system that themes consume as a shared visual primitive. It contributes: one Livewire/Blade widget component (`src/View/Components/Widget/Hero.php` extending `AbstractWidget`), six anonymous Blade partials (`wrapper`, `slide`, `background`, `media`, `content`, `related`), three Data objects (`HeroWidgetRenderData`, `HeroBackgroundData`, `HeroMediaData`, plus `HeroAssetSlideData`), three resolver Actions (`ResolveHeroBackgroundDataAction`, `ResolveHeroMediaDataAction`, and the slide hydration in `HeroAssetSlideData::fromWidgetAsset`), an install Action (`InstallHeroLayoutDefaultsAction`) behind `capell:hero-setup`, and a Filament schema (`HeroBackgroundSchema`) injected into the Theme settings, Widget display, and Widget-asset forms via three extenders. It declares no migrations, settings, or permissions. Hard runtime deps per `capell.json`: `admin`, `core`, `frontend`, `layout-builder`. Marketplace summary now highlights responsive video/decorative overlay backgrounds, carousel, and inheritable theme styling. Screenshot media is runner-backed again: `capell.json` promotes the extension card plus a clean anonymous Capell public homepage PNG captured after `capell:hero-setup --force`.

## Completed Improvement Slices

- **2026-06-03:** Rewrote marketplace/composer copy, added a real `HeroHealthCheck`, and set `fetchpriority="high"` on the hero media poster image.
- **2026-06-04:** Declared `capell-app/admin` as a hard dependency, added the `admin` surface, populated Hero feature capabilities, removed the empty provider branch, and updated docs/tests for the admin schema extenders.
- **2026-06-04:** Added responsive width/density descriptors and `sizes="100vw"` hints to hero media image/poster sources for LCP.
- **2026-06-05:** Built the two declared screenshot captures, added them to `capell.json` marketplace media, and pinned the screenshot paths/files in manifest coverage.
- **2026-06-06:** Reopened screenshot media after audit: the committed product captures were mock artwork rather than Capell runner output, so they were removed from marketplace media and deleted pending real runner captures.
- **2026-06-06:** Replaced the mock product media with a real Capell screenshot runner capture for the public home Hero widget, added an anonymous consent-prime runner entry, promoted `docs/screenshots/hero-home-widget.png`, and fixed a stray Blade token in the page-hero fallback path.

## 2. Improvements (existing functionality)

1. **Shipped 2026-06-06: memoized per-slide background/media resolution.** `ResolveHeroBackgroundDataAction` and `ResolveHeroMediaDataAction` now expose base theme+widget resolution plus asset-layer application, and `Hero::slides()` reuses the shared base data across all slides instead of recomputing theme/widget layers per asset. The page fallback also reuses the same base data. — `src/View/Components/Widget/Hero.php`, `src/Actions/ResolveHeroBackgroundDataAction.php`, `src/Actions/ResolveHeroMediaDataAction.php` — M

2. **Shipped 2026-06-06: deduplicated `HeroAssetSlideData` construction** — `HeroAssetSlideData::withResolvedLayers()` now owns clone-with semantics for resolved background/media layers and fallback background properties, so `Hero::slides()` no longer re-instantiates the value object by copying every constructor field. Why: 18-arg re-instantiation was error-prone and obscured intent. — `src/View/Components/Widget/Hero.php`, `src/Data/HeroAssetSlideData.php` — S

3. **Extract the inline `<picture>`/`<video>` source maps into the Data layer** — `resources/views/components/hero/media.blade.php` hardcodes the `mobile/tablet/desktop` → media-query mapping as PHP arrays inside Blade (lines 8–18). Per the capell skill ("Public Blade must not query the DB; pass hydrated render data in" and the package's own boost guideline "Prepare all render data before Blade"), this breakpoint table belongs on `HeroMediaData` (e.g. `orderedSources(): array`). The view still does no DB work, but moving the logic tightens the "all data prepared before Blade" contract and makes breakpoints testable/overridable. — `resources/views/components/hero/media.blade.php`, `src/Data/HeroMediaData.php` — S

4. **Shipped 2026-06-06: make the hero render budget enforceable.** `HeroWidgetViewTest` now renders fully hydrated page hero state with the query log enabled and asserts zero database queries while still outputting the expected hero copy. This gives the manifest `frontendRenderBudgetMs` a regression guard without adding brittle wall-clock timing. — `tests/Feature/HeroWidgetViewTest.php` — S

5. **Replace the empty no-op `if` block in the provider** — `HeroServiceProvider::packageBooted()` ends with `if (! $this->isPackageInstalled()) {}` (empty body). Either remove it or implement the intended "not-installed" branch (e.g. skip schema-extender registration when uninstalled). Dead control flow. — `src/Providers/HeroServiceProvider.php` (around lines 51–52) — S

6. **Give `AbstractWidget` its own home or fold it in** — `AbstractWidget` lives in `hero` and references `capell-hero::components.widget.default` (a view that does not exist in this package — only `widget/hero.blade.php` ships). If a sibling widget package ever extends it, the missing default view is a latent fatal. Either ship the `default` view, or move `AbstractWidget` to `layout-builder` (its natural owner) and have Hero depend on it. Why: a base class with a dangling default view is a footgun. — `src/View/Components/Widget/AbstractWidget.php` (line 16) — M

7. **Carousel config: collapse the duplicated data-attributes in `wrapper.blade.php`** — the wrapper emits both legacy and current attribute names for the same value (`data-auto` + `data-carousel-autoplay`, `data-loop` + `data-carousel-loop`, `data-delay` + `data-carousel-autoplay-delay`, `data-align` + `data-carousel-align`, `data-drag` + `data-carousel-drag`, `data-wheel` + `data-carousel-wheel`, `data-fade`). Confirm which the frontend JS reads and drop the rest. Why: doubles attribute payload on every hero and invites drift. — `resources/views/components/hero/wrapper.blade.php` (lines 27–53) — S

## 3. Missing Features (gaps)

Hero now declares public feature capabilities for the widget, video backgrounds, decorative overlays, carousel, and theme inheritance. Against hero-component norms:

- **Table-stakes, present:** responsive video background (desktop/tablet/mobile sources, autoplay/loop/mute/preload, pause-out-of-view via IntersectionObserver with `prefers-reduced-motion` respect — `media.blade.php`); decorative SVG overlay backgrounds with 4 styles (`mesh`/`ribbons`/`grid`/`contours`) and accent-colour CSS vars (`HeroBackgroundData`); content alignment + width variants; carousel with arrows/pagination/fade/loop; per-layer inheritance (theme → widget → asset). This is a strong feature set that the manifest hides.
- **CTA / actions gap:** `HeroAssetSlideData` carries `actions: mixed` (raw `getMeta('actions')`) and a single `linkText`/`url`, but `content.blade.php` only renders the title as an optional link. There is no structured, validated CTA-button group (primary/secondary buttons) in the Data layer or view. This is the most common hero requirement and is effectively absent from public render. — differentiator.
- **No video `<source>` `type`/codec hints for `<picture>` art-direction parity:** images use `<source media>` with a single `srcset` URL (no `srcset` density/width descriptors, no `type="image/webp"` negotiation beyond the `x-capell::media format=webp`). True responsive art direction (per-breakpoint crops with width descriptors) is partial. — table-stakes.
- **No reduced-data / `loading` strategy for the decorative SVG overlay:** the overlay renders inline SVG on every hero with no option to disable for performance-sensitive pages beyond the per-layer `off` mode. Fine, but there's no "lite" variant.
- **Accessibility gaps:** decorative background SVG is correctly `aria-hidden`; video has no track/caption affordance (acceptable for muted decorative video) but there is **no visible-focus or pause control** for autoplaying video — WCAG 2.2.2 expects a mechanism to pause moving content. The IntersectionObserver pauses off-screen but an on-screen autoplaying loop has no user pause button. — differentiator/compliance.
- **No animation/transition options** beyond Swiper carousel effects (`slide`/`fade`). No entrance animation, parallax, or Ken Burns — these are common hero differentiators and absent.
- **Single hero variant only:** the package ships exactly one layout shape (media-beside-content + optional carousel). No "split", "full-bleed centered", "minimal text-only", or "stacked" named presets. Given it's the _foundation_ hero, a small set of named variants (selectable in the widget schema) would materially raise its value. — differentiator.
- **No capability/health surfacing of the feature matrix:** `HeroHealthCheck` (see §4) reports nothing, so Diagnostics cannot tell an operator whether hero video/overlay assets resolved.

## 4. Issues / Risks

1. **Admin dependency declared.** `capell-app/admin` is now required in Composer and `capell.json`, matching the Hero schema extenders that import admin form components and interfaces. Keep the manifest test to prevent this regressing. — `composer.json`, `capell.json`, `tests/Feature/HeroHealthCheckTest.php`

2. **Admin surface declared.** `capell.json` now includes `"admin"` in `surfaces`, and docs describe the schema extenders instead of claiming Hero has no Filament classes. — `capell.json`, `docs/overview.md`

3. **Health check shipped.** `HeroHealthCheck` now verifies the `capell::widget.hero` component alias, the `capell-hero` view namespace, and the Hero-managed home layout defaults (`hero` + `page-content` containers) with remediation to run `capell:hero-setup --force` when seeded state drifts. — `src/Health/HeroHealthCheck.php`, `capell.json healthChecks[0]`

4. **Shipped 2026-06-06: cache safety resolved.** The manifest now marks Hero public output cacheable with `variesBy: ["site","locale"]`, backed by deterministic rendering and the zero-query hydrated render guard. — `capell.json performance.cacheSafety`, `tests/Feature/HeroHealthCheckTest.php` — **Medium**

5. **Shipped 2026-06-06: invalidation sources declared.** Hero now declares cache invalidation sources for Core media, pages, themes, translations, Layout Builder widgets, and widget assets so cached hero output can be invalidated when author-controlled hero state changes. — `capell.json performance.cacheSafety.invalidationSources`, `tests/Feature/HeroHealthCheckTest.php` — **Medium**

6. **LCP risk narrowed.** The hero media poster now uses `fetchpriority="high"` with eager loading. Remaining opportunity: `<picture>` sources still do not provide width/density descriptors, so the browser cannot choose by resolution beyond breakpoint art direction. — `resources/views/components/hero/media.blade.php` — **Medium**

7. **Public-output safety: strong.** The render path is well-guarded — `HeroAssetSlideDataTest` ("keeps public hero blade on prepared slide data") asserts the Blade never calls `getMeta`, `Frontend::`, resolver actions, or touches `$widget->assets`/`translation`; `HeroWidgetViewTest` asserts output excludes `capell-hero`, `theme_id`, `site_id`, `widget_id`, `collection_name`, `hero_media`, and now covers the XSS boundary for author-supplied `meta.hero` HTML rendered through `RenderHtmlContentAction`. The background hash is deterministic (no `uniqid`). — `resources/views/components/hero/content.blade.php`, `tests/Feature/HeroWidgetViewTest.php` — **Low/Medium**

8. **i18n: `media.blade.php` poster `alt=""`.** Decorative empty alt is correct _if_ the poster is purely decorative, but on an image-only hero (no video) the poster image _is_ the hero visual and should carry the slide title as alt, as the slide image path already does (`:alt="$slide->title"`). Currently a video-less media layer renders a meaningful image with empty alt. — `resources/views/components/hero/media.blade.php` (line 34) — **Low**

9. **Test gaps (enumerated).** Covered: background resolver layering/clamping/off; media resolver layering/off/poster; schema group construction + extender append counts; slide data from page/media assets + lazy-load prevention + Blade-purity guard; setup command (create/idempotent/null-containers/force); hero render (page content, empty-skip, inherited background, background-off, responsive media, author-HTML XSS boundary, zero-query hydrated render, carousel multi-slide `data-carousel-*` output). **Not covered:** `HeroMediaData::orderedSources`/breakpoint logic in `media.blade.php`; CTA/actions rendering; the `capell-app/admin`-absent boot path; `related.blade.php` rendering with >3 items (the `--slide-size-lg` calc). — `tests/Feature/` — **Medium**

10. **`php` constraint drift.** `composer.json` requires `php: ^8.3` while the capell skill mandates "PHP 8.4 compatible … avoid PHP 8.5+". Not a bug, but align the floor with the platform (the code already uses typed class constants, an 8.3 feature). — `composer.json` (line 20) — **Low**

## 5. Marketplace & Positioning

Hero is correctly positioned as **free / foundation-bundled** — it's the default home hero every theme leans on, with no standalone admin screen and no tables. It should stay free; its growth value is _platform credibility_ (a polished default hero makes every theme demo look finished) rather than direct revenue.

**Current `summary` (capell.json) and composer `description`:**

- summary: _"Hero provides the default Capell home hero widget, rendering, and layout setup."_
- composer description: _"Hero widget rendering and default home hero setup for Capell."_

Both are flat and describe plumbing ("setup", "rendering"), not the visitor-facing outcome. Neither mentions the actual differentiators: responsive autoplay **video** backgrounds, decorative **overlay** styles, multi-slide **carousel**, and theme→widget→asset **inheritance**.

**Improved summary (marketplace):** "A polished, responsive home hero for every Capell theme — autoplay video or decorative overlay backgrounds, multi-slide carousel, and theme-level styling that pages inherit automatically."

**Improved composer description:** "Foundation hero section for Capell: responsive video/image backgrounds, decorative overlays, carousel slides, and inheritable theme styling, rendered safely for anonymous visitors."

**Screenshot/media status:** `capell.json` ships the extension card plus `docs/screenshots/hero-home-widget.png`, a real Capell screenshot runner capture of the anonymous public homepage after `capell:hero-setup --force`. `docs/screenshots.json` includes a runner-only consent-prime entry so the promoted marketplace capture does not include analytics consent chrome. A richer slide/related-content variant remains deferred until a true multi-slide demo fixture exists.

**Platform-pitch contribution:** "Every Capell site ships with a hero that supports video, overlays, and carousels out of the box — no theme author has to build one." Declare the feature set as `capabilities[]` (currently empty) so the marketplace and Diagnostics can surface it.

**Suggested keywords/tags (8–12):** `hero`, `hero-section`, `video-background`, `carousel`, `landing-page`, `homepage`, `cms-widget`, `responsive`, `overlay`, `foundation`, `frontend`, `theme-primitive`.

## 6. Prioritized Roadmap

| Item                                                                               | Bucket | Effort | Impact | Section ref |
| ---------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Declare `capell-app/admin` dependency (or guard admin registration)                | Done   | S      | High   | §4.1        |
| Add `"admin"` to `surfaces` + fix overview "no Filament" claim                     | Done   | S      | High   | §4.2        |
| Add responsive width/density descriptors to hero media sources (LCP)               | Done   | S      | Medium | §4.6        |
| Capture real Capell runner PNGs for `hero-home-widget` and optional `hero-slide-variant` | Done   | S      | Medium | §1, §5      |
| Rewrite marketplace `summary` + composer `description`                             | Done   | S      | Medium | §5          |
| Populate `capabilities[]` (video, overlay, carousel, inheritance)                  | Done   | S      | Medium | §3, §5      |
| Shipped 2026-06-06: Memoize theme/widget resolver layers across slides (render budget) | Done   | M      | High   | §2.1        |
| Shipped 2026-06-06: Extend `HeroHealthCheck` to verify seeded default-home layout state | Done   | S      | Low    | §4.3        |
| Shipped 2026-06-06: Resolve cacheable/invalidationSources truth (cache safety)     | Done   | M      | Medium | §4.4, §4.5  |
| Shipped 2026-06-06: Add render budget / zero-query render test                     | Done   | S      | Medium | §2.4, §4.6  |
| Shipped 2026-06-06: Add XSS-boundary test for author hero HTML                     | Done   | S      | Medium | §4.7        |
| Shipped 2026-06-06: Add carousel multi-slide + `data-carousel-*` render test       | Done   | S      | Medium | §4.9        |
| Structured CTA-button group in Data + content view                                 | Later  | M      | High   | §3          |
| Named hero variants (split/full-bleed/minimal) in widget schema                    | Later  | L      | High   | §3          |
| Add user pause control for autoplaying video (WCAG 2.2.2)                          | Later  | M      | Medium | §3          |
| Move/define `AbstractWidget` default view; collapse duplicated carousel data-attrs | Later  | M      | Low    | §2.6, §2.7  |
