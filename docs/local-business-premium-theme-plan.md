# Local Business Premium Theme Plan

Use this plan when expanding Capell's first-party theme catalogue into premium local-business verticals. The goal is fewer, stronger themes that are recognisable from screenshots and sell a real workflow, not a set of colour-swapped service websites.

## Recommendation

Build five premium local-business themes:

| Theme                      | Package key           | Buyer                                                  | Primary workflow                                                                                          | Why it deserves its own theme                                                                                       |
| -------------------------- | --------------------- | ------------------------------------------------------ | --------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| Estate Agents & Lettings   | `theme-estate-agents` | Estate agents, lettings teams, property consultants    | Property search, featured listings, valuation CTA, local guides, agent proof, viewing requests            | Property search creates a distinct interactive product surface that `local-services` should not own.                |
| Equestrian / Riding School | `theme-equestrian`    | Riding schools, yards, instructors, equestrian centres | Lesson calendar, clinics, camps, rider-level paths, instructor and horse profiles, booking enquiry        | Events-calendar-backed lessons and clinics make this a strong vertical rather than a generic class provider.        |
| Restaurant & Venue         | `theme-restaurant`    | Restaurants, cafes, pubs, private dining venues        | Menu browsing, reservations, opening times, private dining, events, reviews, offers                       | Food/menu presentation and booking intent are visually distinct and commercially easy to understand.                |
| Salon / Hair & Beauty      | `theme-salon`         | Hairdressers, barbers, beauty salons, spas             | Treatment menu, stylist profiles, availability prompts, before/after proof, reviews, appointment requests | Salon buyers need visual proof, services, staff trust, and repeat booking cues that differ from quote-led services. |
| Professional Practice      | `theme-practice`      | Solicitors, accountants, advisers, local consultants   | Practice areas, credentials, compliance/trust proof, consultation routing, resources, client intake       | Solicitors and accountants should be one premium practice lane. Separate themes would overlap too much.             |

Do not create separate `theme-solicitors` and `theme-accountants` packages unless a later product decision gives one of them a genuinely different content model, compliance workflow, or client journey. Start with `theme-practice` and make its presets/copy flexible enough for both.

## Themes To Avoid For Now

Avoid adding these as standalone themes until the first five are excellent:

- Trades / home services: keep this inside `theme-local-services` unless it grows a dedicated emergency dispatch or quote-routing workflow.
- Venue / accommodation: overlaps with Restaurant unless availability, rooms, and packages become a first-class booking flow.
- Fitness / studio: viable later, but it overlaps Equestrian's event/class pattern and Salon's appointment pattern until bookings mature.
- Legal-only and accounting-only: both belong in `theme-practice` at first.

## Non-Overlap Rules

Every new premium theme must pass this before package creation:

- A buyer can recognise the industry from the homepage screenshot without reading the theme name.
- The theme owns one workflow that `theme-local-services` does not own.
- The homepage anatomy, listing cards, proof treatment, and conversion path are structurally different from the other local-business themes.
- Any live data behaviour lives in an existing or new companion package, not in public Blade.
- The theme stays a presentation package: no migrations, models, admin resources, or route ownership by default.

## Livewire And Optional Package Strategy

Use Livewire only when server-side interaction improves the visitor workflow. Static sections should stay Blade.

Required Livewire standards:

- stable `wire:key` values for looped elements and nested components
- visible loading state using Livewire loading attributes or equivalent component state
- empty state for no matches or no availability
- package-missing fallback that still looks intentional in screenshots
- focused component tests and route-backed screenshot coverage
- no admin/editor metadata, field paths, model IDs, permissions, package internals, or signed URLs in public HTML

Use existing packages first:

- `capell-app/events`: Equestrian lessons, clinics, camps, restaurant events, private dining events, and later fitness classes.
- `capell-app/form-builder`: reservation requests, rider assessments, appointment requests, valuation enquiries, consultation intake, and contact forms.
- `capell-app/newsletter`: restaurant offers, salon offers, venue updates, and local market reports.
- `capell-app/search` or a future property/listing companion package: estate-agent property search and location-guide search.

## Theme Briefs

### Estate Agents & Lettings

Package: `capell-app/theme-estate-agents`

Visual direction: confident property editorial, bright photography, map/listing rhythm, premium agency proof, and strong valuation/viewing CTAs. It should not look like a corporate consultancy site.

Core sections:

- property search hero with location, price, bedrooms, status, and sale/let filters
- featured property carousel
- property listing grid with strong photography and facts
- property detail hero with gallery, key facts, agent card, and viewing CTA
- valuation CTA
- local area guide
- agent/team proof
- mortgage/lettings/resource teaser
- contact/viewing form

Livewire component:

- `PropertySearch` starts with hydrated demo/listing data and an extension contract.
- Initial implementation can search array/DTO data from render payloads.
- Later integration can swap in a `property-listings` provider without changing theme Blade.

Minimum screenshots:

- homepage with search
- property search results
- property detail/gallery
- valuation landing page
- local guide
- viewing/contact form
- empty search fallback

### Equestrian / Riding School

Package: `capell-app/theme-equestrian`

Visual direction: open-air, tactile, warm, premium but practical. It should feel like lessons, riders, yards, clinics, and events, not a generic education theme.

Core sections:

- lesson-path hero by rider level
- events calendar for lessons, clinics, pony days, camps, and shows
- instructor cards
- horse/profile cards
- facilities/stables proof
- rider assessment form CTA
- timetable/listing section
- safeguarding/safety/trust block
- testimonials and results

Livewire component:

- Prefer the existing `events` package `EventCalendar` surface where possible.
- Add theme renderers around lesson/category filters if the events package exposes the data safely.
- Booking intent should route through `form-builder` or the events registration provider, not theme-owned tables.

Minimum screenshots:

- homepage with rider-level paths
- lesson/event calendar
- clinic/event detail
- instructor/horse profile listing
- rider assessment form
- no-upcoming-events fallback

### Restaurant & Venue

Package: `capell-app/theme-restaurant`

Visual direction: high-trust hospitality, strong menu typography, food imagery, opening-hours clarity, and simple reservation intent. Avoid generic luxury gradients.

Core sections:

- menu/category browser
- reservation/private dining CTA
- opening-hours strip
- chef/story block
- reviews/social proof
- events/private dining listing
- offers/newsletter strip
- location/contact map-style section
- gallery carousel

Livewire component:

- Menu filtering can be Livewire if categories, dietary filters, or search are interactive.
- Reservation flow should use `form-builder` until a proper bookings package exists.
- Restaurant events should use `events` with graceful fallback.

Minimum screenshots:

- homepage with menu preview
- menu browser
- reservation/private dining landing page
- event/private dining listing
- gallery/reviews section
- contact/opening-hours page

### Salon / Hair & Beauty

Package: `capell-app/theme-salon`

Visual direction: polished, human, image-led, with before/after proof and appointment-first paths. It must not feel like Restaurant with different copy.

Core sections:

- treatment menu with pricing bands
- stylist/team profiles
- before/after gallery
- reviews and repeat-client proof
- appointment request CTA
- service detail section
- packages/offers
- care advice/resources
- Instagram-style gallery strip without requiring external embeds

Livewire component:

- Treatment/category filtering is useful when there are many services.
- Appointment request should use `form-builder`; later availability can integrate with a bookings package.

Minimum screenshots:

- homepage with service/stylist emphasis
- treatment menu
- stylist profile/listing
- before/after gallery
- appointment request form
- no-availability or no-matching-treatment fallback

### Professional Practice

Package: `capell-app/theme-practice`

Visual direction: local trust, clarity, professional reassurance, and conversion without looking like generic Corporate. It should feel built for client acquisition by solicitors, accountants, advisers, and consultants.

Core sections:

- practice-area/service navigation
- credential and accreditation proof
- consultation intake CTA
- team profiles
- case/client outcome summaries without overclaiming
- resource/guidance hub
- compliance/trust block
- locations/contact routes
- fee/process explainer

Livewire component:

- Intake routing can be Livewire if the visitor selects service area, urgency, and contact preference.
- Forms should use `form-builder`.
- Guidance/resources can reuse search/listing patterns rather than creating theme-owned records.

Minimum screenshots:

- homepage with practice-area routing
- practice-area listing
- professional profile/team page
- guidance/resource hub
- consultation intake form
- trust/compliance/contact page

## Implementation Order

1. `theme-estate-agents`: proves the extensible Livewire search contract and listing/detail screenshot pattern.
2. `theme-equestrian`: proves events-calendar integration for lessons, clinics, and camps.
3. `theme-restaurant`: high visual payoff, reuses events and form-builder.
4. `theme-salon`: appointment and proof-led local business, visually distinct from Restaurant.
5. `theme-practice`: commercially useful, but must be protected from collapsing into Corporate.

## Build Contract For Each Theme

Each package should include:

- `capell.json` with stable `themeKey`, `kind: "theme"`, `tier: "premium"`, and `extends: "capell-app/foundation-theme"`
- service provider registering package metadata, theme definition, page renderer, and section renderers
- standard section set: navigation, hero, features, proof, content-listing, search, pagination, form, cta, footer
- domain section set matching the theme brief
- package demo Action that seeds editable Capell content, not designed markup
- docs overview and `docs/screenshots.json`
- at least five marketplace screenshots, with extra screenshots for the theme-specific workflow
- unit tests for definition, manifest requirements, package-aware renderers, optional fallback states, and public-output safety
- route-backed screenshot tests covering homepage, listing/search, detail, contact/conversion, empty/fallback, and at least one domain-specific surface

## Acceptance Standard

A local-business theme is not premium until:

- its main workflow is interactive or deeply structured enough to justify the package
- screenshots show the domain workflow, not just generic homepage/list/contact pages
- optional package absence produces a designed fallback, not blank cards
- the theme looks different from `theme-local-services` at a structural level
- public Blade receives hydrated render data and performs no database discovery
- Livewire components have tests for default, filtered, empty, and loading-equivalent states
- package-local tests and route-backed screenshot tests pass
