# Theme: Manufacturing (`theme-manufacturing`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-manufacturing` distinct.

## Build this

Build a premium Capell child theme for an industrial manufacturer / smart factory / B2B
supplier — the kind of site that wins trust with capabilities, certifications, facility
stats, and case studies, then converts a procurement engineer with a request-for-quote
form and downloadable spec sheets. Everything renders from query-free hydrated render data:
the process capabilities grid, the certifications row, the facility statistics, the
case-study outcomes, the datasheet list, and a static RFQ form definition. The aesthetic is
steel blue and safety amber — precise, dense, no-nonsense industrial. Extend `default`
(Foundation Theme) at runtime and `capell-app/foundation-theme` at the package level.

## Inspiration

Browser tab: **Humble (smart manufacturing)** for the modern industrial framing — capability
grids, certification badges as trust currency, plant statistics, and case studies tied to
named industries. Borrow the moves: a capabilities grid of named processes, a certifications
strip, hard facility stats, industry case studies with outcomes, a static RFQ form, and a
downloadable datasheet list. Do **not** copy their copy, plant, certifications, or numbers;
invent original demo content (brand "Forge Industries").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing for heavy industry or B2B precision supply. Corporate is
restrained but generic; local-services is for trades and click-to-call, not a 120,000 sq ft
plant with CNC lines and aerospace certifications. This is the only theme that renders a
process-capability grid, ISO/IATF/AS certification trust marks, plant statistics, and an
RFQ procurement flow.

## Package identity

| Field       | Value                                                                                                    |
| ----------- | -------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-manufacturing`                                                                         |
| slug        | `theme-manufacturing`                                                                                    |
| namespace   | `Capell\ThemeStudio\Manufacturing`                                                                       |
| themeKey    | `manufacturing`                                                                                          |
| displayName | `Manufacturing`                                                                                          |
| tier        | premium                                                                                                  |
| bestFit     | `["Industrial manufacturers", "Smart factories", "B2B precision suppliers", "Contract / OEM machining"]` |
| tags        | `["Manufacturing", "Industrial", "B2B", "RFQ", "Certified"]`                                             |

## Design direction

| Token              | Value        | Rationale                                                        |
| ------------------ | ------------ | ---------------------------------------------------------------- |
| primaryColor       | `#1d4ed8`    | Steel blue — engineering trust, the brand spine.                 |
| accentColor        | `#f59e0b`    | Safety amber — the hazard/highlight colour of the factory floor. |
| neutralColor       | `#0f172a`    | Slate ink for dense tables and rules.                            |
| surfaceColor       | `#f8fafc`    | Cool light-grey canvas, clean shop-floor feel.                   |
| foregroundColor    | `#0f172a`    | Slate body text, high contrast for spec-dense pages.             |
| headingFont        | `inter`      | Plain, engineered headings — no decoration.                      |
| bodyFont           | `inter`      | Inter for dense technical copy and tables.                       |
| spacing            | `compact`    | Procurement audiences want density and scanability.              |
| alignment          | `left`       | Spec sheets, stats, and tables read left-to-right.               |
| cardStyle          | `bordered`   | Thin borders read like data panels and cert badges.              |
| navigationStyle    | `prominent`  | A solid header with Request a Quote always visible.              |
| layoutPresentation | `structured` | Grid-aligned, predictable, industrial layout.                    |
| motionIntensity    | `none`       | Serious, static; movement would undermine the precision tone.    |
| mediaTreatment     | `flat`       | Flat plant imagery — no stylized framing or duotone.             |
| radius             | `sm`         | 4px corners — machined, tight, utilitarian.                      |
| headingScale       | `balanced`   | Headings present; specs and stats lead.                          |
| cardDensity        | `compact`    | Fit many processes, certs, and stats per row.                    |

Typography: headings and body in **Inter**; tabular stats and spec values use
`tabular-nums`. Motion: **none** — no reveals, no parallax; only standard focus rings for
accessibility (respect `prefers-reduced-motion` is satisfied trivially since nothing
animates). The RFQ form is a **static field definition rendered as plain HTML inputs** — no
live submission wiring, no JS, no Livewire.

## Sections

`includedSections`:
`["navigation", "hero", "capabilities-grid", "certifications", "facility-stats", "features", "case-studies", "spec-downloads", "rfq-form", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **capabilities-grid** — `heading`, `summary`, `processes[]` each `{ name, description }`.
- **certifications** — `heading`, `summary`, `certs[]` each `{ name }` (e.g. ISO 9001, IATF 16949, AS9100).
- **facility-stats** — `heading`, `summary`, `stats[]` each `{ label, value }`.
- **case-studies** — `heading`, `summary`, `cases[]` each `{ industry, outcome }`.
- **spec-downloads** — `heading`, `summary`, `datasheets[]` each `{ title, format }` (plain public hrefs; no signed URLs).
- **rfq-form** — `heading`, `summary`, `fields[]` each `{ name, label, type }` (static field definitions; no live submission).

`content-listing` uses the `pathways` variant for the resource library.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Forge Industries — "Precision components, on spec, on time."

**Hero (hero):** heading "Contract precision machining for demanding industries." Summary:
"From first-article inspection to high-volume production, Forge Industries makes the parts
that can't fail — for automotive, aerospace, and medical OEMs." CTA "Request a quote" →
"/rfq".

**Capabilities grid (capabilities-grid):** heading "What we run." processes:

- name "5-axis CNC milling" — description "Complex geometries in aluminium, titanium, and superalloys to ±0.005 mm."
- name "CNC turning" — description "Live-tooled multi-axis lathes for high-volume turned parts."
- name "Wire & sinker EDM" — description "Fine features, sharp internal corners, and hardened-tool detail."
- name "Surface & cylindrical grinding" — description "Sub-micron finishes on critical mating surfaces."
- name "Anodising & passivation" — description "In-house finishing for corrosion resistance and traceability."
- name "CMM inspection" — description "Automated metrology with full first-article and SPC reporting."

**Certifications (certifications):** heading "Certified to the standards your auditors ask
for." certs:

- name "ISO 9001:2015".
- name "IATF 16949".
- name "AS9100D".
- name "ISO 13485".
- name "ITAR registered".

**Facility stats (facility-stats):** heading "The plant." stats:

- label "Floor space" — value "120,000 sq ft".
- label "CNC lines" — value "18".
- label "Shifts" — value "3, lights-out capable".
- label "On-time delivery" — value "99.2%".
- label "First-pass yield" — value "99.6%".
- label "Lead time" — value "from 2 weeks".

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Full traceability" / "Material certs and lot tracking from raw bar to shipped part." / trust.
- "DFM review on every quote" / "Our engineers flag manufacturability risks before you tool up." / capability.
- "Lights-out machining" / "Automated pallet loading runs unattended overnight." / performance.
- "PPAP & FAIR ready" / "Production-part approval and first-article reports as standard." / trust.
- "Supplier-managed inventory" / "Kanban and consignment programs keep your line fed." / integration.

**Case studies (case-studies):** heading "Proof on the floor." cases:

- industry "Automotive" — outcome "Cut a Tier-1 customer's transmission housing scrap rate from 3.1% to 0.4% in two quarters."
- industry "Aerospace" — outcome "Delivered 12,000 flight-critical brackets across 14 months with zero escapes."
- industry "Medical" — outcome "Validated a titanium implant line under ISO 13485 with full lot traceability."

**Spec downloads (spec-downloads):** heading "Datasheets & capability documents."

- title "Machining capability statement" — format "PDF".
- title "Tolerance & finish reference" — format "PDF".
- title "Quality manual summary" — format "PDF".
- title "Material certifications guide" — format "PDF".

**RFQ form (rfq-form):** heading "Request a quote." Summary: "Tell us about the part and
we'll respond within one business day." fields (static definitions):

- name "company" — label "Company" — type "text".
- name "contact" — label "Contact name" — type "text".
- name "email" — label "Work email" — type "email".
- name "quantity" — label "Annual quantity" — type "number".
- name "material" — label "Material" — type "text".
- name "tolerance" — label "Tightest tolerance" — type "text".
- name "details" — label "Part details" — type "textarea".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "99.2%" / "On-time delivery" / "Forge hits dates the rest of our supply base misses." — Daniel Cross, Supply Chain Director, Meridian Auto.
- "0 escapes" / "On 12k flight parts" / "Their FAIR packages are the cleanest we receive." — Sophia Wren, Quality Manager, Halcyon Aerospace.
- "2 weeks" / "Typical lead time" / "They turned around a prototype run that saved our launch." — Raj Malhotra, NPI Engineer, Vitae Medical.

**Industries note (vertical block):** static descriptive copy — "We serve automotive,
aerospace, defense, and medical OEMs, with dedicated cells and documentation for each
sector's quality requirements." Present as prose.

**Spotlight / pathways:** spotlight "Inside a lights-out machining cell" with pathways
"See our capabilities", "Review our certifications", "Download a datasheet".

**Directory samples (content-listing, 3+):**

- "Designing parts for 5-axis machining" — type "Guide" — "Wall thickness, access, and fixturing that cut cost."
- "What a PPAP package includes" — type "Guide" — "The documents and approvals automotive customers expect."
- "Choosing between EDM and grinding" — type "Guide" — "Feature, finish, and cost trade-offs."

**Detail/article sample:** "How we hold ±0.005 mm in production" — a 4-paragraph original
article on thermal stability, in-process probing, SPC, and metrology discipline.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: manufacturing`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, `product.tier: "premium"`, runtime provider `Capell\ThemeStudio\Manufacturing\ManufacturingThemeServiceProvider`, demo command `capell:theme-manufacturing-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-manufacturing","theme-manufacturing-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-manufacturing"]`, health check `theme-manufacturing.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **ManufacturingThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-manufacturing`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `capabilities-grid`, `certifications`, `facility-stats`, `features`, `case-studies`, `spec-downloads`, `rfq-form`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The `rfq-form` view renders plain static `<input>`/`<textarea>` fields from the field definitions — no `wire:`, no live action wiring.
5. **ManufacturingThemePageAdapter** maps demo render data into typed section Data, including the 6 NEW shapes.
6. **InstallManufacturingThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'manufacturing', 'Manufacturing')`; **DemoCommand** exposes `capell:theme-manufacturing-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-manufacturing.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-manufacturing.css** — cool-grey canvas, steel-blue spine, safety-amber accents, bordered data panels, compact dense tables, `tabular-nums` stats. No animation, no package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-manufacturing::...')`.
10. **Theme Manufacturing health check** for `theme-manufacturing.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\Manufacturing\` → `packages/theme-manufacturing/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `ManufacturingThemeDefinitionTest`,
`ManufacturingThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeManufacturingHealthCheckTest`. Theme-specific assertions:

- `rfq-form` renders plain static fields from the field definitions and emits **no** `wire:`,
  no signed URL, no live submission action in the public output.
- `certifications` renders every cert name (assert ISO 9001, IATF 16949, and AS9100D appear).
- `spec-downloads` renders each datasheet with a plain public link (no `signed`, `data-field`,
  `model_id`).
- `facility-stats` renders static figures and never queries the database at render time.
- Demo install seeds all 7 surfaces with Forge Industries copy (assert brand name, the
  `120,000 sq ft` stat, and the `99.2%` on-time figure appear).

## Verification

```bash
vendor/bin/pest packages/theme-manufacturing/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
