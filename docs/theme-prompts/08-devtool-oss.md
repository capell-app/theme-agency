# Theme: DevtoolOss (`theme-devtool-oss`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-devtool-oss` distinct.

## Build this

Build a free Capell child theme for an open-source developer tool that ships both a
hosted cloud product and a self-host option — the kind of site that opens with a copyable
install command, flexes GitHub social proof, and lets engineers compare self-host vs
cloud before they pick a path. Everything renders from query-free hydrated render data:
the install command, the star/fork/contributor counts, the comparison rows, the SDK grid,
and a static changelog. The aesthetic is a clean light surface with an indigo primary and
a cyan accent, with first-class dark support. Extend `default` (Foundation Theme) at
runtime and `capell-app/foundation-theme` at the package level.

## Inspiration

Browser tabs: **Cal.com** for the open-source-with-cloud framing — install command up top,
self-host vs cloud comparison, GitHub stars worn as a badge of honour — and **Clerk** for
the tidy SDK grid and crisp, low-noise developer layout. Borrow the moves: an install hero
with a one-line command, a GitHub proof strip, a self-host/cloud comparison table, a
language SDK grid, a static changelog, and a contributors wall. Do **not** copy their copy,
brand, repo names, or numbers; invent original demo content (brand "Scheduler OSS").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing for an open-source project that needs to convert GitHub
visitors. `theme-api-platform` sells a hosted API to engineers but says nothing about
stars, contributors, MIT licensing, or self-hosting. SaaS sells closed software with
pricing. This theme is the only one that leads with `npm install`, treats a star count as
proof, and pits self-host against cloud — the canonical open-source-with-cloud story.

## Package identity

| Field       | Value                                                                                                                             |
| ----------- | --------------------------------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-devtool-oss`                                                                                                    |
| slug        | `theme-devtool-oss`                                                                                                               |
| namespace   | `Capell\ThemeStudio\DevtoolOss`                                                                                                   |
| themeKey    | `devtool-oss`                                                                                                                     |
| displayName | `Devtool OSS`                                                                                                                     |
| tier        | free                                                                                                                              |
| bestFit     | `["Open-source developer tools", "Self-host + cloud products", "OSS projects with paid hosting", "Developer libraries and SDKs"]` |
| tags        | `["Open Source", "Developer", "GitHub", "Self-host", "Light + Dark"]`                                                             |

## Design direction

| Token              | Value         | Rationale                                                   |
| ------------------ | ------------- | ----------------------------------------------------------- |
| primaryColor       | `#4f46e5`     | Indigo — the trusted, modern OSS-tooling brand colour.      |
| accentColor        | `#06b6d4`     | Cyan for links, command prompts, and active SDK chips.      |
| neutralColor       | `#0f172a`     | Slate ink for borders and dark-mode panels.                 |
| surfaceColor       | `#ffffff`     | Clean white canvas (dark mode flips to a slate surface).    |
| foregroundColor    | `#0f172a`     | High-contrast slate body text on white.                     |
| headingFont        | `inter`       | Crisp product headings, no flourish.                        |
| bodyFont           | `inter`       | Inter for prose; monospace reserved for commands and code.  |
| spacing            | `balanced`    | Tight enough for docs scanning, roomy enough to breathe.    |
| alignment          | `left`        | Documentation reading flow.                                 |
| cardStyle          | `bordered`    | Thin borders read like repo panels and comparison cells.    |
| navigationStyle    | `minimal`     | Slim top bar: Docs / Self-host / Cloud / GitHub.            |
| layoutPresentation | `structured`  | Predictable, grid-aligned developer layout.                 |
| motionIntensity    | `subtle`      | Quiet hover and a soft copy-feedback flash; nothing flashy. |
| mediaTreatment     | `framed`      | Framed command blocks and diagram panels.                   |
| radius             | `md`          | 8px corners — modern, friendly, not playful.                |
| headingScale       | `balanced`    | Headings present; the command stays the star.               |
| cardDensity        | `comfortable` | SDK and comparison cards need label room.                   |

Typography: headings and prose in **Inter**; install commands and code in a monospace
stack (`ui-monospace, "JetBrains Mono", monospace`) with `tabular-nums` for the GitHub
counts. Motion: subtle hover lift on cards and a soft fade on the GitHub stat counters;
respect `prefers-reduced-motion`. The install command and changelog code are static,
escaped HTML — never executed. Ship a `prefers-color-scheme: dark` block so the white
surface flips to a slate dark mode with the same indigo/cyan accents.

## Sections

`includedSections`:
`["navigation", "install-hero", "github-proof", "self-host-vs-cloud", "features", "sdk-grid", "changelog", "contributors", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **install-hero** — `heading`, `summary`, `ctaLabel`, `ctaUrl`, `install` `{ command, packageManager }` (command is pre-escaped static text).
- **github-proof** — `heading`, `summary`, `stars`, `forks`, `contributors`, `latestRelease` (all static snapshot strings/ints; no live API call).
- **self-host-vs-cloud** — `heading`, `summary`, `rows[]` each `{ feature, selfHost, cloud }`.
- **sdk-grid** — `heading`, `summary`, `sdks[]` each `{ language, install, docsUrl }`.
- **changelog** — `heading`, `summary`, `releases[]` each `{ version, date, highlights[] }`.
- **contributors** — `heading`, `summary`, `people[]` each `{ name, handle }`.

`content-listing` uses the `gallery` variant for the guides/blog feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Scheduler OSS — "Open-source scheduling you can self-host or run in our cloud."

**Hero (install-hero):** heading "Booking infrastructure for developers — open source, MIT,
yours." Summary: "Embed availability, round-robin assignment, and timezone-correct booking
links in any product. Self-host the whole stack, or let our cloud run it for you." ctaLabel
"Star on GitHub", ctaUrl "/github". install: command `npm create scheduler-oss@latest`,
packageManager "npm".

**GitHub proof (github-proof):** heading "Built in the open." Summary: "Shipped by a
community, hardened in production." stars `24300`, forks `1820`, contributors `380`,
latestRelease "v2.4.0". (Render `24300` as a friendly `24k`-style label in Blade — keep the
raw integer in render data.)

**Self-host vs cloud (self-host-vs-cloud):** heading "Run it your way." rows:

- feature "Hosting" — selfHost "Your servers, your data" — cloud "Managed, 99.95% SLA".
- feature "Setup time" — selfHost "Docker compose, ~10 min" — cloud "Sign up, instant".
- feature "Upgrades" — selfHost "You pull and migrate" — cloud "Zero-downtime, automatic".
- feature "Data residency" — selfHost "Anywhere you deploy" — cloud "EU / US regions".
- feature "Price" — selfHost "Free, MIT" — cloud "Free up to 1,000 bookings/mo".
- feature "Support" — selfHost "Community Discord" — cloud "Priority email + Discord".

**Feature cards (features):** 6 cards `{ title, summary, type }`:

- "Availability engine" / "Round-robin, collective, and managed availability across teams." / capability.
- "Timezone-correct links" / "Booking links resolve to the invitee's local time, every time." / capability.
- "Webhooks + API" / "REST and webhooks for every booking lifecycle event." / integration.
- "Embed anywhere" / "Drop the booker into any app with a single script tag or React component." / capability.
- "MIT licensed" / "Fork it, ship it, sell it — no strings, no per-seat tax." / trust.
- "Self-host in minutes" / "One Docker compose file brings up the full stack." / performance.

**SDK grid (sdk-grid):** heading "Embed in your stack."

- TypeScript — install `npm install @scheduler-oss/sdk` — docsUrl "/docs/typescript".
- React — install `npm install @scheduler-oss/react` — docsUrl "/docs/react".
- Python — install `pip install scheduler-oss` — docsUrl "/docs/python".
- Go — install `go get github.com/scheduler-oss/go` — docsUrl "/docs/go".

**Changelog (changelog):** heading "What's new."

- version "v2.4.0" — date "2026-05-28" — highlights: ["Managed availability for teams", "React 19 support", "30% faster booking-link resolution"].
- version "v2.3.0" — date "2026-04-12" — highlights: ["Webhook retries with backoff", "New Go SDK", "Self-host upgrade command"].

**Contributors (contributors):** heading "380 people made this."

- name "Mara Lindqvist" — handle "@maralind".
- name "Dev Patel" — handle "@devp".
- name "Yuki Tanaka" — handle "@yukit".
- name "Hassan Al-Amin" — handle "@hassanio".
- name "Priya Nair" — handle "@priyacodes".
- name "Tomás Ribeiro" — handle "@tomasr".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "24k" / "GitHub stars" / "We self-hosted Scheduler OSS in an afternoon and killed our SaaS booking bill." — Elena Voss, Staff Engineer, Mailroom.
- "380" / "Contributors" / "The cleanest scheduling codebase I've ever forked." — Karim Said, Open-source maintainer.
- "10 min" / "To self-host" / "Compose up, point a domain, done. It just worked." — Greta Holm, Platform Lead, Northwind.

**License note (vertical block):** static descriptive copy — "MIT licensed. Self-host for
free forever. The cloud plan is free up to 1,000 bookings per month, then usage-based with
no per-seat fees." Present as prose, not a live pricing widget.

**Spotlight / pathways:** spotlight "From git clone to first booking in 10 minutes" with
pathways "Read the self-host guide", "Browse the SDKs", "Open the API reference".

**Directory samples (content-listing, 3+):**

- "Self-hosting on a single VPS" — type "Guide" — "Docker compose, TLS, and backups for a one-box deploy."
- "Embedding the booker in Next.js" — type "Guide" — "Server components, theming, and webhook handling."
- "Migrating from a hosted scheduler" — type "Guide" — "Import availability and rewrite your booking links."

**Detail/article sample:** "Why we made the cloud and the code the same" — a 4-paragraph
original article on running an open-core project, keeping self-host first-class, and how the
cloud funds the open-source work.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: devtool-oss`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, `product.tier: "free"`, runtime provider `Capell\ThemeStudio\DevtoolOss\DevtoolOssThemeServiceProvider`, demo command `capell:theme-devtool-oss-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-devtool-oss","theme-devtool-oss-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-devtool-oss"]`, health check `theme-devtool-oss.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **DevtoolOssThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-devtool-oss`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `install-hero`, `github-proof`, `self-host-vs-cloud`, `features`, `sdk-grid`, `changelog`, `contributors`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. Command/code-bearing views must `{{ }}`-escape command text.
5. **DevtoolOssThemePageAdapter** maps demo render data into typed section Data, including the 6 NEW shapes.
6. **InstallDevtoolOssThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'devtool-oss', 'DevtoolOss')`; **DemoCommand** exposes `capell:theme-devtool-oss-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-devtool-oss.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-devtool-oss.css** — light canvas, indigo/cyan accents, command-block chrome, monospace stack, GitHub-stat styling, a `prefers-color-scheme: dark` block. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-devtool-oss::...')`.
10. **Theme DevtoolOss health check** for `theme-devtool-oss.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\DevtoolOss\` → `packages/theme-devtool-oss/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `DevtoolOssThemeDefinitionTest`,
`DevtoolOssThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeDevtoolOssHealthCheckTest`. Theme-specific assertions:

- `install-hero` renders the escaped install command (rendered HTML contains the literal
  `npm create scheduler-oss@latest` text, no executable script, no `wire:`).
- `github-proof` renders static `stars`/`forks`/`contributors` figures and never queries the
  database or hits a live GitHub endpoint at render time.
- `self-host-vs-cloud` renders every comparison row with all three columns (`feature`,
  `selfHost`, `cloud`).
- `sdk-grid` renders all four languages with `install` and `docsUrl`; `docsUrl` values are
  plain public links (no `signed`, `data-field`, `model_id`).
- Demo install seeds all 7 surfaces with Scheduler OSS copy (assert brand name and a star
  count appear).

## Verification

```bash
vendor/bin/pest packages/theme-devtool-oss/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
