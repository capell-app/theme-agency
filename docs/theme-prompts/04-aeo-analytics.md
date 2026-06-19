# Theme: AeoAnalytics (`theme-aeo-analytics`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-aeo-analytics` distinct.

## Build this

Build a premium Capell child theme for an answer-engine-optimization (AEO) /
brand-visibility analytics SaaS — a product that tracks how a brand shows up across AI
answer engines (ChatGPT, Perplexity, Gemini, Copilot). The page is dashboard-led: a
hero that previews the analytics dashboard, metric cards for share-of-voice and
citations, an engine coverage map, a report gallery, and integrations. The look is a
clean white analytics surface with a confident violet primary and a data-lime accent.
Extend `default` (Foundation Theme) at runtime and `capell-app/foundation-theme` at the
package level.

## Inspiration

Browser tab: **Profound** for the dashboard-forward hero (a product screenshot/preview
above the fold), the share-of-voice and citation metric framing, and the engine-coverage
breakdown. Borrow the moves — a dashboard preview rendered from static tile/chart data,
big metric cards, a coverage map across named engines, and a polished report gallery —
and the analytical, "see your brand the way AI sees it" tone. Do **not** copy their copy
or numbers; invent original demo content (brand "Visible AEO").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has no analytics/dashboard-led product theme. SaaS sells software
broadly; this sells a _measurement dashboard_ with charts-as-data and a coverage map —
a distinctly data-viz presentation no other theme provides. It is also the only theme in
the set framed around AI answer engines as a measurement surface.

## Package identity

| Field       | Value                                                                                                 |
| ----------- | ----------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-aeo-analytics`                                                                      |
| slug        | `theme-aeo-analytics`                                                                                 |
| namespace   | `Capell\ThemeStudio\AeoAnalytics`                                                                     |
| themeKey    | `aeo-analytics`                                                                                       |
| displayName | `AEO Analytics`                                                                                       |
| tier        | premium                                                                                               |
| bestFit     | `["AEO/GEO analytics SaaS", "Brand visibility tools", "AI search monitoring", "Marketing analytics"]` |
| tags        | `["Analytics", "Dashboard", "AI Search", "Data", "SaaS"]`                                             |

## Design direction

| Token              | Value         | Rationale                                                     |
| ------------------ | ------------- | ------------------------------------------------------------- |
| primaryColor       | `#7c3aed`     | Violet — distinctive analytics brand color, charts pop on it. |
| accentColor        | `#84cc16`     | Data-lime accent for positive trends and highlights.          |
| neutralColor       | `#1e1b2e`     | Deep plum-slate for headings and axis labels.                 |
| surfaceColor       | `#ffffff`     | Pure white dashboard canvas.                                  |
| foregroundColor    | `#1e1b2e`     | Plum-slate body text for crisp data legibility.               |
| headingFont        | `sora`        | Modern geometric headings with a product feel.                |
| bodyFont           | `inter`       | Neutral body; great for dense metric labels.                  |
| spacing            | `balanced`    | Tight enough for dashboards, open enough to read charts.      |
| alignment          | `left`        | Dashboard/report reading order.                               |
| cardStyle          | `elevated`    | Soft-shadow tiles read like dashboard widgets.                |
| navigationStyle    | `prominent`   | Strong nav with a "Start free audit" CTA.                     |
| layoutPresentation | `structured`  | Grid-aligned, dashboard-like composition.                     |
| motionIntensity    | `subtle`      | Quiet bar/line reveal styling; no spinning charts.            |
| mediaTreatment     | `framed`      | Framed dashboard screenshots and chart panels.                |
| radius             | `md`          | 8px — clean SaaS dashboard corners.                           |
| headingScale       | `balanced`    | Headlines support the data, don't overwhelm.                  |
| cardDensity        | `comfortable` | Metric and report cards need label and figure room.           |

Typography: headings in **Sora**, body in **Inter**, all figures in `tabular-nums`.
Charts are rendered from static data arrays into CSS/SVG bars and lines (no live chart
library fetching data); motion is a subtle reveal; respect `prefers-reduced-motion`.

## Sections

`includedSections`:
`["navigation", "hero", "dashboard-preview", "metric-cards", "features", "coverage-map", "integrations-grid", "report-gallery", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **dashboard-preview** — `heading`, `summary`, `tiles[]` each `{ label, value, trend }`, `chart` `{ label, series[] }` where `series[]` are `{ point, value }` (static chart-as-data).
- **metric-cards** — `heading`, `summary`, `cards[]` each `{ key, label, value, delta }` (keys include `shareOfVoice`, `citations`, `sentiment`).
- **integrations-grid** — `heading`, `summary`, `tools[]` each `{ name, category }`.
- **report-gallery** — `heading`, `summary`, `reports[]` each `{ title, type }`.
- **coverage-map** — `heading`, `summary`, `engines[]` each `{ name, visibilityPercent }`.

`content-listing` uses the `gallery` variant for the insights/blog feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Visible AEO — "See your brand the way AI sees it."

**Hero:** heading "Your brand's answer-engine scoreboard." Summary: "Track every time
ChatGPT, Perplexity, Gemini, and Copilot mention, cite, or recommend you — and see
exactly where you're winning and where you're invisible."

**Dashboard preview (dashboard-preview):** heading "One dashboard, every answer engine."
tiles:

- label "Share of voice" — value "34%" — trend "up".
- label "Total citations" — value "1,284" — trend "up".
- label "Sentiment" — value "+18" — trend "up".
- label "Tracked prompts" — value "412" — trend "flat".
  chart: label "Citations, last 8 weeks" — series `[{W1, 410},{W2, 470},{W3, 520},{W4, 610},{W5, 700},{W6, 880},{W7, 1040},{W8, 1284}]`.

**Metric cards (metric-cards):** heading "The three numbers that matter."

- key "shareOfVoice" — label "Share of voice" — value "34%" — delta "+9 pts vs last quarter".
- key "citations" — label "Citations" — value "2.1x" — delta "up 2.1x in 90 days".
- key "sentiment" — label "Sentiment" — value "+18" — delta "+12 vs baseline".

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Multi-engine tracking" / "Monitor ChatGPT, Perplexity, Gemini, and Copilot from one view." / capability.
- "Citation source breakdown" / "See which pages get cited so you can double down on them." / capability.
- "Sentiment monitoring" / "Know whether engines describe you positively, neutrally, or not." / capability.
- "Competitor share of voice" / "Benchmark your visibility against named rivals." / capability.
- "Scheduled reports" / "Weekly visibility digests delivered to your team automatically." / workflow.

**Coverage map (coverage-map):** heading "Where you show up."

- ChatGPT — visibilityPercent `41`.
- Perplexity — visibilityPercent `52`.
- Gemini — visibilityPercent `28`.
- Copilot — visibilityPercent `33`.

**Integrations grid (integrations-grid):** heading "Connect your stack."

- Google Analytics — category "Web analytics".
- HubSpot — category "Marketing".
- Slack — category "Alerts".
- Looker — category "BI".
- Search Console — category "Search".
- Zapier — category "Automation".

**Report gallery (report-gallery):** heading "Reports your team will actually read."

- "Weekly visibility digest" — type "Recurring".
- "Citation source audit" — type "On-demand".
- "Competitor share-of-voice" — type "Benchmark".
- "Sentiment trend report" — type "Recurring".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "2.1x" / "More AI citations in 90 days" / "We finally know which content the answer engines actually quote." — Sofia Marchetti, Head of SEO, Cobalt.
- "34%" / "Share of voice in our category" / "Visible turned 'are we even mentioned?' into a number we track weekly." — Daniel Frost, CMO, Wayline.
- "+18" / "Sentiment improvement" / "We caught a negative narrative early and corrected it." — Reena Patel, Brand Lead, Halcyon.

**Spotlight / pathways:** spotlight "How Cobalt tripled its AI citations" with pathways
"Run a free audit", "See the coverage map", "Book a walkthrough".

**Directory samples (content-listing, 3+):**

- "AEO vs SEO: what actually changes" — type "Guide" — "How answer engines pick and cite sources."
- "Getting cited by Perplexity" — type "Playbook" — "Structuring content for citation."
- "Q2 AI search visibility report" — type "Report" — "Category-wide visibility benchmarks."

**Detail/article sample:** "How to measure share of voice across answer engines" — a
4-paragraph original article on prompt sampling, citation parsing, and normalizing across
engines.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: aeo-analytics`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, runtime provider `Capell\ThemeStudio\AeoAnalytics\AeoAnalyticsThemeServiceProvider`, demo command `capell:theme-aeo-analytics-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-aeo-analytics","theme-aeo-analytics-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-aeo-analytics"]`, health check `theme-aeo-analytics.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **AeoAnalyticsThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `isPackageInstalled`, loads translations + views (`capell-theme-aeo-analytics`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `dashboard-preview`, `metric-cards`, `features`, `coverage-map`, `integrations-grid`, `report-gallery`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`.
5. **AeoAnalyticsThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes; the dashboard chart series renders to static SVG/CSS bars.
6. **InstallAeoAnalyticsThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'aeo-analytics', 'AeoAnalytics')`; **DemoCommand** exposes `capell:theme-aeo-analytics-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-aeo-analytics.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-aeo-analytics.css** — white canvas, violet primary, lime accent, dashboard-tile shadows, CSS chart bars, `tabular-nums`. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-aeo-analytics::...')`.
10. **Theme AeoAnalytics health check** for `theme-aeo-analytics.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\AeoAnalytics\` → `packages/theme-aeo-analytics/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `AeoAnalyticsThemeDefinitionTest`,
`AeoAnalyticsThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeAeoAnalyticsHealthCheckTest`. Theme-specific assertions:

- `metric-cards` exposes `shareOfVoice`, `citations`, and `sentiment` keys; `coverage-map`
  renders an entry per engine with a `visibilityPercent`.
- `dashboard-preview` chart `series` renders as static SVG/CSS markup — no chart library
  fetch, no `wire:`, no database query at render time.
- `report-gallery` renders every report with `title` and `type`; no `signed`,
  `data-field`, or `model_id` in output.
- Demo install seeds all 7 surfaces with Visible AEO copy (assert brand name and the
  "34%" share-of-voice figure appear).

## Verification

```bash
vendor/bin/pest packages/theme-aeo-analytics/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
