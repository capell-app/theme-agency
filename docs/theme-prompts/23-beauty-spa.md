# Theme: BeautySpa (`theme-beauty-spa`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
package skeleton, manifest rules, service-provider shape, public-output-safety
test, and the monorepo autoload steps. This prompt only describes what makes
`theme-beauty-spa` distinct.

## Build this

Build a calm, luxurious spa and salon theme for day spas, beauty clinics, nail
and skincare studios, and wellness retreats. The mood is editorial and unhurried:
warm taupe and blush-mauve on a soft cream paper background, a serif display
voice, and generous whitespace. It leads with a treatment menu (durations and
prices), a practitioner roster, curated packages, gift cards, and a gentle
booking call to action. It extends Foundation, owns the seven section views plus
five new spa sections, ships rich "Lumière Spa" demo data, and carries no
migrations, routes, models, or settings.

## Inspiration

Borrow from refined day-spa and skincare-clinic sites (luxury hotel spas, premium
facial bars, editorial salon brands). Take: the menu-as-list treatment with price
and duration on a hairline rule, the soft full-width imagery, the "meet your
therapist" roster, the curated package bundles, and the gift-card panel framed as
a thoughtful present. Invent all copy, treatment names, and pricing — do not copy
any source brand's wording.

## Gap it fills

Against the current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas), there is no calm-luxury personal-care vertical.
`healthcare` is clinical and appointment-system led; `restaurant` is hospitality
but loud and food-led; `local-services` is quote-and-trades. None offer a priced
treatment menu, a practitioner roster, package bundles, or a gift-card surface in
a soft editorial register. BeautySpa owns the serene, book-a-treatment use case.

## Package identity

| Field       | Value                                                                                      |
| ----------- | ------------------------------------------------------------------------------------------ |
| package     | `capell-app/theme-beauty-spa`                                                              |
| slug        | `theme-beauty-spa`                                                                         |
| namespace   | `Capell\ThemeStudio\BeautySpa`                                                             |
| themeKey    | `beauty-spa`                                                                               |
| displayName | `Beauty & Spa`                                                                             |
| tier        | `premium`                                                                                  |
| bestFit     | `["Day spas", "Beauty clinics", "Nail & skincare studios", "Wellness retreats", "Salons"]` |
| tags        | `["Spa", "Beauty", "Wellness", "Luxury", "Editorial"]`                                     |

View namespace `capell-theme-beauty-spa`; translation namespace
`capell-theme-beauty-spa`.

## Design direction

| Token              | Value         | Rationale                                                       |
| ------------------ | ------------- | --------------------------------------------------------------- |
| primaryColor       | `#9d8567`     | Warm taupe — grounded, spa-luxe primary for headings and links. |
| accentColor        | `#b9a0b4`     | Blush-mauve — soft secondary for highlights and price chips.    |
| neutralColor       | `#2b2420`     | Deep cocoa brown for text-on-cream legibility.                  |
| surfaceColor       | `#f6f1ea`     | Soft cream paper — the calm canvas everything sits on.          |
| foregroundColor    | `#2b2420`     | Cocoa foreground; restful, never harsh black.                   |
| headingFont        | `fraunces`    | High-contrast serif display for editorial elegance.             |
| bodyFont           | `inter`       | Clean sans body keeps long menus readable.                      |
| spacing            | `airy`        | Generous whitespace signals calm and premium service.           |
| alignment          | `left`        | Left-aligned editorial measure, not centred shouting.           |
| cardStyle          | `flat`        | Flat cards keep the menu quiet and paper-like.                  |
| navigationStyle    | `minimal`     | Understated nav with a single "Book" action.                    |
| layoutPresentation | `editorial`   | Magazine rhythm: rules, captions, generous margins.             |
| motionIntensity    | `subtle`      | Gentle fades only — nothing that breaks the calm.               |
| mediaTreatment     | `framed`      | Framed imagery for treatment and interior photography.          |
| radius             | `md`          | Soft, rounded but restrained corners.                           |
| spacing (note)     | `airy`        | Reinforced: airy is core to the brand.                          |
| headingScale       | `balanced`    | Large serif, but not theatrical — refined, not loud.            |
| cardDensity        | `comfortable` | Room around menu rows and practitioner cards.                   |

Typography: `fraunces` for headings and treatment names with optical sizing, set
left with a generous measure; `inter` for body and price/duration metadata; small
caps or letter-spaced eyebrows above sections. Motion: subtle fade-and-rise on
scroll, soft hover tint on menu rows, no parallax. Respect
`prefers-reduced-motion`.

## Sections

`includedSections`:

```
['navigation', 'hero', 'treatment-menu', 'practitioners', 'packages',
 'gift-cards', 'features', 'proof', 'content-listing', 'booking-cta', 'footer']
```

NEW sections (each a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-beauty-spa::sections.<key>', failLoudly: true)`):

- **treatment-menu** (NEW) — render data:
  `{ heading, summary, groups: [{ label, treatments: [{ name, duration, price, description }] }] }`.
- **practitioners** (NEW) — render data:
  `{ heading, summary, team: [{ name, specialty, bio }] }`.
- **packages** (NEW) — render data:
  `{ heading, summary, packages: [{ name, includes: [string], price, duration }] }`.
- **gift-cards** (NEW) — render data:
  `{ heading, body, denominations: [string], note }`.
- **booking-cta** (NEW) — render data:
  `{ heading, summary, primaryLabel, secondaryLabel, hours }` — a presentational
  booking prompt; no form submission, no DB queries.

`features`, `proof`, `content-listing`, `navigation`, `hero`, `footer` reuse the
standard typed Data objects.

## Beta data (demo profile)

Seed via `Install BeautySpa ThemeDemoAction` →
`ThemeDemoPageInstaller::run(...)` plus a profile entry. All copy original.

**Brand:** Lumière Spa — "A quiet city sanctuary for skin, body, and calm."

**Hero:** heading "Slow down. You're due some care." Summary: "A boutique spa in
the old town, with skin therapists, restorative massage, and a steam suite. Treat
yourself, or someone you love, to an hour that resets everything."

**Features (4 cards — title / summary / type):**

1. Skin Therapists — "Advanced facialists who tailor every treatment to your skin,
   not a script." / `service`.
2. Steam & Relaxation Suite — "Arrive early and unwind in the steam room and
   tranquillity lounge before your treatment." / `facility`.
3. Clean, Considered Products — "Cruelty-free, fragrance-light ranges chosen for
   sensitive and city-tired skin." / `feature`.
4. Couples & Friends Rooms — "Book a double room and enjoy a treatment side by
   side." / `service`.

**Proof (3 metrics — metric / name / quote):**

1. "4.9 / 5" — 480+ reviews — "The most relaxing hour of my month, every month."
2. "12 years" — caring for the city — "Lumière got my skin back after a rough
   winter." — Hannah M.
3. "98%" — rebook within 8 weeks — "I never used to look forward to facials. Now I
   do." — Dev P.

**Treatment menu (groups with name / duration / price / description):**

- _Facials_ — Signature Facial (75 min, £95, "Deep cleanse, exfoliation, and a
  tailored mask for visibly calmer skin."); Express Glow (30 min, £45, "A quick
  reset before an event."); Advanced Renewal (90 min, £140, "Resurfacing and
  hydration for tired, congested skin.").
- _Massage_ — Swedish Massage (60 min, £75, "Classic full-body relaxation.");
  Deep Tissue (60 min, £85, "Targeted work for tension and knots."); Hot Stone
  (75 min, £95, "Warmth-led release for deep calm.").
- _Body & Hands_ — Body Polish & Wrap (60 min, £80); Luxury Manicure (45 min,
  £40); Spa Pedicure (50 min, £48).

**Practitioners:**

- Camille Roux — Lead skin therapist — "Fifteen years in advanced facials; loves
  rebalancing reactive skin."
- Noah Bennett — Sports & deep-tissue massage — "Gentle hands, serious technique."
- Aisling Doyle — Holistic & hot stone — "Brings the calm into the room with her."
- Priya Sharma — Nails & body — "Detail-obsessed; nothing leaves unfinished."

**Packages (name / includes / price / duration):**

- Half-Day Escape — includes Signature Facial, Swedish Massage, steam suite,
  light lunch — £185, 3 hr.
- Couples Retreat — includes two treatments in a double room, prosecco, steam
  suite — £260, 2.5 hr.
- Bride-to-Be — includes Advanced Renewal facial, luxury manicure, body polish —
  £210, 3 hr.

**Gift cards:** heading "The gift of an hour to themselves." Body: "Spa gift cards
arrive by post in a linen envelope or instantly by email — redeemable against any
treatment or package." Denominations: ["£50", "£75", "£100", "£150", "Any amount"].
Note: "Gift cards are valid for 12 months from purchase."

**Booking CTA:** heading "Book a moment of calm." Summary: "Choose a treatment and
a time that suits you — or call us and we'll help you plan." primaryLabel "Book
online", secondaryLabel "Call the spa", hours "Tue–Sat, 9am–7pm. Sun 10am–4pm."

**Directory sample (content-listing spotlight/gallery/pathways):** 3 entries —
"New: Vitamin-C Brightening Facial", "Our quietest hours for a deep unwind",
"How to prepare for your first treatment". Each a one-line summary.

**Detail sample:** A treatment detail page for "Signature Facial" — intro, what to
expect, aftercare, who it suits, and a "Book this treatment" CTA.

(Where before/after skin photos are implied anywhere, add an illustrative note:
"Skin photos are illustrative and shared with client consent.")

## Build steps

1. Scaffold `packages/theme-beauty-spa/` per shared-contract §2 (copy
   `packages/theme-saas` and rename identity).
2. Write `capell.json` (v3, §3): `themeKey: beauty-spa`,
   `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`, demo command
   `capell:theme-beauty-spa-demo` with `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-beauty-spa", "theme-beauty-spa-frontend"]`,
   `security.publicOutput` true / `riskTier: low`,
   `cacheTags: ["theme-beauty-spa"]`, health check
   `theme-beauty-spa.package-health`, the `admin-page` contribution, `database`
   all false.
3. Write `BeautySpaThemeServiceProvider` (§4): package registration in
   `register()`; `boot(ThemeRegistry)` registers demo command, gates on installed,
   loads translations + views, registers CSS import + Blade source, registers the
   page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the `includedSections` above and one
   "Lumière" preset carrying the token table values; `extends: 'default'`,
   `runtime: FrontendRuntime::Blade`.
5. Register a `ViewSectionRenderer` per section key including the five new keys.
6. Build `BeautySpaThemePageAdapter` mapping `meta.theme_demo.render_data` into the
   typed section data and the new `treatment-menu` / `practitioners` / `packages`
   / `gift-cards` / `booking-cta` shapes, all query-free.
7. Add `Install BeautySpa ThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'beauty-spa', 'BeautySpa')`, a `DemoCommand`
   for `capell:theme-beauty-spa-demo`, and a `profile()` entry with all the copy.
8. Write `page.blade.php` (skip link, brand tokens inline, `data-capell-theme`,
   `{!! $content !!}`), thin `livewire/page/page.blade.php`
   (`RenderCurrentThemePageAction::run()`), and one Blade per customised + new
   section. Use `@frontendAsset('css/theme-beauty-spa.css')`.
9. Add `resources/css/theme-beauty-spa.css` (cream surface, taupe/mauve accents,
   serif rhythm), `resources/lang/en/generic.php`, and the boost guideline view.
10. Add `Theme BeautySpa HealthCheck` and the manifest contribution.
11. Mirror autoload in **both** `composer.json` and `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard tests: `ManifestRequirementsTest`, `BeautySpaThemeDefinitionTest`,
`BeautySpaThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`Theme BeautySpa HealthCheck` feature test.

Theme-specific assertions:

1. The adapter builds a `treatment-menu` whose `groups` carry typed `treatments`
   with `name`, `duration`, and `price`; assert "Signature Facial / 75 min / £95"
   is present.
2. `gift-cards` exposes the `denominations` array and the 12-month validity note.
3. `booking-cta` is purely presentational — the rendered Blade contains no
   `<form` submission, no `wire:`, and no DB query.
4. `PublicOutputSafetyTest` confirms no spa Blade or lang string leaks the package
   name, `authoring`, `Filament`, `signed`, `data-field`, `data-model`, or any DB
   query.

## Verification

```bash
vendor/bin/pest packages/theme-beauty-spa/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
