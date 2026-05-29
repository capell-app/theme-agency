# Theme Premium Improvement Runbook

Use this runbook when continuing the first-party Capell theme improvement work. It captures the product goal, the operating rules, and the repeatable QA loop so future theme work can resume without relying on chat history.

## Goal

Raise the first-party child themes from "working demo themes" to product-quality themes that feel worth paying for.

Ben's intent for this work:

- Review every theme through its route-backed screenshots, not only code.
- Fix concrete visual issues such as invisible backgrounds, weak contrast, bland cards, poor spacing, and sections that read as Foundation with a different palette.
- Make each theme more inviting, richer, and more premium while preserving Capell's public rendering boundaries.
- Add stronger layouts, richer section treatments, and better interactive presentation where the package already has the right runtime surface.
- Keep the work moving theme by theme until the full set has been checked, improved, tested, and frozen.

When Ben refers to the "10 premium themes" in this context, treat that as the full first-party child-theme set:

- `agency`
- `commerce`
- `corporate`
- `education`
- `healthcare`
- `knowledge`
- `local-services`
- `nonprofit`
- `portfolio`
- `saas`

## Working Principles

Work one theme at a time. Do not batch-edit multiple themes before validating the current one.

Make theme-local changes first:

- section Blade
- section renderer classes
- theme translations
- package demo action data
- package-local tests
- screenshot fixtures

Only widen to Foundation, Layout Builder, or core Theme Studio when the same defect is proven in more than one theme.

Keep public output clean:

- no database queries in public Blade
- no relationship lazy-loading in public Blade
- no admin, Filament, Livewire authoring controls, signed URLs, field paths, permissions, model IDs, schema labels, package internals, or editor metadata in public HTML
- no hardcoded designed page markup stored in demo content fields
- optional integrations must degrade into intentional empty states, not broken-looking sections

Use Capell data for anything that varies by site, page, widget, block, locale, or editor decision. Use theme code for presentation shape only.

## Current Source Files

Start from these docs:

- [Theme Screenshot QA Playbook](theme-screenshot-qa-playbook.md)
- [Capell Theme Scale](theme-scale.md)
- [Creating A Capell Theme](creating-a-theme.md)
- [Package Screenshot Automation](package-screenshot-automation.md)

The route-backed screenshot helper lives at:

- `tests/Packages/Support/ThemeDemoLayoutScreenshots.php`

Theme package docs and screenshot manifests live under:

- `packages/theme-<theme>/docs/`

## Execution Order

Use this fixed order unless Ben explicitly changes priority:

1. `commerce`
2. `healthcare`
3. `saas`
4. `portfolio`
5. `education`
6. `nonprofit`
7. `local-services`
8. `knowledge`
9. `corporate`
10. `agency`

A theme is frozen when:

- its package test suite is green
- its route-backed screenshot suite is green
- the regenerated screenshots have been visually reviewed
- missing package-local safety or command tests have been added
- no unresolved visual issues remain from the review pass
- the theme still respects public-output safety boundaries

## Per-Theme Loop

For each theme:

1. Check the current dirty state for that theme package and screenshot folder.
2. Run the package suite.
3. Run the route-backed screenshot suite.
4. Review every screenshot surface.
5. Identify the weakest sections and categorize issues.
6. Make focused theme-local improvements.
7. Add or strengthen package-local tests.
8. Run Pint if PHP files changed.
9. Rerun the package suite.
10. Rerun the screenshot suite.
11. Inspect the regenerated screenshots.
12. Run `git diff --check` for touched paths.
13. Record what changed and pause at a clean stopping point when asked.

Use the smallest useful verification first. Broaden only when the change touches shared runtime contracts.

## Commands

Run package tests for the active theme:

```bash
./vendor/bin/pest packages/theme-<theme>/tests --configuration=phpunit.xml
```

Run the route-backed screenshot test:

```bash
./vendor/bin/pest tests/Packages/Feature/ThemeDemoLayouts/<Theme>ThemeDemoLayoutScreenshotTest.php --configuration=phpunit.xml
```

Run Pint after PHP changes:

```bash
./vendor/bin/pint --format agent <changed-php-files>
```

Check for whitespace and patch issues:

```bash
git diff --check -- packages/theme-<theme> tests/Packages/Fixtures/theme-demo-layout-screenshots/<theme>
```

Inspect touched state:

```bash
git status --short -- packages/theme-<theme> tests/Packages/Fixtures/theme-demo-layout-screenshots/<theme>
git diff --stat -- packages/theme-<theme> tests/Packages/Fixtures/theme-demo-layout-screenshots/<theme>
```

## Screenshot Surfaces

Review these for every theme:

- `homepage`
- `directory`
- `detail`
- `contact`
- `empty`
- `not-found`
- `maintenance`
- `system`
- `cta`
- `visual-review`
- `system-review`

Review extra surfaces when a theme has them:

- `commerce`: `blog-index`, `blog-article`
- `saas`: `blog-index`, `blog-article`
- `healthcare`: `blog-index`, `blog-article`, `healthcare-contact-section`

A green screenshot test is not enough. It proves capture, expected text, image loading, and fixture shape. It does not prove the design is good.

## Visual Quality Bar

Fix issues in these categories:

- weak or invisible background treatment
- low contrast in cards, proof sections, CTA bands, navigation, or footer
- mobile clipping, overlap, unstable spacing, or text that does not fit
- repeated-card monotony
- cards that lack hierarchy, media treatment, metadata, or scanning cues
- weak hero, proof, CTA, empty, not-found, maintenance, or system states
- optional-package fallback states that look like missing data instead of designed empty states
- sections that visually collapse toward Foundation or a sibling theme

Premium does not mean more static content. It means better defaults, richer configurable structures, stronger screenshots, and stronger coverage.

## Theme Differentiation Standard

Treat visual similarity as a defect, even when each theme passes tests. A theme should be recognizable from a full-page screenshot without reading the theme name.

Every theme pass must check these identity levers:

- section rhythm: editorial, dashboard-like, campaign-led, directory-led, search-led, or conversion-led
- card anatomy: what appears inside cards, not only border radius and colors
- media strategy: photography, abstract product panels, publication cards, headshots, maps, work samples, or impact visuals
- proof style: metrics, logos, testimonials, clinical trust, retail signals, case-study outcomes, campaign impact, or operational reliability
- CTA language and layout: theme-specific action paths rather than generic button groups
- empty/system states: designed for the theme domain, not reused Foundation recovery panels
- footer and navigation: reflect the theme's information architecture and user intent

Do not solve similarity with palette changes alone. If two themes share the same hero split, repeated cards, proof grid, CTA band, and footer structure, one of them needs a structural pass.

Use this quick distinction matrix while reviewing screenshots:

- `commerce`: merchandising-first, product dense, stock/checkout/catalog cues, dark retail proof panels.
- `healthcare`: trust-first, appointment and service paths, calm clinical hierarchy, accessible contact routes.
- `saas`: product-led, feature comparison, calculator/trial proof, documentation and integration cues.
- `portfolio`: work-led, editorial case-study rhythm, creator credentials, media-kit and newsletter surfaces.
- `education`: course-led, instructor/event/resource scanning, enrolment and cohort signals.
- `nonprofit`: campaign-led, impact numbers, donation/volunteer paths, stories and events.
- `local-services`: quote-led, service-area and locality cues, job photos, urgency and availability signals.
- `knowledge`: search-led, hub/resource density, tags, reading paths, saved-resource style affordances.
- `corporate`: boardroom restraint, enterprise proof, governance/operations signals, clean report-like rhythm.
- `agency`: expressive studio energy, case-study outcomes, process, client logos, bolder visual movement than Corporate.

## Theme Targets

`commerce`: retail-specific rhythm, dense but readable product cards, strong catalog and checkout-adjacent cues.

`healthcare`: high-trust clinical presentation, clear service/contact paths, calm operational states.

`saas`: product-grade comparison, calculator, proof, and blog surfaces.

`portfolio`: editorial creator or consultant feel, richer work and case-study treatment, stronger media-kit and newsletter sections.

`education`: course and instructor scanning, event/resource clarity, enrolment flow.

`nonprofit`: campaign, impact, volunteer, donate, story, and event hierarchy.

`local-services`: quote-led conversion, locality cues, service-area clarity.

`knowledge`: search-led and hub-led discovery, readable resource density.

`corporate`: restrained high-trust enterprise presentation.

`agency`: more expressive than Corporate while staying clean and production-safe.

## Hardening Backlog

Add or confirm `PublicOutputSafetyTest` coverage for:

- `commerce`
- `healthcare`
- `saas`
- `corporate`
- `agency`

Add or confirm `DemoCommandTest` coverage for:

- `portfolio`
- `education`
- `nonprofit`
- `local-services`
- `knowledge`

Keep existing package-aware renderer and public-safety patterns green for:

- `portfolio`
- `education`
- `nonprofit`
- `local-services`
- `knowledge`

## Improvement Patterns That Fit Capell

Prefer these upgrades when screenshots feel flat:

- Make standard `features` views theme-owned when Foundation cards weaken the theme identity.
- Add stronger card internals: eyebrow labels, proof signals, metadata rows, image wells, stats, chips, and clear CTA affordances.
- Use optional package availability to render intentional fallbacks.
- Improve empty, not-found, maintenance, and system pages so they feel like part of the theme.
- Make CTA sections more domain-specific rather than generic "start now" blocks.
- Use CSS and Blade structure already available in the package before adding new dependencies.

Be careful with JavaScript libraries. Add a dependency only when the theme runtime truly needs client-side behavior that cannot be achieved with the existing frontend stack. Do not add a carousel or gallery library just for decoration. If a carousel, gallery, tabs, or filtering interaction is added, it needs keyboard, mobile, no-JS, and screenshot-safe behavior.

## Completion Gate

The full theme improvement objective is complete only when:

- all 10 theme package suites pass
- all 10 theme screenshot suites pass
- `FoundationThemeDemoLayoutScreenshotTest` passes
- every theme has had its screenshots reviewed after the latest changes
- every theme has theme-appropriate visual distinction from Foundation and from sibling themes
- final diffs are scoped to theme packages, screenshot fixtures, and intentional supporting docs/tests

Do not claim the whole theme programme is done after one theme slice. Report the frozen theme, verification, unresolved risk, and the next theme in the sequence.
