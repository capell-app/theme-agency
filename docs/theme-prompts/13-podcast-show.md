# Theme: PodcastShow (`theme-podcast-show`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-podcast-show` distinct.

## Build this

Build a premium Capell child theme for a podcast / show site — the kind of page that leads
with the latest episode, lists the back catalogue, makes subscribing one tap away on every
platform, and introduces the hosts and recent guests. Everything renders from query-free
hydrated render data: the latest-episode block with a static player shell (no JS audio
player), the episode list, the subscribe-platform links, the host bios, and the guest list.
The aesthetic is plum and warm orange on a soft peach editorial stock — intimate, warm,
broadcast-ready. Extend `default` (Foundation Theme) at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Browser tabs: modern podcast and show sites for the now-standard layout — a hero centred on
the newest episode, a tidy episode list, a row of subscribe buttons for every platform, and
warm host/guest introductions. Borrow the moves: a latest-episode block with title, number,
duration, and a static player shell, a back-catalogue list, a subscribe-platforms row, host
bios, and a guest list. Do **not** copy any real show's copy, episodes, or guests; invent
original demo content (brand "Signal & Noise").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing for audio/show content. Portfolio is for creators' visual work;
newsroom is for written articles. This is the only theme built around episodic audio: a
latest-episode hero, an episode catalogue, platform subscribe links, and host/guest
introductions. No existing theme renders an episode list or a static audio player shell.

## Package identity

| Field       | Value                                                                                                      |
| ----------- | ---------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-podcast-show`                                                                            |
| slug        | `theme-podcast-show`                                                                                       |
| namespace   | `Capell\ThemeStudio\PodcastShow`                                                                           |
| themeKey    | `podcast-show`                                                                                             |
| displayName | `Podcast Show`                                                                                             |
| tier        | premium                                                                                                    |
| bestFit     | `["Podcasts and audio shows", "Interview series", "Video/audio shows with episodes", "Creator-led shows"]` |
| tags        | `["Podcast", "Audio", "Show", "Episodes", "Editorial"]`                                                    |

## Design direction

| Token              | Value         | Rationale                                                |
| ------------------ | ------------- | -------------------------------------------------------- |
| primaryColor       | `#9333ea`     | Plum — a confident, broadcast-warm brand colour.         |
| accentColor        | `#fb923c`     | Warm orange — the play button and "subscribe" energy.    |
| neutralColor       | `#2a1e2e`     | Deep mauve ink for text and rules.                       |
| surfaceColor       | `#fdf7f3`     | Soft peach editorial stock — intimate and warm.          |
| foregroundColor    | `#2a1e2e`     | Mauve body text on the peach stock.                      |
| headingFont        | `sora`        | Modern, friendly headings with a broadcast feel.         |
| bodyFont           | `inter`       | Inter keeps show notes and episode copy readable.        |
| spacing            | `balanced`    | Editorial rhythm with room for episode rows.             |
| alignment          | `left`        | Episode lists and show notes read left-to-right.         |
| cardStyle          | `elevated`    | Raised episode and host cards feel like a media library. |
| navigationStyle    | `prominent`   | A warm header with Subscribe always visible.             |
| layoutPresentation | `editorial`   | Magazine-style flow for the show and its episodes.       |
| motionIntensity    | `subtle`      | Gentle hover on episode cards; nothing busy.             |
| mediaTreatment     | `framed`      | Framed episode artwork and host portraits.               |
| radius             | `lg`          | 16px corners — soft, friendly, media-app feel.           |
| headingScale       | `balanced`    | Headings present; the latest episode leads.              |
| cardDensity        | `comfortable` | Episode and host cards need room for notes.              |

Typography: headings in **Sora** (friendly, modern); body and show notes in **Inter** with
`tabular-nums` for episode numbers and durations. Motion: subtle hover lift on episode and
host cards, a soft pulse on the static play affordance; respect `prefers-reduced-motion`.
The latest-episode block renders a **static player shell** (artwork, title, duration, and a
non-functional play affordance styled as a button) — **no JS audio player**, no `<audio
autoplay>`, no embedded third-party player scripts. Subscribe links are plain public hrefs.

## Sections

`includedSections`:
`["navigation", "hero", "latest-episode", "episode-list", "subscribe-platforms", "features", "hosts", "guests", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **latest-episode** — `heading`, `summary`, `number`, `title`, `duration`, `summary` (episode), `audioCaption` (static; the player shell shows artwork + duration, no JS player).
- **episode-list** — `heading`, `summary`, `episodes[]` each `{ number, title, duration, date }`.
- **subscribe-platforms** — `heading`, `summary`, `platforms[]` each `{ name, url }` (plain public hrefs).
- **hosts** — `heading`, `summary`, `hosts[]` each `{ name, bio }`.
- **guests** — `heading`, `summary`, `guests[]` each `{ name, episode }`.

`content-listing` uses the `gallery` variant for the blog / clips feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Signal & Noise — "A weekly tech podcast about what's actually worth building."

**Hero (hero):** heading "Signal & Noise." Summary: "Every Tuesday, two engineers cut
through the hype and talk about the tools, teams, and trade-offs behind real software. 142
episodes and counting." CTA "Subscribe" → "/subscribe".

**Latest episode (latest-episode):** heading "Latest episode." number "142", title "Shipping
AI at scale", duration "58 min", episode summary: "We sit down with an infra lead who took an
internal AI tool from a weekend prototype to a feature serving millions of requests a day —
and the three things that nearly broke along the way." audioCaption "Episode 142 · 58 minutes
· released 16 June 2026." (Static shell only — no JS player.)

**Episode list (episode-list):** heading "Recent episodes." episodes:

- number "142" — title "Shipping AI at scale" — duration "58 min" — date "2026-06-16".
- number "141" — title "The cost of a microservice" — duration "47 min" — date "2026-06-09".
- number "140" — title "Why your tests are slow" — duration "52 min" — date "2026-06-02".
- number "139" — title "Designing APIs people don't hate" — duration "44 min" — date "2026-05-26".
- number "138" — title "On-call without burning out" — duration "61 min" — date "2026-05-19".
- number "137" — title "Postgres is all you need" — duration "55 min" — date "2026-05-12".

**Subscribe platforms (subscribe-platforms):** heading "Listen everywhere." platforms:

- name "Apple Podcasts" — url "/listen/apple".
- name "Spotify" — url "/listen/spotify".
- name "YouTube" — url "/listen/youtube".
- name "RSS" — url "/feed.xml".
- name "Pocket Casts" — url "/listen/pocketcasts".
- name "Overcast" — url "/listen/overcast".

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "New every Tuesday" / "A fresh episode every week, never longer than your commute." / capability.
- "Working engineers, real stories" / "Guests who actually shipped the thing they're talking about." / trust.
- "Show notes that link out" / "Every reference, tool, and paper, written up and linked." / capability.
- "Clips & highlights" / "The best three minutes of each episode, ready to share." / capability.
- "Ad-light and listener-first" / "One short sponsor read, never a hard sell." / trust.

**Hosts (hosts):** heading "Your hosts." hosts:

- name "Nadia Rahman" — bio "Backend engineer turned platform lead. Has strong opinions about queues and stronger ones about coffee."
- name "Theo Lambert" — bio "Frontend and tooling nerd. Builds the website, edits the show, and loses every argument about tabs vs spaces."

**Guests (guests):** heading "Recent guests." guests:

- name "Dr. Imani Cole" — episode "Ep. 142 — Shipping AI at scale".
- name "Bjorn Eriksson" — episode "Ep. 141 — The cost of a microservice".
- name "Lucia Romano" — episode "Ep. 139 — Designing APIs people don't hate".
- name "Sam Okafor" — episode "Ep. 137 — Postgres is all you need".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "142" / "Episodes published" / "The only tech podcast I actually finish every week." — listener review.
- "85k" / "Weekly listeners" / "Smart guests, no fluff, and they respect my time." — listener review.
- "4.9★" / "Average rating" / "It's like overhearing two senior engineers be honest." — listener review.

**Schedule note (vertical block):** static descriptive copy — "New episodes drop every
Tuesday at 06:00. Subscribe on your platform of choice and the latest episode lands in your
feed automatically — no app, no account, just press play." Present as prose.

**Spotlight / pathways:** spotlight "Behind episode 142: how we book and prep a guest" with
pathways "Hear the latest episode", "Browse the archive", "Subscribe".

**Directory samples (content-listing, 3+):**

- "Clip: the three things that nearly broke at scale" — type "Clip" — "Three minutes from episode 142."
- "Transcript: episode 140" — type "Transcript" — "Full searchable transcript of 'Why your tests are slow'."
- "How we record remotely" — type "Blog" — "Our two-mic, double-ender setup explained."

**Detail/article sample:** "How we make a weekly podcast without burning out" — a
4-paragraph original article on batching, guest prep, editing workflow, and protecting the
Tuesday release.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: podcast-show`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, `product.tier: "premium"`, runtime provider `Capell\ThemeStudio\PodcastShow\PodcastShowThemeServiceProvider`, demo command `capell:theme-podcast-show-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-podcast-show","theme-podcast-show-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-podcast-show"]`, health check `theme-podcast-show.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **PodcastShowThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-podcast-show`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `latest-episode`, `episode-list`, `subscribe-platforms`, `features`, `hosts`, `guests`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The `latest-episode` view renders a static player shell only — no `<audio>`, no autoplay, no third-party player script, no `wire:`.
5. **PodcastShowThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes.
6. **InstallPodcastShowThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'podcast-show', 'PodcastShow')`; **DemoCommand** exposes `capell:theme-podcast-show-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-podcast-show.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-podcast-show.css** — peach editorial stock, plum spine, warm-orange accents, framed episode artwork, elevated episode/host cards, `tabular-nums` episode numbers and durations. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-podcast-show::...')`.
10. **Theme PodcastShow health check** for `theme-podcast-show.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\PodcastShow\` → `packages/theme-podcast-show/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `PodcastShowThemeDefinitionTest`,
`PodcastShowThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemePodcastShowHealthCheckTest`. Theme-specific assertions:

- `latest-episode` renders a static player shell with title, number, and duration and emits
  **no** `<audio`, no autoplay, no embedded player `<script>`, and no `wire:`.
- `episode-list` renders every episode with `number`, `title`, `duration`, and `date` and never
  queries the database at render time.
- `subscribe-platforms` renders each platform link as a plain public href (no `signed`,
  `data-field`, `model_id`).
- Demo install seeds all 7 surfaces with Signal & Noise copy (assert brand name, the `142`
  episode count, and the `Shipping AI at scale` latest title appear).

## Verification

```bash
vendor/bin/pest packages/theme-podcast-show/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
