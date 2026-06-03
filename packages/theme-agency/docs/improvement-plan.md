# Theme Agency — Improvement & Growth Plan
> Package: capell-app/theme-agency · Kind: theme · Tier: free · Product group: Capell Foundation · Bundle: foundation · Status: Draft

## 1. Snapshot

`AgencyThemeServiceProvider` registers theme key `agency` (`extends: 'default'` in code, `extends: "capell-app/foundation-theme"` in `capell.json`), wiring a `BladeThemeRenderer` with layout view `capell-theme-agency::page` and seven `ViewSectionRenderer`s (`navigation`, `hero`, `features`, `proof`, `content-listing`, `cta`, `footer`), each `failLoudly: true`. It ships its own Blade for all seven sections plus a page wrapper, and a 69-line `resources/css/theme-agency.css`; it overrides every Foundation section it lists but inherits content-listing's `gallery`/`pathways`/`spotlight` variants by delegating back to `capell-foundation-theme::theme.sections.content-listing`. The demo command `capell:theme-agency-demo` delegates to `InstallAgencyThemeDemoAction` → `ThemeDemoPageInstaller::run($data, 'agency', 'Agency')`, seeding ≥7 pages idempotently (proven in `DemoCommandTest`). Three presets exist in code (`signal`, `gallery`, `atelier`) but the definition test asserts `presets->toHaveCount(6)` — a contradiction that means either the test or the provider is wrong. Current marketplace summary verbatim: *"Creative campaign and studio theme screenshots from route-backed demo layouts."* Screenshots: `capell.json` `marketplace.screenshots` declares 6 paths; `docs/screenshots.json` declares 12 capture entries; only **6 PNGs are committed** in `docs/screenshots/`, of which just **2** match declared paths — and the marketplace gallery points at SVG line-art placeholders, not real theme captures.

## 2. Improvements (existing functionality)

1. **Make the page shell honour preset tokens instead of hardcoding near-black** — the shell forces `#09090b` / `bg-zinc-950` regardless of preset, so the `atelier` preset ("soft neutrals", `playfair`, `#be123c`) and `gallery` ("media-forward", editorial) render on the same dark background as `signal`. A premium theme sold on three visual presets must actually look like three presets. Drive the shell background/foreground from `$brand->tokens()` (a `--theme-surface` / `--theme-ink` token pair) rather than literal hex. `resources/views/page.blade.php`, `resources/css/theme-agency.css` — **M**

2. **Stop hardcoding hex colors in the section Blade** — `hero.blade.php` and `cta.blade.php` bake `via-fuchsia-500` and `to-orange-900` into gradients alongside the `var(--theme-primary)`/`var(--theme-accent)` tokens, so two of three colors in the marquee gradient never change between presets. Replace literal Tailwind color stops with token-driven CSS custom properties so presets fully recolor. `resources/views/sections/hero.blade.php`, `resources/views/sections/cta.blade.php` — **M**

3. **Fix the contrast trap in the proof/content-listing CSS override** — `theme-agency.css` forces `.theme-content-listing a h3 { color: #09090b }` and card body `#52525b` on a white card, while the page wrapper class is `text-zinc-950` on a `bg-zinc-950` shell. Any section that does *not* paint its own light panel inherits near-black text on a near-black shell. Audit every section for an explicit panel background; the CSS comment-free override is brittle. `resources/css/theme-agency.css` — **S**

4. **Give the navigation a real disclosure pattern** — mobile menu uses `<details>/<summary>` with `marker:hidden`; there is no `aria-expanded`, no focus trap, and the desktop/mobile links are duplicated in markup. It works without JS (good) but reads as a stopgap for a flagship theme. Consider an Alpine disclosure consistent with Foundation Theme's bundled `@ryangjchandler/alpine-tooltip`/floating-ui stack, keeping a no-JS fallback. `resources/views/sections/navigation.blade.php` — **M**

5. **Resolve the proof carousel's progressive-enhancement gaps** — the inline `<script>` carousel in `proof.blade.php` adds prev/next buttons but they are `hidden` until JS measures overflow; there is no `aria-live`, the buttons use raw `‹`/`›` glyphs, and scroll-snap on touch is the only mobile affordance. Add reduced-motion handling (`prefers-reduced-motion`) and proper button labels beyond `aria-label="Next proof"`. `resources/views/sections/proof.blade.php` — **S**

6. **Reconcile the `definition()` assets path with what's actually registered** — `definition()` returns `assets: ['css' => 'vendor/capell/themes/agency.css']` and preview images `/vendor/capell/themes/agency-signal.jpg` (etc.), but the provider registers a Tailwind import of `resources/css/theme-agency.css` and no `agency.css` or preset JPEGs are committed in the package. Either publish those assets or correct the definition so preview images and the declared CSS resolve. `src/AgencyThemeServiceProvider.php` — **S**

7. **Decompose the hero's decorative mock-up** — the hero's right column is ~120 lines of nested decorative divs (fake "scene", "launch board", channel bars) marked `aria-hidden`. It is impressive but unmaintainable inline and repeats token references. Extract to a partial (`sections/partials/hero-canvas.blade.php`) so the hero stays readable and the decoration is reusable. `resources/views/sections/hero.blade.php` — **S**

## 3. Missing Features (gaps)

`capabilities` is only `["theme-agency", "theme-agency-frontend"]` and the theme overrides exactly the seven baseline Foundation sections. For a creative/agency vertical the screenshot plan *describes* nine layout pages (homepage, services, portfolio, case-study, insights, event-landing, lead-form, campaign, search) but these are **page compositions of the same seven renderers plus other packages** (`capell-app/blog`, `capell-app/form-builder`, `capell-app/search`, `capell-app/content-sections`) — there are no agency-specific section renderers behind them. Concrete gaps:

- **Portfolio / project-grid renderer** — an agency lives on work. Today "portfolio" is `content-listing` reusing the foundation `gallery` variant. A dedicated filterable project-grid section (channel/discipline filters, aspect-ratio-preserving media, hover reveal) is table-stakes and is the single biggest differentiator vs Foundation. **Differentiator.**
- **Case-study / long-form project renderer** — no dedicated section for hero metric + challenge/approach/result + result stats + gallery. This is the agency portfolio money-shot. **Differentiator.**
- **Team / people section** — no team renderer (photo grid, roles, socials). Agencies sell people. **Table-stakes.**
- **Services / capabilities section** — "services" currently reuses `features`. A stepped services renderer (discipline → deliverables → process) is expected. **Table-stakes.**
- **Logo / client wall** — `proof` mixes testimonials, metrics, and logos into one carousel. A clean client-logo strip is standard agency proof. **Table-stakes.**
- **Pricing / engagement section** — optional but common for productized agencies; absent. **Differentiator (optional).**
- **Light-mode preset** — all three presets render dark; an editorial/atelier light variant is a genuine product gap (see §2.1). **Differentiator.**

Versus siblings (10 themes exist: foundation-theme, theme-commerce, theme-corporate, theme-education, theme-healthcare, theme-knowledge, theme-local-services, theme-nonprofit, theme-portfolio, theme-saas): `theme-portfolio` and `theme-saas` both ship richer CSS (101 and 198+90 lines respectively vs Agency's 69) and `theme-portfolio` directly overlaps the creative buyer. Agency must out-render portfolio on *campaign motion + case studies* or the two cannibalize.

## 4. Issues / Risks

- **Manifest tier vs commercial conflict (high).** `capell.json` declares `product.tier: "free"`, `product.bundle: "foundation"`, `product.group: "Capell Foundation"`, yet `commercial.proposedLicense: "paid"` and the brief treats themes as premium. `docs/overview.md` separately states `Tier: free · Bundle: themes · Product group: Capell Themes`. Three sources, three different bundle/group values. A paid theme cannot ship `tier: free` in the bundle that gates marketplace pricing. `capell.json`, `docs/overview.md` — decide paid-tier classification and make all three agree.
- **Screenshot manifest mismatch (high, commercial blocker).** `docs/screenshots.json` declares 12 entries; `docs/screenshots/` contains 6 PNGs; **10 of 12 declared screenshot paths are MISSING** on disk (`agency-homepage-layout.png`, `agency-services-layout.png`, `agency-portfolio-layout.png`, `agency-case-study-layout.png`, `agency-insights-layout.png`, `agency-event-landing-layout.png`, `agency-lead-form-layout.png`, `agency-campaign-layout.png`, `agency-search-layout.png`, `theme-admin-list-showing-agency.png`). The 6 committed PNGs include three `-dark` variants referenced by neither manifest. `capell.json` `marketplace.screenshots` declares 6 paths but points at SVG wireframe placeholders (`docs/assets/marketplace/agency-*-layout.svg`), not captures. `docs/screenshots.json`, `capell.json`, `docs/screenshots/`, `docs/assets/marketplace/`.
- **Health check is a stub but declared `critical` (medium).** `ThemeAgencyHealthCheck` implements only `compatibleCapellApiVersion()`; it performs no probe (theme registered? views resolvable? preset valid?). `capell.json` `healthChecks[0].severity: "critical"` overstates a no-op. Either implement real assertions or downgrade the severity. `src/Health/ThemeAgencyHealthCheck.php`, `capell.json`.
- **Preset count contradiction (medium).** `AgencyThemeServiceProvider::definition()` defines 3 presets; `AgencyThemeDefinitionTest` asserts `->toHaveCount(6)`. One is stale. If the suite is green, the provider isn't the code under test here, or the assertion is wrong — either way the canonical preset count is ambiguous. `src/AgencyThemeServiceProvider.php`, `tests/Unit/AgencyThemeDefinitionTest.php`.
- **Public-output safety: covered (low risk, keep it).** `PublicOutputSafetyTest` asserts no `capell-app/theme-agency`, `Filament`, `Livewire`, `wire:`, `data-theme-key`, `signed`, `data-field`, `model_id`, `permission` in rendered Blade + lang, and bans `::query(`, `DB::`, `loadMissing(`, `->translation`, `find(` from Blade. Page wrapper renders pre-hydrated `{!! $content !!}` and `$brand->tokens()` only — no DB access. This is correct per Capell's public-output contract; preserve it when adding sections (every new renderer needs an entry in this guard).
- **WCAG concerns (medium).** Body copy at `text-white/70` / `text-white/65` / card `text-zinc-600` on tinted panels risks failing 4.5:1; `font-weight: 900` headings at `line-height: 0.92` (CSS) hurt legibility at small sizes; decorative-only `aria-hidden` blocks are fine, but the `08`/`14d`/`01` hero stats are unlabeled magic values. Audit contrast across all three presets. `resources/css/theme-agency.css`, `resources/views/sections/hero.blade.php`.
- **LCP / image performance (medium).** `hero.blade.php` and `content-listing.blade.php` emit `<img>` with no `loading`, `decoding`, `width`/`height`, or `fetchpriority="high"` on the hero image — the LCP element is unhinted. `frontendRenderBudgetMs: 20` is declared but there's no asserted render-time test. Add image attributes and consider a render-budget smoke test. `resources/views/sections/hero.blade.php`, `resources/views/sections/content-listing.blade.php`, `capell.json`.
- **Cache safety (low, verify).** `performance.cacheSafety.cacheable: false`, `variesBy: ["site","locale"]`, `queueInvalidation: true`. The inline proof `<script>` and `<details>` are static markup, so `cacheable: false` may be more conservative than needed; confirm whether the theme genuinely can't be cached or whether this is a default left unset. `capell.json`.
- **Hardcoded magic numbers in copy (low).** Hero stats `08`, `14d`, `01` and scene labels are literal in Blade, not data-driven or translated — they will read as lorem on a real client site. Make them section data or remove. `resources/views/sections/hero.blade.php`.

## 5. Marketplace & Selling

**Current summary** (`capell.json`): *"Creative campaign and studio theme screenshots from route-backed demo layouts."* — This describes the screenshot pipeline, not the product. A buyer doesn't care that captures come from "route-backed demo layouts"; that's internal tooling language. **Current composer `description`**: *"Expressive agency theme for Capell"* — accurate but generic and interchangeable with every other theme's one-liner. The `capell.json` `description` is better (*"registers the agency theme key and expressive renderer views for studio, portfolio, and brand-led sites"*) but still leads with "registers the agency theme key," which is plumbing.

**Improved 1-sentence summary:**
> A bold, motion-led theme for creative studios and marketing agencies — campaign hero, case-study proof, and a filterable work showcase, with three presets from high-contrast Signal to editorial Atelier.

**Improved 3–4 sentence description:**
> Theme Agency turns a Capell site into a confident creative portfolio. It ships an expressive page system — full-bleed launch hero, animated proof wall, project showcase, and a conversion-focused brief CTA — built to make studio and agency work look like the work, not a template. Three presets (Signal, Gallery, Atelier) re-skin every section from energetic high-contrast to refined editorial neutrals, all driven by Theme Studio tokens with zero code. Built on Foundation Theme contracts, it stays fast, cache-aware, and safe for public output, so it drops into the standard Capell theme workflow.

**Screenshot / media gaps (commercial blocker):** themes sell on visuals and this package is effectively shipping with **2 valid captures**. Required before listing: replace the 9 SVG wireframe placeholders with real high-resolution captures of each declared layout (homepage, services, portfolio, case-study, insights, event-landing, lead-form, campaign, search), one capture **per preset** for at least the homepage and a case study (so buyers see Signal/Gallery/Atelier differ), plus mobile captures (`hero-mobile.jpg` exists as a placeholder — make it real), and an animated capture of the proof carousel. Reconcile `screenshots.json` (12) ↔ `capell.json` `marketplace.screenshots` (6) ↔ committed files so counts match. Decide whether the `-dark` PNGs are part of the story; if so, declare them.

**Differentiation vs the other 9 themes:** position Agency as the *campaign-and-case-study* theme — the one with motion, a real project showcase, and presets that swing from loud to editorial. Keep a hard line against `theme-portfolio` (which owns the quiet, grid-first creative buyer) by leaning into campaign rhythm, big type, and conversion CTAs; against `theme-saas`/`theme-corporate` by being deliberately expressive rather than restrained. **Target buyer:** boutique creative/branding/marketing agencies and studios (1–30 people) who want a launch-ready, premium-looking site without bespoke front-end build.

**Keywords/tags:** agency theme, creative studio, portfolio, case study, marketing agency, branding, expressive, motion, campaign landing, dark theme, editorial, Capell theme.

## 6. Prioritized Roadmap

| Item | Bucket | Effort | Impact | Section ref |
|------|--------|--------|--------|-------------|
| Reconcile tier/bundle/group across `capell.json` + `docs/overview.md`; set paid classification | Now | S | High | §4 |
| Produce real screenshots for all 12 declared captures; sync `screenshots.json` ↔ `capell.json` ↔ files | Now | M | High | §4, §5 |
| Resolve preset-count contradiction (3 in code vs 6 in test); make canonical | Now | S | Med | §4 |
| Fix `definition()` assets path + missing preset preview JPEGs | Now | S | Med | §2.6, §4 |
| Drive page-shell surface/ink from preset tokens (true light/dark per preset) | Now | M | High | §2.1, §3 |
| Rewrite marketplace `summary` + composer `description` | Now | S | High | §5 |
| Remove hardcoded hex/gradient color stops from hero + CTA | Next | M | High | §2.2 |
| Contrast/WCAG audit across all three presets | Next | M | Med | §4, §2.3 |
| Add dedicated project-showcase (portfolio) renderer | Next | L | High | §3 |
| Add case-study section renderer | Next | L | High | §3 |
| Add hero image LCP hints (`fetchpriority`, dims, `loading`) | Next | S | Med | §4 |
| Implement real `ThemeAgencyHealthCheck` probes (or downgrade severity) | Next | M | Med | §4 |
| Add team + services + client-logo renderers | Later | L | Med | §3 |
| Ship a light-mode preset and `prefers-reduced-motion` handling | Later | M | Med | §2.1, §2.5 |
| Extract hero decorative canvas to a partial; data-drive hero stats | Later | S | Low | §2.7, §4 |
