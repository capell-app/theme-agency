# Premium Theme Upgrade Design

## Goal

Improve the full premium theme lane so each theme feels meaningfully premium through richer public layouts, stronger customization defaults, safer optional integrations, and consistent verification.

## Scope

The premium lane follows `docs/theme-scale.md`, not the currently inconsistent `capell.json` tier fields. The affected themes are:

- `capell-app/theme-commerce`
- `capell-app/theme-education`
- `capell-app/theme-healthcare`
- `capell-app/theme-knowledge`
- `capell-app/theme-local-services`
- `capell-app/theme-nonprofit`
- `capell-app/theme-portfolio`
- `capell-app/theme-saas`

The manifest tier mismatch is part of the work. These packages should be treated as premium in manifests, docs, definitions, screenshots, and tests.

## Product Direction

Premium means more than a color palette over Foundation. Each theme must offer a buyer-specific workflow with page layouts and sections that match the domain.

- Commerce owns merchandising, buying journeys, lookbooks, product comparison, and offer campaigns.
- Education owns course discovery, pathway comparison, learner outcomes, instructors, events, and enrolment.
- Healthcare owns service discovery, care pathways, clinicians, appointment routing, locations, and trust.
- Knowledge owns search-led resource discovery, topic hubs, reading paths, source maps, and newsletters.
- Local Services owns quote-led service selection, service areas, locality proof, packages, and intake.
- Nonprofit owns campaign impact, donation and volunteer paths, events, stories, and outcome evidence.
- Portfolio owns selected work, case-study detail, process, availability, speaking, media kit, and audience growth.
- SaaS owns product workflow, feature pages, pricing, docs/onboarding, demo request, webinars, and launches.

## Customization Contract

All eight premium themes should expose the same baseline preset controls through `ThemePresetData` values:

- `primaryColor`
- `accentColor`
- `neutralColor`
- `surfaceColor`
- `foregroundColor`
- `headingFont`
- `bodyFont`
- `spacing`
- `cardStyle`
- `navigationStyle`
- `layoutPresentation`
- `motionIntensity`
- `mediaTreatment`
- `radius`
- `headingScale`
- `cardDensity`

The preset values are defaults only. Database edits from Theme admin keep winning. Theme packages own the presentation defaults; per-site and per-page choices remain in Capell data.

Page wrappers should consistently apply brand CSS tokens from `$brand->tokens()` and include a translated skip link. Existing wrappers in Commerce, Healthcare, and SaaS are the parity target. Education, Knowledge, Local Services, Nonprofit, and Portfolio should be brought up to the same runtime shape without exposing theme keys or package names in public HTML.

## Layout Additions

Each premium theme gets at least three new or expanded layout surfaces. These are public-safe Blade section renderers and demo/screenshot entries, not new database models.

### Commerce

- `lookbook`: image-led editorial merchandising with shoppable collection cards and media-library fallback copy.
- `promotion`: campaign/offer layout with product groups, countdown-style visual treatment, segmented CTAs, and campaign-studio availability copy.
- `buying-guide`: advice/article layout that pairs editorial guidance with product recommendations and blog availability copy.

### Education

- `pathway-comparison`: compare learning paths by format, outcome, duration, and learner fit.
- `outcomes`: outcome evidence cards with completion, placement, certification, and cohort proof.
- `admissions-checklist`: application steps, required materials, open-day prompt, and form-builder availability copy.

### Healthcare

- `care-pathway`: appointment-led route cards from symptom/service need to clinician or booking path.
- `locations`: clinic access panel with opening-hours style data, transport hints, and contact route cards.
- `insurance-trust`: accepted-cover/trust proof layout with accessible disclaimers and no medical advice claims.

### Knowledge

- `reading-path`: guided next-reading sequence for beginner, intermediate, and advanced readers.
- `source-map`: evidence/source organization panel with freshness and ownership cues.
- `topic-index`: dense topic hub layout with search/package availability copy and no live query in Blade.

### Local Services

- `quote-estimator`: non-calculating estimate/intake layout with scope factors and form-builder availability copy.
- `service-packages`: package cards for common service bundles, urgency, and inclusion cues.
- `locality-proof`: service-area proof with response time, neighbourhood evidence, and customer outcome cards.

### Nonprofit

- `donation-impact`: donation amount/value cards, campaign relationship, and impact evidence.
- `volunteer-shifts`: volunteer roles and event-style opportunities with events/form-builder availability copy.
- `annual-report-proof`: outcome ledger, transparency cues, campaign metrics, and story links.

### Portfolio

- `case-study-detail`: challenge, approach, result, scope, and next-project CTA.
- `process`: engagement stages, review points, deliverables, and proof-linked capabilities.
- `availability`: booking/media-kit expansion with speaking, press, newsletter, and enquiry paths.

### SaaS

- `pricing`: plan comparison, risk reducers, FAQ preview, and content-sections availability copy.
- `docs-onboarding`: docs navigation preview, activation checklist, related resources, and document-lifecycle availability copy.
- `demo-request`: qualification route, proof, routing states, and form-builder availability copy.

## Architecture

Use the existing package shape:

- Theme definitions remain in each `*ThemeServiceProvider`.
- Section views live under each package's `resources/views/sections`.
- Custom renderer classes stay package-local when optional package availability needs to be passed into a view.
- Simple sections use `ViewSectionRenderer`.
- Optional package-aware sections use the existing package-aware renderer pattern already present in Education, Knowledge, Local Services, Nonprofit, and Portfolio, or the package-specific renderer pattern already present in Commerce, Healthcare, and SaaS.
- Demo install Actions continue delegating to `ThemeDemoPageInstaller::run(...)`.

No new routes, migrations, Eloquent models, admin resources, permissions, or package-owned settings tables are introduced for this upgrade.

## Data Flow

Themes render hydrated section data supplied by Capell Theme Studio and Layout Builder. Public Blade may read arrays and DTO/view data passed to it, but it must not query the database, lazy-load relationships, call loaders, call `getMeta()`, or infer editor state.

Optional package availability is detected in service providers or renderer classes and passed to views as plain booleans such as `blogAvailable`, `searchAvailable`, `formBuilderAvailable`, or `eventsAvailable`. Public fallback copy may explain that a visitor can browse the current static content, but it must not mention composer package names or Capell internals.

Demo pages should seed portable copy and configured sections. Designed markup, Tailwind classes, and layout structure belong in theme Blade/CSS.

## Public Safety

Public output from every premium theme must remain safe for anonymous visitors, signed-in non-admin users, admins, crawlers, cached HTML, and static exports.

Theme Blade, translations, and rendered output must not expose:

- authoring controls or authoring JavaScript
- signed editor URLs
- admin routes or Filament labels
- package names or package identifiers
- theme keys in public data attributes
- model IDs, field paths, permissions, selectors, or editor metadata
- database query calls or relationship lazy-loading

The existing public-output safety tests should be preserved and expanded when new views or translations are added.

## Marketplace And Demo Coverage

Each premium package should have screenshot manifest entries for the new layouts and matching marketplace SVG or screenshot assets where the package already follows that pattern. Existing `hero-desktop.jpg` and `hero-mobile.jpg` assets in the dirty tree should be preserved unless they are proven stale or unsafe.

Demo content should make the new layouts visible through the standard theme demo path. The result should be inspectable through:

- theme demo command
- Extensions installer demo option
- full Capell demo install flow
- screenshot runner routes referenced by `docs/screenshots.json`

## Testing

Add or update package-local Pest tests for each changed theme:

- definition includes the new premium sections
- renderer registration covers every included section
- new sections render hydrated sample data
- optional package fallbacks render cleanly when dependencies are absent
- preset customization keys are present and consistent across all premium themes
- public Blade and translations remain free of authoring/admin/package/query tokens
- manifest tier and screenshot metadata match the premium lane

Start with narrow package suites, then run the full premium theme set:

```bash
vendor/bin/pest packages/theme-commerce/tests --configuration=phpunit.xml
vendor/bin/pest packages/theme-education/tests --configuration=phpunit.xml
vendor/bin/pest packages/theme-healthcare/tests --configuration=phpunit.xml
vendor/bin/pest packages/theme-knowledge/tests --configuration=phpunit.xml
vendor/bin/pest packages/theme-local-services/tests --configuration=phpunit.xml
vendor/bin/pest packages/theme-nonprofit/tests --configuration=phpunit.xml
vendor/bin/pest packages/theme-portfolio/tests --configuration=phpunit.xml
vendor/bin/pest packages/theme-saas/tests --configuration=phpunit.xml
```

If shared contracts are touched, also run:

```bash
vendor/bin/pest packages/foundation-theme/tests packages/layout-builder/tests --configuration=phpunit.xml
```

## Implementation Slices

The work should be implemented in small vertical slices:

1. Normalize premium theme metadata and customization keys across all eight themes.
2. Bring page wrappers to brand-token and skip-link parity.
3. Add new Commerce, Healthcare, and SaaS premium layouts using their existing renderer style.
4. Add new Education, Knowledge, Local Services, Nonprofit, and Portfolio premium layouts using their package-aware renderer style.
5. Update demo/screenshot metadata for the new layouts.
6. Expand public-output and definition tests.
7. Run package-level verification and then the shared theme checks.

## Non-Goals

This pass does not build a new Theme Studio customization UI, add live frontend search/filtering, add package-owned data models, migrate stored page content, or rewrite Foundation. It improves theme package presentation and configuration within the existing Capell theme runtime.
