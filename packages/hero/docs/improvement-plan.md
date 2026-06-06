# Hero — Improvement & Growth Plan

> Package: capell-app/hero · Kind: package · Tier: free · Product group: Capell Foundation · Bundle: foundation · Status: Draft

## 1. Snapshot

Hero ships the single default home-page hero widget (`capell::widget.hero`) plus a layout/theme-level hero **background** and **media** system that themes consume as a shared visual primitive. It contributes: one Livewire/Blade widget component (`src/View/Components/Widget/Hero.php` extending `AbstractWidget`), six anonymous Blade partials (`wrapper`, `slide`, `background`, `media`, `content`, `related`), three Data objects (`HeroWidgetRenderData`, `HeroBackgroundData`, `HeroMediaData`, plus `HeroAssetSlideData`), three resolver Actions (`ResolveHeroBackgroundDataAction`, `ResolveHeroMediaDataAction`, and the slide hydration in `HeroAssetSlideData::fromWidgetAsset`), an install Action (`InstallHeroLayoutDefaultsAction`) behind `capell:hero-setup`, and a Filament schema (`HeroBackgroundSchema`) injected into the Theme settings, Widget display, and Widget-asset forms via three extenders. It declares no migrations, settings, or permissions. Hard runtime deps per `capell.json`: `admin`, `core`, `frontend`, `layout-builder`. Marketplace summary now highlights responsive video/decorative overlay backgrounds, carousel, and inheritable theme styling. Screenshot media is open again: `capell.json` keeps only the extension card because the previous product PNG/SVG captures were illustrative mockups, not Capell runner output.

## Completed Improvement Slices

- **2026-06-03:** Rewrote marketplace/composer copy, added a real `HeroHealthCheck`, and set `fetchpriority="high"` on the hero media poster image.
- **2026-06-04:** Declared `capell-app/admin` as a hard dependency, added the `admin` surface, populated Hero feature capabilities, removed the empty provider branch, and updated docs/tests for the admin schema extenders.
- **2026-06-04:** Added responsive width/density descriptors and `sizes="100vw"` hints to hero media image/poster sources for LCP.
- **2026-06-05:** Built the two declared screenshot captures, added them to `capell.json` marketplace media, and pinned the screenshot paths/files in manifest coverage.
- **2026-06-06:** Reopened screenshot media after audit: the committed product captures were mock artwork rather than Capell runner output, so they were removed from marketplace media and deleted pending real runner captures.

## 2. Improvements (existing functionality)

1. **Memoize the per-slide background/media resolution** — `Hero::slides()` rebuilds each `HeroAssetSlideData` and calls `ResolveHeroBackgroundDataAction::run()` and `ResolveHeroMediaDataAction::run()` once _per asset_, then the page fallback calls both again. For a multi-slide carousel this is N×2 resolver passes per render, each iterating theme→widget→asset meta layers. The theme/widget layers are identical across slides; compute them once and only merge the asset layer per slide. Why: the manifest sets `frontendRenderBudgetMs: 20` and this is the dominant cost. — `src/View/Components/Widget/Hero.php` (lines 194–231) — M

2. **Shipped 2026-06-06: deduplicated `HeroAssetSlideData` construction** — `HeroAssetSlideData::withResolvedLayers()` now owns clone-with semantics for resolved background/media layers and fallback background properties, so `Hero::slides()` no longer re-instantiates the value object by copying every constructor field. Why: 18-arg re-instantiation was error-prone and obscured intent. — `src/View/Components/Widget/Hero.php`, `src/Data/HeroAssetSlideData.php` — S

3. **Extract the inline `<picture>`/`<video>` source maps into the Data layer** — `resources/views/components/hero/media.blade.php` hardcodes the `mobile/tablet/desktop` → media-query mapping as PHP arrays inside Blade (lines 8–18). Per the capell skill ("Public Blade must not query the DB; pass hydrated render data in" and the package's own boost guideline "Prepare all render data before Blade"), this breakpoint table belongs on `HeroMediaData` (e.g. `orderedSources(): array`). The view still does no DB work, but moving the logic tightens the "all data prepared before Blade" contract and makes breakpoints testable/overridable. — `resources/views/components/hero/media.blade.php`, `src/Data/HeroMediaData.php` — S

4. **Make the hero render budget enforceable** — there is no test asserting the 20ms `frontendRenderBudgetMs`. Add a render-timing assertion (or at minimum a query-count assertion proving zero queries during `render()`, mirroring the existing `preventLazyLoading` test in `HeroAssetSlideDataTest`) so regressions in §2.1 surface in CI. Why: the budget is advertised in the manifest but currently unverified. — `tests/Feature/HeroWidgetViewTest.php` — S

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

3. **Health check shipped.** `HeroHealthCheck` now verifies the `capell::widget.hero` component alias and the `capell-hero` view namespace. Remaining opportunity: add install-layout/default-home checks if the package later treats seeded layout state as part of health. — `src/Health/HeroHealthCheck.php`, `capell.json healthChecks[0]`

4. **`cacheSafety.cacheable: false` but the widget is pure-render and could be cacheable.** The manifest marks hero output non-cacheable with `variesBy: ["site","locale"]`. The render is deterministic given hydrated relations (the test `expect($html)->toBe(renderHeroWidgetHtml($widget))` proves determinism, and the background hash uses `xxh128`, not `uniqid`). If hero is forced non-cacheable it may be defeating frontend HTML caching for the whole home page. Confirm whether `cacheable:false` is intentional or copy-paste; if the only variance is site+locale (already declared), it should be cacheable with those keys. — `capell.json performance.cacheSafety` — **Medium**

5. **`invalidationSources: []` despite media/translation-driven output.** Hero output depends on `Theme.meta.hero_background`/`hero_media`, `Widget`/`WidgetAsset` meta + media, and page/widget translations. None are listed as invalidation sources, yet `queueInvalidation: true` is set. If hero output is ever cached (see §4.4), edits to theme hero settings or hero media would not bust the cache. Register the relevant cache dependencies (`CacheInvalidationRegistry::registerDependency()` per the skill) or document why none are needed. — `capell.json performance.cacheSafety.invalidationSources` — **Medium**

6. **LCP risk narrowed.** The hero media poster now uses `fetchpriority="high"` with eager loading. Remaining opportunity: `<picture>` sources still do not provide width/density descriptors, so the browser cannot choose by resolution beyond breakpoint art direction. — `resources/views/components/hero/media.blade.php` — **Medium**

7. **Public-output safety: strong, with one thing to watch.** The render path is well-guarded — `HeroAssetSlideDataTest` ("keeps public hero blade on prepared slide data") asserts the Blade never calls `getMeta`, `Frontend::`, resolver actions, or touches `$widget->assets`/`translation`; `HeroWidgetViewTest` asserts output excludes `capell-hero`, `theme_id`, `site_id`, `widget_id`, `collection_name`, `hero_media`. The background hash is deterministic (no `uniqid`). One residual: `related.blade.php` outputs `title="{{ e(strip_tags(...)) }}"` (correctly escaped) but `content.blade.php` renders `{!! $hero->pageHeroContentHtml !!}` and slide `contentHtml` as raw HTML — these come from `RenderHtmlContentAction` on author-controlled translation content, which is expected, but there is **no test asserting that a non-admin viewer cannot inject script via hero content**. Add an explicit XSS-boundary test for author-supplied `meta.hero` HTML. — `resources/views/components/hero/content.blade.php`, `tests/Feature/HeroWidgetViewTest.php` — **Low/Medium**

8. **i18n: `media.blade.php` poster `alt=""`.** Decorative empty alt is correct _if_ the poster is purely decorative, but on an image-only hero (no video) the poster image _is_ the hero visual and should carry the slide title as alt, as the slide image path already does (`:alt="$slide->title"`). Currently a video-less media layer renders a meaningful image with empty alt. — `resources/views/components/hero/media.blade.php` (line 34) — **Low**

9. **Test gaps (enumerated).** Covered: background resolver layering/clamping/off; media resolver layering/off/poster; schema group construction + extender append counts; slide data from page/media assets + lazy-load prevention + Blade-purity guard; setup command (create/idempotent/null-containers/force); hero render (page content, empty-skip, inherited background, background-off, responsive media). **Not covered:** carousel multi-slide rendering and `data-carousel-*` output; `HeroMediaData::orderedSources`/breakpoint logic in `media.blade.php`; render budget / query count during render (§2.4); CTA/actions rendering; the `capell-app/admin`-absent boot path; a real `HeroHealthCheck` (none exists to test); XSS boundary on author HTML (§4.7); `related.blade.php` rendering with >3 items (the `--slide-size-lg` calc). — `tests/Feature/` — **Medium**

10. **`php` constraint drift.** `composer.json` requires `php: ^8.3` while the capell skill mandates "PHP 8.4 compatible … avoid PHP 8.5+". Not a bug, but align the floor with the platform (the code already uses typed class constants, an 8.3 feature). — `composer.json` (line 20) — **Low**

## 5. Marketplace & Positioning

Hero is correctly positioned as **free / foundation-bundled** — it's the default home hero every theme leans on, with no standalone admin screen and no tables. It should stay free; its growth value is _platform credibility_ (a polished default hero makes every theme demo look finished) rather than direct revenue.

**Current `summary` (capell.json) and composer `description`:**

- summary: _"Hero provides the default Capell home hero widget, rendering, and layout setup."_
- composer description: _"Hero widget rendering and default home hero setup for Capell."_

Both are flat and describe plumbing ("setup", "rendering"), not the visitor-facing outcome. Neither mentions the actual differentiators: responsive autoplay **video** backgrounds, decorative **overlay** styles, multi-slide **carousel**, and theme→widget→asset **inheritance**.

**Improved summary (marketplace):** "A polished, responsive home hero for every Capell theme — autoplay video or decorative overlay backgrounds, multi-slide carousel, and theme-level styling that pages inherit automatically."

**Improved composer description:** "Foundation hero section for Capell: responsive video/image backgrounds, decorative overlays, carousel slides, and inheritable theme styling, rendered safely for anonymous visitors."

**Screenshot/media status:** `capell.json` currently ships only the extension card. The `hero-home-widget` and optional `hero-slide-variant` targets still need real Capell screenshot runner captures from a seeded public page that renders the hero widget after `capell:hero-setup`.

**Platform-pitch contribution:** "Every Capell site ships with a hero that supports video, overlays, and carousels out of the box — no theme author has to build one." Declare the feature set as `capabilities[]` (currently empty) so the marketplace and Diagnostics can surface it.

**Suggested keywords/tags (8–12):** `hero`, `hero-section`, `video-background`, `carousel`, `landing-page`, `homepage`, `cms-widget`, `responsive`, `overlay`, `foundation`, `frontend`, `theme-primitive`.

## 6. Prioritized Roadmap

| Item                                                                               | Bucket | Effort | Impact | Section ref |
| ---------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Declare `capell-app/admin` dependency (or guard admin registration)                | Done   | S      | High   | §4.1        |
| Add `"admin"` to `surfaces` + fix overview "no Filament" claim                     | Done   | S      | High   | §4.2        |
| Add responsive width/density descriptors to hero media sources (LCP)               | Done   | S      | Medium | §4.6        |
| Capture real Capell runner PNGs for `hero-home-widget` and optional `hero-slide-variant` | Next   | S      | Medium | §1, §5      |
| Rewrite marketplace `summary` + composer `description`                             | Done   | S      | Medium | §5          |
| Populate `capabilities[]` (video, overlay, carousel, inheritance)                  | Done   | S      | Medium | §3, §5      |
| Memoize theme/widget resolver layers across slides (render budget)                 | Next   | M      | High   | §2.1        |
| Extend `HeroHealthCheck` to verify seeded default-home layout state if required    | Next   | S      | Low    | §4.3        |
| Resolve cacheable/invalidationSources truth (cache safety)                         | Next   | M      | Medium | §4.4, §4.5  |
| Add render budget / zero-query render test                                         | Next   | S      | Medium | §2.4, §4.6  |
| Add XSS-boundary test for author hero HTML                                         | Next   | S      | Medium | §4.7        |
| Add carousel multi-slide + `data-carousel-*` render test                           | Next   | S      | Medium | §4.9        |
| Structured CTA-button group in Data + content view                                 | Later  | M      | High   | §3          |
| Named hero variants (split/full-bleed/minimal) in widget schema                    | Later  | L      | High   | §3          |
| Add user pause control for autoplaying video (WCAG 2.2.2)                          | Later  | M      | Medium | §3          |
| Move/define `AbstractWidget` default view; collapse duplicated carousel data-attrs | Later  | M      | Low    | §2.6, §2.7  |
