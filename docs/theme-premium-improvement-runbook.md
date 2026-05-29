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

When Ben refers to the "10 premium themes" in this context, treat that as the full first-party child-theme quality pass, not a promise that every child theme should stay in the paid/premium lane. The current product direction is fewer, clearer, better themes: every first-party child theme must look production-ready, but only themes with a distinct buyer, domain workflow, and enough unique sections should keep receiving premium-depth investment.

The full first-party child-theme set is:

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

## Theme Portfolio Direction

Use this classification before starting another improvement slice. It prevents spending days polishing themes that are fundamentally too similar.

| Theme            | Direction                               | Rationale                                                                                                                                                                              | Next action                                                                                                                      |
| ---------------- | --------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| `commerce`       | Keep as premium                         | Distinct retail buyer, merchandising rhythm, product/listing cues, and optional commerce-adjacent surfaces.                                                                            | Continue polishing catalog, product finder, proof, and blog/guide states.                                                        |
| `healthcare`     | Keep as premium                         | Distinct service/clinician/contact paths with trust-first clinical presentation.                                                                                                       | Finish resource, contact, booking, and optional-package fallback polish.                                                         |
| `saas`           | Keep as premium                         | Distinct product-led buyer with comparison, calculator, telemetry-style cards, and docs/resource cues.                                                                                 | Deepen product workflow sections and fallback states.                                                                            |
| `knowledge`      | Keep as premium candidate               | Search-led resource hub now has a recognisable editorial command-centre direction.                                                                                                     | Keep tightening library/search/detail states and author/resource hierarchy.                                                      |
| `local-services` | Keep as premium candidate               | Quote-led service-area workflow is visibly different from the generic service themes.                                                                                                  | Improve quote form, route board, service-area, and job-proof states.                                                             |
| `nonprofit`      | Keep as premium candidate               | Campaign, impact, donate/volunteer, story, and event surfaces create a clear buyer.                                                                                                    | Improve donation/campaign pathways and impact-story density.                                                                     |
| `education`      | Keep as premium candidate               | Course, cohort, instructor, enrolment, event, and resource sections give it a real domain workflow.                                                                                    | Strengthen catalogue/detail/enrolment states and avoid generic course cards.                                                     |
| `portfolio`      | Keep as premium studio/case-study theme | It now owns the premium studio lane with work outcomes, case-file mechanics, media-kit surfaces, evidence-led proof, and newsletter/audience-building paths.                           | Continue polishing case-study detail, work index, media-kit, and audience-building states. Do not duplicate this lane in Agency. |
| `agency`         | Basic/free creative campaign preset     | Agency now reads as a campaign/launch-room preset with expressive cards and launch-board hero treatment, not the premium case-study product.                                           | Keep polished and distinctive, but avoid adding Portfolio's deeper case-study/media-kit workflow here.                           |
| `corporate`      | Basic/free business preset              | Corporate now has a restrained boardroom/reporting direction with board pack, decision log, governance, assurance, and register cues, but remains a standard-section business starter. | Keep as a polished basic preset unless a future enterprise workflow justifies premium depth.                                     |

Premium work should target the first seven domain themes plus `portfolio` as the single premium studio/case-study lane. `agency` and `corporate` should stay production-ready basic/free presets unless a future product decision gives either one a distinct workflow that does not overlap the premium lanes.

## Next Expansion: Local Business Verticals

Ben wants more premium themes aimed at local businesses. Do not create five isolated clones of `local-services`. Treat this as a vertical theme family with shared operational ideas and distinct industry workflows.

Recommended premium local-business set:

| Theme                      | Package key           | Primary buyer                                          | Distinct workflow                                                                                           | Livewire / optional package fit                                                                                                                                                       |
| -------------------------- | --------------------- | ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Restaurant & Venue         | `theme-restaurant`    | Restaurants, cafes, pubs, private dining venues        | Menu discovery, bookings, events/private dining, reviews, opening times, gift vouchers                      | `form-builder` for enquiries/reservations, `events` for dining events, optional newsletter for offers.                                                                                |
| Equestrian / Riding School | `theme-equestrian`    | Riding schools, yards, instructors, equestrian centres | Lesson calendar, rider level paths, instructor/horse profiles, events, facility proof, booking enquiry      | Strong `events` integration for lessons/clinics/camps; `form-builder` for rider assessment and booking requests.                                                                      |
| Estate Agents & Lettings   | `theme-estate-agents` | Estate agents, lettings agents, property teams         | Property search, valuation CTA, location guides, featured listings, agent proof, viewing requests           | Needs a Livewire property search component with an extension contract. It should start from hydrated demo/listing data, then optionally integrate a future property-listings package. |
| Salon / Hair & Beauty      | `theme-salon`         | Hairdressers, barbers, beauty salons, spas             | Treatment menu, stylist profiles, availability prompts, before/after proof, reviews, repeat booking         | `form-builder` for appointment requests; later booking/calendar integration if a proper bookings package exists.                                                                      |
| Professional Practice      | `theme-practice`      | Solicitors, accountants, advisers, local consultants   | Practice areas/services, team credentials, compliance proof, consultation routing, resources, client intake | `form-builder` for client intake and triage; `search`/resources for guidance pages. Keep separate from `corporate` by focusing on local client acquisition and trust.                 |

Possible later additions, only after the first five are strong:

- Fitness / studio: class timetable, trainers, membership, trials. This may become valuable if events/bookings mature.
- Trades / home services: emergency repairs, quotes, service areas. Only create this if `local-services` becomes too broad; otherwise keep it inside `local-services`.
- Venue / accommodation: availability, rooms/spaces, packages, events. This overlaps restaurant unless it gets a distinct bookings workflow.

Local-business premium rules:

- Each vertical must be recognisable from a screenshot without reading the theme name.
- Each vertical needs one workflow that `local-services` does not already own.
- Themes should stay thin: no public Blade database queries, no models, no migrations, and no admin resources inside the theme package.
- If a workflow needs searchable records, bookings, calendars, or submissions, prefer an optional companion package or existing package integration. The theme should skin the experience and render safe fallbacks.
- Livewire components are appropriate for interactive public surfaces such as property search, availability filters, class/event filters, menu/category filters, and appointment/quote routing. They must not leak editor/admin state into public HTML.
- Any Livewire component rendered in a theme must have focused tests, stable `wire:key` values in loops, loading/empty states, and safe behaviour when the optional package is missing.

Implementation order:

1. `theme-estate-agents`, because the property search component creates a reusable pattern for searchable vertical data.
2. `theme-equestrian`, because it can prove events-calendar integration with lessons and clinics.
3. `theme-restaurant`, because menus, events, booking CTAs, and local trust are highly visual.
4. `theme-salon`, because it can reuse appointment/intake patterns while staying visually distinct.
5. `theme-practice`, because it is commercially useful but must not collapse back into Corporate.

## Non-Overlap Rule

Themes should not overlap where it is avoidable. A premium theme needs an exclusive buyer and workflow lane, not just a different palette, card style, or hero treatment.

Before improving a theme, write down the lane it owns:

- `commerce`: merchandising, products, collections, buying guides, checkout-adjacent conversion.
- `healthcare`: services, clinicians, care pathways, appointment/contact routes, clinical trust.
- `saas`: product marketing, feature comparison, calculator/trial flows, docs/resources.
- `knowledge`: search-led editorial/resource hubs, topic paths, saved/queued reading.
- `local-services`: quote requests, service areas, dispatch/job proof, local availability.
- `nonprofit`: campaigns, impact, donation, volunteering, stories, events.
- `education`: courses, cohorts, instructors, events, enrolment, learning resources.
- Studio lane: `portfolio` is the premium studio/case-study theme.
- `agency`: basic creative/campaign preset; do not duplicate Portfolio's case-study workflow.
- `corporate`: basic business/boardroom preset unless it becomes a distinct enterprise workflow.

If two themes want the same buyer, same homepage anatomy, same card system, same proof treatment, and same conversion path, do not polish both. Pick the stronger product, merge the useful sections into it, and demote or remove the weaker overlap.

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
- [Local Business Premium Theme Plan](local-business-premium-theme-plan.md)
- [Package Screenshot Automation](package-screenshot-automation.md)

The route-backed screenshot helper lives at:

- `tests/Packages/Support/ThemeDemoLayoutScreenshots.php`

Theme package docs and screenshot manifests live under:

- `packages/theme-<theme>/docs/`

## Execution Order

Use this fixed order unless Ben explicitly changes priority. The order now favours themes with a clear premium buyer before conditional/basic themes.

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

Overlap decisions are part of screenshot QA. When a screenshot pass finds two themes solving the same job, record which one owns the lane before implementing the next slice.

Use this quick distinction matrix while reviewing screenshots:

- `commerce`: merchandising-first, product dense, stock/checkout/catalog cues, dark retail proof panels.
- `healthcare`: trust-first, appointment and service paths, calm clinical hierarchy, accessible contact routes.
- `saas`: product-led, feature comparison, calculator/trial proof, documentation and integration cues.
- `portfolio`: premium studio lane, work-led case files, outcome metrics, creator credentials, media-kit and newsletter surfaces.
- `education`: course-led, instructor/event/resource scanning, enrolment and cohort signals.
- `nonprofit`: campaign-led, impact numbers, donation/volunteer paths, stories and events.
- `local-services`: quote-led, service-area and locality cues, job photos, urgency and availability signals.
- `knowledge`: search-led, hub/resource density, tags, reading paths, saved-resource style affordances.
- `corporate`: basic boardroom restraint, enterprise proof, governance/operations signals, clean report-like rhythm.
- `agency`: basic creative campaign energy, launch-room process, bold visual movement, no premium case-study workflow.

## Theme Targets

`commerce`: retail-specific rhythm, dense but readable product cards, strong catalog and checkout-adjacent cues.

`healthcare`: high-trust clinical presentation, clear service/contact paths, calm operational states.

`saas`: product-grade comparison, calculator, proof, and blog surfaces.

`portfolio`: premium studio/case-study theme for creators, consultants, and studios. Screenshots should visibly lead with work outcomes, case-file mechanics, process, credentials, media-kit surfaces, and audience-building paths.

`education`: course and instructor scanning, event/resource clarity, enrolment flow.

`nonprofit`: campaign, impact, volunteer, donate, story, and event hierarchy.

`local-services`: quote-led conversion, locality cues, service-area clarity.

`knowledge`: search-led and hub-led discovery, readable resource density.

`corporate`: restrained high-trust boardroom/business presentation. Treat as a polished basic/business preset unless a future rewrite gives it a sharper enterprise workflow such as governance, investor relations, compliance, or operational reporting.

`agency`: expressive campaign and launch-room preset while staying clean and production-safe. Treat as a free/basic creative preset and avoid duplicating Portfolio's premium studio/case-study lane.

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
