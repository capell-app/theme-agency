# Theme: ConferenceEvent (`theme-conference-event`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-conference-event` distinct.

## Build this

Build a premium Capell child theme for a conference / summit / event site — the kind of page
that opens with a date, venue, and a countdown, then sells the line-up: a track-by-track
agenda, a speaker wall, ticket tiers with perks, sponsor tiers, and venue/travel detail.
Everything renders from query-free hydrated render data: the event date and a static
countdown target (no live JS timer required), the agenda sessions, the speakers, the ticket
tiers and perks, the sponsor tiers, and the venue block. The aesthetic is electric violet
and pink on a soft lavender wash — energetic, premium, made-for-launch. Extend `default`
(Foundation Theme) at runtime and `capell-app/foundation-theme` at the package level.

## Inspiration

Browser tab: **Stripe Sessions 2026** for the polished event-site formula — a bold date-and-
venue hero, a track-coded agenda, a high-status speaker wall, clean ticket tiers, and tiered
sponsor logos. Borrow the moves: an event hero with date/venue and a countdown target, a
session agenda with tracks, a speaker grid, ticket tiers with perk lists, sponsor tiers, and
a venue/travel block. Do **not** copy their copy, speakers, sponsors, or numbers; invent
original demo content (brand "Forge Summit 2026").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing time-boxed around a single dated event. Education renders
course catalogues and ongoing enrolment; this renders one summit with a countdown, a
multi-track agenda, ticket tiers, and sponsor recognition. It is the only theme built around
a fixed-date event with an agenda and tiered tickets.

## Package identity

| Field       | Value                                                                                                            |
| ----------- | ---------------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-conference-event`                                                                              |
| slug        | `theme-conference-event`                                                                                         |
| namespace   | `Capell\ThemeStudio\ConferenceEvent`                                                                             |
| themeKey    | `conference-event`                                                                                               |
| displayName | `Conference Event`                                                                                               |
| tier        | premium                                                                                                          |
| bestFit     | `["Conferences and summits", "Multi-track events", "Ticketed industry events", "Product launches with agendas"]` |
| tags        | `["Conference", "Event", "Summit", "Agenda", "Tickets"]`                                                         |

## Design direction

| Token              | Value         | Rationale                                                     |
| ------------------ | ------------- | ------------------------------------------------------------- |
| primaryColor       | `#6d28d9`     | Electric violet — the headline brand of a modern tech summit. |
| accentColor        | `#f472b6`     | Hot pink — energy, the "buy a ticket" call.                   |
| neutralColor       | `#1e1b2e`     | Deep aubergine ink for text and rules.                        |
| surfaceColor       | `#faf5ff`     | Soft lavender wash canvas — premium, lightly tinted.          |
| foregroundColor    | `#1e1b2e`     | Aubergine body text on the lavender wash.                     |
| headingFont        | `sora`        | Bold, contemporary headings with launch energy.               |
| bodyFont           | `inter`       | Inter keeps agenda and ticket detail readable.                |
| spacing            | `balanced`    | Energetic but legible across long agendas.                    |
| alignment          | `left`        | Agenda rows and ticket lists read left-to-right.              |
| cardStyle          | `elevated`    | Raised speaker and ticket cards feel premium.                 |
| navigationStyle    | `prominent`   | A bold header with Get tickets always visible.                |
| layoutPresentation | `immersive`   | Full-bleed hero and section reveals for launch energy.        |
| motionIntensity    | `expressive`  | Confident reveals, gradient motion, lively hover.             |
| mediaTreatment     | `framed`      | Framed speaker portraits and venue imagery.                   |
| radius             | `lg`          | 16px corners — rounded, friendly, eventful.                   |
| headingScale       | `dramatic`    | Big date-and-venue headline that announces the event.         |
| cardDensity        | `comfortable` | Speaker, ticket, and agenda cards need room.                  |

Typography: headings in **Sora** (bold, modern); body and agenda copy in **Inter** with
`tabular-nums` for times and prices. Motion: **expressive** — gradient sweep on the hero,
staggered reveals on the speaker wall, lively hover on ticket cards; respect
`prefers-reduced-motion`. The countdown is a **static target value passed in render data** —
render it as a date/label; do not require a live JS timer (an optional progressive
enhancement may animate it, but the page must read correctly with no JS).

## Sections

`includedSections`:
`["navigation", "event-hero", "agenda", "speakers", "ticket-tiers", "features", "sponsors", "venue", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **event-hero** — `heading`, `summary`, `date`, `venue`, `countdownTarget` (ISO date string as data; no live timer required), `ctaLabel`, `ctaUrl`.
- **agenda** — `heading`, `summary`, `sessions[]` each `{ time, title, speaker, track }`.
- **speakers** — `heading`, `summary`, `speakers[]` each `{ name, role, company }`.
- **ticket-tiers** — `heading`, `summary`, `tiers[]` each `{ name, price, perks[] }` (descriptive; no live checkout).
- **sponsors** — `heading`, `summary`, `tiers[]` each `{ tier, names[] }`.
- **venue** — `heading`, `summary`, `location`, `travel`.

`content-listing` uses the `spotlight` variant for the news / blog feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Forge Summit 2026 — "The conference for the people who build the internet."

**Hero (event-hero):** heading "Forge Summit 2026." Summary: "Two days, three tracks, and
the engineers, designers, and product leaders shaping what ships next. Hands-on sessions, no
filler, and the best hallway track in the industry." date "9–10 June 2026", venue "The
Roundhouse, London", countdownTarget "2026-06-09T09:00:00Z", ctaLabel "Get tickets", ctaUrl
"/tickets".

**Agenda (agenda):** heading "The agenda." sessions:

- time "09:00" — title "Opening keynote: Building for the next billion users" — speaker "Priya Anand" — track "Product".
- time "10:30" — title "Designing systems that survive scale" — speaker "Marco Bellini" — track "Engineering".
- time "11:45" — title "Interface craft in an AI-first world" — speaker "Hana Kim" — track "Design".
- time "13:30" — title "Shipping safely without slowing down" — speaker "Daniel Osei" — track "Engineering".
- time "15:00" — title "From research to roadmap" — speaker "Sofia Marchetti" — track "Product".
- time "16:15" — title "Motion, micro-interactions, and meaning" — speaker "Tomasz Wójcik" — track "Design".

**Speakers (speakers):** heading "Who you'll learn from." speakers:

- name "Priya Anand" — role "VP Product" — company "Northwind".
- name "Marco Bellini" — role "Principal Engineer" — company "Helix Systems".
- name "Hana Kim" — role "Head of Design" — company "Lumen".
- name "Daniel Osei" — role "Staff SRE" — company "Cloudbank".
- name "Sofia Marchetti" — role "Director of Research" — company "Atlas Labs".
- name "Tomasz Wójcik" — role "Motion Lead" — company "Studio Kinetic".

**Ticket tiers (ticket-tiers):** heading "Tickets."

- name "Early bird" — price "£349" — perks ["Both days", "All three tracks", "Recordings", "Lunch & socials"].
- name "Standard" — price "£549" — perks ["Both days", "All three tracks", "Recordings", "Lunch & socials", "Workshop access"].
- name "Team (5 seats)" — price "£1,499" — perks ["Five passes", "Reserved seating", "Team dinner", "Priority workshop booking", "Recordings"].

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Three tracks, no filler" / "Product, Engineering, and Design run in parallel — build your own day." / capability.
- "Hands-on workshops" / "Small-group sessions with the people who built the tools." / capability.
- "The hallway track" / "Curated mixers and topic tables so the right conversations happen." / capability.
- "Recordings included" / "Every session captured and shared with ticket holders within a week." / trust.
- "Inclusive by design" / "Captioned talks, quiet rooms, and travel grants for the underrepresented." / trust.

**Sponsors (sponsors):** heading "Backed by." tiers:

- tier "Platinum" — names ["Helix Systems", "Northwind"].
- tier "Gold" — names ["Cloudbank", "Lumen", "Atlas Labs"].
- tier "Community" — names ["Studio Kinetic", "Open Forge", "DevHaus", "Pixel Union"].

**Venue (venue):** heading "Where." location "The Roundhouse, Chalk Farm, London NW1".
travel "Two minutes from Chalk Farm station; step-free access throughout. Discounted hotel
block and a venue map are emailed with every ticket."

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "1,200" / "Attendees in 2025" / "The only conference where I left with three things to ship on Monday." — Elena Roth, Engineering Manager.
- "92%" / "Said they'd return" / "Best speaker line-up and zero wasted sessions." — Omar Haddad, Product Lead.
- "3" / "Parallel tracks" / "I built a day across all three and never had a dull slot." — Greta Lind, Designer.

**Schedule note (vertical block):** static descriptive copy — "Doors open at 08:30 each day.
Keynotes at 09:00, tracks run until 17:30, with workshops in the afternoon and an evening
social on day one. Full session times are confirmed by 1 May 2026." Present as prose.

**Spotlight / pathways:** spotlight "Why we run three tracks instead of one big stage" with
pathways "See the agenda", "Meet the speakers", "Get tickets".

**Directory samples (content-listing, 3+):**

- "Speaker spotlight: Hana Kim on AI-first interfaces" — type "News" — "A preview of the Design-track keynote."
- "First wave of workshops announced" — type "News" — "Six hands-on sessions, limited seats."
- "Travel & accommodation guide" — type "Guide" — "Getting to the Roundhouse and where to stay."

**Detail/article sample:** "How we program a three-track summit" — a 4-paragraph original
article on curation, balancing tracks, choosing speakers, and protecting the hallway track.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: conference-event`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, `product.tier: "premium"`, runtime provider `Capell\ThemeStudio\ConferenceEvent\ConferenceEventThemeServiceProvider`, demo command `capell:theme-conference-event-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-conference-event","theme-conference-event-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-conference-event"]`, health check `theme-conference-event.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **ConferenceEventThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-conference-event`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `event-hero`, `agenda`, `speakers`, `ticket-tiers`, `features`, `sponsors`, `venue`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The `event-hero` renders `countdownTarget` as a static date/label; any countdown animation is optional progressive enhancement and the page must read correctly with no JS.
5. **ConferenceEventThemePageAdapter** maps demo render data into typed section Data, including the 6 NEW shapes.
6. **InstallConferenceEventThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'conference-event', 'ConferenceEvent')`; **DemoCommand** exposes `capell:theme-conference-event-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-conference-event.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-conference-event.css** — lavender wash canvas, violet/pink gradient accents, elevated speaker and ticket cards, track-colour chips, `tabular-nums` agenda times and prices. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-conference-event::...')`.
10. **Theme ConferenceEvent health check** for `theme-conference-event.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\ConferenceEvent\` → `packages/theme-conference-event/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `ConferenceEventThemeDefinitionTest`,
`ConferenceEventThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeConferenceEventHealthCheckTest`. Theme-specific assertions:

- `event-hero` renders the `date`, `venue`, and `countdownTarget` from render data and the page
  reads correctly with no JS (no `wire:`, no required runtime timer).
- `agenda` renders every session with `time`, `title`, `speaker`, and `track`; tracks render as
  static labels, never a database query.
- `ticket-tiers` renders all tiers with their `perks[]` and a plain public `ctaUrl` (no `signed`,
  no checkout markup, no `model_id`).
- Demo install seeds all 7 surfaces with Forge Summit 2026 copy (assert brand name, the
  `9–10 June 2026` date, and the `£349` early-bird price appear).

## Verification

```bash
vendor/bin/pest packages/theme-conference-event/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
