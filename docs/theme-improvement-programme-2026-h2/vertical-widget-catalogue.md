# Appendix — Vertical & SaaS Widget Catalogue (raw expert output, 2026-07-05)

> **Pre-critique material.** Where this file conflicts with `../theme-improvement-programme-2026-h2.md` Part 2 (§0 guardrails, §E decisions), the programme document wins. Known overrides: the Cmd+K fuzzy command palette is DROPPED (reading-room's `search-spotlight-hero` is the re-scope); `latency-uptime-meter` has NO refreshInterval/live fetch (payload metrics only); countdowns use the `data-deadline` pattern; "live" states are editorial payload toggles.

Night-shift ground truth at time of writing: 3 existing bespoke widgets (`changelog-integrations`, `workflow-rails`, `security-proof`) registered via `registerBespokeWidgetRenderables()` → shared `RenderableRegistry` (additive keys, no collisions). Payloads reach Blade via `$widget->getMeta('key')`; colours/motion via theme tokens; optional integrations resolve in the provider with static fallbacks, never in Blade.

---

## Night-shift additions (dark product-led SaaS)

### cli-demo-pane
Simulated terminal window with typed-out command sequences, accent syntax highlighting, play/pause.
- Payload: `{ heading, summary, commands: [{ prompt, input, output, highlightLanguage }], autoplay, playSpeed: 'slow|normal|fast' }`
- Layout: `<pre>` monospace, accent prompt symbols, soft line-highlight on output
- Motion: none→static; minimal→type-out 40ms/char + cursor blink + output fade; energetic→scroll-driven stagger
- Variants: `inline-compact`, `full-pane`, `static-fallback`
- Mobile: one command per scroll-snap; swipe to next
- Why: CLI/terminal is core SaaS vocabulary; dark-native

### keyboard-shortcuts
Searchable shortcut grid, Mac/Windows toggle via `:has()`, grouped by workflow phase.
- Payload: `{ heading, summary, shortcuts: [{ keys: ['cmd','k'], label, description, context }], groupBy, showPlatformToggle }`
- Variants: `compact`, `full-reference`, `contextual-inline`
- Mobile: vertical stack; segment control for platform

### latency-uptime-meter
Conic-gradient meter rings (latency ms, uptime %, availability per region) — **rendered from payload metrics; no live fetch**.
- Payload: `{ heading, metrics: [{ label, value, unit, target, accentOverride, region? }] }`
- Motion: meters fill on scroll-into-view; count-up text via shared module
- Variants: `compact-cards`, `regional-grid`, `leaderboard`

### integration-logo-constellation
Scroll-animated constellation of integration logos with SVG connector lines + hover tooltips.
- Payload: `{ heading, summary, integrations: [{ name, logoUrl, category, url, description }], layout: 'constellation|grid|carousel', showLabels }`
- Motion: lines draw on scroll (stroke-dasharray); logos fade staggered
- Variants: `organic-web`, `grid-badges`, `carousel-tiles`
- Mobile: 2-col grid, no SVG lines (perf)

### pricing-slider-calculator
Usage sliders drive live price calculation via CSS custom properties + minimal JS.
- Payload: `{ basePrice, unit, metric, tiers: [{ name, features[], pricePerUnit, annualDiscount? }], sliders: [{ label, min, max, default, step, multiplier }], currencySymbol }`
- Variants: `simple-tiered`, `slider-based`, `comparison-matrix`

### security-compliance-badge-wall
Badge/seal wall with hover detail, status styling, **expiry states computed at render** from payload dates.
- Payload: `{ heading, badges: [{ name, logoUrl, category, expiryDate?, auditUrl?, verificationStatus }], showExpiry, groupByCategory }`
- Variants: `compact-seal-grid`, `detailed-cards`, `timeline-ladder`

---

## theme-call-out — local service business (service-business)

Conversion vocabulary: locality, visual proof, urgency, trust, price transparency, human connection. Tokens: warm high-vis accent, cream surface, availability-state greens/ambers/reds. Presets: `call-out` (high-vis) + `after-hours` (dark dispatch).

1. **service-area-map-grid** — postcode/neighbourhood coverage grid (pure CSS, no map deps); tap reveals availability detail panel. Payload: `{ areas: [{ name, coverage: 'full|partial|on-request', responseNote }] }`
2. **before-after-comparison** — shared compare-slider module (clip-path, `role="slider"`, arrow keys); before/after job photos. Payload: `{ pairs: [{ beforeImage, afterImage, caption }] }`
3. **emergency-availability-banner** — editorial payload state (`open|after-hours|closed`) + response-time copy; pulse dot at subtle+ motion only. Payload: `{ state, headline, responseEstimate, phone }`
4. **quote-path-stepper** — numbered path (Request → Quote → Schedule → Complete) with inline CTA per step; scroll-driven highlight. Payload: `{ steps: [{ title, description, ctaText?, ctaUrl? }] }`
5. **accreditation-insurance-strips** — accreditation logos, licence numbers, bonding status; expandable proof rows. Payload: `{ items: [{ name, logo, licenceNumber?, detail }] }`
6. **review-proof-wall** — star-rated review cards with platform attribution; grid or carousel (shared module). Payload: `{ reviews: [{ rating, quote, author, platform, url }] }` (≤20)
7. **pricing-guide-table** — service pricing breakdown (call-out fee + hourly labour), expandable detail rows; table-to-cards primitive on mobile. Payload: `{ rows: [{ service, priceFrom, unit, detail }] }`
8. **team-on-the-road-cards** — technician bios: photo, years experience, specialism, service-area note, testimonial snippet. Payload: `{ members: [{ name, photo, years, specialism, areas, quote? }] }`

Integrations (provider-resolved): form-builder (quote intake → fallback mailto CTA card), bookings (scheduling → fallback phone CTA), blog (job stories → fallback hide rail).

---

## theme-reading-room — docs/knowledge-base (docs-knowledge)

Scanability vocabulary: navigation UX, scroll-spy TOC, versions, parameter tables, admonitions. Presets: `reading-room` (paper light) + `late-edition` (dark).

1. **doc-tree-sidebar** — nested `<details>` nav tree in the new `docs-sidebar` Layout Builder area; breadcrumb; current-page highlight. Payload: `{ tree: [{ title, url, children[] }], currentUrl }`
2. **in-article-toc-scroll-spy** — right-rail TOC (floating on mobile); CSS scroll-driven highlight first, IntersectionObserver fallback; `aria-current`. Payload: `{ headings: [{ id, title, level }] }`
3. **search-spotlight-hero** — re-scoped from Cmd+K palette: prominent search box + payload-fed quick links (≤10) + popular-topics chips; real search via the search package integration; fallback = curated links only. Payload: `{ placeholder, quickLinks: [{ title, url }], topics: [{ label, url }] }`
4. **version-changelog-surfaces** — version selector (tabs/dropdown); per-version changelog; breaking changes flagged. Payload: `{ versions: [{ label, entries: [{ type: 'added|changed|breaking|fixed', text }] }] }`
5. **api-reference-parameter-table** — parameter tables with type badges, defaults, required flags; copyable code blocks. Payload: `{ endpoint?, params: [{ name, type, default?, required, description }], example? }`
6. **callout-admonition-system** — note/warning/danger/tip callouts: icon + accent left border. Payload: `{ type, title?, body }`
7. **feedback-footer** — "Was this helpful?" thumbs + optional form-builder integration (fallback: mailto link). Payload: `{ prompt, targetUrl? }`

---

## theme-main-stage — events/conference (events-conference)

FOMO vocabulary — countdown, live/replay, tiers — **all states editorial payload toggles**. Presets: `main-stage` (bold poster) + `green-room` (dark backstage).

1. **agenda-grid-days-tracks-rooms** — days × tracks × rooms grid via CSS subgrid (stacked lists fallback); "now" highlight derived client-side from `data-start`/`data-end` attributes. Payload: `{ days: [{ label, tracks: [{ name, sessions: [{ title, speaker, room, start, end, tags[] }] }] }] }` (≤50 sessions/day)
2. **speaker-wall-hover-bios** — headshot grid, name/title overlay; hover/tap reveals bio + social links. Payload: `{ speakers: [{ name, title, photo, bio, links[] }] }` (≤50)
3. **ticket-tier-comparison** — side-by-side tier cards, "most popular" emphasis, feature checklists. Payload: `{ tiers: [{ name, price, badge?, features[], ctaUrl, soldOut? }] }`
4. **countdown-band** — hero countdown; server-rendered `data-deadline`, client-side tick; timezone note; number-flip at subtle+ motion. Payload: `{ deadline, headline, ctaText, ctaUrl, timezoneNote }`
5. **venue-travel-panels** — venue address + static map image, parking, transit, hotels, accessibility notes; collapsible sections. Payload: `{ venue, address, mapImage, panels: [{ title, body }] }`
6. **sponsor-tier-walls** — logo walls per tier (Platinum/Gold/Silver); expandable sponsor details. Payload: `{ tiers: [{ name, sponsors: [{ name, logo, url, blurb? }] }] }`
7. **live-now-replay-state** — banner driven by payload state `upcoming|live|replay`; red pulse only when `live` and motion ≥ subtle. Payload: `{ state, headline, ctaText, ctaUrl }`
8. **past-editions-archive** — prior years: attendee count, session count, keynote highlight, archive link. Payload: `{ editions: [{ year, stats: { attendees, sessions }, keynote, url, image }] }`

---

## Five-way conversion differentiation (anti-convergence record)

| Dimension | night-shift | launch-pad | call-out | reading-room | main-stage |
|-----------|-------------|-----------|----------|--------------|-----------|
| CTA style | "Start free trial / Deploy" (product-led) | "View template" (gallery) | "Request quote / Book" (urgent local) | "Copy code / Read" (reference, minimized) | "Register / Watch live" (event) |
| Visual proof | workflows, changelogs, security badges | cards, votes, templates | before/after, team, reviews | code, parameters, versions | agenda, speakers, sponsors |
| Motion intent | measured technical polish, scroll-driven metrics | snappy visual delight | state-driven urgency pulses | scanability: scroll-spy, sticky TOC | FOMO: countdown, live pulse |
| Key widget | cli-demo-pane | gallery system | before-after-comparison | in-article-toc-scroll-spy | countdown-band |
| Trust signal | uptime, integrations, certs | votes, popularity | accreditations, reviews | authorship, versions | speakers, past editions |
| Info density | dense, mono labels | airy, rounded | moderate + alert punch | high line-height, serif option | bold, large imagery |

## Per-widget quality rubric

- Payload field names self-document; Blade 40–80 lines with heavy logic in the provider
- All colours/motion via theme tokens; `prefers-reduced-motion` respected
- Semantic HTML + ARIA + keyboard nav; mobile-first
- Optional integrations degrade to static fallbacks resolved in the provider
- Sample-payload render test passes; ≥2 named variants; payload structure documented in a Blade comment
