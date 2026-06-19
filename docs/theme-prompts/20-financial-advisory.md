# Theme: FinancialAdvisory (`theme-financial-advisory`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
skeleton, manifest, provider wiring, safety rules, and acceptance baseline. This
file only specifies what makes FinancialAdvisory distinct.

## Build this

A trustworthy renderer for accountants, financial advisors, and wealth-management
firms. Deep green and gold convey stewardship and discretion; bordered cards and
restrained motion convey rigour. Sections cover the services (tax, audit, wealth,
planning), the credentialed team (CFP / CPA / CFA), static planning calculators,
firm credentials, and the client segments served — all under a mandatory "not
investment advice" disclaimer. Extends `default` at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Borrow from wealth-management and advisory firm sites (RIA and accounting-firm
homepages): the calm institutional palette, the credentialed-advisor roster, the
"who we serve" segmentation, and the simple planning-calculator widgets. Take the
structure and reassuring tone only — invent the firm, the advisors, the segments,
and the calculator copy. Calculators are STATIC marketing widgets, not live
financial tools.

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) covers generic professional services via `corporate`
but nothing tuned to regulated financial advice. FinancialAdvisory adds advisor
credentials (CFP/CPA/CFA), client-segment targeting, static planning calculators,
and — crucially — the mandatory regulatory disclaimer the vertical requires.

## Package identity

| Field          | Value                                                                             |
| -------------- | --------------------------------------------------------------------------------- |
| package        | `capell-app/theme-financial-advisory`                                             |
| slug           | `theme-financial-advisory`                                                        |
| namespace      | `Capell\ThemeStudio\FinancialAdvisory`                                            |
| themeKey       | `financial-advisory`                                                              |
| displayName    | `Financial Advisory`                                                              |
| tier           | `premium`                                                                         |
| bestFit        | `["Financial advisors", "Accountants", "Wealth management", "Tax & audit firms"]` |
| tags           | `["Finance", "Trust", "Advisory", "Green & gold", "Professional"]`                |
| view namespace | `capell-theme-financial-advisory`                                                 |

## Design direction

| Token              | Value         | Rationale                                             |
| ------------------ | ------------- | ----------------------------------------------------- |
| primaryColor       | `#14532d`     | Deep green — stability, growth, stewardship.          |
| accentColor        | `#b08d57`     | Gold accent for figures, seals, and emphasis.         |
| neutralColor       | `#14211a`     | Dark forest ink for borders and dense copy.           |
| surfaceColor       | `#f8faf8`     | Soft green-tinted white, calm and clean.              |
| foregroundColor    | `#14211a`     | Forest text on pale surface reads composed.           |
| headingFont        | `sora`        | Modern geometric face — competent, not stuffy.        |
| bodyFont           | `inter`       | Neutral body for figures, footnotes, and disclaimers. |
| spacing            | `balanced`    | Orderly and dense, like a well-formatted statement.   |
| alignment          | `left`        | Left-aligned reads as advisory correspondence.        |
| cardStyle          | `bordered`    | Hairline borders signal precision and structure.      |
| navigationStyle    | `prominent`   | Nav carries a standing "Book a consultation".         |
| layoutPresentation | `structured`  | Strict grid conveys rigour and reliability.           |
| motionIntensity    | `subtle`      | Quiet transitions; trust is built calmly.             |
| mediaTreatment     | `flat`        | Flat, formal advisor portraits and chart imagery.     |
| radius             | `md`          | Soft-square corners — modern but not casual.          |
| headingScale       | `balanced`    | Confident headings, never theatrical.                 |
| cardDensity        | `comfortable` | Room for advisor credentials and calculator inputs.   |

Typography: Sora headings, Inter body; gold accent reserved for figures, the firm
mark, and key links. Footnotes and disclaimers set small in `neutralColor`.
Motion: subtle fade/lift on cards (~200ms); honour `prefers-reduced-motion`.

## Sections

`includedSections`:
`["navigation", "hero", "services", "advisors", "calculators", "credentials", "client-segments", "content-listing", "cta", "footer"]`

Inherited from Foundation: `navigation`, `hero`, `content-listing`, `cta`,
`footer`. NEW sections:

- **services** (NEW) — what the firm does.
  Render data:
  `{ heading: string, intro: string, services: [{ name, description }] }`
- **advisors** (NEW) — credentialed team.
  Render data:
  `{ heading: string, team: [{ name, credentials, focus }] }`
- **calculators** (NEW, STATIC) — planning-tool teasers. Each lists input _labels_
  only; nothing computes server-side. Must expose no signed URL, model id, or
  `wire:` binding.
  Render data:
  `{ heading: string, intro: string, calcs: [{ name, inputs: string[] }] }`
- **credentials** (NEW) — registrations and memberships.
  Render data:
  `{ heading: string, items: [{ name }] }`
- **client-segments** (NEW) — who the firm serves.
  Render data:
  `{ heading: string, segments: [{ name, description }] }`

Plus a REQUIRED disclaimer surface (rendered in `footer` and on any
calculator/results page):
`{ disclaimer: string }`.

Each NEW key registers a
`ViewSectionRenderer('financial-advisory', '<key>', 'capell-theme-financial-advisory::sections.<key>', failLoudly: true)`.

## Beta data (demo profile)

Seed via `ThemeDemoPageInstaller::profile()` for key `financial-advisory`. All
copy original. The firm, advisors, and figures are entirely fictional.

- **Brand:** `Sterling & Vale Advisors` — an independent advisory and accounting
  firm.
- **summary:** `An independent advisory firm bringing tax, audit, and wealth planning under one fiduciary roof. Advice you pay us for, not commission we earn elsewhere.`
- **heroHeading:** `Stewardship, not salesmanship.`
- **heroSummary:** `Sterling & Vale is a fee-only advisory firm. We plan, file, audit, and advise as fiduciaries — which simply means your interests come first, always, in writing.`

**features[] (4 — firm principles, type = principle):**

1. `Fee-only, fiduciary` — `We are paid by you, not by the products we recommend. No commissions, no hidden incentives.` (type: principle)
2. `One coordinated team` — `Tax, audit, and wealth planning that actually talk to each other, so nothing falls between advisors.` (type: principle)
3. `Plain-language advice` — `We explain the trade-offs in words you can repeat to your family, not in jargon.` (type: principle)
4. `Long-term by design` — `We measure success in decades and downturns survived, not in this quarter's return.` (type: principle)

**services:**

- `Tax Planning & Preparation` — `Year-round planning and filing for individuals and businesses, built around your actual goals.`
- `Audit & Assurance` — `Independent audits, reviews, and compilations that satisfy lenders, boards, and regulators.`
- `Wealth Management` — `Discretionary and advisory portfolios aligned to your risk, horizon, and values.`
- `Financial Planning` — `Retirement, education, estate, and cash-flow planning in one living document we revisit yearly.`

**advisors (with CFP/CPA/CFA credentials — fictional):**

1. `Grace Sterling` · `CFP®, CFA` · `Wealth Management & Retirement Planning`
2. `Theodore Vale` · `CPA` · `Tax Strategy & Business Advisory`
3. `Naomi Brandt` · `CPA, CA` · `Audit & Assurance`
4. `Rohan Mehta` · `CFP®` · `Estate & Education Planning`

**calculators (STATIC — input labels only):**

- intro `Quick, illustrative tools to start a conversation. They are estimates, not advice, and they assume nothing about your specific situation.`
- `Retirement readiness` · inputs `["Current age", "Target retirement age", "Current savings", "Monthly contribution"]`
- `Tax estimate` · inputs `["Filing status", "Annual income", "Deductions", "State"]`
- `Education funding` · inputs `["Child's age", "Years to college", "Estimated annual cost", "Current 529 balance"]`

**credentials (items):**

- `Registered Investment Adviser (RIA)`
- `Members, AICPA`
- `CFP® professionals on staff`
- `CFA® charterholder on staff`
- `Fee-only — NAPFA-aligned compensation model`

**client-segments:**

- `Individuals & Families` — `Households building, protecting, and passing on wealth across generations.`
- `Business Owners` — `Founders and small businesses needing coordinated tax, audit, and exit planning.`
- `Nonprofits & Foundations` — `Mission-driven organisations needing assurance, governance, and reserve strategy.`

**proof[] (3 metrics with quotes):**

1. metric `$1.2B` · name `Advised assets` · quote `We advise on roughly 1.2 billion dollars across about 600 client households and organisations.`
2. metric `97%` · name `Client retention` · quote `Ninety-seven percent of clients stay with us year over year — most relationships outlast a market cycle.`
3. metric `0` · name `Product commissions` · quote `We earn zero product commissions. Every dollar we are paid comes directly and transparently from our clients.`

**MANDATORY disclaimer (render in footer + on any calculator/results page):**
`Sterling & Vale Advisors is a fictional firm shown for demonstration only. This page is for general information and is not investment, tax, legal, or accounting advice. The calculators are illustrative estimates and do not account for your individual circumstances. Past performance is not a guarantee of future results. Consult a qualified professional before making any financial decision.`

**pathways[] (3 — content-listing pathways variant):**

1. `Services` — `Tax, audit, wealth, and planning, and how they fit together.`
2. `Your advisors` — `The credentialed team you'll actually work with.`
3. `Book a consultation` — `Start with a no-obligation conversation about your situation.`

**Directory sample entries (3+ — directory demo page = services / insights):**

1. `Tax Planning & Preparation` · Service — `Year-round tax planning and filing built around your goals, for individuals and businesses.`
2. `Wealth Management` · Service — `Fiduciary portfolios aligned to your risk tolerance, time horizon, and values.`
3. `Audit & Assurance` · Service — `Independent audits and reviews that satisfy lenders, boards, and regulators.`

**Detail sample (detail demo page):** `Wealth Management` — heading
`Wealth Management`, body covering the approach (fiduciary, fee-only,
goals-first), the process (plan, allocate, review yearly), and who it suits,
followed by the mandatory disclaimer. Include `name: Wealth Management` and a
closing consultation CTA. Repeat the MANDATORY disclaimer on this page.

**ctaHeading:** `Book a consultation.` · **ctaSummary:**
`A no-obligation conversation about your situation, with a fiduciary advisor who is paid only by you.`

## Build steps

1. Scaffold `packages/theme-financial-advisory/` per shared-contract §2. Copy
   `packages/theme-corporate` and add the advisory sections.
2. Write `capell.json` (manifest v3, §3): identity from the table,
   `extends: "capell-app/foundation-theme"`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-financial-advisory-demo"`,
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-financial-advisory", "theme-financial-advisory-frontend"]`,
   `healthChecks: ["financial-advisory.package-health"]`, `database` all false,
   `security.publicOutput` flags true / `riskTier: "low"`.
3. `FinancialAdvisoryThemeServiceProvider`: `register()` empty;
   `boot(ThemeRegistry)` per §4 — demo command, install gate, translations +
   views, CSS via `VendorAssetData::tailwindImport`, Blade sources via
   `tailwindSource`, page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the preset table under one
   `ThemePresetData` (`key: 'stewardship'`), full `includedSections`, tags,
   bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
5. One `ViewSectionRenderer` per customised + NEW section key.
6. `FinancialAdvisoryThemePageAdapter` (§5): map render data to `HeroSectionData`,
   `ProofSectionData`, `FeatureSectionData`, `ContentListingSectionData`,
   `CtaSectionData`, plus the NEW Data shapes (services, advisors, calculators,
   credentials, client-segments). Calculators are STATIC — build from fixed input
   labels, never a live form or query. The adapter ALWAYS injects the mandatory
   `disclaimer` into the footer and any calculator/detail page, falling back to the
   standard text if the page omits one. Empty-state fallbacks for the rest.
7. `InstallFinancialAdvisoryThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'financial-advisory', 'FinancialAdvisory')`;
   wire `DemoCommand` (`capell:theme-financial-advisory-demo`); add the
   `financial-advisory` profile entry (§6) with all copy above INCLUDING the
   mandatory disclaimer.
8. `resources/views/page.blade.php`: skip link, `data-capell-theme`, brand tokens
   inline, `{!! $content !!}`,
   `@frontendAsset('css/theme-financial-advisory.css')`. Thin
   `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`.
   Footer Blade must render the mandatory disclaimer visibly.
9. `resources/css/theme-financial-advisory.css`: green/gold palette, bordered
   cards, static calculator styling, small-print disclaimer styling — no authoring
   markers.
10. `resources/lang/en/generic.php` for every user-facing string, including a
    `disclaimer.not_investment_advice` default.
11. `FinancialAdvisoryThemeHealthCheck` registered as
    `financial-advisory.package-health`.
12. Add both PSR-4 entries to `composer.json` AND `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (shared-contract §10): `ManifestRequirementsTest`,
`FinancialAdvisoryThemeDefinitionTest`, `FinancialAdvisoryThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`,
`FinancialAdvisoryThemeHealthCheckTest`. Plus theme-specific:

- Definition `includedSections` contains `services`, `advisors`, `calculators`,
  `credentials`, and `client-segments`.
- The advisors section renders each member with non-empty `credentials` (e.g.
  `CFP®`, `CPA`, `CFA`).
- A page-adapter test asserts the rendered output contains a visible
  `not investment advice` disclaimer (case-insensitive) on the homepage footer and
  on a calculator/detail page, even when the page render data omits one.
- The calculators section is STATIC — `PublicOutputSafetyTest` confirms its Blade
  has no `wire:`, no `signed`, no form action targeting an admin route, and no DB
  queries.

## Verification

```bash
vendor/bin/pest packages/theme-financial-advisory/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
