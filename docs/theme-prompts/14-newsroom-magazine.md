# Theme: NewsroomMagazine (`theme-newsroom-magazine`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-newsroom-magazine` distinct.

## Build this

Build a premium Capell child theme for a news / magazine editorial publication — the kind of
front page that leads with one big featured story, fans the rest out into a category-tagged
story grid, lets readers jump by section, grows a newsletter list, surfaces a most-read
ranking, and credits its contributors. Everything renders from query-free hydrated render
data: the featured story, the story grid, the category navigation, a static newsletter form,
the ranked most-read list, and the contributors. The aesthetic is editorial red and ink on
warm newsprint, with a serif display face — confident, journalistic, made for reading.
Extend `default` (Foundation Theme) at runtime and `capell-app/foundation-theme` at the
package level.

## Inspiration

Browser tabs: **Zapier's blog** and modern editorial publications for the magazine front-page
formula — a dominant featured story, a category-coded grid, section navigation, a newsletter
ask, a most-read rail, and bylined contributors. Borrow the moves: a featured-story lead, a
story grid with category/author/date, a category nav strip, a newsletter signup, a ranked
most-read list, and a contributors block. Do **not** copy any publication's copy, headlines,
or bylines; invent original demo content (brand "The Mainframe").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing for editorial publishing. Knowledge renders documentation and
search-led help; portfolio renders a creator's work. This renders a _publication_ — a
featured lead, a category-driven story grid, a most-read ranking, and contributor bylines.
It is the only theme with a serif display face and a newspaper-front-page information
hierarchy.

## Package identity

| Field       | Value                                                                                                                         |
| ----------- | ----------------------------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-newsroom-magazine`                                                                                          |
| slug        | `theme-newsroom-magazine`                                                                                                     |
| namespace   | `Capell\ThemeStudio\NewsroomMagazine`                                                                                         |
| themeKey    | `newsroom-magazine`                                                                                                           |
| displayName | `Newsroom Magazine`                                                                                                           |
| tier        | premium                                                                                                                       |
| bestFit     | `["News and magazine publications", "Editorial and opinion sites", "Multi-author blogs at scale", "Topic-driven journalism"]` |
| tags        | `["Newsroom", "Magazine", "Editorial", "Serif", "Journalism"]`                                                                |

## Design direction

| Token              | Value         | Rationale                                                |
| ------------------ | ------------- | -------------------------------------------------------- |
| primaryColor       | `#b91c1c`     | Editorial red — the masthead and section-marker colour.  |
| accentColor        | `#1d4ed8`     | Ink blue — links and the secondary editorial accent.     |
| neutralColor       | `#171717`     | Near-black ink for body type and rules.                  |
| surfaceColor       | `#fbfaf8`     | Warm newsprint canvas — easy on the eyes for long reads. |
| foregroundColor    | `#171717`     | Ink body text on newsprint, maximum readability.         |
| headingFont        | `fraunces`    | A serif display face — the journalistic, magazine voice. |
| bodyFont           | `inter`       | Clean sans body type keeps long articles readable.       |
| spacing            | `airy`        | Generous whitespace — a premium editorial front page.    |
| alignment          | `left`        | Headlines, decks, and bylines read left-to-right.        |
| cardStyle          | `flat`        | Flat, ruled story cards read like a printed page.        |
| navigationStyle    | `prominent`   | A masthead with a section nav strip beneath it.          |
| layoutPresentation | `editorial`   | Magazine front-page hierarchy — one lead, then the grid. |
| motionIntensity    | `subtle`      | Quiet hover underlines; the type does the work.          |
| mediaTreatment     | `flat`        | Flat editorial photography — no stylized framing.        |
| radius             | `none`        | Square corners — print-like, austere, serious.           |
| headingScale       | `dramatic`    | Big serif headlines lead the page.                       |
| cardDensity        | `comfortable` | Story cards need room for a deck and byline.             |

Typography: display headlines in **Fraunces** (serif, with optical sizing where available);
decks, bylines, and body in **Inter**; section labels in small-caps tracking. Motion:
**subtle** — underline-on-hover for links and headlines, a quiet fade on the most-read rail;
respect `prefers-reduced-motion`. The newsletter signup is a **static field definition
rendered as plain HTML inputs** — no live submission wiring, no JS, no Livewire.

## Sections

`includedSections`:
`["navigation", "featured-story", "category-nav", "story-grid", "most-read", "newsletter-signup", "contributors", "features", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **featured-story** — `heading`, `lead` `{ title, category, author, excerpt, image }`.
- **category-nav** — `heading`, `categories[]` each `{ name }`.
- **story-grid** — `heading`, `summary`, `stories[]` each `{ title, category, author, date, excerpt }`.
- **most-read** — `heading`, `summary`, `ranked[]` each `{ rank, title }`.
- **newsletter-signup** — `heading`, `summary`, `subscriberNote`, `fields[]` each `{ name, label, type }` (static; no live submission).
- **contributors** — `heading`, `summary`, `people[]` each `{ name, beat }`.

`content-listing` uses the `pathways` variant for the section-archive feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** The Mainframe — "Technology, told straight."

**Hero / featured story (featured-story):** heading "Today's lead." lead:

- title "The quiet rewrite: inside the year a payments giant moved off its mainframe".
- category "Hardware".
- author "Priya Anand".
- excerpt "For four decades the ledger ran on hardware older than most of the engineers maintaining it. We spent six months with the team that finally moved it — and lived to tell the tale."
- image `@frontendAsset('images/theme-newsroom-magazine/lead.jpg')`.

**Category nav (category-nav):** heading "Sections." categories:

- name "AI".
- name "Hardware".
- name "Policy".
- name "Culture".
- name "Security".
- name "Business".

**Story grid (story-grid):** heading "More from the newsroom." stories:

- title "The model that learned to say 'I don't know'" — category "AI" — author "Marco Bellini" — date "2026-06-18" — excerpt "A small lab's bet on calibrated uncertainty is reshaping how teams trust their assistants."
- title "Right to repair clears its biggest hurdle yet" — category "Policy" — author "Hana Kim" — date "2026-06-17" — excerpt "A landmark ruling means manuals, parts, and tools must ship to anyone who asks."
- title "Why your favourite app went quiet" — category "Culture" — author "Daniel Osei" — date "2026-06-16" — excerpt "The slow software movement is winning, one removed notification at a time."
- title "Inside the breach nobody reported" — category "Security" — author "Sofia Marchetti" — date "2026-06-15" — excerpt "How a supply-chain compromise sat undetected for ninety-one days."
- title "The chip startup betting against the cloud" — category "Hardware" — author "Tomasz Wójcik" — date "2026-06-14" — excerpt "On-device inference is back, and this team thinks the data centre had its day."
- title "What founders get wrong about pricing" — category "Business" — author "Amara Diallo" — date "2026-06-13" — excerpt "Three pricing myths that quietly cap growth — and the data that kills them."

**Most read (most-read):** heading "Most read this week." ranked:

- rank "1" — title "The model that learned to say 'I don't know'".
- rank "2" — title "Right to repair clears its biggest hurdle yet".
- rank "3" — title "Inside the breach nobody reported".
- rank "4" — title "The quiet rewrite: inside the mainframe migration".
- rank "5" — title "Why your favourite app went quiet".

**Newsletter signup (newsletter-signup):** heading "The Mainframe, in your inbox." Summary:
"One considered email every weekday morning — the stories that matter, no clickbait."
subscriberNote "Join 48,000 readers." fields (static definitions):

- name "email" — label "Email address" — type "email".
- name "frequency" — label "How often" — type "text".

**Feature cards (features):** 4 cards `{ title, summary, type }`:

- "Reporting, not repackaging" / "Original interviews and on-the-ground stories, not press-release rewrites." / trust.
- "One newsletter, weekday mornings" / "A single edited brief — never a firehose." / capability.
- "Reader-funded" / "Members keep us independent; no advertiser sets our agenda." / trust.
- "Corrections, in public" / "When we get it wrong, we say so, at the top of the page." / trust.

**Contributors (contributors):** heading "Our newsroom." people:

- name "Priya Anand" — beat "Hardware & infrastructure".
- name "Marco Bellini" — beat "AI & research".
- name "Hana Kim" — beat "Policy & regulation".
- name "Sofia Marchetti" — beat "Security".
- name "Daniel Osei" — beat "Culture".
- name "Amara Diallo" — beat "Business".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "48k" / "Newsletter subscribers" / "The only tech newsletter I read top to bottom." — reader.
- "120+" / "Original stories a year" / "Reporting you can't get from a press release." — reader.
- "0" / "Advertiser influence" / "Member-funded, so it actually tells the truth." — reader.

**Membership note (vertical block):** static descriptive copy — "The Mainframe is reader-
funded. Members get the full archive, the Friday long read, and the satisfaction of keeping
independent tech journalism alive. The daily newsletter is free, forever." Present as prose.

**Spotlight / pathways:** spotlight "How we report a story like the mainframe migration"
with pathways "Read the lead", "Browse sections", "Join the newsletter".

**Directory samples (content-listing, 3+):**

- "The AI section" — type "Section" — "Calibrated models, agents, and the research that holds up."
- "The Policy section" — type "Section" — "Regulation, right-to-repair, and the law catching up."
- "The Friday long read" — type "Series" — "One deeply reported feature every week, for members."

**Detail/article sample:** "The quiet rewrite: inside the year a payments giant moved off its
mainframe" — a 4-paragraph original feature on the migration's stakes, the team, the near-
misses, and what finally made it work.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: newsroom-magazine`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, `product.tier: "premium"`, runtime provider `Capell\ThemeStudio\NewsroomMagazine\NewsroomMagazineThemeServiceProvider`, demo command `capell:theme-newsroom-magazine-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-newsroom-magazine","theme-newsroom-magazine-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-newsroom-magazine"]`, health check `theme-newsroom-magazine.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **NewsroomMagazineThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-newsroom-magazine`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `featured-story`, `category-nav`, `story-grid`, `most-read`, `newsletter-signup`, `contributors`, `features`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The `newsletter-signup` view renders plain static `<input>` fields from the field definitions — no `wire:`, no live action wiring.
5. **NewsroomMagazineThemePageAdapter** maps demo render data into typed section Data, including the 6 NEW shapes.
6. **InstallNewsroomMagazineThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'newsroom-magazine', 'NewsroomMagazine')`; **DemoCommand** exposes `capell:theme-newsroom-magazine-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-newsroom-magazine.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-newsroom-magazine.css** — warm newsprint canvas, editorial-red masthead, ink-blue links, Fraunces serif display headings, ruled flat story cards, small-caps section labels, square corners. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-newsroom-magazine::...')`.
10. **Theme NewsroomMagazine health check** for `theme-newsroom-magazine.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\NewsroomMagazine\` → `packages/theme-newsroom-magazine/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `NewsroomMagazineThemeDefinitionTest`,
`NewsroomMagazineThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeNewsroomMagazineHealthCheckTest`. Theme-specific assertions:

- `featured-story` renders the lead's `title`, `category`, `author`, and `excerpt` from render
  data and never queries the database at render time.
- `story-grid` renders every story with `category`, `author`, and `date`; categories render as
  static labels, not a query.
- `newsletter-signup` renders plain static fields and emits **no** `wire:`, no signed URL, no
  live submission action in the public output.
- `most-read` renders the ranked list in order (assert rank `1` precedes rank `2` in the
  output).
- Demo install seeds all 7 surfaces with The Mainframe copy (assert brand name and the `48,000`
  subscriber figure appear).

## Verification

```bash
vendor/bin/pest packages/theme-newsroom-magazine/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
