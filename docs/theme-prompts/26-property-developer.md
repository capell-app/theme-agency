# Theme: PropertyDeveloper (`theme-property-developer`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
package skeleton, manifest rules, service-provider shape, public-output-safety
test, and the monorepo autoload steps. This prompt only describes what makes
`theme-property-developer` distinct.

## Build this

Build a refined, architectural theme for new-build property developers and
housebuilders marketing developments and plots. The mood is elevated and editorial:
charcoal and bronze on warm stone, a serif display voice, generous margins, and
crisp CGI imagery. It leads with a developments overview (scheme, location, homes,
from-price), a plot availability table (plot, beds, price, status), a
specifications block, a location guide, a CGI gallery, and a static
register-interest call to action. It extends Foundation, owns the seven section
views plus six new property sections, ships rich "Crestwood Developments" demo
data, and carries no migrations, routes, models, or settings.

## Inspiration

Borrow from premium new-build developer sites (boutique housebuilders, regeneration
schemes, design-led apartment developments). Take: the architectural hero shot,
the scheme cards with "from £X", the availability table with a status badge
(Available / Reserved / Sold), the specification accordions, the location lifestyle
guide, and the "register your interest" panel. All scheme names, plots, prices, and
copy are invented demo content — do not copy any developer's brochures.

## Gap it fills

The existing `estate-agents` theme is resale-led: search, listings, valuations,
viewings. PropertyDeveloper is its complement — **new-build, off-plan, scheme-led**.
Nothing in the current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) presents a development overview, a plot availability
table, build specifications, or a CGI gallery in an editorial register.
PropertyDeveloper owns the market-a-development / register-interest use case.

## Package identity

| Field       | Value                                                                                                                          |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------ |
| package     | `capell-app/theme-property-developer`                                                                                          |
| slug        | `theme-property-developer`                                                                                                     |
| namespace   | `Capell\ThemeStudio\PropertyDeveloper`                                                                                         |
| themeKey    | `property-developer`                                                                                                           |
| displayName | `Property Developer`                                                                                                           |
| tier        | `premium`                                                                                                                      |
| bestFit     | `["New-build developers", "Housebuilders", "Off-plan apartment schemes", "Regeneration projects", "Property marketing teams"]` |
| tags        | `["Property", "New-build", "Developer", "Architectural", "Editorial"]`                                                         |

View namespace `capell-theme-property-developer`; translation namespace
`capell-theme-property-developer`.

## Design direction

| Token              | Value         | Rationale                                                      |
| ------------------ | ------------- | -------------------------------------------------------------- |
| primaryColor       | `#1f2937`     | Charcoal — architectural, restrained primary for type and nav. |
| accentColor        | `#a07c4f`     | Bronze — premium accent for prices, status, and rules.         |
| neutralColor       | `#111827`     | Near-black for deep text and grounding.                        |
| surfaceColor       | `#f7f5f1`     | Warm stone paper so CGI imagery feels considered.              |
| foregroundColor    | `#1f2937`     | Charcoal foreground; calm and legible on stone.                |
| headingFont        | `fraunces`    | High-contrast serif display for editorial authority.           |
| bodyFont           | `inter`       | Clean sans body for spec and availability detail.              |
| spacing            | `airy`        | Generous margins signal premium and unhurried.                 |
| alignment          | `left`        | Left-aligned editorial measure for credibility.                |
| cardStyle          | `flat`        | Flat cards keep scheme tiles quiet and architectural.          |
| navigationStyle    | `minimal`     | Understated nav with a single "Register interest" action.      |
| layoutPresentation | `editorial`   | Magazine rhythm: rules, captions, generous whitespace.         |
| motionIntensity    | `subtle`      | Gentle reveals; the architecture carries the weight.           |
| mediaTreatment     | `framed`      | Framed CGI keeps imagery crisp and contained.                  |
| radius             | `sm`          | Tight, precise corners suit an architectural voice.            |
| spacing (note)     | `airy`        | Reinforced: whitespace is core to the premium feel.            |
| headingScale       | `dramatic`    | Large serif headings for development presence.                 |
| cardDensity        | `comfortable` | Room around scheme and spec cards.                             |

Typography: `fraunces` for development names and section headings, set left with a
wide measure; `inter` for availability tables, prices, and specs; bronze hairline
rules and small letter-spaced eyebrows. Motion: subtle fade-and-rise on scheme
cards and gallery tiles, no parallax. Respect `prefers-reduced-motion`.

## Sections

`includedSections`:

```
['navigation', 'hero', 'developments', 'availability', 'specifications',
 'location-guide', 'gallery', 'register-interest-cta', 'features', 'proof',
 'content-listing', 'footer']
```

NEW sections (each a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-property-developer::sections.<key>', failLoudly: true)`):

- **developments** (NEW) — render data:
  `{ heading, summary, schemes: [{ name, location, homes, fromPrice, status }] }`.
- **availability** (NEW) — render data:
  `{ heading, summary, units: [{ plot, beds, price, status }], note }` — `status`
  one of "Available" / "Reserved" / "Sold".
- **specifications** (NEW) — render data:
  `{ heading, summary, specs: [{ category, items: [string] }] }`.
- **location-guide** (NEW) — render data:
  `{ heading, points: [{ label, detail }] }`.
- **gallery** (NEW) — render data:
  `{ heading, summary, images: [{ caption }], note }` — captions describe CGI/
  illustrative views.
- **register-interest-cta** (NEW) — render data:
  `{ heading, summary, primaryLabel, secondaryLabel, note }` — STATIC prompt; no
  form submission, no DB queries.

`features`, `proof`, `content-listing`, `navigation`, `hero`, `footer` reuse the
standard typed Data objects.

## Beta data (demo profile)

Seed via `Install PropertyDeveloper ThemeDemoAction` →
`ThemeDemoPageInstaller::run(...)` plus a profile entry. All copy original.

**Brand:** Crestwood Developments — "Considered homes in places worth living."

**Hero:** heading "Homes designed for the way you actually live." Summary: "We
build characterful new homes and apartments in well-connected places, with the
specification right and the detail considered. Explore our current developments
and register your interest to hear about new releases first."

**Features (4 cards — title / summary / type):**

1. Design-Led Throughout — "Architect-designed layouts with light, storage, and
   flow thought through." / `feature`.
2. Energy-Efficient — "Low running costs as standard, with high EPC ratings across
   the range." / `feature`.
3. Help to Buy & Part-Exchange — "Flexible ways to move, including part-exchange
   on selected plots." / `service`.
4. 10-Year Warranty — "Every home comes with a structural warranty for peace of
   mind." / `service`.

**Proof (3 metrics — metric / name / quote):**

1. "1,200+" — homes delivered — "Crestwood actually finish what they start." —
   Cllr R. Ahmed.
2. "9.2 / 10" — reservation satisfaction — "The buying process was the easiest
   part of moving." — The Patel family.
3. "Award-winning" — regional housebuilder 2025 — "Quality you can see in the
   detail." — Northern Homes Review.

**Developments (schemes — name / location / homes / fromPrice / status):**

- Mill Quarter — Leeds — 84 homes — from £325,000 — status "Now selling".
- Harbour Reach — Bristol — 56 apartments — from £410,000 — status "Now selling".
- Foxglove Meadow — York — 42 homes — from £289,000 — status "Coming soon".
- Granary Yard — Manchester — 120 apartments — from £255,000 — status "Final
  phase".

**Availability (units — plot / beds / price / status, with note):**

- Plot 12 — 3 bed — £342,000 — Available.
- Plot 14 — 3 bed — £349,000 — Reserved.
- Plot 18 — 4 bed — £415,000 — Available.
- Plot 21 — 2 bed — £298,000 — Sold.
- Plot 27 — 4 bed — £429,000 — Available.
- Note: "Availability and prices are correct at time of publication and may
  change. Computer-generated images are indicative."

**Specifications (specs — category / items):**

- Kitchen — ["Integrated appliances", "Quartz worktops", "Soft-close units",
  "Under-cabinet lighting"].
- Bathrooms — ["Porcelanosa tiling", "Thermostatic showers", "Heated towel
  rails", "LED mirrors"].
- Heating & Energy — ["Air-source heat pump", "Underfloor heating to ground
  floor", "EV charging point", "Solar-ready roof"].
- Finishes — ["Engineered oak flooring", "Fitted wardrobes to principal bedroom",
  "Smart doorbell", "Turfed rear garden"].

**Location guide (points):**

- "Connections" — "12 minutes to the city centre; mainline station within a mile."
- "Schools" — "Two Ofsted 'Outstanding' primaries within walking distance."
- "Green space" — "Bordered by 40 acres of restored parkland and river path."
- "Everyday" — "Independent cafés, a weekly market, and a new health centre on the
  doorstep."

**Gallery (images — caption, with note):**

- "Street scene — Mill Quarter (CGI)".
- "Show home kitchen — indicative finish".
- "Landscaped courtyard — Harbour Reach (CGI)".
- "Principal bedroom — indicative layout".
- Note: "All images are computer-generated or indicative and may differ from the
  finished homes."

**Register-interest CTA (STATIC):** heading "Be first to hear about new releases."
Summary: "Register your interest and our sales team will keep you posted on plot
releases, pricing, and launch events." primaryLabel "Register your interest",
secondaryLabel "Download the brochure", note: "Registering opens a request — no
obligation, and you can unsubscribe any time."

**Directory sample (content-listing spotlight/gallery/pathways):** 3 entries —
"Final plots released at Granary Yard", "A buyer's guide to reserving off-plan",
"Inside the Mill Quarter show home". Each a one-line summary.

**Detail sample:** A development detail page for "Mill Quarter, Leeds" — overview,
from-price, available plots, specification highlights, location summary, and a
"Register your interest" CTA.

## Build steps

1. Scaffold `packages/theme-property-developer/` per shared-contract §2 (copy
   `packages/theme-saas` and rename identity).
2. Write `capell.json` (v3, §3): `themeKey: property-developer`,
   `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`, demo command
   `capell:theme-property-developer-demo` with
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-property-developer", "theme-property-developer-frontend"]`,
   `security.publicOutput` true / `riskTier: low`,
   `cacheTags: ["theme-property-developer"]`, health check
   `theme-property-developer.package-health`, the `admin-page` contribution,
   `database` all false.
3. Write `PropertyDeveloperThemeServiceProvider` (§4): package registration in
   `register()`; `boot(ThemeRegistry)` registers the demo command, gates on
   installed, loads translations + views, registers CSS import + Blade source,
   registers the page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the `includedSections` above and one
   "Crestwood" preset carrying the token table values; `extends: 'default'`,
   `runtime: FrontendRuntime::Blade`.
5. Register a `ViewSectionRenderer` per section key including the six new keys.
6. Build `PropertyDeveloperThemePageAdapter` mapping `meta.theme_demo.render_data`
   into the typed section data and the new `developments` / `availability` /
   `specifications` / `location-guide` / `gallery` / `register-interest-cta`
   shapes, all query-free. The register-interest section must render static markup
   only.
7. Add `Install PropertyDeveloper ThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'property-developer', 'PropertyDeveloper')`,
   a `DemoCommand` for `capell:theme-property-developer-demo`, and a `profile()`
   entry with all the copy.
8. Write `page.blade.php` (skip link, brand tokens inline, `data-capell-theme`,
   `{!! $content !!}`), thin `livewire/page/page.blade.php`
   (`RenderCurrentThemePageAction::run()`), and one Blade per customised + new
   section. Use `@frontendAsset('css/theme-property-developer.css')`.
9. Add `resources/css/theme-property-developer.css` (warm stone surface,
   charcoal/bronze accents, serif rhythm), `resources/lang/en/generic.php`, and the
   boost guideline view.
10. Add `Theme PropertyDeveloper HealthCheck` and the manifest contribution.
11. Mirror autoload in **both** `composer.json` and `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard tests: `ManifestRequirementsTest`,
`PropertyDeveloperThemeDefinitionTest`, `PropertyDeveloperThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`,
`Theme PropertyDeveloper HealthCheck` feature test.

Theme-specific assertions:

1. The adapter builds a `developments` section whose `schemes` carry `location`,
   `homes`, and `fromPrice`; assert "Mill Quarter / Leeds / from £325,000".
2. `availability` renders units with a `status` constrained to
   Available/Reserved/Sold and includes the "indicative" prices note.
3. `register-interest-cta` is static — rendered Blade contains no `<form`
   submission, no `wire:`, and no DB query.
4. `PublicOutputSafetyTest` confirms no property Blade or lang string leaks the
   package name, `authoring`, `Filament`, `signed`, `data-field`, `data-model`,
   or any DB query.

## Verification

```bash
vendor/bin/pest packages/theme-property-developer/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
