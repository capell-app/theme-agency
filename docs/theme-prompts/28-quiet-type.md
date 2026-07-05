# Theme: QuietType (`theme-quiet-type`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
package skeleton, manifest rules, service-provider shape, public-output-safety
test, and the monorepo autoload steps. This prompt only describes what makes
`theme-quiet-type` distinct.

## Build this

Build a **style** theme — like `liquid-glass`, it is vertical-agnostic and defined
by a typographic treatment rather than an industry. QuietType re-skins the
**standard** section set (navigation, hero, features, proof, content-listing, cta,
footer) with a luxury editorial voice: a large high-contrast serif display, a
generous reading measure, left-aligned rhythm, pull-quotes, and hairline rules on
warm paper. It does **not** add industry sections; it overrides the shared section
**views** so the same portable content reads like a considered print publication.
It adds exactly one new section — `editorial-feature` — for a pull-quote-led
article block. It extends Foundation, ships vertical-neutral "Quarter Press" demo
data, and carries no migrations, routes, models, or settings. Tier: **FREE**.

## Inspiration

Borrow the restraint and craft of design-led portfolio and studio sites — the
typographic precision of Sketch's marketing pages, the editorial calm of Groth
Studio, and the considered detail of emilkowal.ski. Take: oversized serif display
headings, a narrow generous measure, left-aligned paragraphs with real hierarchy,
hairline rules and small letter-spaced labels, and pull-quotes used as structural
beats. Borrow the _feeling_ of refinement only — invent all copy.

## Gap it fills

The current set has exactly one pure style theme: `liquid-glass` (modern glass,
sans-led). Everything else is an industry vertical (agency, commerce, corporate,
education, estate-agents, healthcare, inertia-bookings, knowledge, local-services,
nonprofit, portfolio, restaurant, saas) or the new verticals in this batch. There
is no serif-led, print-grade editorial **style** that any site can adopt regardless
of industry. QuietType is the typographic counterpart to liquid-glass — a free,
vertical-agnostic treatment for writers, studios, publications, and brands that want
their words to feel printed.

## Package identity

| Field       | Value                                                                                                                                    |
| ----------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-quiet-type`                                                                                                            |
| slug        | `theme-quiet-type`                                                                                                                       |
| namespace   | `Capell\ThemeStudio\QuietType`                                                                                                           |
| themeKey    | `quiet-type`                                                                                                                             |
| displayName | `Quiet Type`                                                                                                                             |
| tier        | `free`                                                                                                                                   |
| bestFit     | `["Publications & journals", "Writers & essayists", "Design studios", "Editorial brands", "Any site wanting a print-grade serif voice"]` |
| tags        | `["Editorial", "Serif", "Typography", "Style", "Print"]`                                                                                 |

View namespace `capell-theme-quiet-type`; translation namespace
`capell-theme-quiet-type`. Because this is a free style theme, set the
manifest `product.tier` to `free` and the marketplace category to
`["frontend", "themes"]` as usual.

## Design direction

| Token              | Value         | Rationale                                                        |
| ------------------ | ------------- | ---------------------------------------------------------------- |
| primaryColor       | `#7c2d12`     | Oxblood — a single rich editorial accent for links and rules.    |
| accentColor        | `#166534`     | Forest green — restrained secondary for tags and emphasis.       |
| neutralColor       | `#292524`     | Warm near-black for body text grounding.                         |
| surfaceColor       | `#faf8f4`     | Warm paper — the whole site reads like fine stock.               |
| foregroundColor    | `#1a1a1a`     | Near-black ink for maximum reading contrast.                     |
| headingFont        | `fraunces`    | High-contrast serif display — the heart of the theme.            |
| bodyFont           | `newsreader`  | Reading serif body for a true print feel (fall back to `inter`). |
| spacing            | `airy`        | Generous whitespace is essential to editorial rhythm.            |
| alignment          | `left`        | Left-aligned text and a real measure, never centred blocks.      |
| cardStyle          | `flat`        | Flat cards keep the page quiet and paper-like.                   |
| navigationStyle    | `minimal`     | Understated masthead-style nav.                                  |
| layoutPresentation | `editorial`   | Magazine rhythm: rules, captions, pull-quotes, wide margins.     |
| motionIntensity    | `subtle`      | Gentle fades only — print does not jitter.                       |
| mediaTreatment     | `framed`      | Framed media with captions, like plates in a magazine.           |
| radius             | `none`        | Square corners; nothing rounded breaks the print feel.           |
| headingScale       | `dramatic`    | Oversized serif display headings carry the whole theme.          |
| cardDensity        | `comfortable` | Generous spacing around every block.                             |

Typography: `fraunces` for display headings at dramatic scale with optical sizing
and tight tracking; `newsreader` (or `inter`) for body at a comfortable measure
(~66ch) and relaxed line height; small letter-spaced uppercase eyebrows;
pull-quotes set large in `fraunces` with an oxblood hairline rule. Motion: subtle
fade-and-rise on scroll only. Respect `prefers-reduced-motion`.

## Sections

`includedSections`:

```
['navigation', 'hero', 'features', 'proof', 'content-listing',
 'editorial-feature', 'cta', 'footer']
```

This theme **re-skins the standard section set** — it registers a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-quiet-type::sections.<key>', failLoudly: true)`
for **every** standard key (navigation, hero, features, proof, content-listing, cta,
footer) so each one renders with editorial type, large serif display, a generous
measure, pull-quotes, and hairline rules. The point is the **typographic rhythm of
the shared views**, not new industry sections. Each standard section consumes the
same typed Data objects as Foundation (`HeroSectionData`, `FeatureSectionData`,
`ProofSectionData`, `ContentListingSectionData` with its
`spotlight|gallery|pathways` variants, `CtaSectionData`, `NavigationData`,
`FooterData`) — only the markup and CSS change.

ONE NEW section:

- **editorial-feature** (NEW) — render data:
  `{ heading, pullQuote, body, byline }` — a long-form article beat: a section
  heading, an oversized pull-quote with an oxblood rule, a body paragraph block,
  and a byline line. Render-data is hydrated and query-free; the Blade is pure
  typography.

## Beta data (demo profile)

Seed via `Install QuietType ThemeDemoAction` →
`ThemeDemoPageInstaller::run(...)` plus a profile entry. Because this is a
vertical-agnostic style theme, the demo brand is a neutral publication and the copy
demonstrates the **typographic treatment** across the standard sections rather than
any industry. All copy original.

**Brand:** Quarter Press — "A small press for considered writing."

**Hero (shows the serif display + measure):** heading "Words, set with care."
Summary: "Quarter Press publishes long-form essays, criticism, and the occasional
quiet manifesto. This page exists to show how the Quiet Type theme treats an
ordinary site: large serif headings, a real reading measure, and rhythm borrowed
from print."

**Features (4 cards — title / summary / type — show how features read as editorial
notes, not feature bullets):**

1. A Real Measure — "Body text sits at around sixty-six characters a line, the
   width typographers have trusted for a century." / `feature`.
2. Display & Reading Type — "A high-contrast display serif for headings; a calmer
   reading serif for the body. Two voices, one page." / `feature`.
3. Pull-Quotes As Structure — "Quotes are used as beats in the page, not
   decoration — they break the column and earn their place." / `feature`.
4. Hairlines, Not Boxes — "Oxblood rules and generous margins do the work that
   borders and shadows do elsewhere." / `feature`.

**Proof (3 refined metrics — metric / name / quote — kept understated):**

1. "1 / 1" — measure to meaning — "Every line is set to be read, not skimmed." —
   The Quarter Press style note.
2. "66ch" — the considered measure — "It just feels easier to read, and that's the
   point." — a returning reader.
3. "Zero" — borders on the page — "Restraint reads as confidence." — Quarter Press
   editorial.

**Editorial-feature (the new section — give full copy):**

- heading: "On the case for slow pages".
- pullQuote: "A page that respects the reader's attention will be read more
  slowly, and remembered more clearly."
- body: "Most of the web is built to be skimmed. Quiet Type is built for the
  opposite: a single column, a generous measure, and headings large enough to set
  the pace. The result is a site that asks to be read rather than scanned — useful
  for essays, criticism, and any brand whose words are the product. Nothing here is
  decorative. The rules mark sections, the pull-quotes mark turns in the argument,
  and the whitespace simply gives the writing room to breathe."
- byline: "By the Quarter Press editors".

**Content-listing (vertical-neutral, shows the editorial list treatment):** 3
entries — "An essay on the long sentence", "Notes from the type bench: setting
display serifs", "Why we kept the page to one column". Each a one-line summary
written as a publication would.

**Detail sample:** A single editorial article page — masthead-style heading, a
dateline and byline, a body set in the reading serif, a mid-article pull-quote, and
a quiet "Read the next essay" CTA. No industry-specific structure.

**CTA:** heading "Read the next piece." Summary: "New essays land most weeks. Take
the long way through them." Button label "Browse the archive".

## Build steps

1. Scaffold `packages/theme-quiet-type/` per shared-contract §2 (copy
   `packages/theme-liquid-glass` — the closest free style theme — and rename
   identity; use retained visual theme packages for any demo/health plumbing).
2. Write `capell.json` (v3, §3): `themeKey: quiet-type`,
   `extends: capell-app/theme-foundation`, `product.tier: "free"`,
   `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`, demo command
   `capell:theme-quiet-type-demo` with
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-quiet-type", "theme-quiet-type-frontend"]`,
   `security.publicOutput` true / `riskTier: low`,
   `cacheTags: ["theme-quiet-type"]`, health check
   `theme-quiet-type.package-health`, the `admin-page` contribution,
   `database` all false. Set `commercial.proposedLicense` to a free license to
   match the tier.
3. Write `QuietTypeThemeServiceProvider` (§4): package registration in
   `register()`; `boot(ThemeRegistry)` registers the demo command, gates on
   installed, loads translations + views, registers CSS import + Blade source,
   registers the page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the `includedSections` above (the seven
   standard keys plus `editorial-feature`) and one "Quarter" preset carrying the
   token table values; `extends: 'default'`, `runtime: FrontendRuntime::Blade`.
5. Register a `ViewSectionRenderer` for **every** standard section key plus
   `editorial-feature` — this theme deliberately owns all seven shared views to
   change their typographic rhythm.
6. Build `QuietTypeThemePageAdapter` mapping `meta.theme_demo.render_data`
   into the standard typed section data and the one new `editorial-feature` shape
   (`heading`, `pullQuote`, `body`, `byline`), all query-free.
7. Add `Install QuietType ThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'quiet-type', 'QuietType')`, a
   `DemoCommand` for `capell:theme-quiet-type-demo`, and a `profile()` entry
   with all the copy.
8. Write `page.blade.php` (skip link, brand tokens inline, `data-capell-theme`,
   `{!! $content !!}`), thin `livewire/page/page.blade.php`
   (`RenderCurrentThemePageAction::run()`), and one Blade per standard section plus
   `editorial-feature` — each carrying the serif/measure/pull-quote markup. Use
   `@frontendAsset('css/theme-quiet-type.css')`.
9. Add `resources/css/theme-quiet-type.css` (warm paper surface, oxblood
   rules, two-serif typography, 66ch measure, square corners),
   `resources/lang/en/generic.php`, and the boost guideline view.
10. Add `Theme QuietType HealthCheck` and the manifest contribution.
11. Mirror autoload in **both** `composer.json` and `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard tests: `ManifestRequirementsTest`,
`QuietTypeThemeDefinitionTest`, `QuietTypeThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`,
`Theme QuietType HealthCheck` feature test.

Theme-specific assertions:

1. The manifest declares `product.tier: "free"` and the definition registers a
   `ViewSectionRenderer` for **all seven** standard section keys (this theme owns
   the shared views), plus `editorial-feature`.
2. The adapter builds an `editorial-feature` section carrying `heading`,
   `pullQuote`, `body`, and `byline`; assert the pull-quote text "A page that
   respects the reader's attention will be read more slowly, and remembered more
   clearly." is present.
3. The hero and content-listing views render with the serif display heading and
   the editorial measure (assert the theme CSS variable for the heading font
   resolves to a serif and the page wrapper sets `--theme-heading-font`).
4. `PublicOutputSafetyTest` confirms no editorial Blade or lang string leaks the
   package name, `authoring`, `Filament`, `signed`, `data-field`, `data-model`,
   or any DB query (`::query(`, `DB::`, `find(`, lazy relations).

## Verification

```bash
vendor/bin/pest packages/theme-quiet-type/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
