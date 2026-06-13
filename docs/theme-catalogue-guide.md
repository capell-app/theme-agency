# Capell Theme Catalogue Review

Last reviewed: 2026-06-13

This review covers the first-party theme catalogue in `packages/foundation-theme` and `packages/theme-*`. It checks theme positioning, manifest/docs consistency, public-output safety signals, screenshot coverage, and whether the premium themes feel professionally differentiated.

## 2026-06-13 Premium Additions

The catalogue now includes two additional premium themes chosen from the non-overlap review:

- `theme-restaurant`: a hospitality lane for menu discovery, reservations, private dining, events, opening hours, location guidance, chef story, and venue proof. This avoids Commerce's product-buying lane and Local Services' quote/dispatch lane by centring the visitor workflow on dining intent and table conversion.
- `theme-estate-agents`: a property lane for public search, featured listings, vendor valuations, local-area guides, agent proof, market evidence, and viewing requests. This avoids Local Services, Portfolio, SaaS, and Commerce by centring the visitor workflow on buyer/vendor property decisions rather than generic lead capture or retail catalogue browsing.

Both new themes are first-party paid themes, extend the built-in default runtime, own no schema, use safe optional-package fallbacks, and include package-local public-output safety, manifest, definition, and health-check tests.

Evidence used:

- `capell.json` metadata, Composer metadata, service providers, theme definitions, and included section lists.
- Package READMEs, `docs/overview.md`, `docs/screenshots.json`, and improvement plans.
- Committed homepage and workflow screenshots under each theme package.
- Public Blade/source scans for database access, authoring markers, admin/editor strings, and package-internal leaks.
- Package-local tests present under `packages/<theme>/tests`.

## Executive Diagnosis

The catalogue is in good shape structurally. The premium themes are not simple colour swaps: Commerce, Healthcare, Knowledge, Local Services, SaaS, Agency, and Inertia Bookings each own a clear market lane and ship domain sections rather than relying only on Foundation sections.

The main weakness is consistency at the marketplace/documentation layer. Some central docs still described older tiers and manifest shapes, `capell.json.marketplace.screenshots` is thinner than the route-backed screenshot manifests for several premium themes, and Foundation references one missing screenshot asset. There is also a visual-system risk: Local Services, Nonprofit, Portfolio, Corporate, and Education share a calm card-and-metrics grammar. That grammar is professional, but premium themes need stronger first-viewport differentiation when they are shown side by side.

## Review Loop

I ran the review as four local passes:

1. Frontend/product design pass: checked committed screenshots for visual lane clarity, premium feel, first-viewport strength, and overlap.
2. Capell architecture pass: checked theme ownership, Foundation inheritance, public rendering boundaries, package surfaces, optional integrations, and tests.
3. Documentation/marketplace pass: checked README/overview consistency, theme tiering, screenshot contracts, and buyer-facing customisation guidance.
4. QA pass: checked strict typing presence in theme PHP files, Composer autoload alignment, screenshot path existence, and focused docs/test commands.

The result is accepted with follow-up items below. No PHP/theme runtime changes were needed for this review.

## Findings

### Important Gaps

- [P2] `theme-agency` was documented as basic/free in central theme guidance while its manifest marks it as premium. The manifest and package copy are the stronger source of truth, so central docs should treat Agency as a premium creative/campaign theme.
- [P2] `docs/creating-a-theme.md` described the old manifest v2 shape and only listed the earlier six theme examples. Theme packages now use manifest v3 and the catalogue includes Education, Knowledge, Local Services, Nonprofit, Portfolio, and the Inertia Bookings family.
- [P2] `docs/theme-scale.md` described `docs/screenshots.json` as if it used marketplace-gallery fields such as `path`, `description`, `layout`, `editable`, `useful`, and `distinctive`. The committed manifests use the screenshot-runner contract with `entries`, `screenshotPath`, `targetType`, `url`, `required`, and route/browser metadata.
- [P2] `packages/foundation-theme/docs/screenshots.json` references `packages/foundation-theme/docs/screenshots/foundation-theme-header-area.png`, but that file is not committed. Either add the capture or remove/rename the entry.
- [P2] Premium marketplace galleries are uneven in `capell.json`. Several packages have 5+ route-backed screenshot-runner entries but only 1-4 marketplace screenshots in `capell.json`. The admin/marketplace gallery should promote at least five useful buyer-facing screenshots per premium theme.
- [P2] Screenshot required flags are inconsistent. Newer premium themes such as SaaS, Local Services, Nonprofit, Portfolio, Inertia Bookings, and the React/Vue adapters mark captures as required. Agency, Commerce, Corporate, Education, Healthcare, and Knowledge still have many route captures marked optional. That made sense while the runner was unstable, but it now weakens premium QA.

### Improvements

- [P3] Education needs a stronger first viewport before it feels as premium as its section set. The package owns good learning-pathway sections, but the committed homepage is sparse and should bring course proof, cohort energy, or instructor/event signals higher.
- [P3] Nonprofit and Portfolio are professional, but their homepage skeletons are close to Local Services. Nonprofit should lean more into campaign story, donation path, and supporter impact; Portfolio should lean more into work media, case-study artifacts, and creator authority.
- [P3] Mobile screenshot coverage is uneven. Healthcare and the Inertia booking family have explicit mobile captures; most other premium themes do not. Premium themes should have at least one mobile marketplace capture for the core conversion page.
- [P3] The Inertia Bookings family needs to be explained as one product family: `theme-inertia-bookings` is the theme, while the React/Vue packages are adapter plugins. Treating the adapters like normal themes creates catalogue confusion.

## Consistency Matrix

| Package                        | Current role                            | Manifest tier/kind | Extends Foundation | Screenshot-runner entries | Marketplace screenshots | Review verdict                                                                         |
| ------------------------------ | --------------------------------------- | ------------------ | ------------------ | ------------------------: | ----------------------: | -------------------------------------------------------------------------------------- |
| `foundation-theme`             | Shared runtime/base theme               | free theme         | n/a                |                        12 |                      11 | Stable baseline; one missing declared screenshot.                                      |
| `theme-agency`                 | Creative/campaign premium theme         | premium theme      | yes                |                        10 |                       3 | Visually strong and premium; central docs needed tier correction.                      |
| `theme-commerce`               | Editorial retail/commerce theme         | premium theme      | yes                |                        10 |                       2 | Premium and distinct; marketplace gallery should expose more captures.                 |
| `theme-corporate`              | Restrained business/public-sector theme | free theme         | yes                |                        10 |                       2 | Professional basic theme; should stay deliberately conservative.                       |
| `theme-education`              | Course/enrolment theme                  | premium theme      | yes                |                         7 |                       3 | Structurally premium; first viewport needs more confidence and proof.                  |
| `theme-estate-agents`          | Property search and valuation theme     | premium theme      | yes                |                         5 |                       5 | New premium property lane with search, valuation, local-guide, and viewing proof.      |
| `theme-healthcare`             | Clinic/service/appointment theme        | premium theme      | yes                |                         6 |                       6 | Strong premium lane with mobile coverage.                                              |
| `theme-inertia-bookings`       | Inertia booking-business theme          | premium theme      | no                 |                         5 |                       3 | Strong booking product family with promoted desktop/mobile booking proof.              |
| `theme-inertia-bookings-react` | React booking adapter                   | premium plugin     | no                 |                         5 |                       1 | Adapter plugin, not a standalone theme.                                                |
| `theme-inertia-bookings-vue`   | Vue booking adapter                     | premium plugin     | no                 |                         5 |                       1 | Adapter plugin, not a standalone theme.                                                |
| `theme-knowledge`              | Search/resource/documentation theme     | premium theme      | yes                |                         7 |                       3 | Distinct premium lane; make runner captures required when stable.                      |
| `theme-local-services`         | Quote-led local service theme           | premium theme      | yes                |                         9 |                       4 | Strong premium operational-service theme.                                              |
| `theme-nonprofit`              | Campaign/donation/volunteer theme       | premium theme      | yes                |                         9 |                       4 | Professional and useful; needs more visual differentiation in hero.                    |
| `theme-portfolio`              | Creator/consultant case-study theme     | premium theme      | yes                |                         9 |                       4 | Structurally premium; needs stronger media/case-study expression.                      |
| `theme-restaurant`             | Hospitality menu and reservation theme  | premium theme      | yes                |                         5 |                       5 | New premium hospitality lane with menu, reservation, private dining, and events proof. |
| `theme-saas`                   | Product-led software/subscription theme | premium theme      | yes                |                         9 |                       8 | Strong premium theme and best current marketplace coverage.                            |

## Customisation Model

Use the same customisation model across the catalogue:

- Theme Studio tokens: colours, surfaces, foregrounds, heading/body fonts, spacing, card style, navigation style, media treatment, radius, heading scale, and motion intensity.
- Layout Builder: section order, reusable page composition, page/widget assets, and editable areas such as header/footer/announcement regions.
- Page and widget content: copy, media, CTA links, selected section variants, list items, metrics, local proof, case-study data, and conversion form data.
- Optional integrations: Blog, Events, Form Builder, Bookings, Newsletter, Search, Content Sections, Media Library, Shopify Commerce, Document Lifecycle, and related packages only when the theme renderer has a safe fallback.
- Package views/CSS: only for renderer shape, responsive layout, domain presentation, and package-owned design systems.

Do not customise themes by storing designed page markup in database content fields, querying from public Blade, exposing admin/editor metadata, or leaking package internals into cached public HTML.

## Theme Write-Up And Customisation Guide

### Foundation Theme

Foundation Theme is the shared runtime and fallback renderer. It is best for starter sites, general publishing, documentation pages, and as the base contract for child themes. It owns the Tailwind asset path, design-token rendering, generic navigation/footer/content sections, widget presentation, media/SVG handling, and the baseline public-output safety rules.

Customise it through Theme Studio tokens, Foundation layout areas, Layout Builder sections, and child theme overrides. Do not turn Foundation into a domain-specific theme; put domain presentation into a child theme unless the behaviour is genuinely shared by every theme.

### Theme Agency

Agency is now a premium creative/campaign theme. It is good for studios, agencies, launch campaigns, service pages, project showcases, lightweight case-study proof, and lead forms. The committed homepage is one of the most visually distinct captures in the catalogue: dark launch-room treatment, bold type, and a campaign-focused hero.

Customise it with Agency presets, hero media, project showcase data, case-study/launch recap sections, team/services/client-logo sections, and Theme Studio tokens for surface, foreground, gradient, spacing, and card treatment. Keep Agency focused on campaign and launch-room presentation; deeper outcome-led case files belong to Portfolio.

### Theme Corporate

Corporate is a free/basic professional theme for B2B, public-sector, professional-services, governance, locations, resources, careers, and board-grade proof. It should feel restrained, legible, and trustworthy rather than visually experimental.

Customise it with formal palettes, service/proof/resource sections, location data, investor-relations blocks, careers listings, and contact paths. It can be polished and premium-looking, but its product role is a conservative default for serious organisations rather than a high-value vertical upsell.

### Theme Commerce

Commerce is a premium retail/editorial theme. It is good for catalogues, product collections, product stories, buying guides, lookbooks, campaigns, search, newsletter conversion, and product-led publishing. The screenshot direction feels curated rather than catalogue-only, which is the right premium lane.

Customise it through product-finder data, collections, product grid/detail, comparison, campaign, lookbook, buying-guide, newsletter, Blog, Shopify Commerce, and media-library integration. The warm editorial palette is acceptable for this buyer, but future presets should avoid making every retail site feel like the same beige boutique.

### Theme Education

Education is a premium course and enrolment theme for schools, academies, training providers, course catalogues, instructors, events, admissions, resources, and FAQs. Structurally it has the right sections: course catalogue/detail, pathway comparison, outcomes, instructors, faculty directory, events, admissions funnel, enrolment CTA, and resources.

Customise it with programme taxonomy, cohort stats, instructor/faculty data, event listings, Form Builder enrolment capture, Blog/resource content, and pathway/outcome copy. The current homepage screenshot is too sparse for a premium catalogue comparison; add stronger course proof, instructor energy, or event/admissions signals above the fold.

### Theme Healthcare

Healthcare is a strong premium clinical theme for private clinics, healthcare groups, specialist providers, service discovery, clinician profiles, care pathways, locations, insurance/trust proof, booking, and appointment conversion. It is one of the clearest professional themes because the utility bar, service finder, clinician surfaces, and mobile capture directly match the buyer workflow.

Customise it with service finder data, clinician profiles, care-pathway sections, locations, insurance/trust content, appointment CTAs, Bookings, Events, Form Builder, and Blog/resource fallbacks. Keep contrast, accessibility, and emergency/escalation language conservative.

### Theme Inertia Bookings

Inertia Bookings is a premium booking-business theme for appointment-led services, clinics, consultants, classes, and locations. It differs from Foundation child themes because it renders through Inertia and connects to the Bookings public request flow with lazy slot loading.

Customise the base package around service proof, booking CTA placement, location/FAQ sections, and booking flow content. Choose `theme-inertia-bookings-react` or `theme-inertia-bookings-vue` to provide the frontend component adapter. The React/Vue packages are not separate visual themes; they are implementation plugins for the same booking theme family.

### Theme Knowledge

Knowledge is a premium search-led publishing and documentation theme. It is good for knowledge bases, resource hubs, docs, topic hubs, featured content, research libraries, author benches, newsletters, and archive/search workflows. The dark homepage and structured search capture make it one of the more distinct themes.

Customise it with topic hubs, resource-library entries, featured-content lanes, search-listing configuration, reading paths, source maps, authors, Newsletter, Search, Blog, and SEO-related packages. Keep it reader-first: navigation, search, and article clarity matter more than marketing decoration.

### Theme Local Services

Local Services is a premium quote-led service-business theme. It is good for trades, local operators, salons, cleaners, clinics, service-area businesses, emergency work, click-to-call flows, quote requests, job proof, reviews, opening hours, structured data, and local SEO.

Customise it with service packages, service areas, locality proof, quote form/estimator data, Form Builder, Bookings handoff, reviews/testimonials, trust badges, opening hours, before/after galleries, Blog resources, and LocalBusiness JSON-LD data. It already feels premium because the dispatch-board/quote-desk mechanics are specific to the buyer.

### Theme Nonprofit

Nonprofit is a premium campaign and supporter-conversion theme for charities, NGOs, civic organisations, campaigns, donations, volunteering, events, impact reports, stories, newsletters, and contact paths. It is professional and practical, with clear supporter CTAs and campaign progress surfaces.

Customise it with donation-impact blocks, campaign progress, volunteer/donate pathways, annual-report proof, events, stories, newsletter capture, Form Builder, payments/donation integration, Campaign Studio, and Blog/resource content. To feel more premium in the catalogue, it should push mission imagery, supporter stories, and donation journey depth further above the fold.

### Theme Portfolio

Portfolio is a premium creator and consultant theme for work showcases, outcome-led case studies, services, proof, media kits, speaking, availability, newsletters, resume/CV, client logos, and audience-building. It is structurally strong and has the right product lane: it sells a person or small studio through evidence, not just aesthetics.

Customise it with work-grid entries, case-study detail, gallery/lightbox media, testimonials, process/services, resume/CV items, client logos, speaking/media kit, availability, Newsletter, Content Sections, and Media Library. The next visual step is to make the first viewport more media/artifact-led so it does not look too close to the Local Services/Nonprofit card system.

### Theme SaaS

SaaS is a premium product-led theme for software, subscriptions, product marketing, pricing, comparison, ROI calculators, documentation onboarding, demo requests, proof, launches, and blog/content marketing. It currently has the strongest marketplace screenshot coverage and a clear product-evaluation journey.

Customise it with pricing, comparison, calculators, docs-onboarding content, demo-request forms, logo/proof sections, FAQs, Blog, Form Builder, Document Lifecycle, and Content Sections. Keep the theme focused on activation, trial/demo intent, and product clarity rather than generic startup marketing.

## Recommended Fix Order

1. Align central docs with manifest v3, the current catalogue, Agency's premium tier, and the screenshot-runner contract. Done in this review.
2. Add or remove the missing Foundation `foundation-theme-header-area.png` screenshot entry.
3. Promote at least five useful `capell.json.marketplace.screenshots` entries for every premium theme, using committed route-backed PNGs where available.
4. Standardise `docs/screenshots.json` `required` flags now that route-backed captures exist for the older themes.
5. Add one mobile capture for each premium theme's primary conversion route.
6. Strengthen Education, Nonprofit, and Portfolio first-viewport screenshots so their premium value is visible before scrolling.

## Verification Notes

Useful commands for this review class:

```bash
find packages -maxdepth 2 -name capell.json | rg '/(foundation-theme|theme-)'
find packages -path '*/docs/screenshots.json' | rg '/(foundation-theme|theme-)' | sort
find packages -path '*/resources/views/*' -type f | rg '/(foundation-theme|theme-)' | xargs rg -n '(::query\(|DB::|loadMissing\(|signedRoute|filament|admin|editor|authoring|field_path|model_id)'
vendor/bin/pest packages/foundation-theme/tests packages/theme-agency/tests packages/theme-commerce/tests packages/theme-corporate/tests packages/theme-education/tests packages/theme-healthcare/tests packages/theme-inertia-bookings/tests packages/theme-inertia-bookings-react/tests packages/theme-inertia-bookings-vue/tests packages/theme-knowledge/tests packages/theme-local-services/tests packages/theme-nonprofit/tests packages/theme-portfolio/tests packages/theme-saas/tests --configuration=phpunit.xml
```

For browser/route QA, use the screenshot-runner app described in `docs/package-screenshot-automation.md`; this package repo workbench is not a reliable full admin-panel surface.
