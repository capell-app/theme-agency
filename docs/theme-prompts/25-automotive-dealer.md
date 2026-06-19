# Theme: AutomotiveDealer (`theme-automotive-dealer`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
package skeleton, manifest rules, service-provider shape, public-output-safety
test, and the monorepo autoload steps. This prompt only describes what makes
`theme-automotive-dealer` distinct.

## Build this

Build a sleek, performance-led theme for car dealerships, used-car retailers, and
automotive specialists. The mood is dark and confident: performance-red and
electric-blue on near-black, large model imagery, and a clean inventory grid. It
leads with a featured-inventory grid (make, model, year, price, mileage), a static
finance calculator panel, a models showcase, a static service-booking prompt, a
trade-in call to action, and a dealership-info block (hours, location). It extends
Foundation, owns the seven section views plus six new automotive sections, ships
rich "Apex Motors" demo data, and carries no migrations, routes, models, or
settings.

## Inspiration

Borrow from `legalshowplates` (the automotive-niche tab) and modern dealership
sites (premium used-car retailers, manufacturer model pages). Take: the dark
spec-sheet aesthetic, the inventory card with price and mileage badges, the model
showcase tiles with a tagline, the finance illustration panel, and the
"book a service" / "value my trade-in" calls to action. All inventory, prices, and
copy are invented demo content — do not copy any dealer's listings.

## Gap it fills

Against the current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas), there is no automotive retail vertical. `commerce`
is general product catalogue and image-led but not spec-and-price-per-vehicle;
`local-services` is trades and quotes; `estate-agents` is property listings. None
present a vehicle inventory grid, a finance illustration, a models showcase, or a
service-booking prompt. AutomotiveDealer owns the browse-stock / enquire use case.

## Package identity

| Field       | Value                                                                                                                           |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-automotive-dealer`                                                                                            |
| slug        | `theme-automotive-dealer`                                                                                                       |
| namespace   | `Capell\ThemeStudio\AutomotiveDealer`                                                                                           |
| themeKey    | `automotive-dealer`                                                                                                             |
| displayName | `Automotive Dealer`                                                                                                             |
| tier        | `premium`                                                                                                                       |
| bestFit     | `["Car dealerships", "Used-car retailers", "Automotive specialists", "Performance & prestige cars", "Multi-franchise dealers"]` |
| tags        | `["Automotive", "Dealership", "Inventory", "Dark", "Performance"]`                                                              |

View namespace `capell-theme-automotive-dealer`; translation namespace
`capell-theme-automotive-dealer`.

## Design direction

| Token              | Value           | Rationale                                                     |
| ------------------ | --------------- | ------------------------------------------------------------- |
| primaryColor       | `#dc2626`       | Performance red — drives CTAs and price badges.               |
| accentColor        | `#0ea5e9`       | Electric blue — secondary highlight for spec chips and links. |
| neutralColor       | `#18181b`       | Charcoal for card grounding against the dark stage.           |
| surfaceColor       | `#0d0d0f`       | Sleek near-black background; the whole site runs dark.        |
| foregroundColor    | `#f4f4f5`       | Off-white body text for crisp contrast.                       |
| headingFont        | `space-grotesk` | Technical, mechanical display voice for spec confidence.      |
| bodyFont           | `inter`         | Neutral, dense-friendly companion for spec detail.            |
| spacing            | `balanced`      | Tight enough for a stock grid, never airy.                    |
| alignment          | `left`          | Left-aligned for scannable spec rows.                         |
| cardStyle          | `elevated`      | Raised cards lift vehicle imagery off the black.              |
| navigationStyle    | `prominent`     | Persistent bar with "View stock" and "Book a service".        |
| layoutPresentation | `immersive`     | Full-bleed hero and inventory band for showroom feel.         |
| motionIntensity    | `subtle`        | Restrained reveals; the cars carry the drama.                 |
| mediaTreatment     | `framed`        | Framed media keeps vehicle photography sharp.                 |
| radius             | `md`            | Tight, modern corners suit the technical voice.               |
| headingScale       | `dramatic`      | Oversized headings for showroom presence.                     |
| cardDensity        | `comfortable`   | Room around inventory and model cards.                        |

Typography: `space-grotesk` at dramatic scale for the hero and model names;
uppercase spec eyebrows; `inter` for mileage, price, and finance figures. Motion:
subtle fade-and-rise on inventory cards, a soft lift on hover, no autoplay video.
Respect `prefers-reduced-motion`.

## Sections

`includedSections`:

```
['navigation', 'hero', 'inventory', 'finance-calculator', 'models-showcase',
 'service-booking', 'trade-in-cta', 'dealership-info', 'features', 'proof',
 'content-listing', 'footer']
```

NEW sections (each a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-automotive-dealer::sections.<key>', failLoudly: true)`):

- **inventory** (NEW) — render data:
  `{ heading, summary, vehicles: [{ make, model, year, price, mileage, fuel, badge }] }`.
- **finance-calculator** (NEW) — render data:
  `{ heading, summary, inputs: { deposit, term, apr }, note }` — STATIC
  illustration only; renders example figures, no live calculation, no DB queries.
- **models-showcase** (NEW) — render data:
  `{ heading, summary, models: [{ name, tagline }] }`.
- **service-booking** (NEW) — render data:
  `{ heading, summary, services: [string], primaryLabel, note }` — STATIC prompt;
  no form submission, no DB queries.
- **trade-in-cta** (NEW) — render data:
  `{ heading, summary, primaryLabel, secondaryLabel, note }`.
- **dealership-info** (NEW) — render data:
  `{ heading, address, phone, hours: [{ days, open }] }`.

`features`, `proof`, `content-listing`, `navigation`, `hero`, `footer` reuse the
standard typed Data objects.

## Beta data (demo profile)

Seed via `Install AutomotiveDealer ThemeDemoAction` →
`ThemeDemoPageInstaller::run(...)` plus a profile entry. All copy original.

**Brand:** Apex Motors — "Prestige and performance, prepared properly."

**Hero:** heading "Drive something you'll look back at." Summary: "A hand-picked
selection of prestige and performance cars, every one inspected, prepared, and
warrantied. Reserve online, view in our showroom, and drive away the same week."

**Features (4 cards — title / summary / type):**

1. 142-Point Inspection — "Every car is mechanically and cosmetically prepared
   before it reaches the floor." / `service`.
2. Nationwide Delivery — "We'll deliver your car to your door anywhere in the UK."
   / `feature`.
3. Tailored Finance — "PCP, HP, and balloon options arranged across multiple
   lenders." / `service`.
4. Part-Exchange Welcome — "Drive in, drive out — we take care of the swap." /
   `feature`.

**Proof (3 metrics — metric / name / quote):**

1. "4.8 / 5" — 920 verified reviews — "Slickest car-buying experience I've had."
2. "12-month" — warranty as standard — "Everything was exactly as described." —
   Daniel O.
3. "Same-week" — average delivery — "Reserved Monday, on my drive by Friday." —
   Hana K.

**Inventory (vehicles — make / model / year / price / mileage / fuel / badge):**

- Audi — RS6 Avant — 2023 — £92,500 — 8,400 mi — Petrol — badge "Reserved-fast".
- BMW — M3 Competition — 2022 — £64,950 — 14,200 mi — Petrol — badge "Just in".
- Porsche — 911 Carrera — 2021 — £88,000 — 11,800 mi — Petrol — badge "Low miles".
- Tesla — Model 3 Performance — 2023 — £39,750 — 9,600 mi — Electric — badge
  "EV".
- Mercedes-Benz — C220d AMG Line — 2022 — £31,495 — 22,300 mi — Diesel — badge
  "Value".
- Land Rover — Defender 110 — 2023 — £71,900 — 12,100 mi — Diesel — badge
  "Family".

**Finance calculator (STATIC illustration):** inputs example deposit £5,000, term
48 months, APR 9.9%. Note: "Figures are for illustration only and are not a
finance quote. Finance is subject to status and affordability checks. Apex Motors
is a credit broker, not a lender."

**Models showcase (name / tagline):**

- Performance Saloons — "Everyday usable, weekend thrilling."
- Prestige SUVs — "Command the road and the school run."
- Electric & Hybrid — "Future-proof without the compromise."
- Modern Classics — "Cars that hold their value and their appeal."

**Service booking (STATIC):** services ["MOT", "Full service", "Brakes & tyres",
"Diagnostics", "Air-con regas"]. primaryLabel "Book a service". note: "Booking
opens a request — our service team confirms your slot by phone."

**Trade-in CTA:** heading "Your car could be worth more than you think." Summary:
"Get an honest part-exchange figure and put it straight toward your next car."
primaryLabel "Value my car", secondaryLabel "View stock", note: "Valuations are
indicative and confirmed on inspection."

**Dealership info:** address "Unit 4, Riverside Trade Park, Sheffield S9 2XX";
phone "0114 555 0142"; hours [{Mon–Fri, "9:00–18:00"}, {Saturday,
"9:00–17:00"}, {Sunday, "10:00–16:00"}].

**Directory sample (content-listing spotlight/gallery/pathways):** 3 entries —
"Just arrived: 12 new cars this week", "Buying guide: PCP vs HP explained",
"How our 142-point inspection works". Each a one-line summary.

**Detail sample:** A vehicle detail page for "Audi RS6 Avant 2023" — overview,
key specs, price, mileage, finance illustration link, and a "Reserve this car"
CTA.

## Build steps

1. Scaffold `packages/theme-automotive-dealer/` per shared-contract §2 (copy
   `packages/theme-saas` and rename identity).
2. Write `capell.json` (v3, §3): `themeKey: automotive-dealer`,
   `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`, demo command
   `capell:theme-automotive-dealer-demo` with
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-automotive-dealer", "theme-automotive-dealer-frontend"]`,
   `security.publicOutput` true / `riskTier: low`,
   `cacheTags: ["theme-automotive-dealer"]`, health check
   `theme-automotive-dealer.package-health`, the `admin-page` contribution,
   `database` all false.
3. Write `AutomotiveDealerThemeServiceProvider` (§4): package registration in
   `register()`; `boot(ThemeRegistry)` registers the demo command, gates on
   installed, loads translations + views, registers CSS import + Blade source,
   registers the page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the `includedSections` above and one
   "Apex" preset carrying the token table values; `extends: 'default'`,
   `runtime: FrontendRuntime::Blade`.
5. Register a `ViewSectionRenderer` per section key including the six new keys.
6. Build `AutomotiveDealerThemePageAdapter` mapping `meta.theme_demo.render_data`
   into the typed section data and the new `inventory` / `finance-calculator` /
   `models-showcase` / `service-booking` / `trade-in-cta` / `dealership-info`
   shapes, all query-free. The finance and service sections must render static
   illustration markup only.
7. Add `Install AutomotiveDealer ThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'automotive-dealer', 'AutomotiveDealer')`,
   a `DemoCommand` for `capell:theme-automotive-dealer-demo`, and a `profile()`
   entry with all the copy.
8. Write `page.blade.php` (skip link, brand tokens inline, `data-capell-theme`,
   `{!! $content !!}`), thin `livewire/page/page.blade.php`
   (`RenderCurrentThemePageAction::run()`), and one Blade per customised + new
   section. Use `@frontendAsset('css/theme-automotive-dealer.css')`.
9. Add `resources/css/theme-automotive-dealer.css` (near-black surface, red/blue
   accents), `resources/lang/en/generic.php`, and the boost guideline view.
10. Add `Theme AutomotiveDealer HealthCheck` and the manifest contribution.
11. Mirror autoload in **both** `composer.json` and `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard tests: `ManifestRequirementsTest`,
`AutomotiveDealerThemeDefinitionTest`, `AutomotiveDealerThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`,
`Theme AutomotiveDealer HealthCheck` feature test.

Theme-specific assertions:

1. The adapter builds an `inventory` section whose `vehicles` carry `make`,
   `model`, `year`, `price`, and `mileage`; assert "Audi / RS6 Avant / 2023 /
   £92,500 / 8,400 mi".
2. `finance-calculator` renders STATIC illustration markup only — no `<form`
   submission, no `wire:`, no `<script>` performing calculation, and the credit-
   broker note is present.
3. `service-booking` is a static prompt — no `<form` submission and no DB query.
4. `PublicOutputSafetyTest` confirms no automotive Blade or lang string leaks the
   package name, `authoring`, `Filament`, `signed`, `data-field`, `data-model`,
   or any DB query.

## Verification

```bash
vendor/bin/pest packages/theme-automotive-dealer/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
