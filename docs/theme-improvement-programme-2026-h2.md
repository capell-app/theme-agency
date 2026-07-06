# Capell Theme Improvement Programme — 2026-H2

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. This document supersedes `docs/theme-review-and-improvement-plan.md` (75-theme era, tombstoned) and absorbs the remaining items from `docs/superpowers/plans/2026-06-28-real-theme-screenshots.md`.

**Goal:** Bring all 19 catalogue themes to a shared quality bar (visual + functional + tested), give every theme a distinct modern content-display identity through the Widget Design Catalogue (Part 2), and extend the catalogue into uncovered commercial verticals (local services, docs/KB, events) — with commerce explicitly deferred behind a `shopify-commerce` render-data bridge.

**Provenance:** Investigated and designed 2026-07-05 through an expert loop — 4 ideation experts (editorial art direction, gallery interaction design, dense-archive information design, conversion design) reviewed by 2 adversarial critics (creative-director similarity/freshness audit; staff-engineer feasibility audit). ~90 widgets proposed, 10 killed or merged as stale, 15 modern replacements added. Raw expert output is preserved in `docs/theme-improvement-programme-2026-h2/` appendices; **this document is the post-critique source of truth — where an appendix conflicts with Part 2 or the §0 guardrails, this document wins.**

---

## Verified current state (2026-07-05)

- 19 theme packages, 1:1 with `docs/themes.json` (schemaVersion 1), enforced by `ThemeCatalogueTest` + `ThemeCatalogueRenderingTest` in `packages/theme-foundation/tests/Unit/`. Every entry carries `priorityPhase` and `overlapRisk` (portfolio cluster flagged HIGH).
- No section-view duplication remains (md5-verified) — the old 28-theme dedupe finding died with the 75→19 cull.
- Screenshot fixtures are fully retired; all 19 themes ship a `*DemoContent` provider + 15–48 real PNGs. The fleet-wide `ThemeDemoContentContractTest` was never created. `theme-foundation` has installer machinery (`src/Support/Demo/ThemeDemoPageInstaller.php`) but no own DemoContent provider.
- Foundation is rich: ~40 test files, `VariantViewSectionRenderer` variant system, dark tokens (2026-06), runtime CSS tokens, `RegistersLayoutNativeThemeDefaults`, health check, `lightbox.js` + `carousel.js`.
- Two rendering patterns coexist: layout-native (night-shift, liquid-glass — definition-only provider + bespoke widget keys via `RenderableRegistry`) and classic `ChromeSplitBladeThemeRenderer` (other 17).
- Registry seam: `packages/theme-night-shift/src/NightShiftThemeServiceProvider.php:176-214` documents that `Widget::getComponent()` → `RenderableRegistry` has no per-theme scoping. **Feasibility review conclusion: the entire Part 2 widget catalogue works WITHOUT the seam** (per-theme bespoke keys, night-shift pattern). The seam remains valuable only for per-theme overrides of foundation-owned section keys and is an enhancement, not a blocker.
- Safety tests are unhardened per-theme copies (each bans only its own slug). 402 `@php` blocks in theme views (down from 854); policy undecided.
- Maturity: visually rich = night-shift, art-paper, quiet-type, first-light (2–4 test files each); the other ~14 thinner across the board.

## Programme structure — 6 tracks, 8 waves

| Wave | Track | Name                                                           | Depends on |
| ---- | ----- | -------------------------------------------------------------- | ---------- |
| 0    | E     | Doc reset & catalogue governance                               | —          |
| 1    | E     | Quality gates & tooling                                        | W0         |
| 2    | A     | Foundation & shared runtime upgrades (incl. shared JS modules) | W1         |
| 3    | D     | Demo content & screenshot lock-in                              | W1         |
| 4a   | B+C   | Level-up: editorial-publishing (4) — Part 2 §B                 | W2, W3     |
| 4b   | B+C   | Level-up: archive-directory (4) — Part 2 §C                    | W2, W3     |
| 4c   | B+C   | Level-up: portfolio-gallery (8) + free pair (2) — Part 2 §A/§D | W2, W3     |
| 5    | F     | New theme: local services (`theme-call-out`) — Part 2 §E       | W1, W2     |
| 6    | F     | New theme: docs/KB (`theme-reading-room`) — Part 2 §E          | W5         |
| 7    | F     | New theme: events (`theme-main-stage`) — Part 2 §E + roadmap   | W6         |

Waves 4a–4c are parallelisable with each other and with 5–7 once W2/W3 land, subject to the concurrent-writer rules under Risks.

---

# PART 1 — Programme waves

## Wave 0 — Doc reset & catalogue governance

- [ ] 0.1 Tombstone `docs/theme-review-and-improvement-plan.md` — header pointing here; carried forward: safety hardening (→W1.1), `@php` policy (→W1.2), tiering review (→W0.3), generator/validator (→W1.3/1.4).
- [ ] 0.2 Annotate `docs/superpowers/plans/2026-06-28-real-theme-screenshots.md` — Phases 0–3 complete; residual items (fleet contract test, validator check, CI lock) moved to W3. New recipe reference: theme-quiet-type (the agency pilot no longer exists).
- [ ] 0.3 Tier & overlap review over `docs/themes.json`: confirm 17 premium / 2 free intended; re-score the HIGH `overlapRisk` cluster (launch-pad / one-take / deep-bench / front-row / soft-focus; ink-press ↔ far-field); record each theme's Part 2 headline mechanic in its `notes`; bump `lastReviewed`; document quarterly review cadence in `docs/theme-catalogue-guide.md`.
- [x] 0.4 This programme document created.
- [ ] 0.5 **Screenshot-grounded visual audit (2026-07-05)** — reviewed all 19 committed homepage captures (`packages/theme-*/docs/screenshots/*-homepage.png`) against the Part 2 catalogue. Confirms the Wave 0.3 token/overlap diagnosis but surfaces what a code-level review can't: 12 of 19 themes' hero (and grid-thumbnail) imagery is the exact same stock photo (a shared `ThemeDemoMedia` fixture), including themes with strong token differentiation like off-grid and liquid-glass; a further 3 draw from the same photoshoot. deep-bench (unique photography), art-paper (oxblood duotone) and off-grid (desaturation) prove the fix already exists somewhere in the fleet — it isn't systematised. Full findings: Appendix `screenshot-audit-2026-07-05.md`. Feeds 0.6, 2.7, theme-bar criterion 8, and Risks 7–8.
- [ ] 0.6 **Demo-content artifact fixes** — cheap copy-only fixes ahead of any redesign wave, each confirmed on at least two pages (not one-off homepage quirks): open-studio's rendered brand is literally "Case Study Platform Demo" on both its homepage and detail page (nav, footer, and hero all say "Demo" — check its `DemoContent` provider for a hardcoded placeholder), and its detail-page footer nav exposes literal internal surface names as public links ("Empty state," "404") — the sharpest "looks unfinished" tell in the fleet; front-row's hero eyebrow reads "PREMIUM PORTFOLIO COLLECTION" on both pages too, leftover product-tier language rather than a fictional buyer brand like every sibling theme has; wild-card and reel-room's detail pages independently invent the identical fictional project name "Tidal States" — a buyer comparing screenshots side by side would notice. Fix and recapture before Wave 3.5 sign-off.
- [ ] 0.7 **Interior-page audit (2026-07-05)** — extended 0.5 to the other DemoContent surfaces (directory/detail/contact/empty/not-found/cta). Two findings beyond 0.5/0.6: (1) the shared-photo problem is _denser_ on grid-style interior pages than on homepages — field-guide's directory page shows one stock photo 4–5 times captioned as different indexed sites, quiet-type's directory page reuses its shared corridor photo 3× captioned as 3 different essays (quiet-type's homepage genuinely avoids the photo; its interior pages don't — corrects the 0.5 proof point). (2) **Screenshot-manifest surface coverage is thin** — 17 of 19 themes' captured screenshots use ad hoc surface names (`landing`/`listing`/`search`) instead of the DemoContent contract's names, and none of those 17 have `empty`, `not-found`, or `cta` captured at all — only liquid-glass and quiet-type match the contract's naming and cover all 7 surfaces. Where empty/404 states are visible (those two themes), they're genuinely well executed — on-brand typography, a shared recovery-action pattern, no generic framework-default look — but that's unverified for the other 17 because nobody, including this audit, can see them. Sharpens Wave 3.4/3.5: manifest completeness should explicitly gate on all 7 surface names, not just count-of-entries. Full detail: Appendix `screenshot-audit-2026-07-05.md` (updated).

Verify: `vendor/bin/pest packages/theme-foundation/tests/Unit/ThemeCatalogueTest.php packages/theme-foundation/tests/Unit/ThemeCatalogueRenderingTest.php --configuration=phpunit.xml`.

## Wave 1 — Quality gates & tooling (Track E)

Land before any Blade is touched so later waves are graded by the new gates.

- [ ] 1.1 **Canonical safety trait** — `packages/theme-foundation/src/Testing/AssertsPublicThemeOutputSafety.php`: existing banned tokens **plus** `capell-app/` (generic prefix), `CapellCore::`, `isPackageInstalled`, `Filament\`. Rewrite each of the 19 per-theme `PublicOutputSafetyTest`s to a ~5-line trait invocation (one commit per theme). Add a fleet glob-guard in theme-foundation scanning all `packages/theme-*/resources/views` so a theme without the trait still gets scanned.
- [ ] 1.2 **`@php` policy** — allowed for defaulting/prep only (`??=`, `data_get`, `@class` prep); banned for queries, facades, side-effectful conditionals. Document in `docs/creating-a-theme.md`; lint assertion in the trait flagging `::` static calls inside `@php` (whitelist `data_get`, `collect`, `trans`); freeze per-theme counts at the 402 baseline via snapshot array (counts may only go down).
- [ ] 1.3 **`capell:make-theme` generator** — `packages/theme-foundation/src/Console/Commands/MakeThemeCommand.php` + stubs: manifest v3 `capell.json`, night-shift-shaped definition-only provider using `RegistersLayoutNativeThemeDefaults`, DemoContent skeleton for all 7 surfaces, `Install<X>ThemeDemoAction`, demo command, `screenshots.json`, Pest tests referencing the shared trait. Feature test scaffolds into a temp dir.
- [ ] 1.4 **`capell:validate-themes`** — checks `capell.json` ↔ `docs/themes.json` ↔ registered `ThemeDefinitionData` agreement (themeKey, extends, tier, sections, token list vs presets), screenshot-manifest completeness (≥5 required entries), classification fields populated. Extract shared logic from `ThemeCatalogueTest`/`ThemePackageManifestTest` into Actions (e.g. `ValidateThemeCatalogueEntryAction`) consumed by both command and tests; wire into the composer `manifest:check` chain.

Verify: `vendor/bin/pest packages/theme-foundation/tests --configuration=phpunit.xml`, then each theme suite, then `COMPOSER=composer.local.json composer lint analyze`.

## Wave 2 — Foundation & shared runtime upgrades (Track A)

Everything here lifts all 19 at once; child themes get markup-only work later.

- [ ] 2.1 **Registry seam (enhancement, not blocker)** — theme-scoped override lookup (`registerThemeOverride(themeKey, key, definition)`, resolution theme-scoped → global) in the path used by `Widget::getComponent()`. Cross-repo: `capell-4` core `RenderableRegistry` + `packages/layout-builder`. Unblocks night-shift's "inert copy pending seam" sections. If it slips, all other work proceeds unaffected.
- [ ] 2.2 **Section-variant depth** — 2–3 named variants for each Foundation standard section (hero: `split|stacked|full-bleed`; content-listing: `grid|rows|masonry-safe`; cta: `band|card|inline`), declared centrally so child themes inherit the vocabulary. Update `SectionVariantDeclarationTest`.
- [ ] 2.3 **Motion presets** — map `motionIntensity` (`none|minimal|subtle|energetic`) to CSS tokens (`--foundation-motion-duration`, `--foundation-motion-ease`), `prefers-reduced-motion` guard, data-attribute contract. "None" must still look intentional (composition/depth, not absence). Settings migration if defaults persist.
- [ ] 2.4 **Dark-mode parity test** — `ThemeDarkModeParityTest` asserting presets with light-surface colours resolve legible dark tokens; fleet Blade scan for hardcoded hexes outside token vars.
- [ ] 2.5 **Standard-section coverage** — first-class Foundation `search`, `pagination`, `form` views + renderers (theme-scale names 10 standard sections; Foundation documents 7). Update the README override-contract list.
- [ ] 2.6 **Shared JS module suite** (themes opt in; target near-zero per-theme JS; total budget ≈ 20KB gzipped; every module keyboard-accessible + reduced-motion aware):
    - `carousel.js`, `lightbox.js` (exist — version + document), `tabs.js`, `count-up.js` (IntersectionObserver + `Intl.NumberFormat`), `scroll-spy.js` (CSS scroll-driven first, IO fallback), `compare-slider.js` (`role="slider"` + arrow keys), `accordion-toggle.js`.
- [x] 2.7 **Shared display primitives** — `art-directed-picture` (multi-source, focal point, aspect-ratio tokens), `hover-video-poster`, `card-frame-wrapper` (variant + hover-effect via tokens), `responsive-table-to-cards`, `count-up-stat`, `byline-with-metadata`, `timestamp-metadata-block`, `photo-treatment-filter` (per-theme duotone/tone-map via CSS `filter` + `mix-blend-mode`, deterministic token-driven skin applied over the shared `ThemeDemoMedia` stock pool — the systematised version of art-paper/off-grid's existing treatments, see 0.5). Token-skinned per theme; payload schemas in Appendix files.
- [ ] 2.8 **Health check + scaffolding** — extend `FoundationThemeHealthCheck` (themes missing catalogue entries / demo content / fresh screenshots); shared Pest helpers under `packages/theme-foundation/src/Testing/` for the generator.

Verify: `vendor/bin/pest packages/theme-foundation/tests packages/layout-builder/tests --configuration=phpunit.xml`; `COMPOSER=composer.local.json composer preflight`.

## Wave 3 — Demo content & screenshot lock-in (Track D, re-scoped to 19)

Rollout is done; this wave locks it against regression.

- [ ] 3.1 Create `packages/theme-foundation/tests/Feature/ThemeDemoContentContractTest.php` — auto-discovers every `ProvidesThemeDemoContent` implementation; asserts all 7 surfaces (homepage, directory, detail, contact, empty, not-found, cta), ordered non-empty sections, nav+footer chrome.
- [ ] 3.2 Add `FoundationDemoContent` + `InstallFoundationThemeDemoAction` so `default` passes the same contract (20 providers total).
- [ ] 3.3 Write `packages/theme-quiet-type/docs/COMPLETE-THEME-RECIPE.md` superseding the deleted agency pilot doc.
- [ ] 3.4 CI lock: confirm capell-screenshot-runner validator no longer requires the retired `url` field; add screenshot-manifest completeness to `capell:validate-themes`; ensure changed-file detection drives re-capture.
- [ ] 3.5 Full capture sweep (`bash scripts/local-package-screenshots.sh`) + contact sheets (`node scripts/build-theme-screenshot-contact-sheets.js`); recapture any theme whose PNGs predate its DemoContent; human sign-off per family.

## Waves 4a–4c — Per-theme level-up (Tracks B+C)

**The theme bar** — a theme is done when:

1. Pattern is deliberate: classic-with-intent (owns all sections with real visual identity, like art-paper) or layout-native (definition-only + bespoke widgets, like night-shift). No half-way re-registration of Foundation-identical markup.
2. Standard 10 sections covered (own or inherited, incl. new search/pagination/form).
3. **Headline display mechanic + 4–6 signature widgets from Part 2 implemented.** Each signature section/widget declares ≥2 variants.
4. Full 17-token preset set; ≥2 presets; dark-parity test passing.
5. Motion tokens consumed; mobile/tablet captures present; RTL + landmark tests clean.
6. ≥5 meaningful test files: DefinitionTest, manifest assertions, hardened safety trait, `<X>DemoContentRendersTest` (full-page render), CopyPreservationTest (night-shift model), widget/variant tests.
7. Catalogue entry updated: `sectionVariants` real, headline mechanic in `notes`, `overlapRisk` re-scored, ≥5 fresh screenshots.
8. Section rhythm and connective-tissue copy differ too — a signature mechanic doesn't clear the bar if the surrounding search/filter/stat-row furniture and section order still read as the same template as its siblings (0.5 finding).

**Visual acceptance per theme** (contact-sheet review): presets visibly differ on homepage (screenshot diff); variants switchable via Layout Builder container meta; dark capture legible; no mobile horizontal overflow; type scale via `headingScale` token; CSS within `ThemeCssIsolationTest` budget.

- [ ] 4a **editorial-publishing** (ink-press, art-paper, quiet-type, far-field) — implement Part 2 §B. art-paper first as the classic-pattern exemplar. Resolve ink-press ↔ far-field overlap (sharpen lanes or merge; record in themes.json `notes`).
- [ ] 4b **archive-directory** (wild-card, reel-room, off-grid, gold-rush) — implement Part 2 §C. off-grid is the `motionIntensity: none` reduced-motion showcase.
- [ ] 4c **portfolio-gallery ×8 + free pair** — implement Part 2 §A and §D in two sub-batches of 4. The HIGH-overlap cluster is differentiated by orthogonal headline mechanics, not new sections. Capstone: night-shift inert-section conversion if 2.1 landed.

Per-theme loop: `vendor/bin/pest packages/theme-<x>/tests --configuration=phpunit.xml` → pint → targeted phpstan → `bash scripts/local-package-screenshots.sh --package theme-<x>` → commit.

## Waves 5–7 — New themes (Track F)

Built with `capell:make-theme`, layout-native, born passing all W1 gates + the W3 contract. Each adds a **new family** to themes.json — catalogue entry + catalogue-test allow-list updates land in the **same commit**. Full widget specs: Part 2 §E.

- [ ] 5 **`theme-call-out`** — local service business. Family (new) `service-business`, premium, `overlapRisk: low`. Lane: quote-led trades & local services (plumbers, electricians, clinics, salons). Presets `call-out` (high-vis accent, bold headings, bordered cards) + `after-hours` (dark dispatch). Integrations (provider-resolved, never Blade): form-builder (fallback mailto card), bookings (fallback phone CTA), blog (fallback hide rail).
- [ ] 6 **`theme-reading-room`** — docs/knowledge-base. Family (new) `docs-knowledge`. Search-first sidebar-tree reading, distinct from editorial browsing and night-shift's SaaS-marketing lane. New Layout Builder area `docs-sidebar`. Presets `reading-room` (paper light) + `late-edition` (dark). Integrations: knowledge-base + search packages (fallback: curated static links).
- [ ] 7 **`theme-main-stage`** — events/conference. Family (new) `events-conference`. Companion data: events package; theme stays model-free. Presets `main-stage` (bold poster) + `green-room` (dark backstage). Fallback: static agenda copy when events absent.

**Roadmap (spec in a later programme):** `theme-open-hands` (nonprofit-cause: impact metrics, campaign progress, donate path), `theme-round-table` (community-membership: member directory, gated-content states, join path), `theme-set-menu` (hospitality: menu sections, reservations, supper-club events). **Commerce: DEFERRED** — prerequisite is a public-safe render-data bridge (product/catalogue payload contributors + fallback states) in `shopify-commerce` or a companion package. **Standing rule:** no new portfolio/gallery/archive themes without an overlap review; the HIGH flags in themes.json are the tripwire.

---

# PART 2 — Widget & Section Design Catalogue

Post-critique source of truth. Raw expert specs with full payload schemas live in the appendix directory `theme-improvement-programme-2026-h2/` — mine them during implementation, but §0 guardrails and the replacement decisions below always win.

## §0 Platform guardrails (bind every widget)

1. **Determinism / page-cache rule:** html-cache serves identical HTML to everyone. No `Math.random()` at render; "scatter"/"shuffle" layouts seed positions server-side from a payload/page hash and ship them as data-attributes/CSS custom properties. No client re-layout.
2. **Live-state policy:** nothing polls or pushes. Countdowns render a server-side target as `data-deadline` and tick client-side. "Live now / replay / closed" are **editorially toggled payload states**, never detected. Activity "pulses" render historical stats from payload.
3. **Payload caps:** carousels ≤20 items; grids ≤50; tables ≤100; timelines ≤50; client-side-filterable lists ≤50. Beyond a cap → pagination or curation, never bigger payloads.
4. **JS budget:** themes consume the Wave 2 shared modules; per-theme custom JS ≈ 0–2KB. Dropped outright by feasibility review: fuzzy-search command palettes (library + unbounded index → route to backend search) and public drag-reorder (no persistence target).
5. **A11y minimums:** compare sliders = `role="slider"` + arrow keys; scroll-spy = CSS scroll-driven first with `aria-current`; all state changes keyboard-reachable; `prefers-reduced-motion` honoured; light + dark legible.
6. **Motion tiers:** none = static but composed (depth/scale variance); minimal = fades 300–400ms, stagger 30–50ms; subtle = slides + ≤20px parallax; energetic = spring physics, 30–50px parallax, kinetic type.
7. **Similarity policy:** timelines, stat bands, sticky asides, logo strips are _shared primitives_ (Foundation-owned, token-skinned). Raw carousels/grids/accordions as a theme's _signature_ are banned — signatures must be one of the named mechanics below. Every future widget must name its mechanic and pass a similarity check against this catalogue before implementation.
8. **Modern-CSS support:** view transitions (~50% support) and CSS masonry (~60%) are enhancement-only; every mechanic specifies its fallback; no widget may _require_ either.

## §A Portfolio-gallery — 8 orthogonal headline mechanics

Verified zero-overlap matrix; each theme gets its mechanic + ~5 signature widgets built around it. Full payload specs: Appendix `portfolio-widget-catalogue.md`.

| Theme           | Headline mechanic                                                                                                     | Signature widgets                                                                                                                                                                                            |
| --------------- | --------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **open-studio** | **Filmstrip scrubbing** — sequential frame-by-frame project narrative                                                 | `filmstrip-project-showcase` (scrub bar + thumbnail timeline), `discipline-carousel-browse` (sticky tab filter), `process-notes-timeline` (spine draws on scroll), `credits-grid-roster`, `next-project-cta` |
| **field-guide** | **Tessellation grid** — dense taxonomy reflow (grid auto-fit; no true masonry)                                        | `taxonomy-grid-browser` (client-side facet reshuffle ≤50), `latest-designs-showcase`, `editor-picks-curated` (alternating curator notes), `faq-archives-accordion` (shared module), `collection-cta-browse`  |
| **launch-pad**  | **Stagger launch sequence** — scroll-staged reveal, momentum scroll-snap                                              | `launch-sequence-hero`, `category-navigation-grid`, `website-examples-grid`, `paid-templates-upsell`, `launch-cta-sequence`                                                                                  |
| **first-light** | **Lightbox reel** — contact-sheet grid → shared-lightbox deep inspection                                              | `curation-feed-grid`, `lightbox-carousel-viewer` (shared lightbox.js), `best-of-views-carousel`, `source-metadata-credits`, `next-item-lightbox-cta`                                                         |
| **one-take**    | **Dossier pages** — document metaphor; scroll-snap "page" navigation; view transitions where supported, fade fallback | `showcase-hero-dossier` (page numbers, margin notes), `category-tabs-pagination` (tabs as page ranges), `one-page-grid-showcase`, `tools-sponsors-sidebar`, `build-resources-cta`                            |
| **deep-bench**  | **Roster sorting** — lineup filtering/sorting (drag-reorder DROPPED per feasibility; multi-select filters stay)       | `directory-hero-roster` (count-up role stats), `role-filters-toolbar`, `portfolio-grid-cards`, `resume-resources-sidebar`, `curated-lists-cta`                                                               |
| **front-row**   | **Salon gallery wall** — curated hang, varied deterministic grid spans, featured floats above                         | `featured-portfolios-hero` (parallax float), `filter-taxonomies-grid`, `portfolio-grid-gallery-wall`, `awarded-profiles-spotlight` (gold/silver/bronze), `education-upsell-cta`                              |
| **soft-focus**  | **Scatter light table** — organic overlap, click-to-front; positions server-seeded from payload hash                  | `browse-panels-scatter`, `style-type-categories-scattered`, `latest-showcase-organic`, `sponsor-space-floating`, `random-best-of-cta` (shuffle = seeded rotation)                                            |

The five HIGH-overlap themes differ by mechanic, not palette — record the mechanic in each themes.json `notes` (W0.3).

## §B Editorial-publishing — print heritages + post-critique replacements

Each theme = a distinct print institution. Critique replaced 6 stale widgets (marked ↻) and merged the two pull-quote systems. Full payload specs: Appendix `editorial-widget-catalogue.md` (pre-critique — apply the ↻ decisions below).

**ink-press — broadsheet, "live-velocity journalism"** (velocity is editorial state, never polled):

- `breaking-news-ribbon` — re-scoped: editorially pinned alert strip, `state` from payload
- `live-event-timeline` — re-scoped: chronological dispatch feed + scroll progress
- `reading-progress-with-markers` — chapter-jump article progress bar
- ↻ `news-web-topology` (replaces `related-analysis-carousel`) — related articles as an orbital graph: center article, satellites positioned by relevance score via CSS calc; vertical-list fallback on touch
- ↻ `author-credibility-inline` (replaces `byline-fact-boxes`) — author credentials surface inline at narrative moments via scroll trigger, not a sticky sidebar
- `opinion-grid-with-bylines`

**art-paper — art catalogue, "kinetic design curation"**:

- ↻ `kinetic-image-sequence` (replaces `image-grid-with-captions`) — bento grid driven by image aspect ratios, scroll-driven bloom reveals, deterministic layout seed
- `designer-profile-card-cluster`
- ↻ `material-swatch-studies` (replaces `product-comparison-table`; simplified from the critic's iframe playground) — token-driven swatch cards whose finish/weight render via CSS custom properties + the shared compare-slider module
- `trend-forecast-infographic` — colour-swatch trend report
- `process-documentation-timeline` — shared timeline primitive, catalogue-plate skin

**quiet-type — literary journal, "literary intelligence"** (scored 5/5 — keep + add):

- `essay-with-dropcap-and-marginalia`, `serialized-chapters-navigator`, `issue-contents-table-of-contents`, `author-bio-with-bibliography`
- ↻ `quote-context-weaving` (merges both pull-quote systems) — quotes set into the running text at scale, margin medallion attribution, context-on-hover
- NEW `contextual-glossary-hover` — term definitions on hover/long-press from a payload glossary
- NEW `scroll-position-menu` — heading map as a side rail, `aria-current` scroll-spy (shared module)

**far-field — travel/culture monograph, "geographic + audio narrative"**:

- `audio-embedded-with-transcript` — re-scoped: audio element + timestamped transcript blocks, highlight-follow via small JS, no sync library
- `photo-essay-with-lazy-captions` — full-bleed scroll narrative
- ↻ `destination-atlas` (replaces `destination-grid-with-guides`; the critic's map-fusion minus MapboxGL) — inline SVG region map, pin hover syncs the adjacent card rail, map/list tabs on mobile; all payload-driven
- `cultural-dispatch-timeline`, `columnists-with-latest-essay-preview`

## §C Archive-directory — four institutions

Full payload specs: Appendix `archive-widget-design-spec.md` (pre-critique — apply the re-scopes below).

**wild-card = arcade cabinet:** `card-shuffle-grid` (absorbs `featured-carousel`; seeded shuffle-forward mechanic), `metadata-facet-wall`, `featured-today-banner` (re-scoped: editorial pick + `data-deadline` countdown to next rotation), `winners-ledger-table` (table-to-cards primitive), `submission-pulse` (re-scoped: historical 24h/7d stats band, CSS chart), NEW `infinite-scroll-depth-pressure` (CSS scroll-driven: shadows/borders deepen as you descend the collection).

**reel-room = cinema archive (5/5):** `video-preview-grid` (hover-video-poster primitive), `date-filter-rail`, `featured-project-showcase` (inline credits absorbed; standalone credits-sidebar cut), `jury-score-matrix`, `archive-wall-index` (deterministic grid spans).

**off-grid = field station (5/5, keep as-is):** `rough-links-directory`, `irregular-index-grid` (seeded irregularity), `archive-wall-text`, `submission-markers`, `zine-annotations`, `archive-calendar`. The `motionIntensity: none` showcase.

**gold-rush = assay office:** `score-criteria-table`, `voting-status-gauge` (re-scoped: conic-gradient progress from payload totals + `data-deadline`), `newest-nominees-carousel` (shared module), `previous-winners-ledger`, `nominee-heat-map`, `award-countdown-ticker` (re-scoped: server target, client tick).

**Cross-archive addition** (one implementation, four token skins): NEW `time-capsule-browser` — archive eras as 3D-perspective capsules; hover previews 3–5 items, click expands one at a time (CSS perspective + `:has()`).

## §D Free pair — the platform demo themes

**foundation `default` — "transparent, delightful utility"** (was 2/5; both stale widgets replaced):

- ↻ `pricing-value-spectrum` (replaces `pristine-pricing-table`) — range slider unlocks features progressively; discrete-tier fallback via `:has(:checked)`; pure CSS/small JS
- ↻ `faq-search-discovery` (replaces accordion FAQ) — type-to-filter over ≤50 payload items with match highlighting; no fuzzy library
- `changelog-stream`, `stats-display-band` (count-up primitive)
- NEW `helpful-form-hints` — progressive validation encouragement, `aria-live="polite"`

**liquid-glass — glassmorphism showcase:** `glass-feature-card`, `translucent-stat-band`, `layered-depth-hero` (z-depth layers; pointer-parallax at energetic tier only), `refraction-grid`, `floating-glass-nav`. Dark mode inverts the effect (less blur, more contrast).

## §E Vertical themes — night-shift additions + the three new themes

Full payload specs: Appendix `vertical-widget-catalogue.md`.

**night-shift (product-saas) additions** (command palette DROPPED per feasibility; existing widgets: `changelog-integrations`, `workflow-rails`, `security-proof`):
`cli-demo-pane` (typed-out terminal sequences; scroll-snap per command on mobile), `keyboard-shortcuts` (platform toggle via `:has()`), `latency-uptime-meter` (re-scoped: conic-gradient gauges from payload metrics; no refreshInterval), `integration-logo-constellation` (SVG connector lines draw on scroll; grid fallback mobile), `pricing-slider-calculator` (usage sliders → CSS-var price calc), `security-compliance-badge-wall` (expiry states computed at render).

**theme-call-out (service-business)** — conversion vocabulary: urgency + proof:
`service-area-map-grid` (locality coverage grid, no map deps), `before-after-comparison` (shared compare-slider, full ARIA), `emergency-availability-banner` (payload state: open/after-hours/closed + response-time copy), `quote-path-stepper`, `accreditation-insurance-strips`, `review-proof-wall`, `pricing-guide-table` (table-to-cards primitive), `team-on-the-road-cards`.

**theme-reading-room (docs-knowledge)** — scanability vocabulary:
`doc-tree-sidebar` (`<details>` tree, `docs-sidebar` area), `in-article-toc-scroll-spy` (CSS scroll-driven, IO fallback, `aria-current`), `search-spotlight-hero` (re-scoped from Cmd+K palette: prominent search box + payload-fed quick links ≤10; real search via the search package, fallback curated links), `version-changelog-surfaces` (breaking changes flagged), `api-reference-parameter-table` (copyable blocks, type badges), `callout-admonition-system` (note/warning/danger/tip), `feedback-footer`.

**theme-main-stage (events-conference)** — FOMO vocabulary, all states editorial:
`agenda-grid-days-tracks-rooms` (CSS subgrid; "now" highlight derived client-side from data-times), `speaker-wall-hover-bios`, `ticket-tier-comparison`, `countdown-band` (`data-deadline`), `venue-travel-panels`, `sponsor-tier-walls`, `live-now-replay-state` (payload state: upcoming|live|replay), `past-editions-archive`.

**Five-way conversion differentiation** (recorded so future themes don't converge): night-shift = measured technical polish, mono labels, dense; launch-pad = snappy visual delight, airy; call-out = state-driven urgency (green/amber/red), bold numbers; reading-room = minimized CTAs, scanability, serif-option body; main-stage = poster-bold, countdown FOMO, multi-colour tiers.

## §F Expert-loop verdicts worth keeping

- Distinctiveness after replacements: off-grid, reel-room, quiet-type, liquid-glass 5/5; catalogue average 4.5/5 (was 3.5).
- Marketing headliners: `news-web-topology`, `kinetic-image-sequence`, `time-capsule-browser`, `destination-atlas`, `pricing-value-spectrum`, `quote-context-weaving`, `cli-demo-pane`.
- Killed as stale (do not resurrect): raw `image-grid-with-captions`, `related-analysis-carousel`, `pristine-pricing-table`, accordion-only FAQ, `destination-grid-with-guides`, `product-comparison-table`, `byline-fact-boxes`, duplicate pull-quote systems, standalone `featured-carousel` (wild-card), standalone `media-credits-sidebar` (reel-room), fuzzy command palettes, public drag-reorder.

---

# Verification

- **Per item:** `vendor/bin/pest packages/<pkg>/tests --configuration=phpunit.xml`, pint + targeted phpstan on changed files.
- **Per widget:** render test with sample payload; variant declaration test; reduced-motion render; dark render; payload-cap boundary test where capped.
- **Per wave:** family suites + theme-foundation (+ layout-builder when contracts move); per-theme screenshot capture; contact-sheet sign-off against §0/§A–E acceptance criteria.
- **Programme gates:** `COMPOSER=composer.local.json composer preflight` before each wave merge; `composer test` + `manifest:check` + `capell:validate-themes` at W2, 4c, 7; full screenshot sweep at W3 and W7; 90% coverage floor.

# Risks & dependencies

1. **Registry seam** — enhancement, not blocker (feasibility-verified). Only night-shift's inert-section conversion waits on it.
2. **Concurrent writers** — one commit per item; `docs/themes.json` + catalogue-test allow-lists always in the same commit; re-verify HEAD before each item; overlay `composer dump-autoload` after pulling before diagnosing failures.
3. **New families break catalogue tests** — update `ThemeCatalogueTest`, `ThemeCatalogueRenderingTest`, `ThemePackageManifestTest`, `ThemeDemoPremiumDefinitionsTest`, `ThemeStylesheetDifferentiationTest`, `ThemeCssIsolationTest` fixtures in the same commit as each new theme.
4. **Copy quality at scale** — ~90 widgets need real copy; batch by family with contact-sheet review; copy-preservation tests guard regressions.
5. **Modern-CSS support** — §0.8: view transitions / CSS masonry are enhancement-only with mandatory fallbacks.
6. **Stale docs** — W0 tombstones prevent re-execution of the 75-theme plan or the completed screenshot rollout.
7. **Shared demo photography** — `ThemeDemoMedia`'s stock pool is reused across 12/19 themes' hero and grid imagery, 15/19 counting same-shoot crops (0.5); the 2.7 `photo-treatment-filter` primitive is the planned fix. Verify before Wave 3.5 recapture so no two themes are signed off still sharing a hero photo.
8. **Print stylesheet scope** — the pre-2026-H2 next-tier plan called for `@media print` rules (a natural fit for art-paper/quiet-type's print-institution framing); this programme doesn't currently assign it to a wave. Confirm keep-or-drop before Wave 2 closes out shared primitives.
