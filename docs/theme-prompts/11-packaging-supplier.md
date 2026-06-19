# Theme: PackagingSupplier (`theme-packaging-supplier`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-packaging-supplier` distinct.

## Build this

Build a premium Capell child theme for a sustainable physical-product / packaging supplier —
the kind of B2B site that sells recyclable food packaging by leading with its product range,
its material science and recyclability, its sustainability commitments, and the industries it
serves, then converts a buyer with a sample-request form. Everything renders from query-free
hydrated render data: the product-range categories, the materials table with recyclability
and certifications, the sustainability commitment metrics, the industries served, and a
static sample-request form definition. The aesthetic is leaf green and kraft brown on warm
recycled-paper stock — natural, tactile, credible. Extend `default` (Foundation Theme) at
runtime and `capell-app/foundation-theme` at the package level.

## Inspiration

Browser tab: **Yucca (food/produce packaging)** for the natural, product-forward supplier
framing — a clean product range, material credentials worn proudly, sustainability metrics
that mean something, and industry fit. Borrow the moves: a product-range grid, a materials
table with recyclability and certifications, sustainability commitments with hard metrics,
an industries-served strip, and a sample-request form. Do **not** copy their copy, products,
certifications, or numbers; invent original demo content (brand "Verda Packaging").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing for a sustainability-led physical-product B2B supplier.
Commerce sells consumer retail with carts; manufacturing sells machined metal parts. This
sells recyclable food packaging by material credibility and sustainability metrics, with a
sample request instead of a checkout. It is the only theme that renders a materials table
with recyclability ratings and a sustainability-commitment block.

## Package identity

| Field       | Value                                                                                                                             |
| ----------- | --------------------------------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-packaging-supplier`                                                                                             |
| slug        | `theme-packaging-supplier`                                                                                                        |
| namespace   | `Capell\ThemeStudio\PackagingSupplier`                                                                                            |
| themeKey    | `packaging-supplier`                                                                                                              |
| displayName | `Packaging Supplier`                                                                                                              |
| tier        | premium                                                                                                                           |
| bestFit     | `["Sustainable packaging suppliers", "Food & produce packaging", "Eco physical-product B2B", "Materials & recyclability brands"]` |
| tags        | `["Packaging", "Sustainable", "B2B", "Materials", "Food-grade"]`                                                                  |

## Design direction

| Token              | Value         | Rationale                                                         |
| ------------------ | ------------- | ----------------------------------------------------------------- |
| primaryColor       | `#16a34a`     | Leaf green — the sustainability and food-fresh signal.            |
| accentColor        | `#b45309`     | Kraft brown — recycled-card warmth and the natural-materials cue. |
| neutralColor       | `#1c2a1f`     | Deep forest ink for text and rules.                               |
| surfaceColor       | `#f7f6f0`     | Warm recycled-paper canvas, tactile and natural.                  |
| foregroundColor    | `#1c2a1f`     | Forest body text, soft contrast on the paper stock.               |
| headingFont        | `sora`        | Friendly, modern geometric headings with warmth.                  |
| bodyFont           | `inter`       | Inter keeps spec and material copy clean.                         |
| spacing            | `balanced`    | Roomy enough to feel premium, tight enough for a catalogue.       |
| alignment          | `left`        | Product ranges and material tables read left-to-right.            |
| cardStyle          | `bordered`    | Thin borders read like product and material cards.                |
| navigationStyle    | `prominent`   | A confident header with Request samples in reach.                 |
| layoutPresentation | `structured`  | Grid-aligned product and material layout.                         |
| motionIntensity    | `subtle`      | Gentle hover lift on product cards; nothing flashy.               |
| mediaTreatment     | `framed`      | Framed product photography on the paper stock.                    |
| radius             | `md`          | 8px corners — soft, natural, not sharp.                           |
| headingScale       | `balanced`    | Headings present; products and metrics lead.                      |
| cardDensity        | `comfortable` | Product and material cards need room for detail.                  |

Typography: headings in **Sora** (warm geometric); body and material copy in **Inter** with
`tabular-nums` for recyclability percentages and metrics. Motion: subtle hover lift on
product and material cards, a soft fade on the sustainability counters; respect
`prefers-reduced-motion`. The sample-request form is a **static field definition rendered as
plain HTML inputs** — no live submission wiring, no JS, no Livewire.

## Sections

`includedSections`:
`["navigation", "hero", "product-range", "materials", "sustainability", "features", "industries", "sample-request", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **product-range** — `heading`, `summary`, `categories[]` each `{ name, description }`.
- **materials** — `heading`, `summary`, `materials[]` each `{ name, recyclability, certifications }`.
- **sustainability** — `heading`, `summary`, `commitments[]` each `{ title, metric }`.
- **industries** — `heading`, `summary`, `industries[]` each `{ name }`.
- **sample-request** — `heading`, `summary`, `fields[]` each `{ name, label, type }` (static field definitions; no live submission).

`content-listing` uses the `gallery` variant for the case-study / resource feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Verda Packaging — "Food packaging that goes back to the earth, not the landfill."

**Hero (hero):** heading "Sustainable food packaging your customers can recycle at home."
Summary: "Verda makes 100% recyclable trays, films, and pouches for fresh produce, bakery,
and ready meals — food-safe, beautiful on shelf, and certified compostable where it counts."
CTA "Request samples" → "/samples".

**Product range (product-range):** heading "The range." categories:

- name "Produce trays" — description "Moulded-fibre and rPET trays for soft fruit, tomatoes, and salads."
- name "Films & lidding" — description "Recycle-ready barrier films and peelable lidding for sealed freshness."
- name "Pouches" — description "Stand-up and flow-wrap pouches in mono-material, recyclable structures."
- name "Bakery & deli" — description "Greaseproof, window cartons, and clamshells with FSC board."
- name "Ready meals" — description "Ovenable and microwavable trays rated to 220°C."
- name "Custom print" — description "Low-migration flexo and digital print on every line."

**Materials (materials):** heading "What it's made of." materials:

- name "Moulded fibre" — recyclability "100% kerbside recyclable" — certifications "FSC, home-compostable".
- name "Mono rPET" — recyclability "Widely recycled, 85% rPET content" — certifications "Food-contact approved".
- name "Recyclable mono-PP" — recyclability "Recycle-ready, single polymer" — certifications "BRCGS food-safe".
- name "Paper barrier board" — recyclability "Kerbside recyclable" — certifications "FSC Mix, OPRL ready".
- name "Compostable PLA" — recyclability "Industrially compostable" — certifications "EN 13432, OK Compost".

**Sustainability (sustainability):** heading "Our commitments, measured." commitments:

- title "Carbon reduction" — metric "38% lower CO₂e vs 2021 baseline".
- title "Recycled content" — metric "Average 72% across all rPET lines".
- title "Recyclable range" — metric "100% of catalogue recycle-ready by design".
- title "Renewable energy" — metric "Plants run on 100% renewable electricity".
- title "Waste to landfill" — metric "Zero process waste to landfill since 2024".

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Food-safe by default" / "Every line is food-contact approved and audited to BRCGS." / trust.
- "Designed to be recycled" / "Mono-material structures and OPRL labelling make kerbside recycling easy." / capability.
- "Shelf-life that holds" / "Barrier films extend fresh-produce shelf life without over-packaging." / performance.
- "On-pack carbon labels" / "Printed footprint figures help your shoppers choose." / trust.
- "Made to your spec" / "Custom sizes, prints, and structures with low minimum runs." / capability.

**Industries (industries):** heading "Who we pack for." industries:

- name "Fresh produce".
- name "Bakery".
- name "Ready meals".
- name "Deli & food-to-go".
- name "Meat, fish & dairy".
- name "Coffee & tea".

**Sample request (sample-request):** heading "Request samples." Summary: "Tell us what you
pack and we'll send the right swatch pack within five working days." fields (static
definitions):

- name "company" — label "Company" — type "text".
- name "contact" — label "Contact name" — type "text".
- name "email" — label "Work email" — type "email".
- name "product" — label "Product type" — type "text".
- name "volume" — label "Annual volume" — type "number".
- name "needs" — label "What you're looking for" — type "textarea".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "38%" / "Lower carbon since 2021" / "Switching to Verda cut our packaging footprint and our shoppers noticed." — Hannah Pryce, Head of Sustainability, Greenfield Grocers.
- "100%" / "Recyclable catalogue" / "Finally, produce packaging that passes our recyclability audit." — Lucas Berg, Packaging Lead, Harvest Foods.
- "5 days" / "Sample turnaround" / "Swatches arrived fast and the spec sheets were spotless." — Amara Diallo, Buyer, Sunrise Bakery.

**Certifications note (vertical block):** static descriptive copy — "Verda is FSC certified,
BRCGS food-safe audited, and our compostable lines meet EN 13432. Recyclability is verified
against OPRL guidance for the markets we serve." Present as prose.

**Spotlight / pathways:** spotlight "How a fibre tray goes from field to kerbside bin" with
pathways "Browse the range", "Compare materials", "Request samples".

**Directory samples (content-listing, 3+):**

- "Switching from black plastic trays" — type "Case study" — "Moving a produce range to detectable, recyclable trays."
- "Reading recyclability labels" — type "Guide" — "OPRL, kerbside, and what 'widely recycled' really means."
- "Mono-material pouches explained" — type "Guide" — "Why single-polymer structures recycle and laminates don't."

**Detail/article sample:** "Designing packaging that actually gets recycled" — a 4-paragraph
original article on mono-materials, kerbside collection realities, food-contact safety, and
honest on-pack labelling.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: packaging-supplier`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, `product.tier: "premium"`, runtime provider `Capell\ThemeStudio\PackagingSupplier\PackagingSupplierThemeServiceProvider`, demo command `capell:theme-packaging-supplier-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-packaging-supplier","theme-packaging-supplier-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-packaging-supplier"]`, health check `theme-packaging-supplier.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **PackagingSupplierThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `CapellCore::isPackageInstalled`, loads translations + views (`capell-theme-packaging-supplier`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `product-range`, `materials`, `sustainability`, `features`, `industries`, `sample-request`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The `sample-request` view renders plain static `<input>`/`<textarea>` fields from the field definitions — no `wire:`, no live action wiring.
5. **PackagingSupplierThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes.
6. **InstallPackagingSupplierThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'packaging-supplier', 'PackagingSupplier')`; **DemoCommand** exposes `capell:theme-packaging-supplier-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-packaging-supplier.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-packaging-supplier.css** — warm recycled-paper canvas, leaf-green spine, kraft-brown accents, framed product imagery, bordered material cards, `tabular-nums` recyclability figures. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-packaging-supplier::...')`.
10. **Theme PackagingSupplier health check** for `theme-packaging-supplier.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\PackagingSupplier\` → `packages/theme-packaging-supplier/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `PackagingSupplierThemeDefinitionTest`,
`PackagingSupplierThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemePackagingSupplierHealthCheckTest`. Theme-specific assertions:

- `materials` renders every material with its `recyclability` and `certifications` (assert the
  `100% kerbside recyclable` and `FSC` values appear).
- `sustainability` renders the commitment metrics (assert the `38%` carbon figure appears) and
  never queries the database at render time.
- `sample-request` renders plain static fields and emits **no** `wire:`, no signed URL, no live
  submission action in the public output.
- Demo install seeds all 7 surfaces with Verda Packaging copy (assert brand name and the `38%`
  carbon-reduction figure appear).

## Verification

```bash
vendor/bin/pest packages/theme-packaging-supplier/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
