# Theme: RoboticsHardware (`theme-robotics-hardware`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-robotics-hardware` distinct.

## Build this

Build a premium Capell child theme for a robotics / hardware deep-tech product — the kind
of site that reveals a physical machine with cinematic restraint, lists hard engineering
specs, walks through capabilities and subsystems, and converts intent into a deposit-backed
pre-order. Everything renders from query-free hydrated render data: the spec sheet, the
static video-hero poster and caption, the capability cards, the tech deep-dive, and the
pre-order figures. The aesthetic is graphite-and-electric-orange — industrial, confident,
slightly futuristic. Extend `default` (Foundation Theme) at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Browser tab: **Sunday Robotics** for the deep-tech hardware reveal — a quiet hero that lets
the machine speak, a spec sheet treated like a product fact, capability and subsystem
breakdowns, and a pre-order with a small refundable deposit. Borrow the moves: a static
video-hero with poster + caption, a label/value/unit spec sheet, capability cards, a
subsystem tech deep-dive, and a pre-order CTA with price and deposit. Do **not** copy their
copy, product, imagery, or numbers; invent original demo content (brand "Atelier One").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing for selling a physical engineered machine. Commerce sells
catalog retail with carts; this sells a single flagship device with a spec sheet, a
deposit pre-order, and subsystem deep-dives. No existing theme renders a static video-hero,
an engineering spec table with units, or a reserve-with-deposit flow. This is the only
hardware/deep-tech product theme.

## Package identity

| Field       | Value                                                                                                              |
| ----------- | ------------------------------------------------------------------------------------------------------------------ |
| package     | `capell-app/theme-robotics-hardware`                                                                               |
| slug        | `theme-robotics-hardware`                                                                                          |
| namespace   | `Capell\ThemeStudio\RoboticsHardware`                                                                              |
| themeKey    | `robotics-hardware`                                                                                                |
| displayName | `Robotics Hardware`                                                                                                |
| tier        | premium                                                                                                            |
| bestFit     | `["Robotics products", "Hardware and deep-tech devices", "Pre-order launches", "Engineering-led product reveals"]` |
| tags        | `["Robotics", "Hardware", "Deep Tech", "Graphite", "Pre-order"]`                                                   |

## Design direction

| Token              | Value           | Rationale                                                    |
| ------------------ | --------------- | ------------------------------------------------------------ |
| primaryColor       | `#111827`       | Graphite — the industrial machined-metal base colour.        |
| accentColor        | `#f97316`       | Electric orange — power, motion, the "on" state.             |
| neutralColor       | `#1c1917`       | Warm near-black stone for chrome and rules.                  |
| surfaceColor       | `#f5f5f4`       | Soft warm-grey canvas so the orange and machine images pop.  |
| foregroundColor    | `#111827`       | Graphite body text, high contrast on the warm canvas.        |
| headingFont        | `space-grotesk` | Technical, geometric headings with a hardware edge.          |
| bodyFont           | `inter`         | Inter keeps long spec prose legible.                         |
| spacing            | `airy`          | Cinematic breathing room around the hero and specs.          |
| alignment          | `left`          | Spec sheets and subsystems read left-to-right.               |
| cardStyle          | `elevated`      | Raised capability cards feel like physical components.       |
| navigationStyle    | `prominent`     | A confident header with Reserve always in reach.             |
| layoutPresentation | `immersive`     | Full-bleed hero and section reveals — a product launch feel. |
| motionIntensity    | `subtle`        | Restrained reveals; the machine, not the chrome, moves.      |
| mediaTreatment     | `framed`        | Framed video poster and subsystem imagery.                   |
| radius             | `md`            | 8px corners — engineered, not soft.                          |
| headingScale       | `dramatic`      | Big launch headlines for the reveal.                         |
| cardDensity        | `comfortable`   | Capability and spec cards need room for detail.              |

Typography: headings in **Space Grotesk** (geometric, technical); body and spec prose in
**Inter** with `tabular-nums` so spec numbers align. Motion: subtle fade-and-rise on
sections, a slow parallax-free poster reveal, and an orange focus ring on the Reserve CTA;
respect `prefers-reduced-motion`. The video-hero is a **static poster image with a caption
only** — no autoplay, no embedded player scripts, no JS. Spec values are static render data.

## Sections

`includedSections`:
`["navigation", "video-hero", "spec-sheet", "capabilities", "features", "tech-deep-dive", "preorder-cta", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **video-hero** — `heading`, `summary`, `poster` (image path/url), `caption` (static; no autoplay, no player scripts).
- **spec-sheet** — `heading`, `summary`, `specs[]` each `{ label, value, unit }`.
- **preorder-cta** — `heading`, `summary`, `price`, `deposit`, `availability`, `ctaLabel`, `ctaUrl` (descriptive; no live checkout).
- **capabilities** — `heading`, `summary`, `caps[]` each `{ title, description }`.
- **tech-deep-dive** — `heading`, `summary`, `subsystems[]` each `{ name, description }`.

`content-listing` uses the `spotlight` variant for the engineering-notes feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Atelier One — "The home robot that actually helps."

**Hero (video-hero):** heading "Meet Atelier One." Summary: "A quiet, capable home robot
with a 1.2-metre reach and a gentle grip. It clears the table, loads the dishwasher, and
folds the laundry — then steps back out of the way." poster
`@frontendAsset('images/theme-robotics-hardware/atelier-one-hero.jpg')`, caption "Atelier
One in a working kitchen — captured in one continuous take, no edits." (No autoplay, static
poster.)

**Spec sheet (spec-sheet):** heading "The numbers." specs:

- label "Reach" — value "1.2" — unit "m".
- label "Payload" — value "5" — unit "kg".
- label "Battery life" — value "6" — unit "h".
- label "Recharge time" — value "90" — unit "min".
- label "Degrees of freedom" — value "7" — unit "per arm".
- label "Footprint" — value "0.4" — unit "m²".
- label "Top speed" — value "1.4" — unit "m/s".
- label "Noise" — value "42" — unit "dB".

**Capabilities (capabilities):** heading "What it does today." caps:

- title "Clears the table" — description "Identifies plates, cups, and cutlery, then stacks and carries them to the sink."
- title "Loads the dishwasher" — description "Orients each item and places it without breakage, learning your machine's racks."
- title "Folds laundry" — description "Folds shirts, towels, and trousers into tidy stacks, then sorts by household member."
- title "Tidies surfaces" — description "Returns out-of-place objects to their home zones using a map you teach once."

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Soft gentle grip" / "Force-sensing fingers handle a wine glass and a cast-iron pan with the same care." / capability.
- "Teach by showing" / "Guide its arm through a task once; it remembers and repeats." / capability.
- "On-device vision" / "All perception runs locally — no video ever leaves the home." / trust.
- "Quiet by design" / "Runs at 42 dB, quieter than a dishwasher, so it works while you sleep." / performance.
- "Over-the-air skills" / "New capabilities arrive as signed updates, with your approval." / capability.

**Tech deep-dive (tech-deep-dive):** heading "Under the shell." subsystems:

- name "Perception stack" — description "Stereo depth plus tactile sensing fuse into a single world model updated 60 times a second."
- name "Manipulation" — description "Two 7-DoF arms with harmonic drives and 0.1 mm repeatability for delicate placement."
- name "Compute" — description "An on-board accelerator runs vision and planning locally, with a 12-core controller for real-time loops."
- name "Safety" — description "Redundant force limits, a physical stop, and a watchdog that halts motion on any anomaly."

**Pre-order CTA (preorder-cta):** heading "Reserve Atelier One." Summary: "Be in the first
production run. Your deposit is fully refundable until your unit ships." price "$2,990",
deposit "$100", availability "Ships Q3 2026", ctaLabel "Reserve for $100", ctaUrl
"/reserve". (Descriptive only — no live checkout, no payment markup.)

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "5 kg" / "Payload, single arm" / "It carried a full stockpot across the kitchen without a wobble." — Dr. Naomi Frost, Robotics reviewer.
- "42 dB" / "Operating noise" / "I forgot it was running. It folded a week of laundry overnight." — Theo Marsh, early access owner.
- "6 h" / "Battery on one charge" / "A single charge covers a full day of light household work." — Ingrid Solberg, Field test lead.

**Reservation note (vertical block):** static descriptive copy — "A $100 deposit reserves
your place in the queue and is fully refundable any time before shipping. Final price
$2,990 plus local tax and delivery. First units ship Q3 2026." Present as prose, no payment
form.

**Spotlight / pathways:** spotlight "One continuous morning with Atelier One" with pathways
"Read the spec sheet", "See the capabilities", "Explore the subsystems".

**Directory samples (content-listing, 3+):**

- "How Atelier One learns a new task" — type "Engineering note" — "Teach-by-showing, demonstrated on dishwasher loading."
- "Designing for a gentle grip" — type "Engineering note" — "Force sensing and compliant fingers for fragile objects."
- "On-device privacy" — type "Engineering note" — "Why no camera frame ever leaves the home."

**Detail/article sample:** "Building a home robot that earns its place" — a 4-paragraph
original article on safety-first manipulation, on-device perception, and designing hardware
people actually want in their kitchen.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: robotics-hardware`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, `product.tier: "premium"`, runtime provider `Capell\ThemeStudio\RoboticsHardware\RoboticsHardwareThemeServiceProvider`, demo command `capell:theme-robotics-hardware-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-robotics-hardware","theme-robotics-hardware-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-robotics-hardware"]`, health check `theme-robotics-hardware.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **RoboticsHardwareThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-robotics-hardware`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `video-hero`, `spec-sheet`, `capabilities`, `features`, `tech-deep-dive`, `preorder-cta`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The `video-hero` view renders a static `<img>` poster + caption only — no `<video autoplay>`, no `<script>`.
5. **RoboticsHardwareThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes.
6. **InstallRoboticsHardwareThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'robotics-hardware', 'RoboticsHardware')`; **DemoCommand** exposes `capell:theme-robotics-hardware-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-robotics-hardware.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-robotics-hardware.css** — warm-grey canvas, graphite ink, electric-orange accents, framed poster styling, elevated capability cards, `tabular-nums` spec rows. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-robotics-hardware::...')`.
10. **Theme RoboticsHardware health check** for `theme-robotics-hardware.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\RoboticsHardware\` → `packages/theme-robotics-hardware/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `RoboticsHardwareThemeDefinitionTest`,
`RoboticsHardwareThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeRoboticsHardwareHealthCheckTest`. Theme-specific assertions:

- `video-hero` renders a static poster image and caption and emits **no** `<video autoplay`,
  no `<script>`, and no `wire:` in the output.
- `spec-sheet` renders every `label`/`value`/`unit` triple from render data and never queries
  the database at render time.
- `preorder-cta` renders `price`, `deposit`, and `availability` as static copy with a plain
  public `ctaUrl` (no `signed`, no checkout markup, no `model_id`).
- Demo install seeds all 7 surfaces with Atelier One copy (assert brand name, the `1.2` reach
  spec, and the `$2,990` price appear).

## Verification

```bash
vendor/bin/pest packages/theme-robotics-hardware/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
