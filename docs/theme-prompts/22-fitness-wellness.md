# Theme: FitnessWellness (`theme-fitness-wellness`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
package skeleton, manifest rules, service-provider shape, public-output-safety
test, and the monorepo autoload steps. This prompt only describes what makes
`theme-fitness-wellness` distinct.

## Build this

Build a high-energy, dark-mode gym and studio theme for boutique fitness clubs,
CrossFit boxes, yoga studios, and personal-training brands. The theme is built
to sell memberships and free trials: a bold immersive hero, an at-a-glance weekly
class schedule, transparent membership tiers, a trainer roster, a transformations
strip, and a facilities list — all framed by a charged lime-on-near-black palette
and dramatic display type. It extends Foundation, owns the seven section views it
customises plus five new fitness sections, ships rich "Forge Fitness" demo data,
and carries no migrations, routes, models, or settings.

## Inspiration

Borrow from modern boutique-gym and studio sites (Barry's, F45, Third Space,
Equinox-style energy). Take: the dark stage-lit hero with a single dominant verb,
the timetable-as-product treatment, the membership tier cards with a highlighted
"most popular" option, the trainer grid with specialties, and the punchy "book a
free class" call to action. Do not copy any of their copy, class names, or
imagery — invent original demo copy for "Forge Fitness".

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) has no membership-and-schedule vertical. `education`
handles enrolment journeys but reads academic and calm; `healthcare` is clinical
and appointment-led; `restaurant` is hospitality. None give a recurring weekly
timetable, membership pricing tiers with perks, a trainer roster, or a
high-intensity dark aesthetic. FitnessWellness owns the energetic
sell-a-membership use case.

## Package identity

| Field       | Value                                                                                                    |
| ----------- | -------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-fitness-wellness`                                                                      |
| slug        | `theme-fitness-wellness`                                                                                 |
| namespace   | `Capell\ThemeStudio\FitnessWellness`                                                                     |
| themeKey    | `fitness-wellness`                                                                                       |
| displayName | `Fitness & Wellness`                                                                                     |
| tier        | `premium`                                                                                                |
| bestFit     | `["Boutique gyms", "Fitness studios", "CrossFit boxes", "Personal trainers", "Yoga & wellness studios"]` |
| tags        | `["Fitness", "Gym", "Membership", "Dark", "Energetic"]`                                                  |

View namespace `capell-theme-fitness-wellness`; translation namespace
`capell-theme-fitness-wellness`.

## Design direction

| Token              | Value         | Rationale                                                 |
| ------------------ | ------------- | --------------------------------------------------------- |
| primaryColor       | `#84cc16`     | Electric lime — drives CTAs and active schedule slots.    |
| accentColor        | `#f97316`     | Warm orange — secondary energy, hover states, badges.     |
| neutralColor       | `#18181b`     | Near-black charcoal for card and section grounding.       |
| surfaceColor       | `#0f1115`     | Deep stage-lit background; the whole site runs dark.      |
| foregroundColor    | `#f4f4f5`     | Off-white body text for strong contrast on dark surfaces. |
| headingFont        | `sora`        | Geometric, athletic display voice for big verbs.          |
| bodyFont           | `inter`       | Neutral, legible companion at small sizes.                |
| spacing            | `balanced`    | Dense enough to feel energetic, not cramped.              |
| alignment          | `left`        | Left-aligned for momentum and scan speed.                 |
| cardStyle          | `elevated`    | Raised cards read like equipment plates against the dark. |
| navigationStyle    | `prominent`   | Persistent bar with a standout "Free trial" button.       |
| layoutPresentation | `immersive`   | Full-bleed hero and schedule, edge-to-edge energy.        |
| motionIntensity    | `expressive`  | Confident entrances and hover lifts suit the brand.       |
| mediaTreatment     | `framed`      | Framed media keeps gym photography crisp and contained.   |
| radius             | `lg`          | Soft-large corners feel modern and approachable.          |
| headingScale       | `dramatic`    | Oversized headings for stage presence.                    |
| cardDensity        | `comfortable` | Breathing room around tier and trainer cards.             |

Typography: `sora` at dramatic scale with tight tracking on the hero verb;
uppercase eyebrow labels above headings; `inter` body at comfortable line height.
Motion: expressive but disciplined — staggered fade-up on schedule rows, a subtle
lift and lime-glow on tier/trainer card hover, marquee-free. Respect
`prefers-reduced-motion`.

## Sections

`includedSections`:

```
['navigation', 'hero', 'class-schedule', 'membership-tiers', 'trainers',
 'transformations', 'facilities', 'features', 'proof', 'content-listing',
 'cta', 'footer']
```

NEW sections (beyond the standard seven), each rendered by a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-fitness-wellness::sections.<key>', failLoudly: true)`:

- **class-schedule** (NEW) — render data:
  `{ heading, summary, days: [{ label, classes: [{ time, name, instructor, level }] }] }`.
- **membership-tiers** (NEW) — render data:
  `{ heading, summary, tiers: [{ name, price, cadence, featured: bool, perks: [string] }], note }`.
- **trainers** (NEW) — render data:
  `{ heading, summary, trainers: [{ name, specialty, bio }] }`.
- **transformations** (NEW) — render data:
  `{ heading, summary, stories: [{ name, result, weeks }], disclaimer }` —
  the `disclaimer` must read "Individual results vary." and the view must render it.
- **facilities** (NEW) — render data:
  `{ heading, summary, items: [{ name, detail }] }`.

`features`, `proof`, `content-listing`, `cta`, `navigation`, `footer`, `hero`
reuse the standard typed Data objects.

## Beta data (demo profile)

Seed via `Install FitnessWellness ThemeDemoAction` →
`ThemeDemoPageInstaller::run(...)` plus a profile entry. All copy original.

**Brand:** Forge Fitness — "Strength club + studio, open 5am–11pm."

**Hero:** heading "Train harder. Recover smarter." Summary: "A coached strength
floor, 40+ studio classes a week, and recovery rooms under one roof in central
Leeds. Book a free trial session and feel the difference in a week."

**Features (5 cards — title / summary / type):**

1. Coached Strength Floor — "Barbell racks, plates to 50kg, and a coach on the
   floor every session to dial in your form." / `service`.
2. 40+ Weekly Classes — "Conditioning, mobility, spin and yoga across the day, all
   included in your membership." / `service`.
3. Recovery Suite — "Sauna, contrast plunge, and percussion guns to help you turn
   up again tomorrow." / `facility`.
4. InBody Scans — "Track body composition every six weeks so progress is measured,
   not guessed." / `feature`.
5. App-Based Booking — "Reserve a class in two taps and get a reminder before it
   starts." / `feature`.

**Proof (4 metrics — metric / name / quote):**

1. "−9kg in 12 weeks" — Priya N., member 14 months — "The coaches kept me
   accountable. I've never felt fitter." (pair with "Individual results vary.")
2. "4.9 / 5" — 612 Google reviews — "Cleanest kit, best classes in the city."
3. "40+" — classes every week — "There's always something on when I finish work."
4. "92%" — trial-to-member rate — "People come for the trial and stay for the
   community."

**Class schedule (weekly, abbreviated — give at least Mon/Wed/Sat fully):**

- Monday: 06:15 Power Hour (Marcus, All levels), 12:10 Express Conditioning
  (Sofia, Intermediate), 18:30 Spin 45 (Dane, All levels), 19:30 Yin Yoga (Imani,
  Beginner).
- Wednesday: 06:15 Mobility Flow (Imani, Beginner), 07:15 Strength Foundations
  (Marcus, Beginner), 18:00 HIIT 30 (Sofia, Advanced), 19:00 Spin Climb (Dane,
  Intermediate).
- Saturday: 08:00 Long & Strong (Marcus, All levels), 09:30 Vinyasa (Imani,
  All levels), 11:00 Community WOD (Sofia, All levels).
- Fill Tue/Thu/Fri/Sun with 2–3 plausible slots each in the demo.

**Membership tiers (with note):**

- Day Pass — £12, per visit — perks: full gym access, one studio class,
  recovery suite. (`featured: false`)
- Monthly — £49, per month — perks: unlimited gym, unlimited classes, recovery
  suite, app booking, one free InBody scan. (`featured: true`)
- Annual — £490, per year — perks: everything in Monthly, two months free,
  guest passes, priority class booking. (`featured: false`)
- Note: "No joining fee. Cancel any time. Prices include VAT."

**Trainers:**

- Marcus Reid — Strength & barbell coaching — "Ex-county powerlifter; obsessed
  with clean technique."
- Sofia Marchetti — Conditioning & HIIT — "Makes hard sessions feel possible."
- Imani Clarke — Yoga & mobility — "Helps members move better, in and out of the
  gym."
- Dane Whitlock — Indoor cycling — "Brings the playlist and the pace."

**Transformations (with disclaimer "Individual results vary."):**

- Tom — Lost 11kg, deadlifts 2× bodyweight — 16 weeks.
- Aisha — Ran a first half-marathon after a year off training — 20 weeks.
- Greg — Off blood-pressure medication after GP sign-off — 24 weeks.
  (Where photos are implied, add an illustrative note: "Before/after photos are
  illustrative and shared with member consent.")

**Facilities:** Free-weight strength floor; two studios; spin room (24 bikes);
sauna & contrast plunge; recovery lounge; secure cycle store; showers & towels.

**Directory sample (content-listing, spotlight/gallery/pathways):** 3 programmes —
"Beginner Reset (6 weeks)", "Strength 5×5 (12 weeks)", "Run Club (rolling)".
Each with a one-line summary.

**Detail sample:** A single programme page for "Strength 5×5" with a short intro,
what's included, who it's for, and a "Book a free assessment" CTA.

**CTA:** heading "Your first session is on us." Summary: "Grab a free trial,
meet a coach, and try a class. No card needed." Button label "Book free trial".

## Build steps

1. Scaffold `packages/theme-fitness-wellness/` per shared-contract §2 (copy
   `packages/theme-saas` as the base and rename identity).
2. Write `capell.json` (manifest v3, §3): `themeKey: fitness-wellness`,
   `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-fitness-wellness-demo"` with
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-fitness-wellness", "theme-fitness-wellness-frontend"]`,
   `security.publicOutput` all true / `riskTier: low`,
   `performance.cacheTags: ["theme-fitness-wellness"]`, one health check
   `theme-fitness-wellness.package-health`, the `admin-page` contribution, and
   `database` all false.
3. Write `FitnessWellnessThemeServiceProvider` (§4): `register()` registers the
   package as `PackageTypeEnum::Theme`; `boot(ThemeRegistry $registry)` registers
   the demo command, early-returns unless installed, loads translations + views,
   registers the CSS vendor asset (`tailwindImport`) and Blade source
   (`tailwindSource`), registers the page adapter, then
   `$registry->register(self::definition(), new BladeThemeRenderer(...), $sectionRenderers)`.
4. `definition(): ThemeDefinitionData` — key, name, description, package,
   previewImage, tags, bestFit, the `includedSections` above, presets (one
   "Forge" preset carrying the token table values), assets, `extends: 'default'`,
   `runtime: FrontendRuntime::Blade`.
5. Register a `ViewSectionRenderer` per section key including the five new keys.
6. Build `FitnessWellnessThemePageAdapter` to turn `meta.theme_demo.render_data`
   into the typed section data, building the new `class-schedule`,
   `membership-tiers`, `trainers`, `transformations`, and `facilities` shapes
   query-free.
7. Add `Install FitnessWellness ThemeDemoAction implements InstallsThemeDemo`
   calling `ThemeDemoPageInstaller::run($data, 'fitness-wellness', 'FitnessWellness')`,
   a `DemoCommand` registering `capell:theme-fitness-wellness-demo`, and a
   `profile()` entry carrying all the beta copy above.
8. Write `resources/views/page.blade.php` (skip link, brand tokens as inline CSS
   custom properties, `data-capell-theme`, `{!! $content !!}`), thin
   `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`,
   and one Blade per customised + new section. Use
   `@frontendAsset('css/theme-fitness-wellness.css')`.
9. Add `resources/css/theme-fitness-wellness.css` (dark surface, lime accents),
   `resources/lang/en/generic.php` for all strings, and
   `resources/boost/guidelines/core.blade.php`.
10. Add `Theme FitnessWellness HealthCheck` and the manifest contribution.
11. Mirror autoload in **both** `composer.json` and `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard tests (shared-contract §10): `ManifestRequirementsTest`,
`FitnessWellnessThemeDefinitionTest`, `FitnessWellnessThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`, `Theme FitnessWellness HealthCheck`
feature test.

Theme-specific assertions:

1. The adapter builds a `class-schedule` section whose `days` contain typed
   `classes` with `time`, `name`, `instructor`, and `level`.
2. `membership-tiers` renders exactly one tier with `featured: true` (Monthly) and
   the note "No joining fee. Cancel any time. Prices include VAT."
3. The `transformations` view renders the literal disclaimer "Individual results
   vary." and the rendered HTML contains it.
4. `PublicOutputSafetyTest` confirms no schedule/tier/trainer Blade leaks
   `wire:`, `data-field`, `data-model`, `Filament`, `signed`, the package name, or
   any DB query (`::query(`, `DB::`, `find(`, lazy relations).

## Verification

```bash
vendor/bin/pest packages/theme-fitness-wellness/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
