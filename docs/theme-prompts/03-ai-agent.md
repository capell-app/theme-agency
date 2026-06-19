# Theme: AiAgent (`theme-ai-agent`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-ai-agent` distinct.

## Build this

Build a premium Capell child theme for an outcome-led AI agent / automation product —
the kind that resolves support tickets and runs ops workflows autonomously. The page
sells _measurable outcomes_: it shows the agent in action as a trigger→action→result
flow, headlines auto-resolution and CSAT numbers, lists the tools it integrates, runs a
static ROI illustration, and breaks down use cases by department. Bright, optimistic,
trustworthy — light surface with a confident sky-blue primary and a resolution-green
accent. Extend `default` (Foundation Theme) at runtime and
`capell-app/foundation-theme` at the package level.

## Inspiration

Browser tabs: **Fin.ai** for the outcome-first hero (resolution rate front and center),
the agent-in-action walkthrough, and integration logos as proof; **Humble (smart-
manufacturing AI)** for the operational, department-by-department framing and clean
metric cards. Borrow the moves — outcome metrics above the fold, a stepwise "watch it
work" panel, an integrations grid, and a use-case breakdown — and the confident,
business-value tone. Do **not** copy their copy or numbers; invent original demo content
(brand "Aria Agents").

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing that frames a product around _autonomous outcomes_ and ops
automation. SaaS sells features and pricing; AiLab/ApiPlatform sell capability to
engineers. This sells resolved tickets and saved hours to a support/ops buyer, with an
agent-in-action flow and an ROI illustration no other theme renders.

## Package identity

| Field       | Value                                                                                       |
| ----------- | ------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-ai-agent`                                                                 |
| slug        | `theme-ai-agent`                                                                            |
| namespace   | `Capell\ThemeStudio\AiAgent`                                                                |
| themeKey    | `ai-agent`                                                                                  |
| displayName | `AI Agent`                                                                                  |
| tier        | premium                                                                                     |
| bestFit     | `["AI support agents", "Ops automation products", "Customer service AI", "RevOps tooling"]` |
| tags        | `["AI", "Automation", "Support", "Outcomes", "Light"]`                                      |

## Design direction

| Token              | Value         | Rationale                                                   |
| ------------------ | ------------- | ----------------------------------------------------------- |
| primaryColor       | `#0ea5e9`     | Sky blue — trustworthy, energetic product primary.          |
| accentColor        | `#22c55e`     | Resolution green for "resolved" states and positive deltas. |
| neutralColor       | `#0f172a`     | Deep slate for headings and high-contrast text.             |
| surfaceColor       | `#f8fafc`     | Bright off-white canvas; optimistic and clean.              |
| foregroundColor    | `#0f172a`     | Slate-900 body text for crisp legibility.                   |
| headingFont        | `sora`        | Friendly geometric headings with momentum.                  |
| bodyFont           | `inter`       | Neutral, dependable body copy.                              |
| spacing            | `balanced`    | Comfortable rhythm for metric-heavy sections.               |
| alignment          | `left`        | Scannable, value-led reading order.                         |
| cardStyle          | `elevated`    | Soft-shadow cards lift the metric and use-case panels.      |
| navigationStyle    | `prominent`   | Strong nav with a clear "Book a demo" CTA.                  |
| layoutPresentation | `structured`  | Predictable sections that build the value argument.         |
| motionIntensity    | `subtle`      | Gentle count-up feel via styling; no gimmicks.              |
| mediaTreatment     | `framed`      | Framed product/flow screenshots.                            |
| radius             | `lg`          | 12px rounded corners — approachable and modern.             |
| headingScale       | `balanced`    | Headlines support the numbers, don't shout over them.       |
| cardDensity        | `comfortable` | Use-case and metric cards need breathing room.              |

Typography: headings in **Sora**, body in **Inter**, deltas in `tabular-nums` with
green/red tinting handled in CSS. Motion: subtle on-scroll fades and hover lift;
respect `prefers-reduced-motion`. The ROI calculator is a static illustration, not a
live interactive form.

## Sections

`includedSections`:
`["navigation", "hero", "outcome-metrics", "agent-in-action", "features", "integrations-grid", "roi-calculator", "use-cases", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **agent-in-action** — `heading`, `summary`, `steps[]` each `{ trigger, action, result }`.
- **outcome-metrics** — `heading`, `summary`, `cards[]` each `{ label, value, delta }`.
- **integrations-grid** — `heading`, `summary`, `tools[]` each `{ name, category }`.
- **roi-calculator** — `heading`, `summary`, `inputs[]` each `{ label, value }`, `outputs[]` each `{ label, value }` (all static figures; no live JS computation).
- **use-cases** — `heading`, `summary`, `cases[]` each `{ department, problem, outcome }`.

`content-listing` uses the `pathways` variant for the resources feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Aria Agents — "The AI teammate that resolves, not deflects."

**Hero:** heading "Resolve more, queue less." Summary: "Aria reads the ticket,
gathers context from your tools, takes the action, and closes the loop — autonomously.
Your team handles the hard 49%; Aria handles the rest."

**Outcome metrics (outcome-metrics):** heading "What changes in 30 days."

- label "Auto-resolution rate" — value "51%" — delta "+51 pts".
- label "Median first response" — value "9s" — delta "from 6h".
- label "CSAT" — value "4.7 / 5" — delta "+0.6".
- label "Tickets per agent" — value "−38%" — delta "−38%".

**Agent in action (agent-in-action):** heading "Watch Aria close a ticket."

- trigger "Customer asks 'Where's my refund?'" — action "Aria looks up the order in Salesforce and the payment in Stripe." — result "Confirms refund status and posts an ETA — no human touch."
- trigger "User reports a failed login" — action "Aria checks the auth logs and the account state." — result "Resets the session and walks the user through MFA re-enrollment."
- trigger "Shopper requests an exchange" — action "Aria verifies eligibility and creates the return label." — result "Sends the label and updates the order — resolved in 12 seconds."

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Acts, doesn't just answer" / "Aria executes real workflows across your stack, not canned replies." / capability.
- "Knows your business" / "Grounded on your docs, macros, and historical tickets." / capability.
- "Human handoff that sticks" / "Escalates with full context so agents never start cold." / workflow.
- "Guardrails by design" / "Per-action policies, approval gates, and a full audit trail." / trust.
- "Live the same day" / "Connect a helpdesk and Aria starts resolving within hours." / onboarding.

**Integrations grid (integrations-grid):** heading "Plugs into your stack."

- Zendesk — category "Helpdesk".
- Salesforce — category "CRM".
- Slack — category "Messaging".
- Intercom — category "Helpdesk".
- Stripe — category "Payments".
- Jira — category "Ticketing".

**ROI calculator (roi-calculator):** heading "Your savings, illustrated." inputs:

- label "Monthly tickets" — value "12,000".
- label "Avg handle time" — value "8 min".
- label "Loaded agent cost" — value "$28 / hr". outputs:
- label "Tickets auto-resolved" — value "6,120 / mo".
- label "Agent hours saved" — value "816 / mo".
- label "Estimated monthly savings" — value "$22,800". (Static illustration; label it
  "illustrative — your results will vary.")

**Use cases (use-cases):** heading "By department."

- department "Customer Support" — problem "Backlogs spike during launches." — outcome "Aria absorbs Tier-1 volume so humans focus on edge cases."
- department "IT Service Desk" — problem "Password and access tickets drown the queue." — outcome "Aria handles resets and provisioning end to end."
- department "Operations" — problem "Order exceptions need manual chasing." — outcome "Aria reconciles exceptions across systems automatically."
- department "RevOps" — problem "Lead routing is slow and inconsistent." — outcome "Aria enriches and routes leads the moment they land."

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "51%" / "Tickets auto-resolved" / "Half our queue closes before a human sees it." — Dana Whitfield, Head of Support, Brightline.
- "9s" / "First response, down from 6h" / "Customers think we hired a night shift. We hired Aria." — Owen Marsh, CX Director, Tidewater.
- "4.7" / "CSAT after rollout" / "Resolution quality went up, not down — that surprised us." — Mei-Ling Chua, VP Service, Northpeak.

**Spotlight / pathways:** spotlight "How Brightline cut backlog 40% in a quarter" with
pathways "Read the case study", "See the integrations", "Book a 20-minute demo".

**Directory samples (content-listing, 3+):**

- "The autonomy ladder: from suggest to resolve" — type "Guide" — "How to safely move work to the agent."
- "Designing guardrails for AI support" — type "Guide" — "Approval gates, scopes, and audit trails."
- "Case study: Tidewater" — type "Case study" — "From 6-hour responses to 9 seconds."

**Detail/article sample:** "Measuring real auto-resolution (not deflection)" — a
4-paragraph original article distinguishing resolved from deflected and how to instrument it.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: ai-agent`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, runtime provider `Capell\ThemeStudio\AiAgent\AiAgentThemeServiceProvider`, demo command `capell:theme-ai-agent-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-ai-agent","theme-ai-agent-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-ai-agent"]`, health check `theme-ai-agent.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **AiAgentThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `isPackageInstalled`, loads translations + views (`capell-theme-ai-agent`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `outcome-metrics`, `agent-in-action`, `features`, `integrations-grid`, `roi-calculator`, `use-cases`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`.
5. **AiAgentThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes.
6. **InstallAiAgentThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'ai-agent', 'AiAgent')`; **DemoCommand** exposes `capell:theme-ai-agent-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-ai-agent.css')`. Keep `livewire/page/page.blade.php` thin.
8. **resources/css/theme-ai-agent.css** — light canvas, sky primary, resolution-green positive deltas, elevated card shadows, `tabular-nums` for metrics. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-ai-agent::...')`.
10. **Theme AiAgent health check** for `theme-ai-agent.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\AiAgent\` → `packages/theme-ai-agent/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `AiAgentThemeDefinitionTest`,
`AiAgentThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeAiAgentHealthCheckTest`. Theme-specific assertions:

- `agent-in-action` renders every step with `trigger`, `action`, `result`; `use-cases`
  renders every case with `department`, `problem`, `outcome`.
- `roi-calculator` renders static `inputs`/`outputs` figures and performs no live JS
  computation or database query at render time.
- `outcome-metrics` delta values render with sign styling but expose no `wire:`,
  `data-field`, or `model_id`.
- Demo install seeds all 7 surfaces with Aria Agents copy (assert brand name and the
  "51%" auto-resolution figure appear).

## Verification

```bash
vendor/bin/pest packages/theme-ai-agent/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
