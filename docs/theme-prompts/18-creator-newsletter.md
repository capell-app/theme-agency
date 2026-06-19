# Theme: CreatorNewsletter (`theme-creator-newsletter`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
skeleton, manifest, provider wiring, safety rules, and acceptance baseline. This
file only specifies what makes CreatorNewsletter distinct.

## Build this

A subscribe-first renderer for a solo creator / independent-media brand built
around a newsletter. The whole page funnels toward one action: get the email.
Warm coral palette, friendly rounded cards, a prominent subscribe hero, a back
catalogue of issues, subscriber testimonials, sponsor slots, and an author bio.
It is the opposite of PersonalDev's austerity — this theme is conversion-shaped
and personable. Extends `default` at runtime and `capell-app/foundation-theme` at
the package level.

## Inspiration

Borrow from modern creator/newsletter landing pages (Substack-style and
indie-creator homepages): the single big email-capture hero, the social-proof
subscriber count, the issue archive with excerpts, and the "as featured / loved
by readers" testimonial strip. Take the conversion architecture and warm,
first-person tone only — invent every issue, quote, sponsor, and the author.

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) has no subscribe-first creator theme. `portfolio`
mentions newsletter growth but is structured around a work grid and case studies;
`saas` is product-and-pricing. CreatorNewsletter is purpose-built for a single
writer/creator whose product _is_ the newsletter, with sponsorship as the
business model.

## Package identity

| Field          | Value                                                                                            |
| -------------- | ------------------------------------------------------------------------------------------------ |
| package        | `capell-app/theme-creator-newsletter`                                                            |
| slug           | `theme-creator-newsletter`                                                                       |
| namespace      | `Capell\ThemeStudio\CreatorNewsletter`                                                           |
| themeKey       | `creator-newsletter`                                                                             |
| displayName    | `Creator Newsletter`                                                                             |
| tier           | `premium`                                                                                        |
| bestFit        | `["Newsletter creators", "Solo media brands", "Independent writers", "Audience-first creators"]` |
| tags           | `["Newsletter", "Creator", "Subscribe", "Warm", "Conversion"]`                                   |
| view namespace | `capell-theme-creator-newsletter`                                                                |

## Design direction

| Token              | Value         | Rationale                                               |
| ------------------ | ------------- | ------------------------------------------------------- |
| primaryColor       | `#e11d48`     | Coral-rose — warm, energetic, friendly, on-brand.       |
| accentColor        | `#7c3aed`     | Violet accent for secondary highlights and tags.        |
| neutralColor       | `#2a1620`     | Deep mulberry ink for text and chrome.                  |
| surfaceColor       | `#fff8f6`     | Warm blush background, inviting not clinical.           |
| foregroundColor    | `#2a1620`     | Mulberry text reads warm against blush surface.         |
| headingFont        | `sora`        | Rounded geometric display, friendly and modern.         |
| bodyFont           | `inter`       | Highly legible body for issue excerpts and bio.         |
| spacing            | `balanced`    | Comfortable rhythm that keeps the CTA always in view.   |
| alignment          | `center`      | Centered hero and CTA maximise subscribe focus.         |
| cardStyle          | `elevated`    | Soft shadows make issue and testimonial cards inviting. |
| navigationStyle    | `prominent`   | Nav carries a persistent "Subscribe" button.            |
| layoutPresentation | `structured`  | Clear funnel order: hero → proof → archive → sponsors.  |
| motionIntensity    | `subtle`      | Gentle hover and reveal; warmth, not noise.             |
| mediaTreatment     | `flat`        | Flat author photo and issue thumbnails.                 |
| radius             | `xl`          | Large rounded corners read friendly and approachable.   |
| headingScale       | `balanced`    | Strong but not shouty — the email field is the star.    |
| cardDensity        | `comfortable` | Roomy cards for issue excerpts and quotes.              |

Typography: Sora headings, Inter body; coral primary for the subscribe button and
key numbers, violet accent for issue tags. Motion: subtle hover-lift on cards
(~200ms) and a gentle fade on the hero; honor `prefers-reduced-motion`.

## Sections

`includedSections`:
`["navigation", "subscribe-hero", "features", "archive", "testimonials", "sponsors", "about-author", "content-listing", "cta", "footer"]`

Inherited from Foundation: `navigation`, `features`, `content-listing`, `cta`,
`footer`. NEW sections:

- **subscribe-hero** (NEW) — the primary above-the-fold conversion block with a
  static email-capture field.
  Render data:
  `{ heading: string, subhead: string, emailPlaceholder: string, buttonLabel: string }`
  The email field is STATIC (posts nowhere by default); it must expose no signed
  URL, model id, `wire:`, or admin route.
- **archive** (NEW) — back catalogue of past issues.
  Render data:
  `{ heading: string, issues: [{ number, title, date, excerpt }] }`
- **testimonials** (NEW) — subscriber quotes.
  Render data:
  `{ heading: string, quotes: [{ quote, name }] }`
- **sponsors** (NEW) — sponsorship slots / tiers.
  Render data:
  `{ heading: string, intro: string, slots: [{ name, tier }] }`
- **about-author** (NEW) — who writes it.
  Render data:
  `{ name: string, bio: string }`

Each NEW key registers a
`ViewSectionRenderer('creator-newsletter', '<key>', 'capell-theme-creator-newsletter::sections.<key>', failLoudly: true)`.

## Beta data (demo profile)

Seed via `ThemeDemoPageInstaller::profile()` for key `creator-newsletter`. All
copy original.

- **Brand:** `The Long Game` — a weekly newsletter about doing creative work that
  lasts, written by `Priya Anand`.
- **summary:** `A weekly letter about doing creative work that compounds — read by 28,000 makers, writers, and founders.`
- **heroHeading (subscribe-hero heading):** `Play the long game.`
- **subscribe-hero subhead:**
  `Every Sunday, one short essay on doing creative work that compounds instead of burning out. Join 28,000 readers who'd rather build slowly than chase the algorithm.`
- **subscribe-hero emailPlaceholder:** `you@example.com`
- **subscribe-hero buttonLabel:** `Get Sunday's letter`

**features[] (4 — what readers get, type = benefit):**

1. `One idea a week` — `A single, finished thought you can actually use — not a link dump and not a thread.` (type: benefit)
2. `Five-minute read` — `Short enough to read with your coffee, dense enough to think about all week.` (type: benefit)
3. `No noise` — `No tracking-heavy "growth hacks", no daily spam. One letter, every Sunday.` (type: benefit)
4. `Full archive` — `Every back issue is free to read, forever, the moment you join.` (type: benefit)

**proof[] (3 metrics with quotes):**

1. metric `28k` · name `Subscribers` · quote `Twenty-eight thousand readers, grown entirely by word of mouth and forwarded emails.`
2. metric `61%` · name `Open rate` · quote `A 61% average open rate — roughly three times the industry norm — because people actually want it.`
3. metric `4.9★` · name `Reader rating` · quote `Rated 4.9 out of 5 across two thousand subscriber replies and survey responses.`

**archive issues (with numbers, dates, excerpts):**

1. `#142` · `Why slow is a strategy` · `2026-06-14` · `Speed wins races, but compounding wins decades. Here's how to tell which game you're in.`
2. `#141` · `The two-hour rule` · `2026-06-07` · `On protecting two undisturbed hours a day, and why everything else is negotiable.`
3. `#140` · `Quitting well` · `2026-05-31` · `Most advice tells you to never quit. That advice has ruined a lot of good projects.`
4. `#139` · `The audience trap` · `2026-05-24` · `Building an audience can quietly become the thing instead of the work. A way out.`

**testimonials (subscriber quotes):**

1. quote `The one newsletter I actually read on the day it arrives. It's like a coffee with a smart, calm friend.` · name `Devon R., designer`
2. quote `I've unsubscribed from everything else. This is the only one that consistently changes how I work.` · name `Maya T., founder`
3. quote `Short, generous, and never trying to sell me a course. Increasingly rare.` · name `Olu K., writer`

**sponsors (slots / tiers):**

- intro `The Long Game runs one classy sponsor per issue. Reach 28,000 thoughtful makers without the race-to-the-bottom ad networks.`
- `Presenting sponsor` · `Premium` — one issue, top placement, hand-written intro
- `Classifieds` · `Standard` — three short text ads at the foot of an issue
- `Annual partner` · `Premium` — twelve issues, first refusal on the calendar

**about-author:** name `Priya Anand` · bio
`Priya Anand has spent twelve years building things on the internet — two failed startups, one that worked, and a lot of essays in between. The Long Game is where she writes down what actually held up. She lives in Lisbon and answers every reply.`

**pathways[] (3 — content-listing pathways variant):**

1. `Read the archive` — `Every back issue, free, newest first.`
2. `Meet the writer` — `Who Priya is and why she writes this.`
3. `Sponsor an issue` — `Reach 28,000 makers with one tasteful placement.`

**Directory sample entries (3+ — directory demo page = the issue archive):**

1. `#142 — Why slow is a strategy` · 2026-06-14 — `On the difference between speed games and compounding games, and how to stop confusing the two.`
2. `#141 — The two-hour rule` · 2026-06-07 — `Why two protected hours a day beats a heroic all-nighter every single time.`
3. `#140 — Quitting well` · 2026-05-31 — `A defence of quitting the right projects at the right time, with three signals to watch for.`

**Detail sample (detail demo page):** `#142 — Why slow is a strategy` — heading
`Why slow is a strategy`, dated `2026-06-14`, body covering the hook (everyone
optimises for speed), the turn (compounding rewards patience), and a Sunday-letter
sign-off that nudges sharing. Include `number: #142`, `date: 2026-06-14`. End the
page with an inline subscribe prompt reusing the subscribe-hero copy.

**ctaHeading:** `One letter, every Sunday.` · **ctaSummary:**
`Join 28,000 readers playing the long game. Free, and you can leave any time.`

## Build steps

1. Scaffold `packages/theme-creator-newsletter/` per shared-contract §2. Copy
   `packages/theme-portfolio` (closest creator/newsletter base) as a starting
   point.
2. Write `capell.json` (manifest v3, §3): identity from the table,
   `extends: "capell-app/foundation-theme"`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-creator-newsletter-demo"`,
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-creator-newsletter", "theme-creator-newsletter-frontend"]`,
   `healthChecks: ["creator-newsletter.package-health"]`, `database` all false,
   `security.publicOutput` flags true / `riskTier: "low"`.
3. `CreatorNewsletterThemeServiceProvider`: `register()` empty;
   `boot(ThemeRegistry)` per §4 — demo command, install gate, translations +
   views, CSS via `VendorAssetData::tailwindImport`, Blade sources via
   `tailwindSource`, page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the preset table under one
   `ThemePresetData` (`key: 'subscribe'`), full `includedSections`, tags, bestFit,
   assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
5. One `ViewSectionRenderer` per customised + NEW section key.
6. `CreatorNewsletterThemePageAdapter` (§5): map render data to `FeatureSectionData`,
   `ProofSectionData`, `ContentListingSectionData`, `CtaSectionData`, plus the NEW
   Data shapes (subscribe-hero, archive, testimonials, sponsors, about-author).
   The subscribe-hero and any inline subscribe block are STATIC — build them from
   fixed profile copy, never from a query or admin-bound form. Empty-state
   fallbacks.
7. `InstallCreatorNewsletterThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'creator-newsletter', 'CreatorNewsletter')`;
   wire `DemoCommand` (`capell:theme-creator-newsletter-demo`); add the
   `creator-newsletter` profile entry (§6) with all copy above.
8. `resources/views/page.blade.php`: skip link, `data-capell-theme`, brand tokens
   inline, `{!! $content !!}`,
   `@frontendAsset('css/theme-creator-newsletter.css')`. Thin
   `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`.
9. `resources/css/theme-creator-newsletter.css`: warm coral palette, xl-radius
   elevated cards, centered subscribe hero, persistent subscribe button styling —
   no authoring markers.
10. `resources/lang/en/generic.php` for every user-facing string.
11. `CreatorNewsletterThemeHealthCheck` registered as
    `creator-newsletter.package-health`.
12. Add both PSR-4 entries to `composer.json` AND `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (shared-contract §10): `ManifestRequirementsTest`,
`CreatorNewsletterThemeDefinitionTest`, `CreatorNewsletterThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`,
`CreatorNewsletterThemeHealthCheckTest`. Plus theme-specific:

- Definition `includedSections` contains `subscribe-hero`, `archive`,
  `testimonials`, `sponsors`, and `about-author`.
- The archive section renders issues each carrying `number`, `title`, `date`, and
  `excerpt`.
- `PublicOutputSafetyTest` confirms the subscribe-hero email field is static: its
  Blade has no `wire:`, no `signed`, no `data-model`, and no form action targeting
  an admin route.
- The sponsors section renders each slot with its `tier`.

## Verification

```bash
vendor/bin/pest packages/theme-creator-newsletter/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
