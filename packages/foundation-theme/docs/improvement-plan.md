# Foundation Theme — Improvement & Growth Plan
> Package: capell-app/foundation-theme · Kind: theme · Tier: free · Product group: Capell Foundation · Bundle: foundation · Status: Draft

## 1. Snapshot

Foundation Theme is the base theme every vertical theme builds on. It registers a single Theme Studio definition (`themeKey: "default"`, Blade runtime) with seven view section renderers (`navigation`, `hero`, `features`, `proof`, `content-listing`, `cta`, `footer`) via `BladeThemeRenderer` against `capell-foundation-theme::theme.page`, and contributes the shared `capell::` Blade namespace, anonymous component path, Layout Builder widget views, the `header` Layout Builder area, an SVG media sanitiser, a `CapellUrlGenerator`, Blade directives (`@buildAssets`, `@capellBuffer`), and the `TailwindAssetsGenerator` that aggregates `@import`/`@plugin`/`@source`/`@theme` into one frontend CSS entrypoint (`src/Providers/FoundationThemeServiceProvider.php`). Design tokens are split between a Spatie settings class (`FoundationThemeSettings` — 18 colour/spacing/radius fields) emitted as CSS custom properties at runtime by `resources/views/components/app/head/tokens.blade.php`, and a `default-colors` palette from core's `DefaultColorEnum`. Surfaces are `["admin", "frontend"]`; capabilities are `["frontend-assets", "cache-blocking"]`. Marketplace summary (verbatim): *"Default Capell page foundations, layout areas, content widgets, media, forms, and package-aware frontend states."* The manifest declares 9 marketplace screenshots (8 hand-drawn `.svg` layout mockups + 1 `.jpg` card); `docs/screenshots.json` declares 12 capture entries; only 6 real `.png` screenshots are committed (3 use cases × light/dark) — a three-way mismatch.

## 2. Improvements (existing functionality)

1. **Promote the health check from stub to a real probe** — **What:** `FoundationThemeHealthCheck` implements only `compatibleCapellApiVersion()`; it performs no checks despite `capell.json` declaring it `severity: critical` with the label "surfaces, providers, and install health are discoverable by Diagnostics." A critical health check that asserts nothing is worse than none — it reports green when the theme is broken. Add probes: published assets exist at `public/vendor/capell-foundation-theme` (manifest of `publishes/build`), the Theme Studio `default` definition is registered, the generated Tailwind entrypoint exists, and required deps (`frontend`, `layout-builder`) are installed. — `src/Health/FoundationThemeHealthCheck.php` — **M**

2. **Delete or wire the orphaned demo subsystem** — **What:** `ThemeDemoPageInstaller` (869 lines), `ThemeDemoMedia` (296 lines), `ThemeDemoPageDefinition`, the `InstallsThemeDemo` contract, and `ThemeDemoInstallData` are referenced by nothing outside themselves — no provider, no command, no manifest entry (`capell.json` `commands.demo = null`, no `actions.demo`). ~1,200 lines of untested dead code (all flagged uncovered in §4). Either register a `capell:foundation-theme-demo` command + manifest `commands.demo`/`actions.demo` so it ships value, or remove it. — `src/Support/Demo/*`, `src/Contracts/InstallsThemeDemo.php` — **M**

3. **Remove the no-op `AdminServiceProvider`** — **What:** `AdminServiceProvider::register()` is an empty body with an `#[Override]` attribute. It is not in `capell.json` `providers.admin` (empty), but the README "At A Glance" lists it as a service provider, misleading readers. Delete the class and the README line, or give it the admin registration it implies. — `src/Providers/AdminServiceProvider.php` — **S**

4. **Add `getMeta(` and `->translation->` to the public-Blade data-loading guard** — **What:** `tests/Feature/SafeOutputTest.php` "public blade keeps data loading out of templates" bans `DB::`, `loadMissing(`, `relationLoaded(`, `getMedia(` but **not** `getMeta(` or relationship property reads. `footer/site-info.blade.php` calls `$site->translation->getMeta('tagline')` and `$site->translation->title`; `content.blade.php`, `footer/index.blade.php`, `app.blade.php` all call `getMeta(`. These pass the guard today only because the data is currently hydrated upstream — nothing in this package prevents a future edit from introducing a lazy-load/N+1 in public render. Extend the forbidden-pattern list and assert the `translation` relation is pre-loaded. — `tests/Feature/SafeOutputTest.php` — **S**

5. **Consolidate the two CSS-variable token sources** — **What:** Tokens are emitted from two unrelated places with overlapping concerns: `TailwindAssetsGenerator::renderThemeWidget()` writes `@theme { --color-*: … }` into the build CSS from `DefaultColorEnum` + vendor theme colours, while `app/head/tokens.blade.php` re-derives `--color-*` (again from `DefaultColorEnum`) plus `--foundation-*` at runtime. The palette is computed twice with two different sanitisers (`isSafeThemeColor()` in PHP vs the inline `$isSafeToken` closure in Blade). Extract one shared token-resolution Action returning a typed token map, consumed by both the generator and the head partial, so the safe-list and defaults cannot drift. — `src/Support/Tailwind/TailwindAssetsGenerator.php`, `resources/views/components/app/head/tokens.blade.php` — **M**

6. **Type the runtime token contract instead of a 95-line Blade `@php` prologue** — **What:** `app/head/tokens.blade.php` opens with ~95 lines of PHP: `resolve(FoundationThemeSettings::class)` inside try/catch, per-property `$resolveSettingColor` closures, and inline regex sanitisation, all in a public template. This is rendering logic that belongs in a hydrated Data object (`spatie/laravel-data` is already a dependency, see `src/Data/`). Move resolution into `BuildThemeTokenRenderData` and pass a `ThemeTokenRenderData` in; the Blade then only echoes safe, pre-validated values. — `resources/views/components/app/head/tokens.blade.php` — **M**

7. **Make `isNodeModuleImport()` declarative instead of heuristic** — **What:** `TailwindAssetsGenerator::isNodeModuleImport()` hard-codes `tippy.js` and `tailwindcss` as "known packages" and otherwise falls back to a regex guess. New vendor CSS imports (e.g. another `swiper/*`-style package) rely on the regex catching them correctly. Drive node-module detection off the `VendorAssetData` origin (it already knows `packageName`) rather than string-sniffing the import value. — `src/Support/Tailwind/TailwindAssetsGenerator.php:261` — **M**

8. **Reconcile the README "At A Glance" surfaces with the manifest** — **What:** README says *"Surfaces: Livewire, console"* and *"Runtime Surface — Livewire"*, but `capell.json` declares `surfaces: ["admin", "frontend"]`. The README also lists only 3 screenshots under "Screens And Workflow" while the manifest declares 9 and `screenshots.json` 12. Align all three. — `README.md`, `docs/overview.md` — **S**

## 3. Missing Features (gaps)

Tied to `capabilities: ["frontend-assets", "cache-blocking"]` and theme-foundation norms. As the base every child theme inherits, each gap below is multiplied across the whole theme line.

- **No RTL support (table-stakes).** Zero `dir=`, `[dir="rtl"]`, or logical-property conventions in `resources/css` or `resources/views` (grep: 0 matches). The base shell `app.blade.php` sets `lang` but never `dir`. A base theme that cannot lay out Arabic/Hebrew caps every vertical theme at LTR-only. Adopt Tailwind logical utilities (`ps-*`/`pe-*`/`ms-*`) and emit `dir` from the site/language. — **table-stakes**

- **Dark mode is declared but barely implemented.** `variants.css` defines `@custom-variant dark (&:where(.dark, .dark *))` and committed screenshots advertise a dark variant, but the only `.dark` rules in `theme/theme.css` target `.sidebar-sticky .widget-pages` (one widget). There is no dark token set in `head/tokens.blade.php` (all `--foundation-*` values are light-only) and no `prefers-color-scheme` wiring. Either ship a complete dark token layer or stop advertising dark screenshots. — **differentiator** (full token-driven dark mode), **table-stakes** (honest advertising)

- **No documented child-theme override surface.** The theme's entire reason for existing is `extends: capell-app/foundation-theme`, yet `capell.json` has `extends: null` and there is no contract describing which `capell::` views/sections/tokens a child may safely override vs. which are internal. The README says "layers parent defaults, child defaults, and database edits" but nothing in this package enforces or documents that order. Publish a stable override map (section keys, token names, `capell::` component names) and an arch test pinning the public surface. — **differentiator**

- **No typography scale token.** `FoundationThemeSettings` exposes colours, `section_spacing`, `widget_gap`, `image_radius` — but no font-size/line-height scale or `headingScale` persistence (the Theme Studio preset names `headingScale: 'balanced'` but no setting backs it). A foundation theme should own a modular type scale as tokens. — **table-stakes**

- **No theme settings preview / no accessibility primitives in the base shell.** The base `app.blade.php` body has no skip-to-content link, no landmark assertions, no `aria-live` region for the SPA-like widget areas (grep found only `lang=`). Accessibility primitives belong in the base so every child inherits them. — **table-stakes**

- **`cache-blocking` is implemented but undocumented as a capability surface.** `RecordExtensionRenderContributionAction` is correctly called with `cacheable: false, sensitiveOutput: true` in `src/View/Components/Actions.php` and `src/Livewire/Widget/AbstractWidget.php` (capability is reachable, not dead), but neither README nor `docs/` explains the cache-blocking contract child themes must honour. — **table-stakes**

## 4. Issues / Risks

- **Stub critical health check (false-green).** `src/Health/FoundationThemeHealthCheck.php` only returns `compatibleCapellApiVersion()`. `capell.json` declares it `severity: critical`. Diagnostics will report the theme healthy regardless of actual state. (See §2.1.)

- **~1,200 lines of orphaned, untested demo code.** `src/Support/Demo/ThemeDemoPageInstaller.php` (869), `ThemeDemoMedia.php` (296), `ThemeDemoPageDefinition.php`, `src/Contracts/InstallsThemeDemo.php`, `src/Data/ThemeDemoInstallData.php` — referenced only by each other; confirmed no provider/command/manifest wiring. All appear in the uncovered list below.

- **Public-Blade data-loading guard has holes.** `getMeta(` and relationship property access (`$site->translation->title`, `$site->translation->getMeta(…)`) are not in the `SafeOutputTest` forbidden list. `resources/views/components/footer/site-info.blade.php:35,40`, `resources/views/components/content.blade.php:156`, `resources/views/app.blade.php:37-38`. Capell rule: public Blade must not lazy-load relationships. Currently safe by upstream hydration only; no regression guard. (See §2.4.)

- **Colour tokens written into `<style>` via `{{ }}` (HTML escaping, not CSS escaping).** `resources/views/components/app/head/tokens.blade.php` and `resources/views/components/footer/index.blade.php:18,34,45` emit `--color-…: {{ ColorConverterAction::run(...) }}` inside `<style>`. The palette loop is gated by the inline `$isSafeToken` closure, but `$brandColor`, `$linkColor`, `$linkColorActive`, `$dividerColor`, and the `footer/index.blade.php` footer colours are passed through `ColorConverterAction` only — **not** through `$isSafeToken`/`isSafeThemeColor` — before being printed into a CSS context. The safety therefore depends entirely on `ColorConverterAction` never returning a string containing `;`/`}`/`<`. Route every value emitted inside `<style>` through the same `[\x00-\x1F\x7F;{}<>]` safe-list the palette loop and `TailwindAssetsGenerator::isSafeThemeColor()` already use. — **render/output safety**

- **Test coverage gaps (32 test files for 64 src classes).** Heuristic (classname never referenced under `tests/`): `src/Contracts/InstallsThemeDemo.php`, `src/Support/Demo/ThemeDemoPageInstaller.php`, `src/Support/Demo/ThemeDemoPageDefinition.php`, `src/Console/Commands/SetupCommand.php`, `src/Livewire/Assets/Table/AbstractAssets.php`, `src/View/Components/App/Body.php`. The capell testing reference targets a 90% minimum; the demo subsystem alone is a large untested block.

- **`AdminServiceProvider` is a no-op** with an `#[Override]` on an empty `register()`. Tech debt / misleading README. (See §2.3.)

- **WCAG: base shell lacks landmarks/skip-link.** `app.blade.php`/`components/app/body.blade.php` provide only `lang`. Foundation-level a11y omissions propagate to every theme. (See §3.) Note: widget-level a11y *is* tested (`LightboxAccessibilityTest`, language-flag dimensions and `alt=""` in `SafeOutputTest`) — the gap is the page shell, not the widgets.

- **Performance budget present but unverified.** `capell.json` `performance.frontendRenderBudgetMs: 20`, `adminQueryBudget: 40`. No test asserts the head-token prologue (a per-request `resolve(FoundationThemeSettings::class)` + ~18 closure evaluations + two regex-validated palette passes) stays within 20ms, and no admin query-count test backs the 40 budget. Add a render-budget/query-count assertion, especially given §2.6.

- **`cacheSafety.cacheable: false` with `variesBy: ["site", "locale"]` is broad.** The whole theme is marked non-cacheable. Given most of the output is static chrome and only `Actions`/CSRF widgets are truly per-request (correctly marked `cacheable: false` at the component level), a blanket theme-level non-cacheable flag may defeat HTML caching the platform offers. Confirm whether the manifest flag is intended to disable caching theme-wide or is redundant with the component-level contributions.

- **i18n:** translation files exist (`resources/lang/en/{form,generic}.php`) and user-facing strings use `__()` (verified in `SafeOutputTest` assertions, e.g. `scroll_to_top`). Only `en` ships — acceptable for a base, but combined with the RTL gap there is no locale-direction story.

- **Tailwind `tw:` prefix:** N/A here. This package owns its own `@import "tailwindcss"` entrypoint (`resources/css/foundation-theme.css`) with no Bootstrap to collide with; it correctly uses unprefixed utilities (grep: 0 `tw:` occurrences). The `tw:`-prefix convention is a host-frontend rule and must **not** be applied to this package.

## 5. Marketplace & Positioning

**Current manifest `summary`:** *"Default Capell page foundations, layout areas, content widgets, media, forms, and package-aware frontend states."* — Reads as an undifferentiated feature list ("foundations… states") and buries the single most important fact: this is the **base every other theme extends**. "Forms" and "package-aware frontend states" over-promise relative to what this package actually owns (it renders layouts whose data comes from other packages).

**Improved `summary`:** *"The base theme every Capell site and child theme builds on: shared Blade layouts, a runtime design-token system (colours, spacing, radius), the Tailwind asset pipeline, an SVG sanitiser, and the section/area contracts that vertical themes override."*

**Current composer `description`:** *"Capell default theme — ships the standard Tailwind asset pipeline, Blade directives, URL generator, and SVG media component."* — Accurate but narrow; omits the design-token system and the child-theme foundation role that are the real value.

**Improved composer `description`:** *"Capell's foundation theme — base Blade layouts, runtime design tokens, the Tailwind asset pipeline, Blade directives, media/SVG handling, and the override contracts that all vertical Capell themes extend."*

**Quality gates the whole line:** Because every theme inherits `capell::` views, the §4 issues are line-wide liabilities: a stub health check means *no* installed theme self-reports brokenness; the public-Blade guard holes apply to chrome rendered under every theme; the missing RTL/dark-token layer means no child theme can offer them without re-implementing the base. Conversely, fixing the token consolidation (§2.5/2.6) and publishing a documented override surface (§3) is the single highest-leverage investment in the catalogue — it raises the floor under every paid theme.

**Design-system/token consistency:** Today the source of truth for colour tokens is split (build-time `@theme` vs runtime head partial) with two divergent safe-lists; settings cover colour/spacing/radius but not typography. A single typed token contract (§2.5) plus a published token vocabulary is the differentiator that lets the marketplace advertise "consistent design system across all themes."

**Screenshot/media gaps:** 9 manifest screenshots are hand-drawn `.svg` layout mockups, not product captures; `screenshots.json` lists 12 deployment captures; only 6 real `.png`s exist (settings/frontend/tailwind × light+dark). Several manifest entries are conditional on packages not required here (blog, tags, form-builder, search, events, access-gate) — fine as "best with" previews, but they should be labelled mockups, and the 3-way count mismatch (9 / 12 / 6) must be reconciled before listing.

**Keywords/tags:** `capell-cms`, `base-theme`, `theme-foundation`, `design-tokens`, `css-custom-properties`, `tailwind-pipeline`, `blade-layouts`, `child-theme-extends`, `svg-sanitizer`, `layout-builder-areas`, `dark-mode`, `accessibility`.

## 6. Prioritized Roadmap

| Item | Bucket | Effort | Impact | Section ref |
| --- | --- | --- | --- | --- |
| Replace stub health check with real probes | Now | M | High | §2.1, §4 |
| Route all `<style>` token values through the CSS safe-list | Now | S | High | §4 |
| Add `getMeta(`/`->translation->` to public-Blade guard | Now | S | High | §2.4, §4 |
| Delete no-op `AdminServiceProvider` + fix README surfaces | Now | S | Med | §2.3, §2.8 |
| Decide demo subsystem: wire a `:demo` command or delete ~1,200 LOC | Now | M | High | §2.2, §4 |
| Reconcile screenshot counts (9/12/6) + label SVG mockups | Now | S | Med | §1, §5 |
| Consolidate the two token sources into one typed Action | Next | M | High | §2.5, §5 |
| Move head-token prologue into a hydrated Data object | Next | M | Med | §2.6 |
| Publish documented child-theme override surface + arch test | Next | M | High | §3, §5 |
| Add base-shell a11y primitives (skip-link, landmarks, aria-live) | Next | M | High | §3, §4 |
| Ship a full dark token layer or drop dark screenshots | Next | M | Med | §3 |
| Add render-budget + admin query-count assertions vs manifest | Next | S | Med | §4 |
| Add RTL: `dir` emission + logical-property conventions | Later | L | Med | §3 |
| Add typography-scale tokens + back `headingScale` setting | Later | M | Med | §3 |
| Make `isNodeModuleImport()` origin-driven, not heuristic | Later | M | Low | §2.7 |
| Rewrite marketplace `summary` + composer `description` | Later | S | Med | §5 |
