# Theme: AiLab (`theme-ai-lab`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-ai-lab` distinct.

## Build this

Build a premium Capell child theme for a frontier AI research lab / foundation-model
company. It should read like the home page of a serious model provider: a dark,
near-black canvas, restrained electric-indigo accents, and content that leads with
model capability and evaluation rigor rather than marketing froth. The page must
present model cards, benchmark tables, a research index, and a static playground
exchange, all rendered from query-free hydrated render data. Extend `default`
(Foundation Theme) at runtime and `capell-app/foundation-theme` at the package level.

## Inspiration

Browser tabs to keep open: **x.ai** (the lab-grade dark hero, terse model naming,
benchmark-forward proof) and **ElevenLabs FLUX.2 / model pages** (framed media,
modality chips, clean capability grids). Borrow the layout moves — full-bleed dark
hero, monospace-adjacent technical headings, a benchmark table treated as primary
content, a calm research list — and the confident, understated tone. Do **not** copy
their copy, model names, or numbers; invent original demo content (brand "Lumen AI",
model "Lumen-3").

## Gap it fills

The current set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has no deep-tech / research-lab voice. SaaS sells a product with
pricing and demo requests; this theme sells _capability and evaluation credibility_ —
model specs, benchmark scores, published research. Nothing else in the set renders a
benchmark table or a model-card grid.

## Package identity

| Field       | Value                                                                                                      |
| ----------- | ---------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-ai-lab`                                                                                  |
| slug        | `theme-ai-lab`                                                                                             |
| namespace   | `Capell\ThemeStudio\AiLab`                                                                                 |
| themeKey    | `ai-lab`                                                                                                   |
| displayName | `AI Lab`                                                                                                   |
| tier        | premium                                                                                                    |
| bestFit     | `["AI research labs", "Foundation model providers", "Deep-tech R&D teams", "ML infrastructure companies"]` |
| tags        | `["AI", "Research", "Dark", "Technical", "Frontier"]`                                                      |

## Design direction

| Token              | Value           | Rationale                                                 |
| ------------------ | --------------- | --------------------------------------------------------- |
| primaryColor       | `#6366f1`       | Electric indigo — the one saturated accent on near-black. |
| accentColor        | `#22d3ee`       | Cyan for benchmark highlights and active states.          |
| neutralColor       | `#18181b`       | Card and divider neutral, a step above the canvas.        |
| surfaceColor       | `#0a0a0b`       | Near-black research-console canvas.                       |
| foregroundColor    | `#f4f4f5`       | High-contrast off-white body text.                        |
| headingFont        | `space-grotesk` | Technical, geometric headings with a lab feel.            |
| bodyFont           | `inter`         | Neutral, legible body for dense spec copy.                |
| spacing            | `airy`          | Lets the dark canvas breathe; gallery-like calm.          |
| alignment          | `left`          | Engineering-doc reading rhythm.                           |
| cardStyle          | `flat`          | Borderless surfaces; depth comes from subtle tone shifts. |
| navigationStyle    | `minimal`       | A thin top bar, no heavy chrome.                          |
| layoutPresentation | `immersive`     | Full-bleed dark sections, edge-to-edge hero.              |
| motionIntensity    | `subtle`        | Quiet fades; no bouncing — this is a serious lab.         |
| mediaTreatment     | `framed`        | Thin-bordered framed media for model/sample visuals.      |
| radius             | `sm`            | Tight 4px corners read precise and technical.             |
| headingScale       | `dramatic`      | Oversized hero heading for the model name.                |
| cardDensity        | `comfortable`   | Spec cards need room for params/context labels.           |

Typography: headings in **Space Grotesk**, body in **Inter**, with `tabular-nums` for
benchmark and spec figures. Motion: opacity/translate fades on scroll only; respect
`prefers-reduced-motion`; no parallax.

## Sections

`includedSections`:
`["navigation", "hero", "model-cards", "benchmarks", "features", "research-index", "playground", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes the page adapter passes to Blade):

- **model-cards** — `heading`, `summary`, `items[]` each `{ name, params, contextWindow, modality, status }`.
- **benchmarks** — `heading`, `summary`, `rows[]` each `{ benchmark, score, unit, peerScores[] }` (peerScores each `{ label, value }`).
- **research-index** — `heading`, `summary`, `papers[]` each `{ title, authors, date, abstract, url }` (`url` is a plain public link, no signed/admin URLs).
- **playground** — `heading`, `summary`, `model`, `prompt`, `response` (a single static exchange; no live API, no interactive JS calls).

`content-listing` uses the `spotlight` variant for the research/changelog feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original.

**Brand:** Lumen AI — "Frontier models, evaluated in the open."

**Hero:** heading "Lumen-3: reasoning at the edge of the context window." Summary:
"A multimodal foundation model with a 200k-token context window, built for long-horizon
reasoning, tool use, and grounded answers. Run it in our hosted API or evaluate it
against your own benchmark suite today."

**Model cards (model-cards):**

- Lumen-3 — params "Mixture-of-Experts, 47B active", contextWindow "200k tokens", modality "Text + Vision + Audio", status "Generally available".
- Lumen-3 Mini — params "8B dense", contextWindow "128k tokens", modality "Text + Vision", status "Generally available".
- Lumen-3 Code — params "22B dense", contextWindow "200k tokens", modality "Text + Code", status "Preview".
- Lumen-Embed — params "1.2B", contextWindow "32k tokens", modality "Text embeddings", status "Generally available".

**Benchmarks (benchmarks):** heading "Measured, not asserted."

- MMLU — score `88.4`, unit `%`, peerScores `[{Open-7B, 71.2}, {Prior-2, 84.0}]`.
- GPQA Diamond — score `59.2`, unit `%`, peerScores `[{Open-7B, 34.8}, {Prior-2, 51.6}]`.
- HumanEval — score `92.1`, unit `% pass@1`, peerScores `[{Open-7B, 67.4}, {Prior-2, 88.9}]`.
- MATH — score `74.6`, unit `%`, peerScores `[{Open-7B, 39.1}, {Prior-2, 68.2}]`.

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "200k-token context" / "Reason over entire codebases, contracts, or research corpora in a single call." / capability.
- "Native tool use" / "Structured function calling with parallel tool execution and typed results." / capability.
- "Grounded retrieval" / "First-class citation spans so every claim traces back to a source." / capability.
- "On-prem deployment" / "Run Lumen-3 inside your VPC with no data leaving your boundary." / deployment.
- "Open evaluation harness" / "Reproduce every published score with our released eval scripts." / trust.

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "200k tokens" / "Context window" / "We fit our entire monorepo into one prompt and Lumen-3 kept the thread." — Priya Nair, Staff Engineer, Northwind.
- "12ms" / "Time to first token" / "Streaming feels instant in production at our request volume." — Tomas Eklund, Platform Lead, Cadence.
- "98.6%" / "Factual eval pass rate" / "The grounded citations cut our hallucination tickets to near zero." — Dr. Hana Sato, Research Director, Atlas Labs.

**Research index (research-index):** heading "Recent work."

- "Stable Long-Context Attention Without Quadratic Cost" — authors "Lumen AI Research" — date "2026-04-11" — abstract "We introduce a sparse attention scheme that holds quality flat to 200k tokens." — url "/research/long-context-attention".
- "Citation-Faithful Decoding for Grounded Answers" — authors "Lumen AI Research" — date "2026-02-28" — abstract "A decoding objective that ties generated spans to retrieved evidence." — url "/research/citation-faithful-decoding".
- "Evaluating Tool Use Beyond Single-Step Tasks" — authors "Lumen AI Research" — date "2026-01-09" — abstract "A benchmark for multi-step agentic workflows with verifiable outcomes." — url "/research/tool-use-eval".

**Playground (playground):** model "Lumen-3", prompt "Summarize this 40-page incident report and list the three root causes with evidence.", response "Three root causes: (1) a stale cache key reused across tenants (p.12, p.27); (2) a retry storm from an un-backed-off client (p.18); (3) an unmonitored queue that silently dropped events (p.31). Each is cited to the source pages above."

**Spotlight / pathways:** spotlight "Inside the Lumen-3 evaluation suite" with three pathways — "Read the model card", "Run the eval harness", "Request API access".

**Directory samples (content-listing, 3+):**

- "Lumen-3 Technical Report" — type "Report" — "Architecture, training data summary, and full benchmark tables."
- "Safety & Alignment Policy" — type "Policy" — "Red-team process, refusal behavior, and misuse reporting."
- "API Reference" — type "Docs" — "Endpoints, rate limits, and streaming examples."

**Detail/article sample:** "How we evaluate long-context reasoning" — a 4-paragraph original article on needle-in-a-haystack tests, distractor density, and reproducibility.

**Safety / alignment statement (vertical block):** a short section: "Lumen AI publishes a model card and a safety policy for every release. Models are red-teamed before launch, refuse clearly unsafe requests, and ship with an open evaluation harness so any claim in this page can be reproduced." Keep it static — no live model calls.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: ai-lab`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, runtime provider `Capell\ThemeStudio\AiLab\AiLabThemeServiceProvider`, demo command `capell:theme-ai-lab-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-ai-lab","theme-ai-lab-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, `performance.frontendRenderBudgetMs: 20`, `cacheTags: ["theme-ai-lab"]`, one health check `theme-ai-lab.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **AiLabThemeServiceProvider** (§4): empty `register()` (manifest registers package); `boot(ThemeRegistry)` registers the demo command, early-returns unless `CapellCore::isPackageInstalled(self::$packageName)`, `loadTranslationsFrom` + `loadViewsFrom` (`capell-theme-ai-lab`), registers vendor CSS (`VendorAssetData::tailwindImport` for the CSS and `tailwindSource` for the Blade views), registers the page adapter, then `$registry->register(definition, new BladeThemeRenderer(...), sectionRenderers)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table above, `includedSections`, package name, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `model-cards`, `benchmarks`, `features`, `research-index`, `playground`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`.
5. **AiLabThemePageAdapter** maps the demo render data into the typed section Data objects, including the 4 NEW section shapes above.
6. **InstallAiLabThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'ai-lab', 'AiLab')`; **DemoCommand** exposes `capell:theme-ai-lab-demo`; add the **profile()** entry with all copy above.
7. **page.blade.php** (§7): skip link, `data-capell-theme="{{ $themeKey }}"`, brand tokens as inline CSS custom properties, `{!! $content !!}`, `@frontendAsset('css/theme-ai-lab.css')`. Keep `livewire/page/page.blade.php` thin (`RenderCurrentThemePageAction::run()`).
8. **resources/css/theme-ai-lab.css** — dark canvas, indigo/cyan accent tokens, benchmark table styling with `tabular-nums`. No package names or authoring markers in CSS.
9. **resources/lang/en/generic.php** — every user-facing string via `__('capell-theme-ai-lab::...')`.
10. **Theme AiLab health check** for `theme-ai-lab.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\AiLab\` → `packages/theme-ai-lab/src`, `...\Tests\` → `.../tests`), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `AiLabThemeDefinitionTest`,
`AiLabThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeAiLabHealthCheckTest`. Theme-specific assertions:

- Page adapter builds `model-cards` with every item exposing `name`, `params`,
  `contextWindow`, `modality`, `status`, and `benchmarks` rows expose `peerScores[]`.
- Rendered public HTML for the playground section contains the static prompt/response
  text but **no** `wire:`, fetch/XHR script, model IDs, or signed URLs.
- `research-index` paper `url` values render as plain public links (no `signed`,
  no `data-field`, no `model_id`).
- Demo install seeds all 7 surfaces with Lumen AI / Lumen-3 copy (assert the brand
  name and a benchmark figure appear).

## Verification

```bash
vendor/bin/pest packages/theme-ai-lab/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
