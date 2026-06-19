# Theme: LawFirm (`theme-law-firm`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
skeleton, manifest, provider wiring, safety rules, and acceptance baseline. This
file only specifies what makes LawFirm distinct.

## Build this

An authoritative renderer for a legal / professional-services firm. It projects
gravity and credibility: a serif display face, navy-and-gold restraint, bordered
cards, almost no motion. Sections are built around what a prospective client and
a referring professional look for — practice areas, the attorneys and their bar
admissions, representative results (with the mandatory legal disclaimer), firm
credentials, and a consultation booking path. Extends `default` at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Borrow from premier legal-firm websites (large litigation and corporate firms):
the calm, dense, serif-led layout; the attorney roster with credentials and bar
admissions; the "representative matters" results list; and the rankings/awards
credential strip. Take the structure, tone, and information architecture only —
invent the firm, every attorney, every matter, and every outcome. Do not imply
any real firm or real case.

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas) has `corporate` for generic B2B and professional
services, but nothing tuned for a regulated legal practice. LawFirm adds the
domain furniture corporate lacks: practice areas, attorney bar admissions, a
results section that _requires_ a results-disclaimer block, and a consultation CTA
framed for legal intake.

## Package identity

| Field          | Value                                                                                     |
| -------------- | ----------------------------------------------------------------------------------------- |
| package        | `capell-app/theme-law-firm`                                                               |
| slug           | `theme-law-firm`                                                                          |
| namespace      | `Capell\ThemeStudio\LawFirm`                                                              |
| themeKey       | `law-firm`                                                                                |
| displayName    | `Law Firm`                                                                                |
| tier           | `premium`                                                                                 |
| bestFit        | `["Law firms", "Barristers' chambers", "Legal practices", "Professional-services firms"]` |
| tags           | `["Legal", "Authoritative", "Serif", "Professional", "Navy & gold"]`                      |
| view namespace | `capell-theme-law-firm`                                                                   |

## Design direction

| Token              | Value         | Rationale                                            |
| ------------------ | ------------- | ---------------------------------------------------- |
| primaryColor       | `#1e293b`     | Navy — sober, institutional, trustworthy.            |
| accentColor        | `#b08d57`     | Muted gold for seals, rules, and emphasis.           |
| neutralColor       | `#0f172a`     | Deep slate for borders and dense text.               |
| surfaceColor       | `#f7f5f2`     | Warm parchment — establishment, not start-up white.  |
| foregroundColor    | `#1e293b`     | Navy text on parchment reads formal and legible.     |
| headingFont        | `fraunces`    | High-contrast serif signals tradition and authority. |
| bodyFont           | `inter`       | Neutral sans keeps long legal copy readable.         |
| spacing            | `balanced`    | Dense but orderly, like a well-set legal document.   |
| alignment          | `left`        | Left-aligned reads as formal correspondence.         |
| cardStyle          | `bordered`    | Hairline borders evoke letterhead and precision.     |
| navigationStyle    | `prominent`   | Clear nav with a standing "Request a consultation".  |
| layoutPresentation | `structured`  | Strict grid signals rigour and reliability.          |
| motionIntensity    | `none`        | Stillness conveys seriousness; nothing animates.     |
| mediaTreatment     | `flat`        | Flat, formal attorney portraits.                     |
| radius             | `sm`          | Minimal rounding — close to square, never playful.   |
| headingScale       | `balanced`    | Authoritative headings that don't theatricalise.     |
| cardDensity        | `comfortable` | Room for attorney credentials and matter summaries.  |

Typography: Fraunces for headings (formal display setting), Inter for body and
credentials; gold accent reserved for rules, the firm seal, and key links. Motion:
none — do not add scroll reveals. Honour `prefers-reduced-motion` by default
anyway.

## Sections

`includedSections`:
`["navigation", "hero", "practice-areas", "attorneys", "case-results", "credentials", "consultation-cta", "content-listing", "cta", "footer"]`

Inherited from Foundation: `navigation`, `hero`, `content-listing`, `cta`,
`footer`. NEW sections:

- **practice-areas** (NEW) — what the firm handles.
  Render data:
  `{ heading: string, intro: string, areas: [{ name, description }] }`
- **attorneys** (NEW) — the roster, with credentials.
  Render data:
  `{ heading: string, lawyers: [{ name, title, focus, barAdmission }] }`
- **case-results** (NEW) — representative matters, ALWAYS paired with a
  disclaimer.
  Render data:
  `{ heading: string, results: [{ matter, outcome }], disclaimer: string }`
  The `disclaimer` field is REQUIRED and must render visibly with the results.
- **credentials** (NEW) — bar admissions, rankings, memberships.
  Render data:
  `{ heading: string, items: [{ name }] }`
- **consultation-cta** (NEW) — legal intake call to action.
  Render data:
  `{ heading: string, body: string, buttonLabel: string }`

Each NEW key registers a
`ViewSectionRenderer('law-firm', '<key>', 'capell-theme-law-firm::sections.<key>', failLoudly: true)`.

## Beta data (demo profile)

Seed via `ThemeDemoPageInstaller::profile()` for key `law-firm`. All copy
original. The firm, attorneys, and matters are entirely fictional.

- **Brand:** `Harlow & Finch LLP` — a full-service business law firm.
- **summary:** `A business law firm advising founders, boards, and institutions on the decisions that matter most. Counsel that is candid, prepared, and discreet.`
- **heroHeading:** `Counsel for the decisions that matter.`
- **heroSummary:** `Harlow & Finch advises companies and their leaders through formation, growth, disputes, and exits. We bring senior attention to every matter and tell you what we actually think.`

**features[] (4 — practice strengths, type = strength):**

1. `Senior attention` — `Partners lead every engagement. The lawyer who pitches your matter is the lawyer who works it.` (type: strength)
2. `Prepared, not posturing` — `We win on preparation and judgement, not bluster. We tell you the weaknesses before opposing counsel does.` (type: strength)
3. `Cross-disciplinary` — `Corporate, litigation, IP, and employment lawyers who actually talk to each other on your matter.` (type: strength)
4. `Discreet by default` — `Sensitive matters handled quietly. Confidentiality is the baseline, not a premium add-on.` (type: strength)

**practice-areas:**

- `Corporate & Transactions` — `Formation, financings, M&A, governance, and the commercial agreements that run a business.`
- `Litigation & Disputes` — `Commercial litigation, arbitration, and pre-action strategy across courts and tribunals.`
- `Intellectual Property` — `Trademark and copyright protection, licensing, and IP disputes for brand-driven companies.`
- `Employment` — `Executive agreements, workforce policy, investigations, and the difficult exits.`

**attorneys (with bar admissions — fictional):**

1. `Eleanor Harlow` · `Managing Partner` · `Corporate & M&A` · `Admitted: New York, England & Wales`
2. `Marcus Finch` · `Partner, Head of Litigation` · `Commercial Disputes` · `Admitted: New York, Illinois`
3. `Priya Raman` · `Partner` · `Intellectual Property` · `Admitted: California, USPTO`
4. `Daniel Okafor` · `Senior Associate` · `Employment` · `Admitted: New York`

**case-results (representative matters — WITH MANDATORY DISCLAIMER):**

- results:
    1. matter `Cross-border acquisition` · outcome `Closed a $140M acquisition of a logistics company across three jurisdictions in nine weeks.`
    2. matter `Commercial arbitration` · outcome `Secured a full defence award for a manufacturing client in an ICC arbitration.`
    3. matter `Trademark dispute` · outcome `Resolved a contested mark for a consumer brand without litigation, preserving the launch date.`
- **disclaimer (MANDATORY, render visibly):**
  `Prior results do not guarantee a similar outcome. Each matter is different and depends on its own facts. The matters described are representative and have been generalised to protect client confidentiality. Nothing on this page is legal advice or forms an attorney–client relationship.`

**credentials (items):**

- `Ranked in Chambers (Corporate) — Band 2`
- `Recognised by The Legal 500 for Commercial Litigation`
- `Members, American Bar Association`
- `Members, International Bar Association`
- `Registered trademark attorneys before the USPTO`

**consultation-cta:** heading `Request a consultation` · body
`Tell us briefly about your matter through our secure intake form. A partner will review your enquiry and respond within one business day. Initial consultations are confidential.` · buttonLabel `Request a consultation`

**proof[] (3 metrics with quotes):**

1. metric `35 yrs` · name `In practice` · quote `Thirty-five years advising companies through formation, growth, dispute, and exit.`
2. metric `4` · name `Core practices` · quote `Corporate, litigation, IP, and employment lawyers under one roof, on one matter.`
3. metric `1 day` · name `Intake response` · quote `Every consultation request is reviewed by a partner and answered within one business day.`

**pathways[] (3 — content-listing pathways variant):**

1. `Practice areas` — `Where we work and what we handle.`
2. `Our attorneys` — `The partners and associates who will run your matter.`
3. `Request a consultation` — `Start a confidential conversation about your matter.`

**Directory sample entries (3+ — directory demo page = practice areas / insights):**

1. `Corporate & Transactions` · Practice area — `Formation, financings, M&A, and governance for companies at every stage.`
2. `Litigation & Disputes` · Practice area — `Commercial litigation and arbitration, with a preparation-first approach.`
3. `Intellectual Property` · Practice area — `Trademark and copyright protection, licensing, and IP disputes.`

**Detail sample (detail demo page):** `Corporate & Transactions` — heading
`Corporate & Transactions`, body covering what the practice does (formation
through exit), how the firm staffs it (partner-led), and a representative matter,
followed by the same case-results disclaimer. Include `name: Corporate & Transactions`
and a closing consultation-cta. Repeat the MANDATORY disclaimer on any page that
shows a result.

**ctaHeading:** `Speak with a partner.` · **ctaSummary:**
`Confidential consultations, reviewed by a partner, answered within one business day.`

## Build steps

1. Scaffold `packages/theme-law-firm/` per shared-contract §2. Copy
   `packages/theme-corporate` (closest professional base) and add the legal
   sections.
2. Write `capell.json` (manifest v3, §3): identity from the table,
   `extends: "capell-app/foundation-theme"`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`,
   `commands.demo: "capell:theme-law-firm-demo"`,
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-law-firm", "theme-law-firm-frontend"]`,
   `healthChecks: ["law-firm.package-health"]`, `database` all false,
   `security.publicOutput` flags true / `riskTier: "low"`.
3. `LawFirmThemeServiceProvider`: `register()` empty; `boot(ThemeRegistry)` per §4
   — demo command, install gate, translations + views, CSS via
   `VendorAssetData::tailwindImport`, Blade sources via `tailwindSource`, page
   adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the preset table under one
   `ThemePresetData` (`key: 'chambers'`), full `includedSections`, tags, bestFit,
   assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
5. One `ViewSectionRenderer` per customised + NEW section key.
6. `LawFirmThemePageAdapter` (§5): map render data to `HeroSectionData`,
   `FeatureSectionData`, `ProofSectionData`, `ContentListingSectionData`,
   `CtaSectionData`, plus the NEW Data shapes (practice-areas, attorneys,
   case-results, credentials, consultation-cta). The case-results Data MUST carry
   a non-empty `disclaimer`; the adapter falls back to the standard disclaimer text
   if the page omits one. Empty-state fallbacks for the rest.
7. `InstallLawFirmThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'law-firm', 'LawFirm')`; wire `DemoCommand`
   (`capell:theme-law-firm-demo`); add the `law-firm` profile entry (§6) with all
   copy above INCLUDING the mandatory disclaimer.
8. `resources/views/page.blade.php`: skip link, `data-capell-theme`, brand tokens
   inline, `{!! $content !!}`, `@frontendAsset('css/theme-law-firm.css')`. Thin
   `livewire/page/page.blade.php` calling `RenderCurrentThemePageAction::run()`.
   The case-results Blade must render the `disclaimer` in a visible
   `<p class="...">` near the matters, not hidden.
9. `resources/css/theme-law-firm.css`: navy/gold/parchment palette, bordered cards,
   serif display scale, formal results-disclaimer styling — no authoring markers.
10. `resources/lang/en/generic.php` for every user-facing string, including a
    `case_results.disclaimer` default.
11. `LawFirmThemeHealthCheck` registered as `law-firm.package-health`.
12. Add both PSR-4 entries to `composer.json` AND `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (shared-contract §10): `ManifestRequirementsTest`,
`LawFirmThemeDefinitionTest`, `LawFirmThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`, `LawFirmThemeHealthCheckTest`. Plus
theme-specific:

- Definition `includedSections` contains `practice-areas`, `attorneys`,
  `case-results`, `credentials`, and `consultation-cta`.
- The attorneys section renders each lawyer with a non-empty `barAdmission`.
- The case-results section ALWAYS renders a visible `disclaimer`; a page-adapter
  test asserts the rendered HTML contains
  `prior results do not guarantee a similar outcome` (case-insensitive) even when
  the page render data omits a custom disclaimer.
- `PublicOutputSafetyTest` confirms attorney/results Blade has no package name,
  `wire:`, `signed`, `data-field`, or DB queries.

## Verification

```bash
vendor/bin/pest packages/theme-law-firm/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
