# Theme: QuantTrading (`theme-quant-trading`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-quant-trading` distinct.

## Build this

Build a premium Capell child theme for an algorithmic / quantitative trading firm or
product. The page reads like a serious systematic shop: a dark terminal-grade surface, a
performance equity curve rendered from static data, strategy cards with Sharpe and
drawdown, headline CAGR/Sharpe/win-rate metric cards, a year-by-year track-record table,
and a PROMINENT risk-disclosure block. Every figure is static demo content — no live
market data, no order entry, no broker integration. The risk disclosure is MANDATORY and
must be unmissable. Extend `default` (Foundation Theme) at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Browser tab: **Blackalgo** for the systematic-trading aesthetic — dark terminal canvas, a
prominent equity/performance curve, strategy breakdowns with quant metrics, and a
year-by-year track record, all wrapped in compliance-aware framing. Borrow the moves —
equity-curve hero, strategy cards keyed on Sharpe/drawdown, a CAGR/Sharpe/win-rate metric
row, and a returns-by-year table — and the precise, numbers-first, disclaimer-forward
tone. Do **not** copy their copy or numbers; invent original demo content (brand
"Meridian Quant") and keep all figures static.

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing for finance/trading. It is the only theme that renders an
equity curve, quant strategy metrics (Sharpe, max drawdown), and a year-by-year track
record — and the only one that foregrounds a regulatory risk disclosure as a primary
section, all as safe static content.

## Package identity

| Field       | Value                                                                                                    |
| ----------- | -------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-quant-trading`                                                                         |
| slug        | `theme-quant-trading`                                                                                    |
| namespace   | `Capell\ThemeStudio\QuantTrading`                                                                        |
| themeKey    | `quant-trading`                                                                                          |
| displayName | `Quant Trading`                                                                                          |
| tier        | premium                                                                                                  |
| bestFit     | `["Quant trading firms", "Algorithmic strategy products", "Systematic funds", "Trading research teams"]` |
| tags        | `["Quant", "Trading", "Finance", "Dark", "Data"]`                                                        |

## Design direction

| Token              | Value           | Rationale                                                |
| ------------------ | --------------- | -------------------------------------------------------- |
| primaryColor       | `#2dd4bf`       | Teal — the "gain"/equity-curve color on a dark terminal. |
| accentColor        | `#f43f5e`       | Loss-red, reserved strictly for drawdowns and negatives. |
| neutralColor       | `#161b22`       | GitHub-dark-style panel neutral for cards and tables.    |
| surfaceColor       | `#0b0e14`       | Deep terminal-black canvas.                              |
| foregroundColor    | `#e6edf3`       | Cool light-grey body text, easy for dense figures.       |
| headingFont        | `space-grotesk` | Technical display headings with a quant feel.            |
| bodyFont           | `inter`         | Inter for prose; monospace numerals for all figures.     |
| spacing            | `balanced`      | Dense enough for tables, readable enough for prose.      |
| alignment          | `left`          | Report/spec reading order.                               |
| cardStyle          | `bordered`      | Thin borders read like terminal panels and data grids.   |
| navigationStyle    | `minimal`       | Slim top bar; "Request access" is the only CTA.          |
| layoutPresentation | `structured`    | Grid-aligned, data-room composition.                     |
| motionIntensity    | `subtle`        | Quiet curve draw-in; nothing that reads as hype.         |
| mediaTreatment     | `framed`        | Framed chart and table panels.                           |
| radius             | `sm`            | 4px corners read precise and terminal-like.              |
| headingScale       | `balanced`      | Restraint; the numbers carry the page.                   |
| cardDensity        | `compact`       | Strategy and metric cards pack figures tightly.          |

Typography: headings in **Space Grotesk**, body in **Inter**, all numerals in a
monospace stack with `tabular-nums` so columns align. Motion: a subtle equity-curve
draw-in via CSS only; respect `prefers-reduced-motion`. No live tickers, no order entry,
no broker/market-data code anywhere.

## Sections

`includedSections`:
`["navigation", "hero", "performance-chart", "metric-cards", "strategy-cards", "features", "track-record-table", "risk-disclosure", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **performance-chart** — `heading`, `summary`, `equityCurve` `{ label, points[] }` where `points[]` are `{ date, value }` (static; rendered to SVG/CSS).
- **strategy-cards** — `heading`, `summary`, `strategies[]` each `{ name, sharpe, maxDrawdown, assetClass }`.
- **risk-disclosure** — `heading`, `body` (a prominent, legally-worded static block).
- **metric-cards** — `heading`, `summary`, `cards[]` each `{ key, label, value }` (keys include `cagr`, `sharpe`, `winRate`).
- **track-record-table** — `heading`, `summary`, `years[]` each `{ year, return }`.

`content-listing` uses the `spotlight` variant for the research/notes feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original; all figures static.

**Brand:** Meridian Quant — "Systematic strategies, disciplined risk."

**Hero:** heading "Returns engineered by research, governed by risk." Summary: "Meridian
runs a diversified book of systematic strategies across equities, futures, and FX —
backed by transparent reporting and hard risk limits. Figures shown are illustrative."

**Performance chart (performance-chart):** heading "Composite equity curve (illustrative)."
equityCurve label "Composite, growth of $100" — points:
`[{2019-01, 100},{2019-07, 109},{2020-01, 118},{2020-07, 126},{2021-01, 141},{2021-07, 155},{2022-01, 168},{2022-07, 162},{2023-01, 184},{2023-07, 201},{2024-01, 223},{2024-07, 241},{2025-01, 266},{2025-07, 281}]`.

**Metric cards (metric-cards):** heading "Headline figures (illustrative)."

- key "cagr" — label "CAGR" — value "18.2%".
- key "sharpe" — label "Sharpe ratio" — value "2.1".
- key "winRate" — label "Win rate" — value "61%".
- key "maxDrawdown" — label "Max drawdown" — value "−8.4%".

**Strategy cards (strategy-cards):** heading "The book."

- name "Cross-Asset Momentum" — sharpe "1.9" — maxDrawdown "−9.2%" — assetClass "Equities & Futures".
- name "Statistical Arbitrage" — sharpe "2.4" — maxDrawdown "−5.1%" — assetClass "Equities".
- name "Volatility Carry" — sharpe "1.7" — maxDrawdown "−11.0%" — assetClass "Options & FX".
- name "Trend Following" — sharpe "1.5" — maxDrawdown "−12.8%" — assetClass "Futures".

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Research-driven signals" / "Every strategy starts as a falsifiable hypothesis, not a hunch." / process.
- "Hard risk limits" / "Position, sector, and portfolio limits enforced in code, pre-trade." / risk.
- "Daily transparency" / "Investors see attribution, exposures, and drawdowns daily." / reporting.
- "Low correlation" / "Strategies are selected to diversify, not to stack the same bet." / construction.
- "Independent operations" / "Execution, risk, and research are separated by design." / governance.

**Track record table (track-record-table):** heading "Returns by year (illustrative)."

- 2019 — return "+9.4%".
- 2020 — return "+15.1%".
- 2021 — return "+19.8%".
- 2022 — return "−2.3%".
- 2023 — return "+21.6%".
- 2024 — return "+17.9%".
- 2025 — return "+12.4% (YTD)".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "2.1" / "Composite Sharpe (illustrative)" / "Risk-adjusted, not just headline returns — that's the point." — David Reinholt, CIO, Sterling Endowment.
- "−8.4%" / "Max drawdown" / "Their drawdown discipline is why we allocated." — Naomi Chen, Portfolio Manager, Atlas Multi-Family Office.
- "7 yrs" / "Track record" / "Consistency across regimes matters more than any single year." — Faisal Rahman, Head of Manager Research, Beacon.

**Spotlight / pathways:** spotlight "How Meridian constructs a low-correlation book" with
pathways "Read the methodology", "See the strategies", "Request data-room access".

**Directory samples (content-listing, 3+):**

- "Methodology overview" — type "Research" — "Signal construction, sizing, and risk limits."
- "Risk framework" — type "Reference" — "Pre-trade checks and portfolio constraints."
- "2024 annual letter" — type "Letter" — "Attribution and lessons from the year."

**Detail/article sample:** "Why we report drawdowns first" — a 4-paragraph original
article on risk-adjusted thinking, regime changes, and honest reporting.

**Risk disclosure (MANDATORY prominent section + footer):** rendered as a prominent,
clearly labeled block — heading "Important risk disclosure", body:
"Past performance does not guarantee future results. All figures, charts, and track
records shown on this page are illustrative demonstration values and do not represent
actual client returns. Trading involves substantial risk of loss and is not suitable for
every investor. Nothing on this page is investment advice, a solicitation, or an offer to
buy or sell any security. Consult a qualified financial advisor before investing." Build
must include NO live market data, ticker, order-entry, or broker-integration code.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: quant-trading`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, runtime provider `Capell\ThemeStudio\QuantTrading\QuantTradingThemeServiceProvider`, demo command `capell:theme-quant-trading-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-quant-trading","theme-quant-trading-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-quant-trading"]`, health check `theme-quant-trading.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **QuantTradingThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `isPackageInstalled`, loads translations + views (`capell-theme-quant-trading`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `performance-chart`, `metric-cards`, `strategy-cards`, `features`, `track-record-table`, `risk-disclosure`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The performance-chart renders static points to SVG/CSS.
5. **QuantTradingThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes plus the risk-disclosure block.
6. **InstallQuantTradingThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'quant-trading', 'QuantTrading')`; **DemoCommand** exposes `capell:theme-quant-trading-demo`; add the **profile()** entry with all copy above, including the risk disclosure.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-quant-trading.css')`. Keep `livewire/page/page.blade.php` thin. No market-data scripts.
8. **resources/css/theme-quant-trading.css** — terminal-black canvas, teal gain / red loss, bordered panels, monospace `tabular-nums` figures, SVG equity-curve styling; respect `prefers-reduced-motion`. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-quant-trading::...')`, including the risk-disclosure text.
10. **Theme QuantTrading health check** for `theme-quant-trading.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\QuantTrading\` → `packages/theme-quant-trading/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `QuantTradingThemeDefinitionTest`,
`QuantTradingThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeQuantTradingHealthCheckTest`. Theme-specific assertions:

- `metric-cards` exposes `cagr`, `sharpe`, and `winRate` keys; `strategy-cards` renders
  every strategy with `sharpe`, `maxDrawdown`, and `assetClass`; `track-record-table`
  renders a row per year with a `return`.
- The rendered public HTML contains the mandatory risk disclosure prominently (assert the
  phrase "Past performance does not guarantee future results" appears).
- `performance-chart` renders the static `equityCurve` points to SVG/CSS — no live market
  data fetch, no `wire:`, `signed`, `data-field`, `model_id`, or database query at render
  time; no order-entry or broker code anywhere.
- Demo install seeds all 7 surfaces with Meridian Quant copy (assert brand name, the
  "18.2%" CAGR figure, and the risk disclosure appear).

## Verification

```bash
vendor/bin/pest packages/theme-quant-trading/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
