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

These all extend Foundation. In internal docs, only some are classed as premium, but when Ben asks for the "10 premium themes", treat that as this full first-party child-theme set.

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

Work one theme at a time. Do not batch-edit several themes before validation.

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
- `portfolio`: editorial creator or consultant feel, richer work and case-study treatment.
- `education`: course and instructor scanning, event/resource clarity, enrolment flow.
- `nonprofit`: clear campaign, impact, volunteer, donate, story, and event hierarchy.
- `local-services`: quote-led conversion, locality cues, service-area clarity.
- `knowledge`: search-led and hub-led discovery, readable resource density.
- `corporate`: restrained high-trust enterprise presentation.
- `agency`: more expressive than Corporate while staying clean and production-safe.

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
