# Theme: TravelTourism (`theme-travel-tourism`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
package skeleton, manifest rules, service-provider shape, public-output-safety
test, and the monorepo autoload steps. This prompt only describes what makes
`theme-travel-tourism` distinct.

## Build this

Build an immersive, wanderlust-led theme for boutique tour operators, travel
agencies, and destination brands. The mood is bright and aspirational: ocean-teal
and sunset-amber on a soft off-white, full-bleed destination imagery, and a
priced itinerary catalogue. It leads with a destination gallery, day-by-day
itineraries (duration, from-price, highlights), signature experiences, a practical
travel-info block, traveller reviews, and an enquiry call to action. It extends
Foundation, owns the seven section views plus six new travel sections, ships rich
"Meridian Travel" demo data, and carries no migrations, routes, models, or
settings.

## Inspiration

Borrow from boutique tour-operator and curated-travel sites (small-group
adventure brands, luxury slow-travel operators, destination marketing sites).
Take: the edge-to-edge hero of a single dream location, the itinerary cards with a
"from £X" price and a highlights list, the experience tiles, the "know before you
go" practical strip, and the warm traveller testimonials. Invent all destinations
phrasing, trip names, and copy — do not lift any operator's wording.

## Gap it fills

Against the current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas), there is no travel/tourism vertical. `commerce` is
product-catalogue led; `restaurant` is single-venue hospitality; `agency` is
creative-studio. None present priced multi-day itineraries, a destination gallery,
experience tiles, or a travel-info block. TravelTourism owns the
plan-a-trip / send-an-enquiry use case.

## Package identity

| Field       | Value                                                                                                                   |
| ----------- | ----------------------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-travel-tourism`                                                                                       |
| slug        | `theme-travel-tourism`                                                                                                  |
| namespace   | `Capell\ThemeStudio\TravelTourism`                                                                                      |
| themeKey    | `travel-tourism`                                                                                                        |
| displayName | `Travel & Tourism`                                                                                                      |
| tier        | `premium`                                                                                                               |
| bestFit     | `["Tour operators", "Travel agencies", "Destination brands", "Boutique adventure travel", "Honeymoon & luxury travel"]` |
| tags        | `["Travel", "Tourism", "Itineraries", "Immersive", "Adventure"]`                                                        |

View namespace `capell-theme-travel-tourism`; translation namespace
`capell-theme-travel-tourism`.

## Design direction

| Token              | Value         | Rationale                                                |
| ------------------ | ------------- | -------------------------------------------------------- |
| primaryColor       | `#0d9488`     | Ocean teal — calm, trustworthy primary for nav and CTAs. |
| accentColor        | `#f59e0b`     | Sunset amber — warm accent for prices and highlights.    |
| neutralColor       | `#0f1f1c`     | Deep forest-teal ink for text grounding.                 |
| surfaceColor       | `#f8faf9`     | Soft off-white so destination imagery stays the star.    |
| foregroundColor    | `#0f1f1c`     | Dark teal foreground; legible and on-brand.              |
| headingFont        | `sora`        | Modern geometric voice for confident place names.        |
| bodyFont           | `inter`       | Neutral, readable companion for itinerary detail.        |
| spacing            | `balanced`    | Roomy but content-rich for catalogue browsing.           |
| alignment          | `left`        | Left-aligned for scannable itinerary lists.              |
| cardStyle          | `elevated`    | Raised cards lift trip imagery off the page.             |
| navigationStyle    | `prominent`   | Persistent bar with a standout "Plan a trip" action.     |
| layoutPresentation | `immersive`   | Full-bleed hero and galleries pull the user in.          |
| motionIntensity    | `subtle`      | Gentle reveals; let photography do the work.             |
| mediaTreatment     | `framed`      | Framed media keeps gallery tiles crisp and consistent.   |
| radius             | `lg`          | Soft-large corners read modern and friendly.             |
| headingScale       | `balanced`    | Strong but not theatrical; place names carry the drama.  |
| cardDensity        | `comfortable` | Breathing room around itinerary and experience cards.    |

Typography: `sora` for destination and trip headings with generous tracking;
`inter` for itinerary copy, durations, and prices; amber price chips and small
eyebrow labels. Motion: subtle fade-and-rise on gallery tiles and itinerary cards,
a soft scale on hover, no autoplay carousels. Respect `prefers-reduced-motion`.

## Sections

`includedSections`:

```
['navigation', 'hero', 'destination-gallery', 'itineraries', 'experiences',
 'travel-info', 'reviews', 'features', 'proof', 'content-listing',
 'enquiry-cta', 'footer']
```

NEW sections (each a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-travel-tourism::sections.<key>', failLoudly: true)`):

- **destination-gallery** (NEW) — render data:
  `{ heading, summary, destinations: [{ name, region, blurb }] }`.
- **itineraries** (NEW) — render data:
  `{ heading, summary, trips: [{ name, days, fromPrice, region, highlights: [string] }] }`.
- **experiences** (NEW) — render data:
  `{ heading, summary, experiences: [{ name, description }] }`.
- **travel-info** (NEW) — render data:
  `{ heading, points: [{ label, detail }] }`.
- **reviews** (NEW) — render data:
  `{ heading, summary, reviews: [{ quote, name, trip }] }`.
- **enquiry-cta** (NEW) — render data:
  `{ heading, summary, primaryLabel, secondaryLabel, note }` — presentational
  enquiry prompt; no form submission, no DB queries.

`features`, `proof`, `content-listing`, `navigation`, `hero`, `footer` reuse the
standard typed Data objects.

## Beta data (demo profile)

Seed via `Install TravelTourism ThemeDemoAction` →
`ThemeDemoPageInstaller::run(...)` plus a profile entry. All copy original.

**Brand:** Meridian Travel — "Small-group journeys to the places worth the
distance."

**Hero:** heading "Go further, slower." Summary: "We design small-group and
tailor-made journeys for travellers who want more than a checklist — local guides,
honest pacing, and time to actually be somewhere. Tell us where you're dreaming
of and we'll build the trip around you."

**Features (4 cards — title / summary / type):**

1. Local Guides Only — "Every journey is led by guides who live where you're
   travelling, not flown-in staff." / `service`.
2. Small Groups — "Maximum twelve travellers, so you're never herded and always
   heard." / `feature`.
3. Honest Pacing — "Itineraries with breathing room — no 5am starts disguised as
   'highlights'." / `feature`.
4. Fully Tailor-Made — "Prefer your own dates and pace? We'll design a private
   version of any trip." / `service`.

**Proof (3 metrics — metric / name / quote):**

1. "4.9 / 5" — 1,100+ traveller reviews — "The best-organised trip we've ever
   taken." — The Okafor family.
2. "32 countries" — across six continents — "Meridian gets you behind the
   postcard." — Lena V.
3. "70%" — travellers who book again — "We've done three trips with them now." —
   Sam & Rory.

**Itineraries (name / days / fromPrice / region / highlights):**

- Patagonia Explorer — 10 days — from £3,200 — South America — highlights:
  ["Torres del Paine W-trek", "Grey Glacier boat day", "Estancia asado evening",
  "Guanaco-rich steppe drive"].
- Kyoto & Coast — 7 days — from £2,450 — Japan — highlights:
  ["Temple morning in Higashiyama", "Tea ceremony with a local host",
  "Coastal ryokan and onsen", "Kyoto food-lane walk"].
- Atlas & Sahara — 8 days — from £1,980 — Morocco — highlights:
  ["High Atlas village stay", "Camel trek to a desert camp", "Stargazing dinner",
  "Marrakech medina with a guide"].
- Iceland Ring — 9 days — from £2,760 — Iceland — highlights:
  ["South-coast waterfalls", "Glacier-lagoon zodiac", "Hot-spring soak",
  "Aurora watch nights (Sep–Mar)"].

**Destinations:**

- Patagonia — South America — "Wind, ice, and the widest skies you'll ever stand
  under."
- Kyoto — Japan — "Temples, tea, and quiet lanes between the crowds."
- Morocco — North Africa — "Mountains, medinas, and the silence of the dunes."
- Iceland — North Atlantic — "Waterfalls, lagoons, and the northern lights."

**Experiences:**

- Guided Photo Walks — "Golden-hour walks with a local photographer."
- Cooking With Locals — "Hands-on meals in family kitchens, not restaurants."
- Slow Rail Days — "Scenic train legs built into the route on purpose."
- Conservation Visits — "Meet the people protecting the places you've come to see."

**Travel info (points):**

- "Best time to go" — "Most trips run Mar–Nov; Iceland aurora trips run Sep–Mar."
- "What's included" — "Guides, accommodation, listed activities, and most meals."
- "Flights" — "Booked separately, or we can arrange them on request."
- "Travel insurance" — "Required for all journeys; we can recommend providers."

**Reviews:**

- "We saw glaciers calving and ate the best meal of our lives. Flawless." — Priya
  & Tom — Patagonia Explorer.
- "Our guide made Kyoto feel like ours, not a tour group's." — Marcus L. — Kyoto
  & Coast.
- "The desert night under the stars is something I'll never forget." — Aisha R. —
  Atlas & Sahara.

**Enquiry CTA:** heading "Where should we take you?" Summary: "Send us a few lines
about your dates, pace, and dreams. A real travel designer — not a chatbot — will
reply within two working days." primaryLabel "Start an enquiry", secondaryLabel
"Browse all journeys", note "No deposit needed to start planning."

**Directory sample (content-listing spotlight/gallery/pathways):** 3 entries —
"New for 2026: Faroe Islands", "Best journeys for first-time adventurers",
"Tailor-made vs small-group: which suits you". Each a one-line summary.

**Detail sample:** An itinerary detail page for "Patagonia Explorer" — overview,
day-by-day outline, what's included, from-price, and a "Enquire about this trip"
CTA.

(Prices are illustrative "from" guides; add a note: "From-prices are per person,
twin-share, and vary by season and group size.")

## Build steps

1. Scaffold `packages/theme-travel-tourism/` per shared-contract §2 (copy
   `packages/theme-saas` and rename identity).
2. Write `capell.json` (v3, §3): `themeKey: travel-tourism`,
   `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`, demo command
   `capell:theme-travel-tourism-demo` with
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-travel-tourism", "theme-travel-tourism-frontend"]`,
   `security.publicOutput` true / `riskTier: low`,
   `cacheTags: ["theme-travel-tourism"]`, health check
   `theme-travel-tourism.package-health`, the `admin-page` contribution,
   `database` all false.
3. Write `TravelTourismThemeServiceProvider` (§4): package registration in
   `register()`; `boot(ThemeRegistry)` registers the demo command, gates on
   installed, loads translations + views, registers CSS import + Blade source,
   registers the page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the `includedSections` above and one
   "Meridian" preset carrying the token table values; `extends: 'default'`,
   `runtime: FrontendRuntime::Blade`.
5. Register a `ViewSectionRenderer` per section key including the six new keys.
6. Build `TravelTourismThemePageAdapter` mapping `meta.theme_demo.render_data`
   into the typed section data and the new `destination-gallery` / `itineraries` /
   `experiences` / `travel-info` / `reviews` / `enquiry-cta` shapes, query-free.
7. Add `Install TravelTourism ThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'travel-tourism', 'TravelTourism')`, a
   `DemoCommand` for `capell:theme-travel-tourism-demo`, and a `profile()` entry
   with all the copy.
8. Write `page.blade.php` (skip link, brand tokens inline, `data-capell-theme`,
   `{!! $content !!}`), thin `livewire/page/page.blade.php`
   (`RenderCurrentThemePageAction::run()`), and one Blade per customised + new
   section. Use `@frontendAsset('css/theme-travel-tourism.css')`.
9. Add `resources/css/theme-travel-tourism.css` (off-white surface, teal/amber
   accents), `resources/lang/en/generic.php`, and the boost guideline view.
10. Add `Theme TravelTourism HealthCheck` and the manifest contribution.
11. Mirror autoload in **both** `composer.json` and `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard tests: `ManifestRequirementsTest`, `TravelTourismThemeDefinitionTest`,
`TravelTourismThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`Theme TravelTourism HealthCheck` feature test.

Theme-specific assertions:

1. The adapter builds an `itineraries` section whose `trips` carry `days`,
   `fromPrice`, and a non-empty `highlights` array; assert "Patagonia Explorer /
   10 days / from £3,200".
2. `destination-gallery` renders at least four destinations each with `region`.
3. `enquiry-cta` is presentational — rendered Blade contains no `<form`
   submission, no `wire:`, and no DB query.
4. `PublicOutputSafetyTest` confirms no travel Blade or lang string leaks the
   package name, `authoring`, `Filament`, `signed`, `data-field`, `data-model`,
   or any DB query.

## Verification

```bash
vendor/bin/pest packages/theme-travel-tourism/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
