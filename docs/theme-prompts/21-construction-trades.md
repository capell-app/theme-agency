# Theme: ConstructionTrades (`theme-construction-trades`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
skeleton, manifest, provider wiring, safety rules, and acceptance baseline. This
file only specifies what makes ConstructionTrades distinct.

## Build this

A rugged, proof-led renderer for builders and contractors — a heavier, more
industrial niche of `local-services`. Slate and safety-amber, compact dense
cards, framed site photography. Sections are built around what a homeowner or
commercial client vets before hiring a builder: a project portfolio with build
values, the services offered, trade accreditations (CHAS / Gas Safe / NICEIC /
FMB), a clear build process, the service areas covered, and a quote request.
Extends `default` at runtime and `capell-app/foundation-theme` at the package
level.

## Inspiration

Borrow from builder and main-contractor websites: the big project gallery with
build types and values, the wall of trade accreditation badges, the "how we work"
survey-to-handover process, and the prominent "request a quote" path. Take the
structure and confident, no-nonsense tone only — invent the company, projects,
values, and guarantee.

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) has `local-services` for general quote-led trades,
but it is light and generic. ConstructionTrades is the heavy-build specialisation:
project values, structural guarantees, construction-specific accreditations, and a
portfolio of physical builds — the credibility signals a builder needs that a
generic services theme doesn't carry.

## Package identity

| Field          | Value                                                                                                |
| -------------- | ---------------------------------------------------------------------------------------------------- |
| package        | `capell-app/theme-construction-trades`                                                               |
| slug           | `theme-construction-trades`                                                                          |
| namespace      | `Capell\ThemeStudio\ConstructionTrades`                                                              |
| themeKey       | `construction-trades`                                                                                |
| displayName    | `Construction Trades`                                                                                |
| tier           | `premium`                                                                                            |
| bestFit        | `["Builders & contractors", "Construction firms", "Extensions & renovations", "Commercial fit-out"]` |
| tags           | `["Construction", "Trades", "Rugged", "Quote-led", "Slate & amber"]`                                 |
| view namespace | `capell-theme-construction-trades`                                                                   |

## Design direction

| Token              | Value        | Rationale                                             |
| ------------------ | ------------ | ----------------------------------------------------- |
| primaryColor       | `#334155`    | Slate — solid, industrial, dependable.                |
| accentColor        | `#f59e0b`    | Safety amber for CTAs, values, and warnings.          |
| neutralColor       | `#1c1917`    | Near-black for heavy chrome and dense text.           |
| surfaceColor       | `#f4f4f5`    | Cool concrete grey, workmanlike not precious.         |
| foregroundColor    | `#1c1917`    | Near-black text reads strong on concrete surface.     |
| headingFont        | `sora`       | Sturdy geometric face — engineered, confident.        |
| bodyFont           | `inter`      | Neutral body for spec lists and process steps.        |
| spacing            | `compact`    | Dense, efficient layout — gets to the proof fast.     |
| alignment          | `left`       | Left-aligned reads as a job sheet, not a brochure.    |
| cardStyle          | `elevated`   | Solid shadowed cards feel weighty and tactile.        |
| navigationStyle    | `prominent`  | Nav carries a standing "Request a quote" / call.      |
| layoutPresentation | `structured` | Grid discipline signals organised site management.    |
| motionIntensity    | `subtle`     | A little hover lift; nothing fussy.                   |
| mediaTreatment     | `framed`     | Framed site/build photography reads as documentation. |
| radius             | `sm`         | Minimal rounding — close to square, hard-edged.       |
| headingScale       | `balanced`   | Strong headings, but the proof carries the page.      |
| cardDensity        | `compact`    | Tight cards pack portfolio, services, and areas in.   |

Typography: Sora headings, Inter body; amber accent for the quote button, build
values, and the guarantee. Motion: subtle hover lift on portfolio cards (~200ms);
honour `prefers-reduced-motion`.

## Sections

`includedSections`:
`["navigation", "hero", "project-portfolio", "services", "accreditations", "process", "service-areas", "quote-cta", "content-listing", "footer"]`

Inherited from Foundation: `navigation`, `hero`, `content-listing`, `footer`. NEW
sections:

- **project-portfolio** (NEW) — physical builds completed.
  Render data:
  `{ heading: string, intro: string, builds: [{ type, location, value, imageAlt }] }`
- **services** (NEW) — what the company builds.
  Render data:
  `{ heading: string, services: [{ name, description }] }`
- **accreditations** (NEW) — trade bodies and certifications.
  Render data:
  `{ heading: string, intro: string, accreds: [{ name }] }`
- **process** (NEW) — survey to handover.
  Render data:
  `{ heading: string, steps: [{ name, detail }] }`
- **service-areas** (NEW) — geographic coverage.
  Render data:
  `{ heading: string, areas: [{ name }] }`
- **quote-cta** (NEW) — request-a-quote call to action.
  Render data:
  `{ heading: string, body: string, buttonLabel: string, phoneLabel: string }`
  The quote path is STATIC by default (no live form binding, no signed URL,
  no `wire:`).

Each NEW key registers a
`ViewSectionRenderer('construction-trades', '<key>', 'capell-theme-construction-trades::sections.<key>', failLoudly: true)`.

## Beta data (demo profile)

Seed via `ThemeDemoPageInstaller::profile()` for key `construction-trades`. All
copy original. The company and projects are fictional.

- **Brand:** `Granite Build Co` — a family-run building and renovation contractor.
- **summary:** `A family-run building firm handling new builds, extensions, and commercial fit-outs across the South West. Twenty-five years on the tools, fully accredited, and backed by a structural guarantee.`
- **heroHeading:** `Built right, backed for 25 years.`
- **heroSummary:** `Granite Build Co takes on new builds, extensions, and commercial fit-outs from first survey to final handover. Fully accredited, directly employed teams, and a 25-year structural guarantee in writing.`

**features[] (4 — why hire them, type = reason):**

1. `Directly employed teams` — `Our own bricklayers, joiners, and site managers — not a chain of subcontractors you never meet.` (type: reason)
2. `Fixed, itemised quotes` — `A written, line-by-line quote after survey. The price we agree is the price you pay, barring changes you sign off.` (type: reason)
3. `25-year structural guarantee` — `Every structural build is backed by a 25-year guarantee, registered and insurance-backed.` (type: reason)
4. `One site manager, start to finish` — `The same site manager runs your job from groundwork to handover, so nothing gets lost in a handover.` (type: reason)

**project-portfolio (builds with types, locations, values):**

1. type `Detached new build` · location `Exeter, Devon` · value `£480,000` · `A four-bedroom timber-frame home, watertight in eleven weeks.`
2. type `Two-storey extension` · location `Taunton, Somerset` · value `£165,000` · `A rear and side extension joining a kitchen, utility, and master suite to a 1930s semi.`
3. type `Commercial fit-out` · location `Bristol` · value `£320,000` · `A full strip-out and fit-out of a 600m² office floor, handed over on schedule.`
4. type `Barn conversion` · location `Wells, Somerset` · value `£295,000` · `A listed stone barn converted to a three-bedroom home with underfloor heating throughout.`

**services:**

- `New Build` — `Complete homes from groundwork to handover, timber-frame or traditional, to your drawings or ours.`
- `Extensions & Renovations` — `Single and two-storey extensions, loft conversions, and whole-house renovations.`
- `Commercial Fit-Out` — `Office, retail, and hospitality strip-outs and fit-outs, working around your trading hours.`
- `Groundworks & Structural` — `Foundations, retaining walls, drainage, and structural alterations with full sign-off.`

**accreditations (trade bodies — names only):**

- `CHAS Accredited Contractor`
- `Gas Safe Registered`
- `NICEIC Approved Contractor`
- `FMB (Federation of Master Builders) Member`
- `Constructionline Gold`
- `ISO 9001 Quality Management`

**process (survey → handover):**

1. `Survey` — `A free on-site survey to understand the job, the access, and the constraints before any numbers.`
2. `Quote` — `A written, itemised quote with a clear scope, programme, and payment schedule.`
3. `Build` — `Directly employed teams, one site manager, weekly progress updates, and a tidy site.`
4. `Handover` — `Snagging signed off with you, all certificates issued, and the 25-year guarantee registered.`

**service-areas (areas — names only):**

- `Devon` · `Somerset` · `Bristol` · `Dorset` · `Cornwall (east)`

**quote-cta:** heading `Request a quote` · body
`Tell us about your project and we'll arrange a free site survey, usually within five working days. No obligation, no hard sell.` · buttonLabel `Request a quote` · phoneLabel `Or call the office: 01392 000 000`

**proof[] (3 metrics with quotes):**

1. metric `25 yrs` · name `On the tools` · quote `Twenty-five years building across the South West, and a structural guarantee to match.`
2. metric `£42M` · name `Built since 2001` · quote `More than 42 million pounds of completed work, from single extensions to commercial floors.`
3. metric `98%` · name `On-time handover` · quote `Ninety-eight percent of our projects handed over on or before the agreed date.`

**pathways[] (3 — content-listing pathways variant):**

1. `Our work` — `Browse completed builds by type, location, and value.`
2. `How we work` — `Survey to handover, and what the guarantee covers.`
3. `Request a quote` — `Book a free site survey, usually within five working days.`

**Directory sample entries (3+ — directory demo page = the project portfolio):**

1. `Detached new build` · Exeter, Devon · £480,000 — `A four-bedroom timber-frame home, watertight in eleven weeks and handed over snag-free.`
2. `Two-storey extension` · Taunton, Somerset · £165,000 — `A rear and side extension adding a kitchen, utility, and master suite to a 1930s semi.`
3. `Commercial fit-out` · Bristol · £320,000 — `A full strip-out and fit-out of a 600m² office floor, completed around the tenant's trading hours.`

**Detail sample (detail demo page):** `Detached new build, Exeter` — heading
`Four-bedroom new build, Exeter`, body covering the brief (a self-build couple
with planning in place), the build (timber-frame, watertight in eleven weeks,
directly employed team), and the outcome (handed over snag-free with the 25-year
guarantee registered). Include `type: Detached new build`,
`location: Exeter, Devon`, `value: £480,000`, and a closing quote-cta.

**ctaHeading:** `Got a project in mind?` · **ctaSummary:**
`Request a free site survey — usually within five working days. No obligation.`

## Build steps

1. Scaffold `packages/theme-construction-trades/` per shared-contract §2. Copy
   `packages/theme-local-services` (closest quote-led base) and add the
   construction sections.
2. Write `capell.json` (manifest v3, §3): identity from the table,
   `extends: "capell-app/foundation-theme"`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-construction-trades-demo"`,
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-construction-trades", "theme-construction-trades-frontend"]`,
   `healthChecks: ["construction-trades.package-health"]`, `database` all false,
   `security.publicOutput` flags true / `riskTier: "low"`.
3. `ConstructionTradesThemeServiceProvider`: `register()` empty;
   `boot(ThemeRegistry)` per §4 — demo command, install gate, translations +
   views, CSS via `VendorAssetData::tailwindImport`, Blade sources via
   `tailwindSource`, page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the preset table under one
   `ThemePresetData` (`key: 'jobsite'`), full `includedSections`, tags, bestFit,
   assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
5. One `ViewSectionRenderer` per customised + NEW section key.
6. `ConstructionTradesThemePageAdapter` (§5): map render data to `HeroSectionData`,
   `ProofSectionData`, `FeatureSectionData`, `ContentListingSectionData`, plus the
   NEW Data shapes (project-portfolio with `value`, services, accreditations,
   process, service-areas, quote-cta). The quote-cta is STATIC — build from fixed
   copy, no live form binding. Empty-state fallbacks.
7. `InstallConstructionTradesThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'construction-trades', 'ConstructionTrades')`;
   wire `DemoCommand` (`capell:theme-construction-trades-demo`); add the
   `construction-trades` profile entry (§6) with all copy above.
8. `resources/views/page.blade.php`: skip link, `data-capell-theme`, brand tokens
   inline, `{!! $content !!}`,
   `@frontendAsset('css/theme-construction-trades.css')`. Thin
   `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`.
9. `resources/css/theme-construction-trades.css`: slate/amber palette, elevated
   compact cards, framed portfolio media, accreditation-badge grid, prominent
   quote button — no authoring markers.
10. `resources/lang/en/generic.php` for every user-facing string.
11. `ConstructionTradesThemeHealthCheck` registered as
    `construction-trades.package-health`.
12. Add both PSR-4 entries to `composer.json` AND `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (shared-contract §10): `ManifestRequirementsTest`,
`ConstructionTradesThemeDefinitionTest`, `ConstructionTradesThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`,
`ConstructionTradesThemeHealthCheckTest`. Plus theme-specific:

- Definition `includedSections` contains `project-portfolio`, `services`,
  `accreditations`, `process`, `service-areas`, and `quote-cta`.
- The project-portfolio section renders each build with a non-empty `value`,
  `type`, and `location`.
- The accreditations section renders the construction badges (e.g. CHAS, Gas Safe,
  NICEIC, FMB).
- `PublicOutputSafetyTest` confirms portfolio/quote-cta Blade has no package name,
  `wire:`, `signed`, `data-field`, or DB queries; the quote path exposes no admin
  form action.

## Verification

```bash
vendor/bin/pest packages/theme-construction-trades/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
