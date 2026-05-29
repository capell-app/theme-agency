# Theme Screenshot QA Playbook

Use this playbook when polishing first-party Capell theme packages, checking route-backed screenshots, or raising a theme from "works" to product-quality. It is intentionally operational: follow the loop, fix the theme-local issues, rerun the checks, and only widen scope when the same defect is proven across more than one theme.

## Theme Set

The first-party child themes covered by this playbook are:

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

These all extend Foundation. When Ben asks for the "10 premium themes", treat that as the full first-party child-theme screenshot quality pass, not a decision that all ten should remain paid/premium products. Every theme still needs route-backed screenshots and public-safety checks. Premium-depth polish should be reserved for themes with a distinct buyer and domain workflow.

Current direction:

- Premium focus: `commerce`, `healthcare`, `saas`, `knowledge`, `local-services`, `nonprofit`, `education`, and `portfolio`.
- Premium studio lane: `portfolio` owns the work outcome, case-file, media-kit, and audience-building workflow.
- Basic/free creative lane: keep `agency` as an expressive campaign/launch-room preset; do not duplicate Portfolio's deeper case-study workflow.
- Basic/free business lane: keep `corporate` as a polished boardroom/business preset unless a future enterprise workflow justifies premium depth.

Do not solve similarity by adding more colour variants. If a theme cannot be recognised from a full-page screenshot without reading its name, it needs structural work or should stay out of the premium lane.

## Non-Overlap Rule

Premium themes should not overlap where it is avoidable. Each premium candidate needs a clear buyer and primary workflow:

- `commerce`: merchandising and buying journeys.
- `healthcare`: service, clinician, care-pathway, and appointment journeys.
- `saas`: product, comparison, trial, and documentation journeys.
- `knowledge`: search-led resource and reading journeys.
- `local-services`: quote, locality, dispatch, and job-proof journeys.
- `nonprofit`: campaign, donation, volunteer, impact, and story journeys.
- `education`: course, cohort, instructor, event, and enrolment journeys.
- Studio lane: `portfolio` is the single premium studio/case-study theme.
- `agency`: basic creative/campaign preset, separate from Portfolio.
- `corporate`: basic business/boardroom preset unless it becomes a distinct enterprise workflow.

If two themes share the same buyer, section anatomy, proof style, listing cards, and CTA path, treat that as a merge/demotion signal. The right fix is to choose the stronger product lane, not to keep both and make another colour pass.

## Future Local-Business Premium Themes

For new local-business premium themes, prefer fewer strong verticals over many thin colour swaps. The recommended first set is:

- `theme-estate-agents`: property search, valuation CTA, viewings, local guides, and agent proof.
- `theme-equestrian`: riding lessons, clinics, camps, instructor/horse profiles, and events-calendar booking paths.
- `theme-restaurant`: menus, reservations, private dining, events, reviews, opening times, and offers.
- `theme-salon`: treatment menus, stylist profiles, availability prompts, before/after proof, reviews, and appointment requests.
- `theme-practice`: solicitors, accountants, and local advisers with practice areas, credentials, consultation intake, resources, and trust/compliance proof.

Screenshot QA for these themes must prove the vertical workflow, not only the standard page set. For example, Estate Agents needs property search and listing/detail screenshots; Equestrian needs lesson/event calendar screenshots; Restaurant needs menu and reservation/event screenshots. Livewire-powered screenshots should include default, filtered, loading/empty-equivalent, and missing optional package fallback states where feasible.

## Source Of Truth

Use the existing route-backed screenshot suites as the visual QA source:

```bash
vendor/bin/pest tests/Packages/Feature/ThemeDemoLayouts/<Theme>ThemeDemoLayoutScreenshotTest.php --configuration=phpunit.xml
```

Use the package-local suite as the safety and integration source:

```bash
vendor/bin/pest packages/theme-<theme>/tests --configuration=phpunit.xml
```

Relevant shared references:

- [Theme scale](theme-scale.md)
- [Creating a Capell theme](creating-a-theme.md)
- [Package screenshot automation](package-screenshot-automation.md)
- `tests/Packages/Support/ThemeDemoLayoutScreenshots.php`

## Fixed Execution Order

Work one theme at a time. Do not batch-edit several themes before validation. The order prioritises themes with the clearest premium buyer before conditional/basic themes.

1. `commerce`
2. `healthcare`
3. `saas`
4. `knowledge`
5. `local-services`
6. `nonprofit`
7. `education`
8. `portfolio`
9. `corporate`
10. `agency`

Freeze each theme before starting the next. A theme is frozen when its package suite is green, its screenshot suite is green, the screenshots have been reviewed, missing local tests have been added, and the review pass has no unresolved findings.

## Per-Theme Loop

For each theme:

1. Run the package suite.
2. Run the route-backed screenshot suite.
3. Review every required screenshot surface.
4. Fix theme-local Blade, renderer, translation, or demo-data issues.
5. Add missing package-local tests when coverage is weaker than the newer themes.
6. Rerun the package suite and screenshot suite.
7. Run a read-only review pass with `gpt-5.5` at low reasoning effort.
8. Resolve findings, rerun affected checks, then freeze the theme.

Review tasks must be read-only. Implementation tasks should keep edits inside the current theme package unless the same defect is proven in at least two themes.

## Screenshot Surfaces

Review these surfaces for every theme:

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

Review these extra surfaces when present:

- `commerce`: `blog-index`, `blog-article`
- `saas`: `blog-index`, `blog-article`
- `healthcare`: `blog-index`, `blog-article`, `healthcare-contact-section`

Do not rely on a green screenshot test alone. The test proves capture, expected text, image loading, and fixture shape. It does not prove the design is good.

For contact-sheet review, use the safe generator rather than hand-writing `montage` commands:

```bash
npm run screenshots:contact-sheets -- --out=/tmp/capell-theme-audit/contact-sheets
```

The generator groups committed route-backed screenshot fixtures by surface, labels each tile by theme key, and refuses to write inside `tests/Packages/Fixtures/theme-demo-layout-screenshots`. Use `--surface=homepage` or repeat `--surface` to build a smaller set.

## Visual Quality Bar

Fix issues in these categories:

- weak or invisible background treatment
- low contrast in cards, proof sections, CTA bands, navigation, or footer
- mobile clipping, overlap, or unstable spacing
- repeated-card monotony or poor scanning density
- weak hero, proof, or CTA hierarchy
- under-designed empty, not-found, maintenance, and system states
- degraded optional-package states that look broken or obviously downgraded
- sections that visually collapse toward Foundation or a sibling theme

Keep the visual language appropriate to the theme:

- `commerce`: retail-specific rhythm, dense but readable product cards, strong catalog and checkout-adjacent cues.
- `healthcare`: high-trust clinical presentation, clear service/contact paths, calm operational states.
- `saas`: product-grade comparison, calculator, proof, and blog surfaces.
- `portfolio`: premium studio/case-study treatment with work outcomes, case files, media-kit, and audience-building surfaces.
- `education`: course and instructor scanning, event/resource clarity, enrolment flow.
- `nonprofit`: clear campaign, impact, volunteer, donate, story, and event hierarchy.
- `local-services`: quote-led conversion, locality cues, service-area clarity.
- `knowledge`: search-led and hub-led discovery, readable resource density.
- `corporate`: restrained boardroom/business presentation, board-pack and governance cues, production-ready but basic/free.
- `agency`: expressive campaign and launch-room preset, visually energetic while staying clean and production-safe.

Avoid making every theme feel like a palette swap. Repeated cards, proof sections, CTAs, footers, and recovery pages should carry the theme's domain.

## Public Output Boundaries

Every theme must preserve these invariants:

- Public Blade must not query the database.
- Public Blade must not lazy-load relationships.
- Public output must not expose authoring controls, signed editor URLs, permissions, admin routes, field paths, schema labels, model IDs, package names, package internals, or editor metadata.
- Public Blade should receive hydrated render data and render presentation only.
- Theme demo content should seed editable content and configuration, not hardcoded designed page markup in content fields.
- Optional integrations must degrade cleanly when the related package is absent.

When a value changes by site, page, widget, block, locale, or editor decision, it belongs in Capell data. When it is only the HTML/CSS/component shape for rendering that data, it belongs in the theme package.

## Theme-Specific Worklist

### Commerce

Primary sections:

- `catalog`
- `collections`
- `product-grid`
- `product-finder`
- `comparison`
- `proof`
- `blog-teaser`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-commerce/tests/Unit/PublicOutputSafetyTest.php` if missing.
- Add explicit fallback assertions for Shopify and Blog behavior.

Quality target: retail-specific homepage, directory, and detail rhythm; readable product-card density; strong proof and CTA treatment; intentional no-Shopify and no-Blog states.

### Healthcare

Primary sections:

- `utility-bar`
- `service-finder`
- `services`
- `clinicians`
- `booking`
- `events`
- `comparison`
- `proof`
- `blog-teaser`
- `contact`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-healthcare/tests/Unit/PublicOutputSafetyTest.php` if missing.
- Add explicit fallback assertions for Blog, Events, and Form Builder behavior.

Quality target: high-trust service and contact flows; clear clinician and booking hierarchy; strong healthcare-specific contact surface; intentional degraded states for optional integrations.

### Saas

Primary sections:

- `features`
- `comparison`
- `calculator`
- `proof`
- `blog`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-saas/tests/Unit/PublicOutputSafetyTest.php` if missing.
- Strengthen Blog fallback assertions.

Quality target: product-grade calculator and comparison presentation; clear blog and article hierarchy; stronger SaaS identity than Foundation.

### Portfolio

Primary sections:

- `work-grid`
- `case-studies`
- `services`
- `testimonials`
- `speaking-media-kit`
- `newsletter`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-portfolio/tests/Feature/Commands/DemoCommandTest.php` if missing.

Quality target: premium editorial creator or consultant feel; richer work and case-study surfaces; intentional media-kit and newsletter sections.

Current status: conditional keep or merge into Agency. The package owns the right section names, but the current homepage and visual-review screenshots still share too much proof/card rhythm with neighbouring themes. The next pass should rewrite the visual hierarchy around work outcomes, selected case studies, process, credentials, media-kit, and newsletter conversion before this theme is treated as premium-ready. If that rewrite is not strong enough, merge the useful sections into Agency instead.

### Education

Primary sections:

- `course-catalog`
- `instructors`
- `events`
- `enrolment-cta`
- `resources`
- `faq`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-education/tests/Feature/Commands/DemoCommandTest.php` if missing.

Quality target: strong course and instructor scanning; clear resources, events, and enrolment states; useful density without clutter.

### Nonprofit

Primary sections:

- `impact`
- `campaigns`
- `volunteer-donate`
- `events`
- `stories`
- `contact`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-nonprofit/tests/Feature/Commands/DemoCommandTest.php` if missing.

Quality target: clear donation and campaign hierarchy; strong story and event presentation; emotionally clear without visual fluff.

### Local Services

Primary sections:

- `services`
- `service-areas`
- `quote-form`
- `case-studies`
- `resources`
- `contact`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-local-services/tests/Feature/Commands/DemoCommandTest.php` if missing.

Quality target: obvious quote-led conversion; strong locality cues; better service and case-study scanning.

### Knowledge

Primary sections:

- `topic-hubs`
- `featured-content`
- `resource-library`
- `search-listing`
- `newsletter`
- `authors`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-knowledge/tests/Feature/Commands/DemoCommandTest.php` if missing.

Quality target: dense but readable discovery; strong hub and search flow; clear author and resource hierarchy.

### Corporate

Primary sections:

- `hero`
- `features`
- `proof`
- `content-listing`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-corporate/tests/Unit/PublicOutputSafetyTest.php` if missing.

Quality target: restrained, high-trust enterprise presentation; clearly distinct from Agency and Foundation.

Current status: polished basic/business preset or fold into Foundation. Keep it safe and visually distinct while it exists, but do not spend premium-depth effort unless the theme gets a sharper rewrite brief around governance, investor relations, compliance, or enterprise operations.

### Agency

Primary sections:

- `hero`
- `features`
- `proof`
- `content-listing`
- `cta`
- `footer`

Hardening:

- Add `packages/theme-agency/tests/Unit/PublicOutputSafetyTest.php` if missing.

Quality target: more expressive than Corporate; clearly distinct from `default`; clean and production-safe.

Current status: free/basic creative preset or merge with Portfolio. Keep it usable as a studio starter theme, but do not treat it as premium unless it gains a stronger studio sales workflow: proposal, process, client proof, campaign outcomes, and case-study depth. Only one of Agency or Portfolio should own the premium studio lane.

## Test Hardening Matrix

Add missing `PublicOutputSafetyTest` coverage for:

- `commerce`
- `healthcare`
- `saas`
- `corporate`
- `agency`

Add missing `DemoCommandTest` coverage for:

- `portfolio`
- `education`
- `nonprofit`
- `local-services`
- `knowledge`

Keep the existing package-aware and public-safety patterns green in:

- `portfolio`
- `education`
- `nonprofit`
- `local-services`
- `knowledge`

## Final Completion Gate

The full objective is complete only when:

- all 10 theme package suites are green
- all 10 theme screenshot suites are green
- `FoundationThemeDemoLayoutScreenshotTest` is green
- every theme has passed screenshot review
- every theme has passed read-only `gpt-5.5` low review
- final comparison against `default` confirms all 10 themes are visually distinct and product-quality

Document unresolved visual or test debt in the relevant package issue or plan before claiming the theme is done.
