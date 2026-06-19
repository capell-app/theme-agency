# Theme: ProductStudio (`theme-product-studio`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
skeleton, manifest, provider wiring, safety rules, and acceptance baseline. This
file only specifies what makes ProductStudio distinct.

## Build this

An engineering-led renderer for a digital-product / web studio that sells on
credibility: a visible tech stack, measurable case-study outcomes, a clear
delivery process, and named engagement models. It sits between `agency` (brand
and creative) and `saas` (single product) — a studio that ships other people's
products and wants prospects to trust its competence. Extends `default` at
runtime and `capell-app/foundation-theme` at the package level.

## Inspiration

Borrow from Bejamas and Drewl. From Bejamas: the explicit stack badges, the
"how we work" process strip, and the outcome-first case-study cards (a metric,
then the story). From Drewl: the structured, slightly technical layout and the
confident-but-plain copy voice. Borrow the structure and tone only — invent all
demo copy, client names, and numbers.

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) covers creative agencies and single SaaS products
but nothing for a _services_ studio whose product is engineering delivery.
`agency` leads with vibe; `saas` assumes one product with pricing tiers.
ProductStudio leads with stack, process, outcomes, and engagement models — the
language a CTO evaluating a build partner expects.

## Package identity

| Field          | Value                                                                                        |
| -------------- | -------------------------------------------------------------------------------------------- |
| package        | `capell-app/theme-product-studio`                                                            |
| slug           | `theme-product-studio`                                                                       |
| namespace      | `Capell\ThemeStudio\ProductStudio`                                                           |
| themeKey       | `product-studio`                                                                             |
| displayName    | `Product Studio`                                                                             |
| tier           | `premium`                                                                                    |
| bestFit        | `["Web & product studios", "Engineering agencies", "Dev shops", "Fractional product teams"]` |
| tags           | `["Engineering", "Structured", "Case studies", "Technical", "B2B"]`                          |
| view namespace | `capell-theme-product-studio`                                                                |

## Design direction

| Token              | Value         | Rationale                                              |
| ------------------ | ------------- | ------------------------------------------------------ |
| primaryColor       | `#2563eb`     | Cobalt — trustworthy, technical, not playful.          |
| accentColor        | `#14b8a6`     | Teal accent for metrics and stack highlights.          |
| neutralColor       | `#0f172a`     | Slate ink for structure and code-like detail.          |
| surfaceColor       | `#ffffff`     | Clean white keeps focus on data and outcomes.          |
| foregroundColor    | `#0f172a`     | High-contrast slate text for dense technical copy.     |
| headingFont        | `sora`        | Geometric, slightly engineered display face.           |
| bodyFont           | `inter`       | Neutral body; pair with a mono face for stack/metrics. |
| spacing            | `balanced`    | Information-dense without feeling cramped.             |
| alignment          | `left`        | Left-aligned reads as documentation, not marketing.    |
| cardStyle          | `bordered`    | Hairline borders give a precise, spec-sheet feel.      |
| navigationStyle    | `prominent`   | Clear nav with a visible "Start a project" action.     |
| layoutPresentation | `structured`  | Grid discipline signals engineering rigour.            |
| motionIntensity    | `subtle`      | Restrained transitions; nothing distracts from data.   |
| mediaTreatment     | `flat`        | Flat screenshots/diagrams, no decorative framing.      |
| radius             | `md`          | Soft-but-square corners — modern SaaS-adjacent.        |
| headingScale       | `balanced`    | Confident headings that don't overwhelm the metrics.   |
| cardDensity        | `comfortable` | Room for stack chips and outcome numbers per card.     |

Typography: Sora for headings; Inter for body; a monospace (e.g. system mono) for
stack chips, metric deltas, and inline code-like spans. Motion: subtle reveal and
hover-lift on case cards (~200ms); honor `prefers-reduced-motion`.

## Sections

`includedSections`:
`["navigation", "hero", "tech-stack", "case-studies", "process", "engagement-models", "features", "proof", "content-listing", "cta", "footer"]`

Inherited from Foundation: `navigation`, `hero`, `features`, `proof`,
`content-listing`, `cta`, `footer`. NEW sections:

- **tech-stack** (NEW) — the tools the studio builds with, grouped.
  Render data:
  `{ heading: string, intro: string, tech: [{ name, category }] }`
- **case-studies** (NEW) — outcome-first project cards.
  Render data:
  `{ heading: string, cases: [{ client, outcome, summary, stack: string[] }] }`
- **process** (NEW) — the delivery method, step by step.
  Render data:
  `{ heading: string, steps: [{ name, detail }] }`
- **engagement-models** (NEW) — how a client can hire the studio.
  Render data:
  `{ heading: string, models: [{ name, description }] }`

Each NEW key registers a
`ViewSectionRenderer('product-studio', '<key>', 'capell-theme-product-studio::sections.<key>', failLoudly: true)`.

## Beta data (demo profile)

Seed via `ThemeDemoPageInstaller::profile()` for key `product-studio`. All copy
original.

- **Brand:** `Northbound Studio` — a product and web engineering studio.
- **summary:** `A small senior team that designs, builds, and ships web products. We measure our work in shipped features and shaved milliseconds.`
- **heroHeading:** `We build web products that survive contact with users.`
- **heroSummary:** `Northbound is a senior product studio. We take ideas from a Figma file to a deployed, observable, fast product — and we leave your team able to maintain it.`

**features[] (4 — capabilities, type = capability):**

1. `Product Engineering` — `End-to-end builds in Next.js, Laravel, and Capell, with CI, tests, and observability wired in from day one.` (type: build)
2. `Performance Engineering` — `Core Web Vitals work that turns slow, janky pages into sub-second loads users actually feel.` (type: performance)
3. `Design Systems` — `Component libraries and design tokens that keep a product consistent as the team and surface area grow.` (type: design)
4. `Platform & DevOps` — `Pipelines, preview environments, and infrastructure-as-code so shipping is boring and safe.` (type: platform)

**proof[] (3 metrics with quotes):**

1. metric `0.6s` · name `Median LCP` · quote `Across the products we shipped last year, median Largest Contentful Paint landed at 0.6 seconds.`
2. metric `100%` · name `Test coverage gate` · quote `Every repository we hand back enforces a coverage gate in CI — no exceptions, no flaky merges.`
3. metric `11 days` · name `Idea to first deploy` · quote `Our average from kickoff to a deployed, password-protected preview was eleven working days.`

**tech-stack (grouped):**

- `Next.js` (Frontend), `TypeScript` (Frontend), `Tailwind CSS` (Frontend)
- `Laravel` (Backend), `Capell` (CMS), `PostgreSQL` (Data)
- `Playwright` (Testing), `Pest` (Testing)
- `GitHub Actions` (Platform), `Terraform` (Platform), `Grafana` (Observability)

**case-studies (with metrics — outcome first):**

1. client `Meridian Freight` · outcome `LCP 2.4s → 0.6s` · summary `Rebuilt a logistics dashboard's render path and data layer; the slowest screen now loads four times faster.` · stack `["Next.js", "Laravel", "PostgreSQL"]`
2. client `Lumen Health` · outcome `+38% conversion` · summary `Redesigned and re-engineered a patient onboarding flow; completed sign-ups rose by more than a third.` · stack `["Next.js", "TypeScript", "Tailwind CSS"]`
3. client `Atlas Books` · outcome `−72% build time` · summary `Migrated a publishing CMS to Capell and a Terraform pipeline; deploys dropped from 14 minutes to under 4.` · stack `["Capell", "Laravel", "Terraform"]`

**process (discovery → ship):**

1. `Discovery` — `A short, paid engagement to map the problem, risks, and the smallest valuable first release.`
2. `Design` — `Interface design and a component system, validated against the real data and edge cases.`
3. `Build` — `Senior engineers ship in weekly increments behind a preview URL you can click any time.`
4. `Ship` — `Production launch with observability, runbooks, and a handover your team can actually own.`

**engagement-models:**

- `Project` — `Fixed scope, fixed timeline, fixed price. Best when the brief is clear and the deadline is real.`
- `Retainer` — `A dedicated senior pod for a monthly fee, for ongoing product work and a roadmap that keeps moving.`
- `Staff Augmentation` — `One or two of our engineers embedded in your team, working your tickets in your repo.`

**pathways[] (3 — content-listing pathways variant):**

1. `Work` — `Read the full case studies, with the numbers and the trade-offs.`
2. `How we work` — `Our delivery process, from discovery to handover.`
3. `Start a project` — `Tell us the problem and the deadline; we'll tell you honestly if we're the right team.`

**Directory sample entries (3+ — directory demo page):**

1. `Meridian Freight Dashboard` · Performance rebuild · 2024 — `Render-path and query work on a logistics dashboard, cutting the slowest screen from 2.4s to 0.6s.`
2. `Lumen Health Onboarding` · Product & design · 2024 — `A redesigned patient onboarding flow that lifted completed sign-ups by 38%.`
3. `Atlas Books Platform` · Platform migration · 2023 — `A Capell migration and Terraform pipeline that cut deploys from 14 minutes to under 4.`

**Detail sample (detail demo page):** `Meridian Freight Dashboard` — heading
`Meridian Freight: a dashboard that stopped being slow`, body covering the
problem (a 2.4s LCP and frustrated dispatchers), the approach (data layer rewrite,
streamed rendering, a coverage gate), and the result (`LCP 2.4s → 0.6s`, zero
regressions in six months). Include `client: Meridian Freight`,
`stack: ["Next.js", "Laravel", "PostgreSQL"]`, `outcome: LCP 2.4s → 0.6s`.

**ctaHeading:** `Have a product to ship?` · **ctaSummary:**
`Tell us the problem and the deadline. We'll come back with a plan, not a pitch.`

## Build steps

1. Scaffold `packages/theme-product-studio/` per shared-contract §2. Copy
   `packages/theme-saas` as the closest structured/B2B base.
2. Write `capell.json` (manifest v3, §3): identity from the table,
   `extends: "capell-app/foundation-theme"`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-product-studio-demo"`,
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-product-studio", "theme-product-studio-frontend"]`,
   `healthChecks: ["product-studio.package-health"]`, `database` all false,
   `security.publicOutput` flags true / `riskTier: "low"`.
3. `ProductStudioThemeServiceProvider`: `register()` empty; `boot(ThemeRegistry)`
   per §4 — demo command, install gate, translations + views, CSS via
   `VendorAssetData::tailwindImport`, Blade sources via `tailwindSource`, page
   adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the preset table under one
   `ThemePresetData` (`key: 'studio'`), full `includedSections`, tags, bestFit,
   assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
5. One `ViewSectionRenderer` per customised + NEW section key.
6. `ProductStudioThemePageAdapter` (§5): map render data to `HeroSectionData`,
   `FeatureSectionData`, `ProofSectionData`, `ContentListingSectionData`,
   `CtaSectionData`, plus the four NEW Data shapes (tech-stack, case-studies with
   `stack[]`, process, engagement-models). Empty-state fallbacks.
7. `InstallProductStudioThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'product-studio', 'ProductStudio')`; wire
   `DemoCommand` (`capell:theme-product-studio-demo`); add the `product-studio`
   profile entry (§6) with all copy above.
8. `resources/views/page.blade.php`: skip link, `data-capell-theme`, brand tokens
   inline, `{!! $content !!}`, `@frontendAsset('css/theme-product-studio.css')`.
   Thin `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`.
9. `resources/css/theme-product-studio.css`: structured grid, mono stack chips,
   teal metric accents, hairline card borders — no authoring markers.
10. `resources/lang/en/generic.php` for every user-facing string.
11. `ProductStudioThemeHealthCheck` registered as `product-studio.package-health`.
12. Add both PSR-4 entries to `composer.json` AND `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (shared-contract §10): `ManifestRequirementsTest`,
`ProductStudioThemeDefinitionTest`, `ProductStudioThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`, `ProductStudioThemeHealthCheckTest`.
Plus theme-specific:

- Definition `includedSections` contains `tech-stack`, `case-studies`,
  `process`, and `engagement-models`.
- The adapter builds case-study cards each carrying an `outcome` string and a
  non-empty `stack` array.
- The tech-stack section groups entries by `category`.
- `PublicOutputSafetyTest` confirms the rendered case-studies and tech-stack
  Blade contain no package name, `wire:`, `signed`, `data-model`, or DB queries —
  including the mono stack chips.

## Verification

```bash
vendor/bin/pest packages/theme-product-studio/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
