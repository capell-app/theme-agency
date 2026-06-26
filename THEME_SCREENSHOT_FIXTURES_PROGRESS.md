# Theme screenshot-fixture setup — progress & resume guide

Goal: every theme package can be captured by the **capell-screenshot-runner**
(`/Users/ben/Sites/packages/capell/capell-screenshot-runner`) via route-rendered
fixtures (`/screenshot-fixtures/theme-<slug>/{screen}`), one screen per manifest entry.

## MILESTONE (2026-06-26): ALL 59 themes render 200 + shell on EVERY screen

Full render verification complete. The 4 strict/premium themes that previously 500'd
(**saas, corporate, liquid-glass, agency**) are fixed: each renderer now has a
`defaultDataFor(string $sectionKey): array` whose values are merged in `section()`
(`[...$this->defaultDataFor($sectionKey), ...$data]`) so strict section views never
`foreach` over null. Shared root cause beyond strict-null: the `footer()` helper passed
`items` while every `footer.blade.php` iterates `$section->columns` (each `heading` +
`links[]`) — fixed in all four. liquid-glass also needed `toViewData()` to spread `$this->data`
(its `content-listing` view reads bare `$heading/$items` vars). Independent re-sweep of all 29
screens across the 4 themes: `failed=0`. `php -l` clean on all four renderers.

Remaining phases (unchanged): manifest rewrite (`url` -> fixture route + `waitFor`),
Playwright PNG capture, pint/analyze, commit.

## Done (verified rendering 200 + correct shell)

- Reference built: **theme-restaurant** (full fixture layer; manifest already uses fixture urls).
- Authored + curl-verified 10/10 screens each: **law-firm, podcast-show, travel-tourism,
  fitness-wellness, beauty-spa, financial-advisory, robotics-hardware, personal-dev**.
- Already "ready" (have a fixture layer): estate-agents, premium-infrastructure,
  premium-portfolio-collection, premium-product-story.
- Runner `composer.json`: all 60 themes now required + symlinked (committed change in runner repo).

## Per-theme recipe (the authoring task)

Full spec lived at the session scratchpad `THEME_FIXTURE_SPEC.md`; summary:
Reference impl to copy = `packages/theme-restaurant/` (and `packages/theme-estate-agents/`).
Per theme create 5 files + 1 provider edit:

1. `src/Support/Screenshots/<Name>ScreenshotSection.php` — `ThemeSection` value object (copy restaurant's, swap namespace).
2. `src/Support/Screenshots/<Name>ScreenshotRenderer.php` — `VIEW_PREFIX='capell-theme-<slug>::sections.'`;
   `sectionsFor($screen)` maps EVERY manifest `entries[].id` -> ordered list of the theme's real
   section keys (always navigation + footer); build `BrandProfileData` (15 args, restaurant order)
   from the provider's `definition()` preset `values`; wrap via `capell-theme-<slug>::page` then
   `capell-theme-<slug>::screenshots.fixture`.
3. `routes/screenshot-fixtures.php` — `Route::get('/screenshot-fixtures/theme-<slug>/{screen}', fn -> renderer->render($screen))`.
4. `resources/views/screenshots/fixture.blade.php` — copy restaurant's verbatim.
5. Provider boot: add `$this->loadScreenshotFixtureRoutes();` after `loadTranslationsFrom`, plus the
   private method gated on `CAPELL_THEME_<SLUG_UPPER>_SCREENSHOT_FIXTURES_ENABLED`
   (SLUG*UPPER = slug uppercased, `-`->`*`, e.g. law-firm -> LAW_FIRM).
Constraints: reuse existing section views only; PHP style matches reference; no cryptic var names.
NOTE: most themes' page shell selector is **`.site-theme-shell`** (shared), NOT `.<slug>-shell`(restaurant is the exception). Verify against`.site-theme-shell`.

## Central runner steps (after authoring / requiring themes) — CRITICAL ORDER

```
cd /Users/ben/Sites/packages/capell/capell-screenshot-runner
# (themes already required; if adding more: composer require capell-app/theme-x:* --no-scripts -W)
php -d memory_limit=2048M artisan package:discover     # <-- REQUIRED after --no-scripts require; runner OOMs at 128MB
php -d memory_limit=2048M artisan optimize:clear
# serve with ALL theme fixture flags enabled (getenv-based gate):
env $(cat /private/tmp/.../theme-flags.env) APP_ENV=local \
  php -d memory_limit=512M artisan serve --host=127.0.0.1 --port=8200
```

Gotcha proven the hard way: without `package:discover` the new theme providers never boot, the
fixture route never registers, and the frontend `{any}` catch-all serves the URL -> 500 from the
"public view query guard" (`CAPELL_FRONTEND_PUBLIC_VIEW_QUERY_GUARD_ENABLED`). Discovery fixes it.

## Verify (per theme)

`curl -s -o /tmp/v.html -w "%{http_code}" http://127.0.0.1:8200/screenshot-fixtures/theme-<slug>/<id>`
expect 200 and body contains `site-theme-shell`. Capture PNGs with Playwright (chromium in
`capell-4/node_modules` + host cache), viewport 1440x900, `waitFor('.site-theme-shell')`, fullPage,
output to `packages/theme-<slug>/docs/screenshots/<id>.png`.

## OPEN: manifest format gap (decide before runner auto-capture)

Most themes' `docs/screenshots.json` entries still point `url` at demo surfaces (`/theme-<slug>`)
with no `waitFor`. Restaurant's manifest uses `url: /screenshot-fixtures/theme-restaurant/<id>` +
`waitFor: .restaurant-shell` + `scenario: frontend-page`. For the runner's automated `capture` to
drive off manifests, rewrite each entry: `url` -> `/screenshot-fixtures/theme-<slug>/<id>`,
add `waitFor: .site-theme-shell`. (Curl verification above is independent of this.)

## Remaining to author (~60; excludes 3 inertia JS themes which use a different mechanism)

Some already render via LEGACY inline routes in the runner's `routes/web.php` (e.g. local-services,
nonprofit, portfolio, foundation-theme) — confirm with route:list before re-authoring.
List of theme dirs without `routes/screenshot-fixtures.php`: run
`for d in packages/theme-* packages/foundation-theme; do [ -f "$d/routes/screenshot-fixtures.php" ] || basename "$d"; done`

## Environment state at checkpoint

- Runner serving on 127.0.0.1:8200 (background) with all fixture flags.
- Throwaway DB for the _admin_ screenshot work: `capell_screenshots` in the `capell-app-mysql-1`
  container (unrelated to themes; the runner uses its own sqlite).
- No theme source committed yet — new files are untracked in capell-packa
