# Complete Theme Recipe — theme-quiet-type

> Supersedes the deleted `theme-agency` pilot recipe (Wave 0.2 of
> `docs/theme-improvement-programme-2026-h2.md`). `theme-agency` no longer
> exists — it was folded into `theme-front-row`'s hand-picked preset during
> the 75→19 theme catalogue consolidation. `theme-quiet-type` is the current
> reference recipe: a "classic-with-intent" theme that reuses Foundation's
> navigation/footer chrome rather than owning bespoke chrome, and ships a
> complete, real-render demo across all seven Foundation surfaces.

This is a worked example of what every theme in the catalogue should look
like end to end — manifest, provider, sections, demo content, tests, and
screenshots — using `theme-quiet-type` as the concrete reference. Use it
alongside `docs/creating-a-theme.md` (general steps) and
`docs/theme-improvement-programme-2026-h2.md` §Waves 4a–4c/5–7 Part 2 (the
per-family widget/section specs) when building or leveling up a theme.

## 1. Manifest (`capell.json`, manifest v3)

- `kind: "theme"`, stable `themeKey: "quiet-type"`, `extends: "default"`.
- `providers.runtime` names the single definition-only service provider
  (`QuietTypeThemeServiceProvider`) — no separate admin/frontend providers
  needed for a theme this size.
- `healthChecks` registers `ThemeQuietTypeHealthCheck` at `critical`
  severity, following the same contract as Foundation's own health check.

## 2. Composer package (`composer.json`)

- PSR-4 root: `Capell\ThemeStudio\QuietType\` → `src/` (every *child* theme
  uses this `Capell\ThemeStudio\<Studio>\` convention — only
  `theme-foundation` itself keeps the older `Capell\FoundationTheme\` root,
  since it predates the convention).
- `autoload-dev`: `Capell\ThemeStudio\QuietType\Tests\` → `tests/`.
- Register the package in the monorepo workflow per `CLAUDE.md`: root
  Composer autoload/autoload-dev, `composer.local.json` overlay mapping,
  root package index/docs, `docs/themes.json` catalogue entry, and
  `tests/Pest.php`.

## 3. Service provider (`QuietTypeThemeServiceProvider.php`)

- Definition-only: registers `ThemeDefinitionData` (key, package, extends,
  `includedSections`, Theme Studio token list, family/tier metadata) and the
  section renderers for its bespoke sections (`essay-index`,
  `issue-archive`, `author-profiles`, `subscription-panel`,
  `editorial-statement`) — plus reuses Foundation's `navigation` and
  `footer` renderers directly rather than re-registering them (`docs/themes.json`
  records this as `"standardSections": ["navigation", "footer"]`).
- No bespoke header/footer Blade — this is the "reuses Foundation chrome"
  pattern, distinct from themes that own their own header/footer (most of
  the catalogue) and from layout-native themes that render through
  Layout Builder containers instead of section renderers (night-shift,
  liquid-glass).

## 4. Sections

- Standard sections used as-is from Foundation: `navigation`, `footer`,
  `features`, `proof`, `content-listing`, `cta`.
- Bespoke signature sections (the theme's literary-journal identity):
  `hero` (theme-owned view, serif-first), `essay-index`, `issue-archive`,
  `author-profiles`, `subscription-panel`, `editorial-statement`.
- Per the Wave 2.2 section-variant vocabulary, a theme this mature should
  also declare named variants for any section it owns more than one visual
  treatment of — quiet-type currently ships one treatment per bespoke
  section, which is acceptable for a theme whose differentiation is
  typographic (serif/dropcap/marginalia) rather than layout-driven.

## 5. Demo content (`src/Support/Demo/QuietTypeDemoContent.php`)

Implements `ProvidesThemeDemoContent`, returning one `ThemeDemoPageDefinition`
per Foundation surface (`homepage`, `directory`, `detail`, `contact`,
`empty`, `not-found`, `cta`). Each surface, per the fleet-wide
`tests/Arch/ThemeDemoCompletenessContractTest.php` contract:

1. Opens its `render_data['sections']` list with a `hero` and contains a
   `cta`.
2. Meets its per-surface minimum section count (homepage ≥6, directory ≥3,
   detail ≥3, contact ≥3, empty ≥3, not-found ≥2, cta ≥3).
3. Carries at least the minimum bespoke "signature" sections beyond the
   shared core types (homepage ≥2, detail ≥1) — quiet-type's signature
   sections are `essay-index`/`issue-archive`/`author-profiles`/
   `editorial-statement`/`subscription-panel`.
4. Seeds only section types the theme actually registers a renderer for
   (`renderableSectionTypes()` globs the theme's `resources/views/sections/*.blade.php`
   — or, for `theme-foundation` itself, `resources/views/theme/sections/*.blade.php`).
5. Carries real `navigation`/`footer` chrome (`brandName` + ≥3 nav items,
   ≥2 footer columns).
6. Contains no placeholder/weak demo copy (`lorem`, `placeholder`, `"demo
   content"`, etc. — see `WEAK_COPY_PATTERNS` in the Arch test).
7. Uses one consistent brand name (`"The Quire Review"`) across all seven
   surfaces.

Helper methods factor each section type into a small, reusable builder
(`heroSection()`, `essayIndexSection()`, `issueArchiveSection()`, …) so the
seven surface methods stay readable — copy this shape directly for a new
theme's demo content class.

## 6. Install action + demo command

- `src/Actions/InstallQuietTypeThemeDemoAction.php` implements
  `InstallsThemeDemo`, delegating to
  `ThemeDemoPageInstaller::run($data, 'quiet-type', 'Quiet Type', new QuietTypeDemoContent)`.
- A `capell:theme-quiet-type-demo` console command (parses `--sites`,
  `--languages`, `--url`, `--force`) invokes the action — registered in the
  service provider's `commands()` alongside the health check.

## 7. Screenshots (`docs/screenshots.json` + `docs/screenshots/`)

- Real renders of the live `/theme-quiet-type*` routes (via
  `capell-screenshot-runner`), not synthetic fixtures — the retired
  `*ScreenshotRenderer`/`routes/screenshot-fixtures.php` pattern from the
  pre-2026-06-28 rollout no longer exists anywhere in the catalogue.
- Full surface coverage: homepage, directory (`essays`), detail, contact,
  empty, not-found, cta — each with light + dark, and desktop/tablet/mobile
  variants where the surface warrants it (quiet-type's manifest is one of
  only two in the fleet with complete 7-surface coverage per the Wave 0.7
  interior-page audit; the other 17 use ad hoc surface names and are
  missing `empty`/`not-found`/`cta` captures — closing that gap is tracked
  under Wave 3.4's screenshot-manifest completeness check).

## 8. Tests

- `tests/Unit/QuietTypeThemeDefinitionTest.php` — asserts the registered
  `ThemeDefinitionData` (key, package, extends, included sections, token
  list) matches the manifest and `docs/themes.json`.
- `tests/Unit/PublicOutputSafetyTest.php` — a ~5-line invocation of the
  shared `Capell\FoundationTheme\Testing\AssertsPublicThemeOutputSafety`
  trait (`assertClassicThemeOutputIsSafe()` + `assertPhpBlockPolicy()`; see
  Wave 1.1/1.2).
- `tests/Arch/ThemeDemoCompletenessContractTest.php` (fleet-wide, at the
  repo root) auto-discovers `QuietTypeDemoContent` by the
  `Capell\ThemeStudio\<Studio>\Support\Demo\<Studio>DemoContent` naming
  convention and runs the full contract described in §5 — no
  quiet-type-specific test file is needed for that coverage.
- `packages/theme-foundation/tests/Unit/ThemeCatalogueTest.php` +
  `ThemeCatalogueRenderingTest.php` (fleet-wide) verify the `docs/themes.json`
  entry agrees with the manifest and registered definition.

## 9. Catalogue entry (`docs/themes.json`)

`themeKey: "quiet-type"`, `family: "editorial-publishing"`,
`overlapRisk: "low"` (quiet-type is the only serif-first, foundation-chrome
theme in its family — see the Wave 0.3 headline-mechanic note: "literary
intelligence"), `standardSections: ["navigation", "footer"]`,
`customSections` listing the five bespoke sections above.

## Reuse this recipe for

Any new "classic-with-intent" theme that reuses Foundation chrome rather
than owning its own header/footer, or as a structural checklist (swap in
your own bespoke section names) for any theme being leveled up under
Waves 4a–4c or built fresh under Waves 5–7.
