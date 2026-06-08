# Capell Theme Scale

Use this scale when creating a new Capell theme, changing one existing theme, or applying a renderer pattern across all themes. The goal is to keep themes expressive without turning public Blade into a second CMS or leaking editor concerns into cached frontend HTML.

## Theme Tiers

| Tier                   | Packages                                                                                                                                                                                                                                                                  | Role                                                                                                                                                           | Expected depth                                                                                                                    |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Foundation             | `capell-app/foundation-theme`                                                                                                                                                                                                                                             | Default frontend runtime, shared renderer conventions, Tailwind asset generation, media URL handling, generic Blade components, and base Layout Builder areas. | Boring, stable, shared. Changes here affect every child theme.                                                                    |
| Basic/free             | `capell-app/theme-corporate`                                                                                                                                                                                                                                              | Restrained first-party business/boardroom renderer when the standard section set plus a few formal business sections is enough.                                | Thin child theme with polished presets, page wrappers, and a conservative professional section set.                               |
| Premium                | `capell-app/theme-agency`, `capell-app/theme-commerce`, `capell-app/theme-education`, `capell-app/theme-healthcare`, `capell-app/theme-knowledge`, `capell-app/theme-local-services`, `capell-app/theme-nonprofit`, `capell-app/theme-portfolio`, `capell-app/theme-saas` | Higher-value themes for domains that need richer page patterns, optional package integrations, more content states, or a distinct case-study workflow.         | Extra renderer layers, domain sections, optional package fallbacks, richer marketplace screenshots, and more regression coverage. |
| Premium Inertia family | `capell-app/theme-inertia-bookings`, `capell-app/theme-inertia-bookings-react`, `capell-app/theme-inertia-bookings-vue`                                                                                                                                                   | Appointment-led booking theme plus React/Vue adapter plugins for Inertia installations.                                                                        | Theme package owns the booking public journey; adapter plugins own framework component registration and screenshots.              |

Foundation is the normal/default theme. Basic and premium Blade themes extend Foundation rather than replacing it unless Foundation's rendering contract is genuinely the wrong base. The Inertia Bookings family is separate because it renders through Inertia and uses React/Vue adapter plugins.

## What A Theme Can Own

A theme package may own:

- A stable `themeKey` in `capell.json`.
- A runtime service provider that registers `ThemeDefinitionData` with `ThemeRegistry`.
- A `BladeThemeRenderer` page wrapper.
- `ViewSectionRenderer` mappings and custom section renderer classes.
- Preset defaults through `ThemePresetData`.
- Theme-owned public views under `resources/views`.
- Theme-specific CSS assets and preview images.
- Marketplace screenshots and package health checks.
- Demo creation Actions and demo commands when the package ships example content.

A theme package should stay thin. Current first-party child themes do not own migrations, routes, models, permissions, settings tables, or admin resources. Their visible admin surface is the shared Theme management page contributed through the manifest.

## What A Theme Must Not Own

A theme must not store designed page markup in content fields or render editor internals to the public frontend.

Do not put these in public Blade, cached HTML, theme assets, or package demo content:

- Authoring controls, signed editor URLs, permissions, admin routes, field paths, schema labels, model IDs, or package internals.
- Database queries, relationship lazy-loading, direct model lookups, or `loadMissing()` calls.
- Client-specific copy, content, layout wrappers, utility classes, or hardcoded content structures that editors should control.
- Theme-specific presentation HTML inside page body/content fields.

Public Blade receives hydrated render data and renders presentation. It must not discover content by querying.

## Configuration Scale

When a theme feature is configurable, choose the narrowest durable owner.

| Need                                                                                                | Put it in                                                          | Why                                                                           |
| --------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------ | ----------------------------------------------------------------------------- |
| Site-wide visual tokens such as colours, type, button shape, logo treatment, and default card style | Theme settings or theme preset database edits                      | These vary by site and should override package defaults without code changes. |
| Page type structure, required fields, SEO defaults, or page-level editorial controls                | Page blueprint/schema                                              | These describe the page contract, not one renderer.                           |
| Reusable content module fields and allowed variants                                                 | Widget blueprint or Layout Builder widget definition               | Editors reuse these across pages and themes.                                  |
| One instance of copy, media, CTA links, selected variant, spacing, or background                    | Page content, widget content, or Layout Builder widget assets/meta | Instance-level content belongs in the database.                               |
| Placement outside the main content loop, such as header, footer, announcement, or utility areas     | Layout Builder area registration plus container `meta.area`        | Placement remains editable without inventing hidden static containers.        |
| Renderer-only markup, responsive layout, utility classes, and domain presentation                   | Blade views and section renderer classes                           | Presentation belongs in code so content survives theme changes.               |
| Build-time asset sources, Tailwind sources, or package asset manifests                              | Package config and asset pipeline                                  | These are developer/runtime concerns, not editor content.                     |

If the value changes per site, page, widget, widget, language, or editor decision, it belongs in persisted Capell data. If it is only the HTML/CSS shape used to display that data, it belongs in the theme package.

## Layout Builder And Page Assets

Layout Builder is the composition layer for themes. It owns layout containers, widgets, widget assets, public layout graphs, reusable presets, layout areas, and the Filament editor. Themes should consume its public graph and render hydrated widget data.

Use Layout Builder when:

- Editors need to arrange sections or widgets.
- A page needs per-instance media, CTA, copy, variant, spacing, or background controls.
- A theme area needs editable content outside the main page loop.
- A preset should duplicate structure and presentation settings without duplicating client content.

Use page assets and widget assets for content that belongs to a rendered instance. Keep portable editorial content in the database and keep design wrappers in Blade. Demo creators should seed minimal editable copy and attach configured widgets, not save full designed HTML.

Public renderers should use `BuildPublicLayoutGraphAction` or existing renderer components. Do not query from public views.

## Creating A New Theme

Start with Foundation unless there is a strong reason not to.

1. Create a Composer package under `packages/theme-client`.
2. Add `capell.json` with `manifest-version: 3`, `kind: "theme"`, a stable `themeKey`, `extends: "capell-app/foundation-theme"`, and runtime provider metadata.
3. Require `capell-app/core` and `capell-app/foundation-theme`. Add supported optional packages only when renderers actually integrate with them.
4. Register the package with `CapellCore::registerPackage()` in `register()`.
5. In `boot()`, stop early when the package is not installed, load views, and register the theme definition, page wrapper, and section renderers.
6. Add the standard section set first: `navigation`, `hero`, `features`, `proof`, `content-listing`, `search`, `pagination`, `form`, `cta`, and `footer`.
7. Ship the required page set: homepage, landing page, plain-text about page with imagery, list page with pagination, search page, contact form page, and at least one resource/detail page.
8. Add premium/domain sections only when the theme has a real domain need and a safe fallback for missing optional packages.
9. Add demo content through an Action. Store content and widget configuration in Capell data; keep presentation in views.
10. Add focused tests for the definition, manifest requirements, renderer registration, optional package fallback, public-output safety, required page coverage, and screenshot metadata.

Theme keys are content identifiers. Renaming one is a migration, not a cosmetic package rename.

## Screenshot And Marketplace Requirements

Every first-party theme package must ship marketplace documentation that can feed Capell App, package docs, generated marketplace pages, and the deployment screenshot runner.

Each theme needs at least five useful screenshots. Store the runner manifest in `packages/<theme>/docs/screenshots.json`, and keep generated route/admin captures under `packages/<theme>/docs/screenshots/`. Package marketplace galleries in `capell.json.marketplace.screenshots` should promote the strongest committed captures or assets, normally from `docs/screenshots/` for real route-backed captures and `docs/assets/marketplace/` for extension cards or hero crops.

The screenshot-runner manifest uses the contract documented in [Package Screenshot Automation](package-screenshot-automation.md). Each entry should include the fields the runner needs, including:

- `id`: stable capture identifier.
- `title`: concise screen name, such as `Homepage`, `Search`, or `Contact form`.
- `surface`: `admin` or `frontend`.
- `targetType`: `admin-surface`, `admin-url`, `filament-resource`, or `frontend-url`.
- `target` and, for frontend fixtures, `url`: the surface to capture.
- `screenshotPath`: output path relative to the repository root.
- `required`: whether CI/deployment should fail when the capture cannot be produced.
- `user`: `false` for anonymous frontend captures; omit or set the required actor for admin captures.
- `notes` and `useCase`: what the screen contains, why it matters, and what buyer/editor problem it proves.

The five minimum screenshots are:

1. Homepage with a strong theme-specific visual direction.
2. Landing page for a campaign, service, course, product, cause, or case study.
3. List page with pagination.
4. Search results page.
5. Contact or conversion form page.

Add more screenshots for domain-specific value: product collection, clinician directory, course catalogue, resource hub, donation path, case study, event listing, pricing, docs, or newsletter surfaces. Screenshots must show real page composition and imagery, not placeholder arcs, blank cards, or the same section order with different colours.

## Visual Differentiation

Themes should share enough structure that maintainers know how to test and customise them, but they must not feel like colour swaps. Keep these contracts consistent across every theme:

- The required page set above exists and renders safely.
- Search and list pages support pagination.
- Contact or conversion forms have a first-class section.
- Imagery is available on all important page types, including text-led about pages.
- Public output stays free of editor/admin metadata.

Make each theme visually different through layout rhythm, section order, media treatment, card density, typography scale, CTA placement, and domain-specific sections. Avoid copying the same homepage, list, search, and contact layout across themes unless Foundation owns that shared fallback and the child theme deliberately overrides the parts that define its market.

## Applying Logic Across All Themes

Use this order when a change should affect every theme:

1. Put shared runtime behaviour in Foundation or the core Theme Studio runtime.
2. Put shared Layout Builder behaviour in Layout Builder Actions, payload contributors, layout areas, or widget presentation projection.
3. Keep child themes limited to theme-specific page wrappers, section views, presets, and custom renderers.
4. Update every child theme only when the public contract changes or each theme needs distinct markup.
5. Add cross-theme tests or visual fixtures for the shared contract, then targeted package tests for each affected renderer.

Do not copy the same logic into five theme service providers if a shared registry, Action, DTO, or Foundation component can own it cleanly.

## Premium Theme Expectations

Premium themes need more than a colour palette over the standard sections. They should include:

- Domain-specific section renderers.
- Optional package integrations with graceful empty/missing-package states.
- More complete marketplace screenshots and demo paths.
- Mobile and desktop visual regression coverage for the most theme-specific surfaces.
- Tests proving public output stays free of authoring metadata.
- Clear health checks for missing manifests, optional integrations, and stale theme data.

Premium does not mean more static content. It means better defaults, richer configurable structures, and stronger coverage.

## Non-Overlap Standard

First-party themes should not overlap where it is avoidable. A premium theme must own a distinct buyer and primary workflow. If two themes share the same buyer, homepage anatomy, card system, proof treatment, listing rhythm, and conversion path, keep the stronger product lane and demote, merge, or fold the weaker one into Foundation.

Current lane ownership:

- `commerce`: merchandising and buying journeys.
- `healthcare`: service, clinician, care-pathway, and appointment journeys.
- `saas`: product, comparison, trial, and documentation journeys.
- `knowledge`: search-led resource and reading journeys.
- `local-services`: quote, locality, dispatch, and job-proof journeys.
- `nonprofit`: campaign, donation, volunteer, impact, and story journeys.
- `education`: course, cohort, instructor, event, and enrolment journeys.
- `portfolio`: premium studio/case-study lane for work outcomes, case files, media kits, and audience-building paths.
- `agency`: premium creative/campaign lane with expressive launch-room treatment; do not duplicate Portfolio's deeper outcome-led case-study workflow.
- `corporate`: basic business/boardroom preset unless it becomes a distinct enterprise workflow.
- `inertia-bookings`: premium appointment, services, location, and public booking request journey for Inertia installs.

Future local-business verticals should extend this non-overlap standard rather than duplicate `local-services`.

Recommended vertical lanes:

- `theme-restaurant`: menu, reservation, private dining, events, opening-hours, and offer journeys.
- `theme-equestrian`: riding lessons, clinics, camps, instructor/horse profiles, rider-level paths, and events-calendar journeys.
- `theme-estate-agents`: property search, featured listings, valuation, local guide, agent proof, and viewing-request journeys.
- `theme-salon`: treatment menu, stylist profiles, availability, before/after proof, reviews, and appointment-request journeys.
- `theme-practice`: local professional services for solicitors, accountants, and advisers, centred on practice areas, credentials, consultation intake, resources, and compliance/trust.

These themes should not own models, migrations, or admin resources by default. If a vertical needs live records or interactive filtering, put the data behaviour in an existing or new companion package and let the theme register a public-safe presentation layer. Examples:

- Use `capell-app/events` for equestrian lessons, clinics, camps, restaurant events, and fitness/studio classes.
- Use `capell-app/form-builder` for reservations, appointment requests, valuations, rider assessments, and client intake.
- Use `capell-app/search` or a future `capell-app/property-listings` package for estate-agent search.
- Use Livewire for public interaction only when it improves the visitor workflow: property search filters, event/class filters, appointment availability, menu filters, or intake routing. Components need stable loop keys, loading states, empty states, and package-missing fallbacks.

## Verification

For documentation-only theme changes, check links and terminology against the package manifests and README files.

For package code changes, start narrow:

```bash
vendor/bin/pest packages/theme-saas/tests --configuration=phpunit.xml
```

Then run the affected package suites and any shared Foundation or Layout Builder tests when the contract changes:

```bash
vendor/bin/pest packages/foundation-theme/tests packages/layout-builder/tests --configuration=phpunit.xml
```

Use `composer preflight` before committing broader theme/runtime changes.

## First-Party Theme Catalogue

The first-party catalogue includes these theme lanes:

- `agency`: studios, portfolios, service pages, case studies, campaigns, lead forms, and search.
- `commerce`: product collections, lookbooks, buying guides, product stories, search, and newsletter conversion.
- `corporate`: governance, services, resources, locations, contact paths, and formal proof.
- `education`: schools and course providers, course catalogues, instructors, events, enrolment CTAs, resources, and FAQs.
- `healthcare`: services, clinicians, appointment CTAs, care pathways, events, resources, and contact paths.
- `inertia-bookings`: appointment-led service businesses using Inertia, Bookings, and React/Vue adapter plugins.
- `knowledge`: editorial/resource sites, topic hubs, featured content, resource libraries, search-led listings, newsletter, and authors.
- `local-services`: quote-led service businesses, local proof, service areas, quote forms, resources, and contact paths.
- `nonprofit`: charities and civic sites, impact, campaigns, volunteer/donate CTAs, events, stories, and contact paths.
- `portfolio`: creators and consultants, work grids, case studies, services, testimonials, speaking/media kit, and newsletter paths.
- `saas`: product marketing, pricing, docs, feature pages, proof, comparison, launches, and demo requests.

Each package stays thin: no migrations, models, routes, or admin resources. Optional integrations are surfaced through safe render data and must never leak package metadata, authoring state, signed editor URLs, or admin selectors to public output.

## Theme Marketplace Cards

Theme admin cards should read like a product marketplace, not a database table. Cards can use installed theme records, registered theme definitions, and manifest/admin metadata to show:

- Preview image or generated fallback.
- Best-fit use cases and tags.
- Included section count.
- Package/composer identity in details.
- Demo readiness and package/install state.
- Compatibility warnings when present.

Keep card copy and labels translated through `capell-admin::*`.
