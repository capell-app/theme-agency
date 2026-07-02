# Theme Differentiation Standard

Last reviewed: 2026-07-01

This document defines what "differentiated" means for the Capell theme catalogue (Phase 1) and what separates the free tier from the premium tier (Phase 3), then applies both standards to the current catalogue in `docs/themes.json`. It complements `docs/theme-catalogue-guide.md` (the per-theme review and customisation guide) and `docs/theme-scale.md` (the tiering and manifest model); it does not repeat either.

## Premium differentiation criteria

A premium theme must differ from its siblings on the axes below — not merely by palette, accent colour, or header chrome. A colour-swap of a shared token preset is a variant, not a premium theme.

- **Layout rhythm** — the density, spacing cadence, grid structure, and first-viewport composition a visitor perceives before reading a word.
- **Section composition** — the set and ordering of sections the theme ships, including which are genuinely domain-specific rather than renamed foundation sections.
- **Conversion path** — the primary action the theme is engineered to produce (subscribe, submit, vote, enquire, buy) and the furniture that leads to it.
- **Media treatment** — how imagery is framed and prioritised (photographic, illustrated, screenshot-grid, motion-preview, score-media), which is often the most visible axis at marketplace-comparison distance.
- **Domain workflow** — the buyer's actual job the theme models (running a news desk, judging awards, curating submissions), evidenced by demo data and section vocabulary that only make sense in that domain.

A theme that differs on colour and section labels alone fails this standard. The catalogue records each theme's position on these axes via `lane`, `standardSections`/`customSections`, `visualDifferentiators`, and `overlapRisk` in `docs/themes.json`.

## Tier standard

Tier definitions and the manifest contract live in `docs/theme-scale.md`. This section defines the quality bar each tier must meet.

| Dimension     | Free                                                                                                             | Premium                                                                                                                     |
| ------------- | ---------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| Purpose       | Polished general-purpose defaults for broad use cases                                                            | A distinct buyer workflow in a named market lane                                                                            |
| Sections      | Fewer custom sections; leans on foundation chrome (`default` ships 7 sections, 0 custom; `liquid-glass` ships 9) | Domain-specific sections that model the buyer's job (e.g. `scoreboard-showcase`'s `score-criteria` and `voting-status`)     |
| Customisation | Strong customisation through Theme Studio tokens and Layout Builder; the buyer shapes the identity               | Tokens still apply, but the theme's identity survives token changes because it lives in layout rhythm and workflow          |
| Demo data     | Representative placeholder content                                                                               | Strong, domain-plausible demo content that proves the workflow                                                              |
| Proof         | Standard screenshot coverage                                                                                     | Richer screenshot proof (route-backed captures, mobile capture of the conversion page) and visual QA against sibling themes |

Free themes earn their place by being excellent defaults (`default` as the shared runtime, `liquid-glass` with its unique `overlayTreatment` token and presets section). Premium themes earn their price by differing on the five axes above — which is exactly where parts of the current catalogue fall short.

## Premium review

Grounded in `docs/themes.json` (21 themes, last reviewed 2026-07-01). Sixteen themes carry `priorityPhase` 3; nine of those are `overlapRisk` high, and two are tiered `candidate-for-merge`. Work one family per PR/commit series so before/after screenshots stay comparable.

### Priority 1 — magazine trio: byte-identical CSS (editorial-publishing)

`dense-news-analysis`, `design-led-magazine`, and `global-culture-magazine` ship byte-identical stylesheets (md5-confirmed); their catalogue notes agree the token values are "colour-swaps of the same preset". All three are premium and all three fail the layout-rhythm and media-treatment axes against each other.

- **dense-news-analysis** — differentiate. It has the clearest lane ("news publishers and analysis desks") and unique furniture (`live-brief`, `video-row`, `missed-it`), but per its own notes it "needs denser layout tokens to earn its name". Give it compact card density, tighter spacing, and a multi-column news front so the density is visible, not nominal.
- **design-led-magazine** — differentiate. Keep the `gallery-feature` and `product-credits` sections and push the photographic, airy, oxblood-on-paper direction into genuinely different layout rhythm: oversized lead-story media, generous whitespace, dramatic heading scale.
- **global-culture-magazine** — differentiate. Its `radio-audio` and `city-guides` sections are a real lane; per its notes it "needs distinct typography or density". Swap the shared sora/inter pairing for its own type voice and let the audio/travel furniture drive the first viewport.

### Priority 2 — illustrated portfolio token-clone cluster (portfolio-gallery)

`case-study-platform`, `character-portfolio-index`, `portfolio-directory`, `premium-portfolio-collection`, and `one-page-showcase` share an identical token set (sora/inter, bordered cards, illustrated media, airy density) differing only in accent/surface colour and section labels — all marked `overlapRisk` high.

- **premium-portfolio-collection** — keep as flagship. The catalogue names it the "anchor of the illustrated portfolio group", and its `awarded-profiles` and `education-upsell` sections give it the strongest domain workflow. Invest its differentiation budget here first.
- **character-portfolio-index** (`tier: candidate-for-merge`, `status: review`) — merge. Its notes call it a "thin variant of portfolio-directory / premium-portfolio-collection: same token values except accent and surface colours, and the same curated-grid concept". Fold its `curated-grid` and `standout-notes` ideas into the flagship as section variants, then retire the package.
- **portfolio-directory** — differentiate or demote to variant. The `role-filters` and `resume-resources` career angle is useful, but its notes say tokens and structure "mirror premium-portfolio-collection"; either give the career workflow its own layout rhythm (directory-table density, profile-first media) or ship it as a preset of the flagship.
- **case-study-platform** — differentiate. Its `process-notes`, `credits-tools`, and `discipline-filters` sections describe a genuine case-study workflow, but per its notes "differentiation lives almost entirely in section names and accent colour". Move to a long-scroll case-study rhythm with process-artefact media treatment.
- **one-page-showcase** — merge candidate in practice. Its notes call the one-page angle "the thinnest differentiator in the gallery cluster" and it is audience-adjacent to `landing-gallery`; either merge it into `landing-gallery` as a one-page lane or give it a radically different single-scroll demo.

### Priority 3 — merge candidate: creative-culture-editorial (editorial-publishing)

- **creative-culture-editorial** (`tier: candidate-for-merge`, `status: review`) — merge. Its notes name it the "strongest merge candidate in the magazine cluster": it differs from `design-led-magazine` "only by accent/surface colours, illustrated vs photographic media, and section labels". Fold its `opinion-block` and `advice-culture` sections into the surviving magazine themes and retire it.

### Priority 4 — remaining gallery overlap (portfolio-gallery, medium risk)

`filter-gallery`, `minimal-curation-feed`, and `quiet-web-gallery` all serve the inspiration-library buyer but have real visual separation (thumbnail-grid vs system-font hairline feed vs muted quiet-image-grid). Differentiate by conversion path rather than looks: `filter-gallery` on taxonomy-first exploration, `minimal-curation-feed` on daily-return feed habits, `quiet-web-gallery` on curated best-of collections. `landing-gallery` keeps its screenshot-first, `paid-templates` monetisation lane — which strengthens the case for absorbing `one-page-showcase`.

### Priority 5 — awards and archive coordination (archive-directory, medium risk)

- **experimental-directory / motion-archive** — differentiate as a pair. They share the "condensed/sharp/dense token skeleton" and awards vocabulary; per the catalogue "the two lanes should be pulled further apart". Anchor `experimental-directory` on submission intake (`metadata-filters`, `sponsor-modules`) and `motion-archive` on its motion-preview treatment and jury-score history.
- **scoreboard-showcase** — differentiate by conversion path. Its live `voting-status` and `score-criteria` mechanics already separate it from `motion-archive`'s historical lane, but the two "court the same buyer and warrant coordinated positioning" — document which awards buyer gets which theme.
- **raw-index** — no action. The only mono/brutalist token set in the catalogue; its notes confirm the zine identity keeps it clear of siblings.

### Phase 2 themes (for contrast, no phase-3 work)

`dark-product-system` (only dark, only SaaS-lane theme), `editorial-serif` (only serif-first token set, reuses foundation chrome deliberately), and `raw-index` show what the standard looks like when met: each differs from every sibling on at least three of the five axes.

## Enforcement

Two guards keep this standard from regressing:

- `packages/theme-foundation/tests/Unit/ThemeCatalogueTest.php` validates catalogue completeness — every theme package must have a well-formed `docs/themes.json` entry with tier, family, lane, sections, customisation surfaces, and overlap metadata.
- A companion test asserts that no two theme stylesheets are byte-identical, so the magazine-trio failure mode (identical CSS shipped under three premium keys) is caught in CI rather than in a marketplace side-by-side.

When a theme is merged or re-laned per the review above, update `docs/themes.json` in the same change; the catalogue test treats it as the source of truth.
