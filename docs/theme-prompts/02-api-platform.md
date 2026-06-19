# Theme: ApiPlatform (`theme-api-platform`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-api-platform` distinct.

## Build this

Build a premium Capell child theme for a developer-facing API product — the kind of
site that sells a voice, auth, or infrastructure API to engineers. The hero leads with
a copyable code sample, the page walks through a quickstart, lists official SDKs,
teases the API reference, and shows live-style service status, all from query-free
hydrated render data. The aesthetic is a calm developer dark surface with a bright
sky-blue primary. Extend `default` (Foundation Theme) at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Browser tabs: **Cartesia (Sonic)** for the dark developer hero with an inline code
block and latency-forward proof, and **Clerk** for the structured SDK grid, quickstart
ladder, and crisp reference teasers. Borrow the moves — code-first hero, a numbered
quickstart, language SDK cards, an endpoint teaser table, and a status/uptime strip —
and the precise, low-noise tone. Do **not** copy their code, brand, or numbers; invent
original demo content (brand "Sonari API").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing that renders code samples as primary content or speaks to
a developer audience. SaaS targets buyers with pricing and demos; this targets the
engineer who will paste a `curl` command and read the SDK grid. No existing theme shows
syntax-styled code blocks, a quickstart ladder, or an uptime strip.

## Package identity

| Field       | Value                                                                                                     |
| ----------- | --------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-api-platform`                                                                           |
| slug        | `theme-api-platform`                                                                                      |
| namespace   | `Capell\ThemeStudio\ApiPlatform`                                                                          |
| themeKey    | `api-platform`                                                                                            |
| displayName | `API Platform`                                                                                            |
| tier        | premium                                                                                                   |
| bestFit     | `["Developer API products", "Voice/auth/infra platforms", "SDK-led tools", "Platform engineering teams"]` |
| tags        | `["Developer", "API", "Dark", "Code", "Infrastructure"]`                                                  |

## Design direction

| Token              | Value         | Rationale                                                   |
| ------------------ | ------------- | ----------------------------------------------------------- |
| primaryColor       | `#38bdf8`     | Sky blue — the bright link/CTA color on a dev-dark surface. |
| accentColor        | `#a78bfa`     | Violet for syntax accents and secondary highlights.         |
| neutralColor       | `#1e293b`     | Slate for borders and code-block chrome.                    |
| surfaceColor       | `#0b1020`     | Deep navy developer canvas.                                 |
| foregroundColor    | `#e2e8f0`     | Cool light-grey body text, easy on the eyes.                |
| headingFont        | `inter`       | Clean product headings, no flourish.                        |
| bodyFont           | `inter`       | Inter for prose; monospace reserved for code blocks.        |
| spacing            | `balanced`    | Dense enough for docs, roomy enough to scan.                |
| alignment          | `left`        | Documentation reading flow.                                 |
| cardStyle          | `bordered`    | Thin borders read like API panels and code frames.          |
| navigationStyle    | `minimal`     | A slim top bar with Docs / SDKs / Status.                   |
| layoutPresentation | `structured`  | Predictable, grid-aligned dev-docs layout.                  |
| motionIntensity    | `subtle`      | Quiet hover/focus states; nothing flashy.                   |
| mediaTreatment     | `framed`      | Framed code blocks and diagram panels.                      |
| radius             | `md`          | 8px corners — modern but not playful.                       |
| headingScale       | `balanced`    | Headings present, code stays the star.                      |
| cardDensity        | `comfortable` | SDK and endpoint cards need label room.                     |

Typography: headings and prose in **Inter**; code blocks in a monospace stack
(`ui-monospace, "JetBrains Mono", monospace`) with `tabular-nums`. Motion: subtle hover
on cards and a soft fade-in on the status dots; respect `prefers-reduced-motion`. Code
blocks are static, escaped HTML — never executed.

## Sections

`includedSections`:
`["navigation", "code-hero", "quickstart", "features", "sdk-grid", "api-reference-teaser", "status-uptime", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **code-hero** — `heading`, `summary`, `ctaLabel`, `ctaUrl`, `samples[]` each `{ language, code }` (code is pre-escaped static text).
- **quickstart** — `heading`, `summary`, `steps[]` each `{ title, code }`.
- **sdk-grid** — `heading`, `summary`, `sdks[]` each `{ language, install, docsUrl }`.
- **api-reference-teaser** — `heading`, `summary`, `endpoints[]` each `{ method, path, description }`.
- **status-uptime** — `heading`, `summary`, `services[]` each `{ name, status, uptimePercent }` (static snapshot; no live polling).

`content-listing` uses the `gallery` variant for the changelog/guides feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Sonari API — "Real-time voice, one API call away."

**Hero (code-hero):** heading "Speech in, speech out — in under 100ms." Summary:
"Sonari is a real-time voice API for streaming text-to-speech and speech-to-text. Drop
in three lines, ship a voice agent this afternoon." ctaLabel "Get an API key", ctaUrl
"/signup". Samples:

- language "bash" — code:
  `curl https://api.sonari.dev/v1/speak \`
  `  -H "Authorization: Bearer $SONARI_KEY" \`
  `  -d '{"voice":"atlas","text":"Welcome to Sonari."}'`
- language "javascript" — code:
  `import { Sonari } from "@sonari/sdk";`
  `const sonari = new Sonari(process.env.SONARI_KEY);`
  `const stream = await sonari.speak({ voice: "atlas", text: "Hello" });`

**Quickstart (quickstart):** heading "Three steps to first audio."

- "Install the SDK" — code `npm install @sonari/sdk`.
- "Set your key" — code `export SONARI_KEY=sk_live_...`.
- "Stream your first clip" — code `await sonari.speak({ voice: "atlas", text: "Live in production." });`.

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Sub-100ms latency" / "Stream the first audio chunk in under a tenth of a second, globally." / performance.
- "32 lifelike voices" / "Expressive, multilingual voices tuned for agents and IVR." / quality.
- "Bidirectional streaming" / "Full-duplex WebSocket transport for true conversation." / capability.
- "Word-level timestamps" / "Sync captions and avatars to the exact spoken word." / capability.
- "SOC 2 + at-rest encryption" / "Audio is encrypted in transit and at rest, never retained by default." / trust.

**SDK grid (sdk-grid):** heading "Official SDKs."

- Node — install `npm install @sonari/sdk` — docsUrl "/docs/node".
- Python — install `pip install sonari` — docsUrl "/docs/python".
- Go — install `go get github.com/sonari/sonari-go` — docsUrl "/docs/go".
- Rust — install `cargo add sonari` — docsUrl "/docs/rust".

**API reference teaser (api-reference-teaser):** heading "The endpoints you'll reach for."

- method "POST" — path "/v1/speak" — description "Synthesize speech from text, streamed or buffered."
- method "POST" — path "/v1/transcribe" — description "Transcribe an audio stream to text with timestamps."
- method "GET" — path "/v1/voices" — description "List available voices and their language support."
- method "POST" — path "/v1/sessions" — description "Open a bidirectional real-time voice session."

**Status / uptime (status-uptime):** heading "Operational." All services static:

- "Speech API" — status "Operational" — uptimePercent `99.99`.
- "Transcription API" — status "Operational" — uptimePercent `99.98`.
- "Realtime sessions" — status "Operational" — uptimePercent `99.95`.
- "Dashboard" — status "Operational" — uptimePercent `100.0`.

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "90ms" / "Median first-chunk latency" / "Our IVR finally feels like a conversation, not a hold queue." — Lena Petrova, CTO, Verbal.
- "99.99%" / "API uptime, trailing 90 days" / "We moved our whole call platform over and haven't looked back." — Marcus Hale, VP Engineering, Dialpoint.
- "3 lines" / "To first audio" / "Junior engineers shipped a voice feature on day one." — Aisha Okoro, Eng Manager, Loop.

**Pricing note (vertical block):** static, no checkout — "Usage-based: $0.012 per 1,000
characters synthesized, $0.006 per minute transcribed. First 100k characters free every
month. No seat fees." Present as descriptive copy, not a live pricing widget.

**Spotlight / pathways:** spotlight "Build a voice agent in 15 minutes" with pathways
"Read the quickstart", "Browse the SDKs", "Open the API reference".

**Directory samples (content-listing, 3+):**

- "Streaming TTS guide" — type "Guide" — "Backpressure, chunking, and barge-in handling."
- "Migrating from a batch TTS provider" — type "Guide" — "Map old endpoints to streaming equivalents."
- "Changelog: v1.4" — type "Changelog" — "Added Rust SDK and word-level timestamps."

**Detail/article sample:** "Designing low-latency voice pipelines" — a 4-paragraph
original article on transport choice, jitter buffers, and measuring time-to-first-audio.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: api-platform`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, runtime provider `Capell\ThemeStudio\ApiPlatform\ApiPlatformThemeServiceProvider`, demo command `capell:theme-api-platform-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-api-platform","theme-api-platform-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-api-platform"]`, health check `theme-api-platform.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **ApiPlatformThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-api-platform`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `code-hero`, `quickstart`, `features`, `sdk-grid`, `api-reference-teaser`, `status-uptime`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. Code-bearing views must `{{ }}`-escape sample text.
5. **ApiPlatformThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes.
6. **InstallApiPlatformThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'api-platform', 'ApiPlatform')`; **DemoCommand** exposes `capell:theme-api-platform-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-api-platform.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-api-platform.css** — navy canvas, sky/violet accents, code-block chrome, monospace stack, status-dot colors. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-api-platform::...')`.
10. **Theme ApiPlatform health check** for `theme-api-platform.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\ApiPlatform\` → `packages/theme-api-platform/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `ApiPlatformThemeDefinitionTest`,
`ApiPlatformThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeApiPlatformHealthCheckTest`. Theme-specific assertions:

- `code-hero` and `quickstart` render escaped code (the rendered HTML contains the
  literal `curl`/`npm install` text but no executable `<script>` running it, no `wire:`).
- `sdk-grid` renders all four languages with `install` and `docsUrl`; `docsUrl` values
  are plain public links (no `signed`, `data-field`, `model_id`).
- `status-uptime` renders static `uptimePercent` figures and never queries the database
  or hits a live status endpoint at render time.
- Demo install seeds all 7 surfaces with Sonari API copy (assert brand name and a
  latency figure appear).

## Verification

```bash
vendor/bin/pest packages/theme-api-platform/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
