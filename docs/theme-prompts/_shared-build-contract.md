# Shared Build Contract for Capell Theme Prompts

Every theme prompt in this folder is a self-contained brief. They all share the
same build contract, captured here so each prompt stays focused on what makes its
theme distinctive (positioning, palette, sections, demo data). Read this once,
then read the individual prompt.

The authoritative engineering guide is [`docs/creating-a-theme.md`](../creating-a-theme.md).
This file is the condensed contract; that file is the source of truth when they
disagree.

The closest working examples to copy from are `packages/theme-saas`,
`packages/theme-agency`, and `packages/theme-corporate`.

---

## 1. What a Capell theme is

A theme is a thin Composer package that registers a **frontend renderer** and a
**theme definition**. It owns presentation only. Page content stays portable: no
presentation markup is stored on pages, and the public route renders from
hydrated, query-free render data.

Most new themes **extend `default` (Foundation Theme)** at runtime and
`capell-app/foundation-theme` at the package level. Foundation owns the shared
Blade, Tailwind, media, settings, and runtime. A child theme provides: a theme
definition, presets, a page wrapper, the section views it intentionally
customises, and demo (beta) data.

Themes have **no migrations, routes, models, admin navigation, or settings** of
their own.

## 2. Package skeleton

```text
packages/theme-<key>/
    capell.json                     # manifest v3 (see §3)
    composer.json                   # PSR-4 + provider registration
    README.md  CHANGELOG.md
    docs/overview.md                # marketplace + usage docs, screenshots
    src/
        <Name>ThemeServiceProvider.php
        Actions/Install<Name>ThemeDemoAction.php
        Console/Commands/DemoCommand.php
        Health/Theme<Name>HealthCheck.php
        Manifest/ThemeManagementPageContribution.php
        ThemeStudio/Adapters/<Name>ThemePageAdapter.php   # only if it customises adaptation
        Rendering/                  # custom section renderers (e.g. BlogSectionRenderer)
    resources/
        views/page.blade.php        # page wrapper
        views/sections/*.blade.php  # one per customised section key
        views/livewire/page/page.blade.php  # thin; calls RenderCurrentThemePageAction
        css/theme-<key>.css
        lang/en/generic.php         # all user-facing strings
        boost/guidelines/core.blade.php
    tests/
        Unit/ManifestRequirementsTest.php
        Unit/<Name>ThemeDefinitionTest.php
        Unit/<Name>ThemePageAdapterTest.php
        Unit/PublicOutputSafetyTest.php
        Feature/Commands/DemoCommandTest.php
        Feature/Theme<Name>HealthCheckTest.php
        Pest.php
```

Namespace: `Capell\ThemeStudio\<Name>` (PascalCase, e.g. `Capell\ThemeStudio\AiLab`).
Package name: `capell-app/theme-<key>`. View namespace: `capell-theme-<key>`.
Translation namespace: `capell-theme-<key>`.

## 3. `capell.json` (manifest v3) — required fields

Copy `packages/theme-saas/capell.json` and change the identity. Key fields:

- `"manifest-version": 3`, `"kind": "theme"`, `"capellApiVersion": "^4.0"`,
  `"version": "4.x-dev"`.
- `name`, `slug`, `displayName`, `namespace`, `themeKey`, `extends: "default"`.
- `surfaces: ["frontend"]`.
- `dependencies.requires`: `["capell-app/core", "capell-app/frontend"]`. Add
  optional companions under `supports` (e.g. `capell-app/blog`,
  `capell-app/form-builder`).
- `providers.runtime`: `["Capell\\ThemeStudio\\<Name>\\<Name>ThemeServiceProvider"]`.
- `contributes`: one `admin-page` entry pointing at
  `Capell\Marketplace\Filament\Pages\ThemeExtensionPage` with
  `pageParameters.themeKey`.
- `database`: all false / empty (no migrations or settings).
- `commands.demo`: `"capell:theme-<key>-demo"`, `demoParams: ["url","languages","sites"]`.
- `capabilities`: `["theme-<key>", "theme-<key>-frontend"]`.
- `security.publicOutput`: `cacheSafe: true`, `forbidAuthoringSurface: true`,
  `forbidSecrets: true`, `forbidPublicBladeQueries: true`. `riskTier: "low"`.
- `performance.frontendRenderBudgetMs: 20`, `cacheTags: ["theme-<key>"]`.
- `healthChecks`: one entry for `theme-<key>.package-health`.
- `marketplace`: `summary`, `description`, `screenshots`, `categories: ["frontend","themes"]`.

## 4. Service provider + theme definition

Follow `SaasThemeServiceProvider`:

- `register()` is empty for first-party packages (the manifest registers the
  package). `boot(ThemeRegistry $registry)`:
    1. register the demo command in console,
    2. early-return unless `CapellCore::isPackageInstalled(self::$packageName)`,
    3. `loadTranslationsFrom` + `loadViewsFrom`,
    4. register vendor CSS assets (`VendorAssetData::tailwindImport` for the CSS and
       `VendorAssetData::tailwindSource` for `resources/views/**/*.blade.php`),
    5. register the page adapter on `ThemePageAdapterRegistry` (only if customised),
    6. `$registry->register(definition, new BladeThemeRenderer(...), sectionRenderers)`.
- `definition(): ThemeDefinitionData` declares: `key`, `name`, `description`,
  `package`, `previewImage`, `tags`, `bestFit`, `includedSections`, `presets`,
  `assets`, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.

### Preset token vocabulary (`ThemePresetData::values`)

Use only these keys. They merge into `BrandProfileData` and surface as CSS custom
properties (`--theme-primary`, `--theme-accent`, `--theme-heading-font`, …) via
the page wrapper.

`primaryColor`, `accentColor`, `neutralColor`, `surfaceColor`, `foregroundColor`,
`headingFont`, `bodyFont`, `spacing`, `alignment`, `cardStyle`,
`navigationStyle`, `layoutPresentation`, `motionIntensity`, `mediaTreatment`,
`radius`, `headingScale`, `cardDensity`.

Each prompt gives concrete hex/enum values for its theme. Common enum-ish values
seen in shipped themes: `spacing` (`compact|balanced|airy`), `cardStyle`
(`flat|bordered|elevated`), `navigationStyle` (`minimal|prominent`),
`layoutPresentation` (`structured|editorial|immersive`), `motionIntensity`
(`none|subtle|expressive`), `mediaTreatment` (`flat|framed|duotone`), `radius`
(`none|sm|md|lg|xl`), `headingScale` (`compact|balanced|dramatic`),
`cardDensity` (`compact|comfortable`).

## 5. Sections

Standard shared section keys (inherit from Foundation unless customised):
`navigation`, `hero`, `features`, `proof`, `content-listing`, `cta`, `footer`.

Shipped premium themes add their own keys (e.g. SaaS adds `logos`,
`testimonials`, `faq`, `comparison`, `calculator`, `pricing`,
`docs-onboarding`, `demo-request`, `blog`). A theme registers a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-<key>::sections.<key>', failLoudly: true)`
per customised key, plus custom renderer classes (e.g. a `BlogSectionRenderer`)
when a section depends on an optional companion package.

Section view data comes from typed Data objects the page adapter builds:
`HeroSectionData`, `FeatureSectionData`, `ProofSectionData`,
`ContentListingSectionData` (supports `variant`: `spotlight|gallery|pathways`),
`CtaSectionData`, `NavigationData`, `FooterData`
(all under `Capell\Core\ThemeStudio\Data`). New sections a prompt introduces
should describe their render-data shape so the adapter and Blade agree.

Each prompt lists `includedSections` and flags which are **new** to that theme.

## 6. Demo / "beta data" (this is the rich seed content)

Demo content is what makes a theme feel launch-ready. Two layers:

1. **Page surfaces** — the installer seeds 7 portable demo pages per site:
   `homepage`, `directory`, `detail`, `contact`, `empty`, `not-found`, `cta`.
   See `ThemeDemoPageInstaller`. Each page stores a `renderData` array under
   `meta.theme_demo.render_data`; the page adapter reads it (query-free) and
   builds sections.
2. **Theme profile** — per-theme copy injected in
   `ThemeDemoPageInstaller::profile()`: `summary`, `heroHeading`, `heroSummary`,
   `featuresHeading/Summary` + `features[]`, `spotlight[]`, `gallery[]`,
   `pathways[]`, `proof[]` (metric/name/quote), `ctaHeading/Summary`.

Your theme's demo plumbing:
`Install<Name>ThemeDemoAction implements InstallsThemeDemo` →
`ThemeDemoPageInstaller::run($data, '<key>', '<Name>')`, exposed through
`capell:theme-<key>-demo`. Add a matching profile entry (or, where the theme
owns extra sections, richer render data) so the demo install produces realistic,
industry-specific pages — **not** generic placeholder text.

**Every prompt must specify rich beta data**: real-sounding brand, headings,
body copy, 3–6 feature cards, 3+ proof metrics with quotes, directory/detail
sample entries, and any vertical-specific content (pricing tiers, schedule,
specs, episodes, etc.). This is the deliverable's main quality bar.

## 7. Page wrapper

`resources/views/page.blade.php` renders a skip link, the brand tokens as inline
CSS custom properties, and `{!! $content !!}`. Include
`data-capell-theme="{{ $themeKey }}"` per the guide. Keep
`livewire/page/page.blade.php` thin (`RenderCurrentThemePageAction::run()`).

Use `@frontendAsset('css/theme-<key>.css')` for theme assets — never
root-relative paths (the frontend emits a `<base>` tag).

## 8. Public output safety (mandatory, tested)

Public Blade, CSS, JS, and cached HTML must never contain: the package name,
`authoring`, `data-theme-key`, `Filament`, `Livewire`, `wire:`, `signed`,
`data-field`, `data-model`, `field_path`, `model_id`, `permission`. Public Blade
must not query the database: no `::query(`, `DB::`, `loadMissing(`,
`relationLoaded(`, `Frontend::`, `->translation`, `find(`, lazy relations.
Pass hydrated render data in. Copy `tests/Unit/PublicOutputSafetyTest.php` from
theme-saas and adapt the namespace.

## 9. Registration in this monorepo

When adding the package, mirror the autoload entries in **both**
`composer.json` and `composer.local.json`:

```
"Capell\\ThemeStudio\\<Name>\\": "packages/theme-<key>/src"
"Capell\\ThemeStudio\\<Name>\\Tests\\": "packages/theme-<key>/tests"
```

Then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## 10. Acceptance criteria (tests every theme ships)

- `ManifestRequirementsTest`: `kind: theme`, manifest v3 fields, stable
  `themeKey`, correct `extends`, demo command name, capabilities, health check.
- `<Name>ThemeDefinitionTest`: presets, `includedSections`, package name,
  preview image, tags, bestFit, assets, `extends: 'default'`.
- `<Name>ThemePageAdapterTest`: render data → expected sections; fallbacks.
- `PublicOutputSafetyTest`: §8 assertions over Blade + lang files.
- `DemoCommandTest`: `capell:theme-<key>-demo` seeds the 7 surfaces with the
  theme's profile copy.
- Health check feature test.

Run the narrowest command first:

```bash
vendor/bin/pest packages/theme-<key>/tests --configuration=phpunit.xml
```

Then broaden (`COMPOSER=composer.local.json composer preflight`) before commit.
For browser-visible verification, render through a workbench or the full Capell
app and confirm anonymous + non-admin output is editor-free.

---

## Prompt file template

Every `NN-<key>.md` prompt follows this structure:

1. **Title** — `# Theme: <Name> (`theme-<key>`)`
2. **Build this** — one-paragraph mandate.
3. **Inspiration** — the source site(s) / browser tab(s) and what to borrow.
4. **Gap it fills** — why the current theme set doesn't cover this.
5. **Package identity** — package name, slug, namespace, themeKey, displayName,
   tier, bestFit, tags.
6. **Design direction** — full preset token table with concrete values + a
   one-line rationale; typography and motion notes.
7. **Sections** — `includedSections` list; mark NEW sections and give their
   render-data shape.
8. **Beta data (demo profile)** — rich, industry-specific copy: brand, hero,
   features, proof metrics+quotes, spotlight/gallery/pathways, directory + detail
   sample entries, and any vertical-specific content blocks.
9. **Build steps** — numbered, referencing the files in §2–§9 above.
10. **Acceptance criteria** — the tests in §10 plus theme-specific checks.
11. **Verification** — exact pest/preflight commands.
