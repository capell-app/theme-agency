# Theme: DesignStudio (`theme-design-studio`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
skeleton, manifest, provider wiring, safety rules, and acceptance baseline. This
file only specifies what makes DesignStudio distinct.

## Build this

A quiet, image-led renderer for interior, architecture, and brand studios — the
refined opposite of the loud `agency` theme. Where Agency shouts with motion and
gradient, DesignStudio whispers: large serif headings, generous whitespace,
framed photography, and editorial pacing. It is built for a small high-end studio
that wins work through portfolio and reputation, not through pop-ups and badges.
Extends `default` at runtime and `capell-app/foundation-theme` at the package
level.

## Inspiration

Borrow from Groth Studio (interior + hospitality, Barcelona & NYC) and Sketch's
product marketing site. From Groth: the full-bleed project photography, the
restrained captioning (location + year only), and the "studio statement" voice.
From Sketch: the disciplined type scale and the calm, unhurried section rhythm.
Borrow the _feel_ — do not copy any text, logos, or images. Invent original demo
copy for a fictional studio.

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) has no calm, serif-led, gallery-first studio theme.
`agency` is expressive and conversion-driven; `portfolio` is creator/consultant
oriented with media kits and newsletters. DesignStudio serves a _physical-craft_
studio (interiors, architecture, spatial brand) that sells through lookbooks and
awards, not lead-capture funnels.

## Package identity

| Field          | Value                                                                                                   |
| -------------- | ------------------------------------------------------------------------------------------------------- |
| package        | `capell-app/theme-design-studio`                                                                        |
| slug           | `theme-design-studio`                                                                                   |
| namespace      | `Capell\ThemeStudio\DesignStudio`                                                                       |
| themeKey       | `design-studio`                                                                                         |
| displayName    | `Design Studio`                                                                                         |
| tier           | `premium`                                                                                               |
| bestFit        | `["Interior design studios", "Architecture practices", "Brand & spatial design", "Hospitality design"]` |
| tags           | `["Editorial", "Serif", "Portfolio", "Minimal", "Photography"]`                                         |
| view namespace | `capell-theme-design-studio`                                                                            |

## Design direction

| Token              | Value         | Rationale                                             |
| ------------------ | ------------- | ----------------------------------------------------- |
| primaryColor       | `#1c1917`     | Near-black ink for headings and chrome.               |
| accentColor        | `#c2683f`     | Warm terracotta — a single restrained brand accent.   |
| neutralColor       | `#292524`     | Warm stone for borders and secondary text.            |
| surfaceColor       | `#faf7f2`     | Off-white paper warmth, never clinical white.         |
| foregroundColor    | `#1c1917`     | Ink text on paper surface for editorial contrast.     |
| headingFont        | `fraunces`    | High-contrast serif; gives the dramatic display feel. |
| bodyFont           | `inter`       | Neutral sans for long captions and body legibility.   |
| spacing            | `airy`        | Generous whitespace is the whole point.               |
| alignment          | `left`        | Editorial left-rag, not centered marketing.           |
| cardStyle          | `flat`        | No shadows; images and rules do the structuring.      |
| navigationStyle    | `minimal`     | Sparse nav stays out of the photography's way.        |
| layoutPresentation | `editorial`   | Magazine-style rhythm, asymmetric columns.            |
| motionIntensity    | `subtle`      | Gentle fades only; nothing bounces.                   |
| mediaTreatment     | `framed`      | Thin framing around imagery for gallery feel.         |
| radius             | `none`        | Hard edges read as architectural and precise.         |
| headingScale       | `dramatic`    | Oversized serif display headings carry the page.      |
| cardDensity        | `comfortable` | Breathing room between gallery and service cards.     |

Typography: Fraunces in its high-optical-contrast setting for display; Inter at a
relaxed line-height (1.7) for captions and body. Pair an oversized hero heading
(clamp up to ~5rem) with small-caps eyebrow labels. Motion: opacity-only fade-ins
on scroll, ~400ms; honor `prefers-reduced-motion`.

## Sections

`includedSections`:
`["navigation", "hero", "project-gallery", "lookbook", "studio-services", "awards", "studio-statement", "content-listing", "cta", "footer"]`

Inherited from Foundation: `navigation`, `hero`, `content-listing`, `cta`,
`footer` (customised views where the editorial type demands it). NEW sections:

- **project-gallery** (NEW) — grid/asymmetric showcase of built projects.
  Render data:
  `{ heading: string, intro: string, projects: [{ title, location, year, discipline, imageAlt }] }`
- **lookbook** (NEW) — full-bleed scrolling image sequence.
  Render data:
  `{ heading: string, images: [{ caption, imageAlt }] }`
- **studio-services** (NEW) — what the studio offers.
  Render data:
  `{ heading: string, services: [{ name, description }] }`
- **awards** (NEW) — press and award recognition.
  Render data:
  `{ heading: string, awards: [{ name, year, project }] }`
- **studio-statement** (NEW) — manifesto/voice block.
  Render data:
  `{ heading: string, body: string }`

Each NEW key registers a
`ViewSectionRenderer('design-studio', '<key>', 'capell-theme-design-studio::sections.<key>', failLoudly: true)`.

## Beta data (demo profile)

Seed via `ThemeDemoPageInstaller::profile()` for key `design-studio`. All copy
original.

- **Brand:** `Atelier Norð` — an interior and hospitality design studio with rooms
  in Copenhagen and New York.
- **summary:** `A spatial design studio working across hospitality, retail, and private residences. We design rooms that feel inevitable.`
- **heroHeading:** `Spaces composed with restraint.`
- **heroSummary:** `Atelier Norð designs hotels, restaurants, and homes where material, light, and proportion do the talking. Quiet rooms, made to last.`

**features[] (4 — studio disciplines, type = discipline):**

1. `Interior Architecture` — `Full spatial design from plan to specification, working alongside the building's structure rather than against it.` (type: interior)
2. `Hospitality Design` — `Hotels, restaurants, and bars designed as complete guest journeys — from arrival sequence to the weight of the door handle.` (type: hospitality)
3. `Brand & Identity` — `Visual and spatial identity systems that carry a place's character from the wall to the menu to the website.` (type: brand)
4. `Material Direction` — `Curated palettes of stone, timber, plaster, and textile, sourced from makers we have worked with for years.` (type: material)

**proof[] (3 metrics with quotes):**

1. metric `18 yrs` · name `Practice` · quote `Eighteen years of built work across three continents, and a client list that mostly comes by referral.`
2. metric `42` · name `Completed projects` · quote `Forty-two finished rooms — hotels, restaurants, and homes we still visit when we are in town.`
3. metric `9` · name `Industry awards` · quote `Recognition from Dezeen and AD, though the work we are proudest of rarely makes the lists.`

**studio-statement:** heading `On restraint` · body:
`We believe a room is finished when there is nothing left to remove. We work slowly, specify carefully, and design for the way light moves through a day. Our best rooms are the ones guests cannot quite explain — only that they did not want to leave.`

**project-gallery (3+ projects):**

1. `Hôtel Brun` · Copenhagen · 2024 · Hospitality
2. `Restaurant Sel` · New York · 2023 · Hospitality
3. `Villa Aubin` · Provence · 2022 · Private Residence
4. `Maison Lind` · Copenhagen · 2021 · Private Residence

**lookbook images (captions, full-bleed):**

- `Hôtel Brun — limewashed lobby in afternoon light`
- `Restaurant Sel — oak banquette and brushed-brass rail`
- `Villa Aubin — travertine bath under a single skylight`

**studio-services:**

- `Concept & Spatial Design` — `Plans, sections, and material studies that set the direction before a single wall moves.`
- `Documentation & Specification` — `Detailed drawings and finish schedules your contractor can build from without guessing.`
- `Furniture & Art Direction` — `Bespoke pieces and curated objects, commissioned from makers and placed by hand.`

**awards:**

- `Dezeen Awards — Interior of the Year (shortlist)` · 2024 · Hôtel Brun
- `AD100 — Studio to Watch` · 2023 · Atelier Norð
- `Restaurant & Bar Design Award` · 2023 · Restaurant Sel

**pathways[] (3 — for content-listing pathways variant):**

1. `Work` — `Browse the full portfolio by discipline and city.`
2. `Studio` — `Who we are, how we work, and the people behind the practice.`
3. `Enquire` — `Tell us about your space and timeline; we take on a handful of projects a year.`

**Directory sample entries (3+ — for the directory demo page):**

1. `Hôtel Brun` · Copenhagen · Hospitality · 2024 — `A 38-room boutique hotel in a converted 1890s merchant house, with a ground-floor wine bar.`
2. `Restaurant Sel` · New York · Hospitality · 2023 — `A seafood restaurant in Tribeca built around a single ten-metre oak counter.`
3. `Villa Aubin` · Provence · Private Residence · 2022 — `A stone farmhouse renovation prioritising cross-ventilation and unglazed terracotta.`

**Detail sample (for the detail demo page):** `Hôtel Brun` — heading
`Hôtel Brun, Copenhagen`, body covering the brief (a tired merchant house),
the move (limewash, oak, brass, restraint), and the outcome (38 rooms, a wine bar,
a Dezeen shortlist). Include `location: Copenhagen`, `year: 2024`,
`discipline: Hospitality`.

**ctaHeading:** `Have a space in mind?` · **ctaSummary:**
`We take on a small number of projects each year. Tell us about yours.`

## Build steps

1. Scaffold `packages/theme-design-studio/` per shared-contract §2. Copy
   `packages/theme-agency` as the closest editorial base, then strip its
   expressive motion.
2. Write `capell.json` (manifest v3, §3): identity from the table above,
   `extends: "capell-app/foundation-theme"`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-design-studio-demo"`,
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-design-studio", "theme-design-studio-frontend"]`,
   `healthChecks: ["design-studio.package-health"]`, all `database` false,
   `security.publicOutput` flags true / `riskTier: "low"`.
3. `DesignStudioThemeServiceProvider`: `register()` empty (manifest registers the
   package); `boot(ThemeRegistry $registry)` per §4 — register demo command,
   gate on `CapellCore::isPackageInstalled()`, `loadTranslationsFrom` +
   `loadViewsFrom`, register CSS via `VendorAssetData::tailwindImport` and Blade
   sources via `tailwindSource`, register the page adapter, then
   `$registry->register(self::definition(), new BladeThemeRenderer(...), $sectionRenderers)`.
4. `definition(): ThemeDefinitionData` with the preset table values under one
   `ThemePresetData` (`key: 'atelier'`), the full `includedSections`, tags,
   bestFit, `assets`, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
5. One `ViewSectionRenderer` per customised + NEW section key.
6. `DesignStudioThemePageAdapter` (§5): map `meta.theme_demo.render_data` to
   `HeroSectionData`, `FeatureSectionData`, `ProofSectionData`,
   `ContentListingSectionData`, `CtaSectionData`, plus the four NEW section Data
   shapes (project-gallery, lookbook, studio-services, awards, studio-statement).
   Provide empty-state fallbacks.
7. `InstallDesignStudioThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'design-studio', 'DesignStudio')`; wire
   `DemoCommand` (`capell:theme-design-studio-demo`); add the `design-studio`
   profile entry (§6) with all copy above.
8. `resources/views/page.blade.php`: skip link, `data-capell-theme`, brand tokens
   as inline CSS vars, `{!! $content !!}`,
   `@frontendAsset('css/theme-design-studio.css')`. Thin
   `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`.
9. `resources/css/theme-design-studio.css`: editorial grid, Fraunces display
   scale, framed media, terracotta accent rules — no authoring markers.
10. `resources/lang/en/generic.php` for every user-facing string via
    `__('capell-theme-design-studio::...')`.
11. `DesignStudioThemeHealthCheck` registered as `design-studio.package-health`.
12. Add both PSR-4 entries to `composer.json` AND `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (shared-contract §10): `ManifestRequirementsTest`,
`DesignStudioThemeDefinitionTest`, `DesignStudioThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`, `DesignStudioThemeHealthCheckTest`.
Plus theme-specific:

- Definition `includedSections` contains `project-gallery`, `lookbook`,
  `studio-services`, `awards`, and `studio-statement`.
- The adapter builds a project-gallery section with at least three projects each
  carrying `location`, `year`, and `discipline`.
- The awards section renders the Dezeen/AD entries with `year` and `project`.
- `PublicOutputSafetyTest` confirms rendered gallery/lookbook Blade contains no
  package name, `wire:`, `data-field`, `signed`, or DB query calls.

## Verification

```bash
vendor/bin/pest packages/theme-design-studio/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
