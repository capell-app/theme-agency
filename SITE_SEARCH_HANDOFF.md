# Site Search Improvement — Handoff

**Branch (packages repo):** `fix/ai-creator-phpstan-preflight` (working on current branch, dirty with unrelated theme-screenshot work — **stage only search files**)
**Branch (app repo `/Users/ben/Sites/capell-app`):** `fix/ai-creator-creator-server-phpstan` (dirty with unrelated work — **stage only search files**)

Two confirmed decisions from the user:

1. **Part B edits the LIVE package** `capell-packages-4/packages/search` (NOT the `-product-completion` checkout the plan named).
2. **Adopt the package as source of truth**: delete the app's published vendor overrides so the newer package dialog renders. Work on current branches; stage only search-related files.

## Repo map (critical — the plan's paths were wrong)

- **App (owns Part A `app/...`)**: `/Users/ben/Sites/capell-app` — a **Dockerized** Laravel app. Run things via `./capell ...` (e.g. `./capell bin pest`, `./capell artisan ...`). DB host is the `mysql` docker service; `mysql` is NOT on the host PATH, so tests fail unless run inside the container. Stack is up (mysql, typesense, redis, web, proxy).
- **Search package (Part B)**: `/Users/ben/Sites/packages/capell/capell-packages-4/packages/search` — consumed by the app via composer path repo (`capell-app/composer.json:20` → `../packages/capell/capell-packages-4/packages/search`).

---

## DONE & VERIFIED — Part A (indexing, all in `/Users/ben/Sites/capell-app`)

All 19 tests in `tests/Feature/Search` pass (`./capell bin pest tests/Feature/Search --compact`). Reindex ran clean; showcase collection verified queryable end-to-end via direct Scout.

- **A1** `app/Actions/Search/RebuildMarketingContentSearchRecordsAction.php`
    - `bodyFor()` now flattens the **full** `meta` tree (was 3 hand-picked keys) + appends `mediaTextFor()`.
    - New `mediaTextFor(Translation $t)`: iterates `$t->media` (Spatie `HasMedia`; superset of `image()`/`backgroundImage()`), appends `getAltText/getCaption/getCredit($t->language_id)` + `name`.
    - Eager-load changed to `->with(['pageUrl', 'media'])`.
    - **Key discovery:** media alt/caption/credit live in a **Translation row morphed onto the `Media` model** (`translatable_type = Media`), read via `LocalizedMediaMetadataResolver`. Meta keys: `alt`, `caption`, `credit`, `decorative`. Media's own translations are NOT in `translatableTypes()` (Page/Section/Widget), so they only reach the index through `mediaTextFor()`. `body` column is `longText` (confirmed) — no schema change.
- **A2** `app/Search/SearchablePayloads/MarketplaceExtensionSearchPayload.php`
    - New `screenshotTextFor()` appends each screenshot's `alt`/`caption`/`title`/`label` (confirmed shape) into `body`.
- **A3** new showcase source:
    - `app/Models/MarketingShowcaseItem.php`: `use Searchable`, `searchableAs() = 'capell_app_showcase_items'`, `toSearchableArray()`, `shouldBeSearchable() = is_visible && published_at?->isPast() === true`.
    - New `app/Search/SearchablePayloads/MarketingShowcaseItemSearchPayload.php` (body = title+summary+image_alt+flattened metadata; url = resource_url abs-normalized, fallback `/showcase`; type `showcase`).
    - Registered in `app/Providers/CapellAppSearchServiceProvider.php` (key `showcase`, weight 1.1).
    - `config/scout.php`: added `MarketingShowcaseItem::class` model-settings entry (mirrors others).
- **Tests added**: extended `RebuildMarketingContentSearchRecordsActionTest.php` (media+meta body assertions); new `MarketingShowcaseItemSearchPayloadTest.php`.
- **Reindex done**: `./capell artisan search:rebuild-marketing --import` (548 records); `scout:import` for `MarketingShowcaseItem` (14) and `MarketplaceExtension` (101).

### ⚠️ Flagged finding (NOT a regression — needs a decision, out of Part A scope)

`ScoutSearch::search()` applies `where('site_id', $siteId)` to **every** source. `RunAutocompleteSearchAction` passes the current site's id. Site-agnostic sources (`site_id = null` — **extensions AND showcase**) get filtered out of autocomplete whenever a site resolves. This is **pre-existing** behavior affecting the existing extensions source identically; my showcase source is correctly registered and the collection IS queryable (proved via `MarketingShowcaseItem::search("Layout")`). Decide whether to make `ScoutSearch` treat null-site (site-agnostic) sources as always-eligible — this is a package search-semantics change affecting all non-marketing sources, so it was deliberately NOT done here.

---

## DONE — Part B package edits (`packages/search/`)

The package's `search-dialog.blade.php` was already a rich, accessible 768-line base (focus trap, `inert`, debounced fetch, combobox/listbox a11y, click-tracking, ⌘K/`/` shortcuts) but had no grouping/icons/idle/empty and still had the double-✕ bug. All Part B edits applied to the **package** (live path):

- **B1** `resources/views/components/header/search-dialog.blade.php`
    - Added `<style>` in `@once` hiding `::-webkit-search-cancel-button`/`::-webkit-search-decoration`/`::-ms-clear` (kills the native clear → fixes double-✕).
    - Moved the single close button to the panel's **top-right corner** (out of the input row) with an `Esc` `<kbd>` hint; input row is now field + submit only.
    - Added a keyboard-hint footer (↑↓ navigate / ↵ open / Esc close).
- **B2** grouped/typed results + states:
    - `autocomplete-results.blade.php`: added `data-site-search-idle` block (shown on open) and `data-site-search-empty` block (with `data-site-search-empty-template` using `no_results`). (Loading skeleton already existed.)
    - `search-dialog.blade.php` JS: added `groupOrder`, `iconPaths`, `typeToIconKey`, `typeIcon()`, `groupResults()`, `groupHeading()`. Rewrote `renderResults()` to render suggestions first then **type-grouped** results under headings (`typeLabel` from payload), each row with a leading heroicon; drives idle/empty visibility; `orderedItems` keeps the flat selectable list so keyboard nav + `aria-activedescendant` stay correct. Updated `clearResults` (→ idle), `openDialog` (show idle), `closeDialog` (hide region), `setLoading` (hide idle/empty), `resultItem` (icon + dropped redundant per-row type), `setActiveResult` (scrollIntoView).
- **B3**
    - `resources/lang/en/generic.php`: refined `search_placeholder`; added `idle_hint`, `hint_navigate`, `hint_open`, `hint_close`, `shortcut_escape`.
    - `search-trigger.blade.php`: optional ⌘K/Ctrl K `<kbd>` hint (only `showLabel` variant + when keyboard shortcuts enabled).

---

## REMAINING — Part B app integration + live verify (was mid-investigation when handed off)

The blocker: **`vendor/capell/search` in the app is a real-dir COPY (not a symlink) and is STALE** — it's missing the dialog file entirely. The app currently renders the dialog/autocomplete only from **published overrides** at `capell-app/resources/views/vendor/capell-search/components/header/{search-dialog,autocomplete-results}.blade.php` (the OLD 231-line versions). To make the new package version render:

1. **Delete the two app published overrides** so Laravel falls back to the package views:
    - `capell-app/resources/views/vendor/capell-search/components/header/search-dialog.blade.php`
    - `capell-app/resources/views/vendor/capell-search/components/header/autocomplete-results.blade.php`
    - (Check whether anything app-specific must be preserved: the old override referenced `@filemtime(...)`, `__('marketing.packages.registry.clear_search')`, `data-site-search-clear`, `data-site-search-empty`. The package version supersedes these; `clear_search` was app marketing lang — confirm nothing else depends on it.)
2. **Refresh the stale vendor copy** so `vendor/capell/search/resources/views/...` contains the new package views. Composer path repo here is a **mirror/copy**, not symlink (NO `"options": {"symlink": ...}` on the search entry — unlike `agent-bridge` which has an `options` block; check why search mirrors). Likely needs `./capell composer install` or `./capell composer update capell/search --no-scripts` (or a re-copy). **Verify** `vendor/capell/search/resources/views/components/header/search-dialog.blade.php` exists and matches the package after.
    - Consider whether making the path repo a symlink (`"options": {"symlink": true}`) is the right durable fix so future package edits render without re-mirroring.
3. **Tailwind `@source`**: the app's `resources/css/app.css` scans `'../views/vendor/capell-search/**/*.blade.php'` (the overrides being deleted). After deletion the rendered blades come from `vendor/capell/search/...`, which is NOT scanned → new utility classes won't compile. **Add** `@source '../../vendor/capell/search/resources/views/**/*.blade.php';` (and remove/keep the now-empty overrides glob). Mind the per-bundle token gotcha noted in the plan.
4. **Build**: `./capell npm run build` and confirm the new classes land in the public `frontend.css` bundle (the marketing/frontend bundle, not just app).
5. **Lint/analyze**: `vendor/bin/pint --dirty` in both repos; `./capell composer analyze` (app) — Part A PHP. Package PHP unchanged (Blade/lang only), but run package lint if touched.
6. **Manual UI QA on capell.test** (browser MCP / preview): open search (icon, `/`, ⌘K) → confirm **single** close affordance (no double-✕), idle hint on open, loading skeleton, grouped results with type icons + headings, empty state, keyboard nav (↑↓/↵/Esc), focus trap, and that click-tracking beacon still fires. Note: live autocomplete may show few results in this dev DB (see the site_id finding above) — verify against marketing `page` terms which do return, and confirm grouping/icons render.

## Verification commands

- App search tests: `cd /Users/ben/Sites/capell-app && ./capell bin pest tests/Feature/Search --compact`
- Direct showcase Scout check: `./capell artisan tinker --execute='echo App\Models\MarketingShowcaseItem::search("Layout")->get()->count();'`
- Reindex: `./capell artisan search:rebuild-marketing --import`; `./capell artisan scout:import "App\Models\MarketingShowcaseItem"`; `... "App\Models\MarketplaceExtension"`

## Files touched

**App (`/Users/ben/Sites/capell-app`):** `app/Actions/Search/RebuildMarketingContentSearchRecordsAction.php`, `app/Search/SearchablePayloads/MarketplaceExtensionSearchPayload.php`, `app/Search/SearchablePayloads/MarketingShowcaseItemSearchPayload.php` (new), `app/Models/MarketingShowcaseItem.php`, `app/Providers/CapellAppSearchServiceProvider.php`, `config/scout.php`, `tests/Feature/Search/RebuildMarketingContentSearchRecordsActionTest.php`, `tests/Feature/Search/MarketingShowcaseItemSearchPayloadTest.php` (new).
**Package (`packages/search/`):** `resources/views/components/header/search-dialog.blade.php`, `resources/views/components/header/autocomplete-results.blade.php`, `resources/views/components/header/search-trigger.blade.php`, `resources/lang/en/generic.php`.
