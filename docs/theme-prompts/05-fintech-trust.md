# Theme: FintechTrust (`theme-fintech-trust`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-fintech-trust` distinct.

## Build this

Build a premium Capell child theme for a fintech / business-identity-verification /
compliance-infrastructure company — the kind that sells KYB, fraud prevention, and
regulatory-grade trust to financial institutions. The page radiates credibility:
compliance badges, a layered security-architecture diagram, a step-by-step verification
flow, approval/fraud metric cards, and a global coverage map. The look is institutional
and calm — light surface, deep-indigo primary, teal accent, restrained motion. A
regulatory/disclaimer block is mandatory. Extend `default` (Foundation Theme) at runtime
and `capell-app/foundation-theme` at the package level.

## Inspiration

Browser tabs: **Baselayer** for the business-identity-verification framing (compliance
badges, the verification flow, approval-rate proof) and **Stripe** for the institutional
polish, the layered architecture explanation, and the trust-by-clarity tone. Borrow the
moves — compliance badge wall, a security-architecture layer stack, a verification-flow
ladder, approval/fraud metric cards, and a coverage map by region — and the precise,
reassuring tone. Do **not** copy their copy or numbers; invent original demo content
(brand "Tessera Verify").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing built for regulated fintech / compliance infrastructure.
Corporate is generic B2B; this theme leads with _trust artifacts_ — SOC 2 / ISO badges,
a security-architecture diagram, and a regulatory disclaimer — that no other theme
renders, framed for a buyer who needs evidence, not vibes.

## Package identity

| Field       | Value                                                                                                   |
| ----------- | ------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-fintech-trust`                                                                        |
| slug        | `theme-fintech-trust`                                                                                   |
| namespace   | `Capell\ThemeStudio\FintechTrust`                                                                       |
| themeKey    | `fintech-trust`                                                                                         |
| displayName | `Fintech Trust`                                                                                         |
| tier        | premium                                                                                                 |
| bestFit     | `["Fintech infrastructure", "Identity verification / KYB", "Compliance & RegTech", "Fraud prevention"]` |
| tags        | `["Fintech", "Compliance", "Trust", "Security", "B2B"]`                                                 |

## Design direction

| Token              | Value         | Rationale                                              |
| ------------------ | ------------- | ------------------------------------------------------ |
| primaryColor       | `#1e3a8a`     | Deep indigo — institutional, trustworthy banking blue. |
| accentColor        | `#14b8a6`     | Teal accent for "verified" states and positive proof.  |
| neutralColor       | `#0f172a`     | Slate for headings and high-contrast body.             |
| surfaceColor       | `#f8fafc`     | Bright, clean off-white compliance canvas.             |
| foregroundColor    | `#0f172a`     | Slate-900 body text for formal legibility.             |
| headingFont        | `inter`       | Sober, corporate-grade headings.                       |
| bodyFont           | `inter`       | Inter throughout — neutral and professional.           |
| spacing            | `balanced`    | Orderly rhythm that reads structured and dependable.   |
| alignment          | `left`        | Document/spec reading order.                           |
| cardStyle          | `bordered`    | Thin borders read like certified panels and ledgers.   |
| navigationStyle    | `prominent`   | Clear nav with "Talk to compliance" / "Get a demo".    |
| layoutPresentation | `structured`  | Predictable, evidence-led composition.                 |
| motionIntensity    | `subtle`      | Restraint signals seriousness; near-still.             |
| mediaTreatment     | `framed`      | Framed diagrams and badge panels.                      |
| radius             | `md`          | 8px — modern but conservative.                         |
| headingScale       | `balanced`    | Authority without theatrics.                           |
| cardDensity        | `comfortable` | Badge, layer, and region cards need label room.        |

Typography: **Inter** for headings and body, figures in `tabular-nums`. Motion: minimal
fades only; respect `prefers-reduced-motion`. All compliance claims, badges, and the
coverage map are static demo content — no live verification calls.

## Sections

`includedSections`:
`["navigation", "hero", "compliance-badges", "features", "security-architecture", "verification-flow", "metric-cards", "coverage-map", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **compliance-badges** — `heading`, `summary`, `badges[]` each `{ name, detail }` (names like SOC 2, ISO 27001, PCI DSS, GDPR).
- **security-architecture** — `heading`, `summary`, `layers[]` each `{ name, description }`.
- **verification-flow** — `heading`, `summary`, `steps[]` each `{ name, detail }`.
- **metric-cards** — `heading`, `summary`, `cards[]` each `{ key, label, value, delta }` (keys include `approvalRate`, `fraudReduction`).
- **coverage-map** — `heading`, `summary`, `regions[]` each `{ name, countries }`.

`content-listing` uses the `spotlight` variant for the compliance resources feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Tessera Verify — "Know who you're doing business with."

**Hero:** heading "Business identity verification, built for regulators and revenue."
Summary: "Verify a business in seconds — ownership, registration, sanctions, and risk —
with an audit trail your compliance team and your auditors both trust."

**Compliance badges (compliance-badges):** heading "Audited and accountable."

- SOC 2 Type II — detail "Independently audited controls, renewed annually."
- ISO 27001 — detail "Certified information-security management system."
- PCI DSS — detail "Level 1 compliance for cardholder data handling."
- GDPR — detail "Data minimization, DPAs, and EU data residency available."

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Ultimate beneficial owner checks" / "Resolve ownership chains and flag hidden control." / capability.
- "Real-time sanctions screening" / "Screen against global watchlists at onboarding and on schedule." / capability.
- "Document & liveness verification" / "Validate registration documents and verify signatories." / capability.
- "Configurable risk policies" / "Set thresholds, approval gates, and manual-review rules." / workflow.
- "Immutable audit trail" / "Every decision is logged with evidence for your auditors." / trust.

**Security architecture (security-architecture):** heading "Defense in depth."

- name "Data isolation" — description "Per-tenant encryption keys; no commingled storage."
- name "Encryption" — description "TLS 1.3 in transit, AES-256 at rest, key rotation enforced."
- name "Access control" — description "Least-privilege RBAC with hardware-key admin access."
- name "Monitoring" — description "Continuous anomaly detection and tamper-evident logs."

**Verification flow (verification-flow):** heading "From application to approved in seconds."

- name "Submit" — detail "Customer enters business details or imports from a registry."
- name "Resolve" — detail "We match against authoritative registries and ownership data."
- name "Screen" — detail "Sanctions, PEP, and adverse-media checks run in parallel."
- name "Decide" — detail "Policy engine returns approve, review, or decline with evidence."

**Metric cards (metric-cards):** heading "Outcomes you can put in a board deck."

- key "approvalRate" — label "Auto-approval rate" — value "99.2%" — delta "+14 pts vs manual review".
- key "fraudReduction" — label "Fraud reduction" — value "60%" — delta "−60% confirmed fraud".
- key "timeToDecision" — label "Median decision time" — value "1.8s" — delta "from 2 days".

**Coverage map (coverage-map):** heading "Global coverage."

- North America — countries "United States, Canada, Mexico".
- Europe — countries "All EU member states, United Kingdom, Switzerland".
- Asia-Pacific — countries "Australia, Singapore, Japan, India".
- Rest of world — countries "190 countries via registry and watchlist partners".

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "99.2%" / "Applications auto-approved" / "We onboard good businesses in seconds and still sleep at night." — Helena Bright, Head of Compliance, Meridian Bank.
- "60%" / "Less confirmed fraud" / "The audit trail alone paid for itself in our first exam." — Joseph Adeyemi, Risk Director, Northgate.
- "190" / "Countries covered" / "One integration replaced four regional vendors." — Carla Mendez, COO, FinBridge.

**Spotlight / pathways:** spotlight "Inside a Tessera verification decision" with pathways
"See the verification flow", "Review the security architecture", "Talk to compliance".

**Directory samples (content-listing, 3+):**

- "KYB onboarding checklist" — type "Guide" — "What regulators expect at business onboarding."
- "Reading a Tessera audit trail" — type "Reference" — "Every field in a decision record explained."
- "Sanctions screening, explained" — type "Guide" — "Lists, match logic, and false-positive handling."

**Detail/article sample:** "Building a defensible KYB program" — a 4-paragraph original
article on risk-based policies, audit trails, and periodic re-screening.

**Regulatory / disclaimer block (MANDATORY vertical block):** a clearly labeled
disclaimer section in the demo content, rendered as static copy:
"Tessera Verify provides identity-verification and screening tooling. It does not provide
legal, regulatory, or compliance advice, and using it does not by itself satisfy any
regulatory obligation. Figures shown on this page are illustrative demo values, not a
performance guarantee. Customers remain responsible for their own compliance program and
should consult qualified counsel." Keep it static — no live screening or external calls.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: fintech-trust`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, runtime provider `Capell\ThemeStudio\FintechTrust\FintechTrustThemeServiceProvider`, demo command `capell:theme-fintech-trust-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-fintech-trust","theme-fintech-trust-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-fintech-trust"]`, health check `theme-fintech-trust.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **FintechTrustThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `isPackageInstalled`, loads translations + views (`capell-theme-fintech-trust`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `compliance-badges`, `features`, `security-architecture`, `verification-flow`, `metric-cards`, `coverage-map`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`.
5. **FintechTrustThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes plus the disclaimer block.
6. **InstallFintechTrustThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'fintech-trust', 'FintechTrust')`; **DemoCommand** exposes `capell:theme-fintech-trust-demo`; add the **profile()** entry with all copy above, including the disclaimer.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-fintech-trust.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-fintech-trust.css** — light canvas, indigo primary, teal verified accent, bordered badge panels, `tabular-nums`. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-fintech-trust::...')`, including the disclaimer text.
10. **Theme FintechTrust health check** for `theme-fintech-trust.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\FintechTrust\` → `packages/theme-fintech-trust/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `FintechTrustThemeDefinitionTest`,
`FintechTrustThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeFintechTrustHealthCheckTest`. Theme-specific assertions:

- `compliance-badges` renders SOC 2, ISO 27001, PCI DSS, and GDPR; `metric-cards` exposes
  `approvalRate` and `fraudReduction` keys.
- The rendered public HTML contains the regulatory/disclaimer copy (assert a distinctive
  phrase such as "does not provide legal, regulatory, or compliance advice").
- `security-architecture` and `verification-flow` render all layers/steps statically with
  no `wire:`, `signed`, `data-field`, `model_id`, or database query at render time.
- Demo install seeds all 7 surfaces with Tessera Verify copy (assert brand name, the
  "99.2%" approval figure, and the disclaimer appear).

## Verification

```bash
vendor/bin/pest packages/theme-fintech-trust/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
