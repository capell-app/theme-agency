# Theme: PersonalDev (`theme-personal-dev`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
skeleton, manifest, provider wiring, safety rules, and acceptance baseline. This
file only specifies what makes PersonalDev distinct.

## Build this

A minimal, typography-first personal brand for a developer who also writes. No
hero illustrations, no gradient, no card shadows — just well-set text, a clean
writing index, a "now" page, a short project list, and a quiet newsletter prompt.
This is the FREE tier theme: deliberately small section set, designed to look
correct out of the box for a single person. Extends `default` at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Borrow from emilkowal.ski. Take the extreme typographic restraint, the
left-aligned reading column, the dated writing index with reading times, and the
near-total absence of motion. The page should feel like a well-typeset document,
not a marketing site. Borrow the structure and discipline only — invent all demo
posts, project names, and bio.

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) has no free, single-person, writing-first developer
brand. `portfolio` is a premium creator/consultant theme with media kits and
work grids; `corporate` is for organisations. PersonalDev serves an individual
engineer/writer who wants a personal site that is essentially a typeset reading
surface plus a newsletter. As a FREE theme it widens the no-cost entry point.

## Package identity

| Field          | Value                                                                                         |
| -------------- | --------------------------------------------------------------------------------------------- |
| package        | `capell-app/theme-personal-dev`                                                               |
| slug           | `theme-personal-dev`                                                                          |
| namespace      | `Capell\ThemeStudio\PersonalDev`                                                              |
| themeKey       | `personal-dev`                                                                                |
| displayName    | `Personal Dev`                                                                                |
| tier           | `free`                                                                                        |
| bestFit        | `["Developer personal sites", "Writers & bloggers", "Engineers' homepages", "Indie hackers"]` |
| tags           | `["Minimal", "Typography", "Writing", "Free", "Personal"]`                                    |
| view namespace | `capell-theme-personal-dev`                                                                   |

In `capell.json`, set `product.tier` to `free` and
`commercial.proposedLicense` to `free`. This is the one FREE theme in this batch.

## Design direction

| Token              | Value         | Rationale                                             |
| ------------------ | ------------- | ----------------------------------------------------- |
| primaryColor       | `#111827`     | Near-black ink; the text is the design.               |
| accentColor        | `#6366f1`     | A single indigo link colour, nothing more.            |
| neutralColor       | `#1f2937`     | Dark grey for secondary metadata (dates, times).      |
| surfaceColor       | `#fcfcfc`     | Faintly off-white page, easy on the eyes.             |
| foregroundColor    | `#111827`     | Ink body text for maximum reading contrast.           |
| headingFont        | `inter`       | Same family as body — one typeface, set well.         |
| bodyFont           | `inter`       | Inter at a comfortable reading measure.               |
| spacing            | `airy`        | Generous vertical rhythm between text blocks.         |
| alignment          | `left`        | A single left-aligned reading column.                 |
| cardStyle          | `flat`        | No cards-as-boxes; list rows separated by space only. |
| navigationStyle    | `minimal`     | A few text links; no sticky bar, no logo lockup.      |
| layoutPresentation | `editorial`   | Document-like flow, narrow measure.                   |
| motionIntensity    | `none`        | Zero motion — the whole point is calm.                |
| mediaTreatment     | `flat`        | Images, if any, sit flat inline at body width.        |
| radius             | `sm`          | Barely-there rounding on the one or two UI elements.  |
| headingScale       | `balanced`    | Headings sized for reading, not for impact.           |
| cardDensity        | `comfortable` | Comfortable line spacing in the writing index.        |

Typography: one family (Inter), a constrained reading measure (~65ch), generous
line-height (1.75). Dates and reading times in `neutralColor` at small size.
Motion: none — do not add scroll reveals. Links underline on hover only.

## Sections

`includedSections`:
`["navigation", "about-intro", "writing-index", "now", "projects", "newsletter-inline", "content-listing", "footer"]`

Inherited from Foundation: `navigation`, `content-listing`, `footer`. There is no
hero/features/proof/cta in the marketing sense — this theme leads with prose. NEW
sections:

- **about-intro** (NEW) — replaces the hero; a short personal statement.
  Render data:
  `{ heading: string, body: string }`
- **writing-index** (NEW) — dated list of posts.
  Render data:
  `{ heading: string, posts: [{ title, date, readingTime }] }`
- **now** (NEW) — a `/now`-style status block.
  Render data:
  `{ heading: string, items: string[] }`
- **projects** (NEW) — a short list of things built.
  Render data:
  `{ heading: string, projects: [{ name, description, url }] }`
- **newsletter-inline** (NEW, STATIC) — a no-backend subscribe prompt. Renders
  fixed copy only; the form posts nowhere by default and exposes no signed URL,
  model id, or `wire:` binding.
  Render data: `{ heading: string, body: string }`

Each NEW key registers a
`ViewSectionRenderer('personal-dev', '<key>', 'capell-theme-personal-dev::sections.<key>', failLoudly: true)`.

## Beta data (demo profile)

Seed via `ThemeDemoPageInstaller::profile()` for key `personal-dev`. All copy
original.

- **Brand:** `Jonah Vance` — a frontend engineer and writer.
- **summary:** `Frontend engineer and occasional writer. I work on interface performance and the small details of motion and accessibility.`
- **heroHeading (about-intro heading):** `Jonah Vance`
- **about-intro body:**
  `I'm a frontend engineer who cares about the unglamorous parts of interfaces — focus management, animation timing, the cost of a re-render. I write here when I figure something out worth keeping. Currently building accessible component libraries and writing about why most of them get menus wrong.`

**writing-index posts (3+, with dates + reading times):**

1. `On animation timing` · `2026-05-14` · `7 min`
2. `Building accessible menus` · `2026-03-02` · `11 min`
3. `The cost of abstraction` · `2026-01-20` · `9 min`
4. `Why your focus ring disappears` · `2025-11-08` · `5 min`

**now items (`/now` block):**

- `Rewriting a component library's menu and combobox to pass full keyboard and screen-reader audits.`
- `Reading "Refactoring UI" again, slowly, with a notebook.`
- `Learning enough Rust to stop being scared of it.`
- `Living in Bristol; available for one short writing or consulting engagement this quarter.`

**projects (with descriptions + urls — original/fictional):**

1. `focus-trap-lite` — `A 1.2kb focus-trap with no dependencies, used in a few design systems I respect.` · url `https://example.com/focus-trap-lite`
2. `menukit` — `An accessible menu and combobox primitive for React, with proper roving tabindex.` · url `https://example.com/menukit`
3. `tinytween` — `A tiny spring-and-ease animation helper for people who don't want a whole motion library.` · url `https://example.com/tinytween`

**newsletter-inline (STATIC):** heading `The occasional email` · body
`A short note when I publish something — a few times a year, never more. No tracking pixels, no "10x your career" nonsense.`

**features[] (used only if the demo homepage wants a compact "what I do" trio; type = focus):**

1. `Interface performance` — `Making interfaces fast in the ways users actually feel.` (type: focus)
2. `Accessibility` — `Components that work with a keyboard and a screen reader, not just a mouse.` (type: focus)
3. `Motion` — `Animation that has a reason and respects reduced-motion.` (type: focus)

**proof[] (kept minimal — 3 light credibility lines, no vanity metrics):**

1. metric `40k` · name `Monthly readers` · quote `Around forty thousand people read these posts each month, mostly from search and word of mouth.`
2. metric `6 yrs` · name `Writing here` · quote `I've kept this site for six years and never run an ad on it.`
3. metric `3` · name `OSS libraries` · quote `Three small open-source libraries I actually maintain, rather than thirty I abandoned.`

**pathways[] (3 — content-listing pathways variant):**

1. `Writing` — `Everything I've published, newest first.`
2. `Projects` — `The open-source bits worth your time.`
3. `Now` — `What I'm actually doing this month.`

**Directory sample entries (3+ — directory demo page = the writing archive):**

1. `On animation timing` · 2026-05-14 · 7 min — `Why most UI animations feel slow even when they're technically fast, and the durations I reach for.`
2. `Building accessible menus` · 2026-03-02 · 11 min — `A walkthrough of the roving-tabindex pattern and the screen-reader behaviour everyone forgets.`
3. `The cost of abstraction` · 2026-01-20 · 9 min — `When a clever wrapper stops paying for itself, with three examples from my own code.`

**Detail sample (detail demo page):** `On animation timing` — heading
`On animation timing`, dated `2026-05-14`, `7 min`, body covering the problem
(animations that test as fast but feel sluggish), the insight (perceived duration
vs measured duration, easing curves), and a closing rule of thumb. No author photo,
no related-posts widget — just the essay.

**ctaHeading / ctaSummary:** there is no hard CTA. The closing prompt is the
newsletter-inline copy above. If the demo `cta` page needs content, reuse the
newsletter copy as a soft close.

## Build steps

1. Scaffold `packages/theme-personal-dev/` per shared-contract §2. Copy
   `packages/theme-corporate` (also free, restrained) and strip it back further.
2. Write `capell.json` (manifest v3, §3): identity from the table,
   `product.tier: "free"`, `commercial.proposedLicense: "free"`,
   `extends: "capell-app/foundation-theme"`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-personal-dev-demo"`,
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-personal-dev", "theme-personal-dev-frontend"]`,
   `healthChecks: ["personal-dev.package-health"]`, `database` all false,
   `security.publicOutput` flags true / `riskTier: "low"`.
3. `PersonalDevThemeServiceProvider`: `register()` empty; `boot(ThemeRegistry)`
   per §4 — demo command, install gate, translations + views, CSS via
   `VendorAssetData::tailwindImport`, Blade sources via `tailwindSource`, page
   adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the preset table under one
   `ThemePresetData` (`key: 'plain'`), full `includedSections`, tags, bestFit,
   assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
5. One `ViewSectionRenderer` per NEW + customised section key.
6. `PersonalDevThemePageAdapter` (§5): map render data to the NEW Data shapes
   (about-intro, writing-index, now, projects, newsletter-inline) plus
   `ContentListingSectionData`, `NavigationData`, `FooterData`. Empty-state
   fallbacks. The newsletter-inline section is static: build it from fixed profile
   copy, never from a query or a form binding.
7. `InstallPersonalDevThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'personal-dev', 'PersonalDev')`; wire
   `DemoCommand` (`capell:theme-personal-dev-demo`); add the `personal-dev`
   profile entry (§6) with all copy above.
8. `resources/views/page.blade.php`: skip link, `data-capell-theme`, brand tokens
   inline, `{!! $content !!}`, `@frontendAsset('css/theme-personal-dev.css')`.
   Thin `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`.
9. `resources/css/theme-personal-dev.css`: single-column reading measure, one type
   family, indigo links, zero motion utilities — no authoring markers.
10. `resources/lang/en/generic.php` for every user-facing string.
11. `PersonalDevThemeHealthCheck` registered as `personal-dev.package-health`.
12. Add both PSR-4 entries to `composer.json` AND `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (shared-contract §10): `ManifestRequirementsTest`,
`PersonalDevThemeDefinitionTest`, `PersonalDevThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`, `PersonalDevThemeHealthCheckTest`.
Plus theme-specific:

- `ManifestRequirementsTest` asserts `product.tier === 'free'` (the only free
  theme in this batch).
- Definition `includedSections` contains `about-intro`, `writing-index`, `now`,
  `projects`, and `newsletter-inline`, and does NOT include a marketing `hero`.
- The writing-index section renders posts each carrying `date` and `readingTime`.
- The newsletter-inline section is static — `PublicOutputSafetyTest` confirms its
  Blade has no `wire:`, no `signed`, no form action pointing at an admin route.

## Verification

```bash
vendor/bin/pest packages/theme-personal-dev/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
