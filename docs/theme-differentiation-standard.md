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
| Sections      | Fewer custom sections; leans on foundation chrome (`default` ships 7 sections, 0 custom; `liquid-glass` ships 9) | Domain-specific sections that model the buyer's job (e.g. `gold-rush`'s `score-criteria` and `voting-status`)               |
| Customisation | Strong customisation through Theme Studio tokens and Layout Builder; the buyer shapes the identity               | Tokens still apply, but the theme's identity survives token changes because it lives in layout rhythm and workflow          |
| Demo data     | Representative placeholder content                                                                               | Strong, domain-plausible demo content that proves the workflow                                                              |
| Proof         | Standard screenshot coverage                                                                                     | Richer screenshot proof (route-backed captures, mobile capture of the conversion page) and visual QA against sibling themes |

Free themes earn their place by being excellent defaults (`default` as the shared runtime, `liquid-glass` with its unique `overlayTreatment` token and presets section). Premium themes earn their price by differing on the five axes above — which is exactly where parts of the current catalogue fall short.

## Premium review

Grounded in `docs/themes.json` (21 themes, last reviewed 2026-07-01). Sixteen themes carry `priorityPhase` 3; nine of those are `overlapRisk` high, and two are tiered `candidate-for-merge`. Work one family per PR/commit series so before/after screenshots stay comparable.

### Priority 1 — magazine trio: byte-identical CSS (editorial-publishing)

`ink-press`, `art-paper`, and `far-field` ship byte-identical stylesheets (md5-confirmed); their catalogue notes agree the token values are "colour-swaps of the same preset". All three are premium and all three fail the layout-rhythm and media-treatment axes against each other.

- **ink-press** — differentiate. It has the clearest lane ("news publishers and analysis desks") and unique furniture (`live-brief`, `video-row`, `missed-it`), but per its own notes it "needs denser layout tokens to earn its name". Give it compact card density, tighter spacing, and a multi-column news front so the density is visible, not nominal.
- **art-paper** — differentiate. Keep the `gallery-feature` and `product-credits` sections and push the photographic, airy, oxblood-on-paper direction into genuinely different layout rhythm: oversized lead-story media, generous whitespace, dramatic heading scale. `creative-culture-editorial` has already been folded into it as the `warm-ink` preset; its `opinion-block`/`advice-culture` section ideas were retired rather than carried over, since a preset in this codebase is token-values-only.
- **far-field** — differentiate. Its `radio-audio` and `city-guides` sections are a real lane; per its notes it "needs distinct typography or density". Swap the shared sora/inter pairing for its own type voice and let the audio/travel furniture drive the first viewport.

### Priority 2 — illustrated portfolio token-clone cluster (portfolio-gallery)

`open-studio`, `deep-bench`, `front-row`, and `one-take` share an identical token set (sora/inter, bordered cards, illustrated media, airy density) differing only in accent/surface colour and section labels — all marked `overlapRisk` high.

- **front-row** — keep as flagship. The catalogue names it the "anchor of the illustrated portfolio group", and its `awarded-profiles` and `education-upsell` sections give it the strongest domain workflow. Invest its differentiation budget here first. `character-portfolio-index` has already been folded into it as the `hand-picked` preset; its curated-grid/standout-notes section ideas were retired rather than carried over, since a preset in this codebase is token-values-only.
- **deep-bench** — differentiate or demote to variant. The `role-filters` and `resume-resources` career angle is useful, but its notes say tokens and structure "mirror front-row"; either give the career workflow its own layout rhythm (directory-table density, profile-first media) or ship it as a preset of the flagship.
- **open-studio** — differentiate. Its `process-notes`, `credits-tools`, and `discipline-filters` sections describe a genuine case-study workflow, but per its notes "differentiation lives almost entirely in section names and accent colour". Move to a long-scroll case-study rhythm with process-artefact media treatment.
- **one-take** — merge candidate in practice. Its notes call the one-page angle "the thinnest differentiator in the gallery cluster" and it is audience-adjacent to `launch-pad`; either merge it into `launch-pad` as a one-page lane or give it a radically different single-scroll demo.

### Priority 4 — remaining gallery overlap (portfolio-gallery, medium risk)

`field-guide`, `first-light`, and `soft-focus` all serve the inspiration-library buyer but have real visual separation (thumbnail-grid vs system-font hairline feed vs muted quiet-image-grid). Differentiate by conversion path rather than looks: `field-guide` on taxonomy-first exploration, `first-light` on daily-return feed habits, `soft-focus` on curated best-of collections. `launch-pad` keeps its screenshot-first, `paid-templates` monetisation lane — which strengthens the case for absorbing `one-take`.

### Priority 5 — awards and archive coordination (archive-directory, medium risk)

- **wild-card / reel-room** — differentiate as a pair. They share the "condensed/sharp/dense token skeleton" and awards vocabulary; per the catalogue "the two lanes should be pulled further apart". Anchor `wild-card` on submission intake (`metadata-filters`, `sponsor-modules`) and `reel-room` on its motion-preview treatment and jury-score history.
- **gold-rush** — differentiate by conversion path. Its live `voting-status` and `score-criteria` mechanics already separate it from `reel-room`'s historical lane, but the two "court the same buyer and warrant coordinated positioning" — document which awards buyer gets which theme.
- **off-grid** — no action. The only mono/brutalist token set in the catalogue; its notes confirm the zine identity keeps it clear of siblings.

### Phase 2 themes (for contrast, no phase-3 work)

`night-shift` (only dark, only SaaS-lane theme), `quiet-type` (only serif-first token set, reuses foundation chrome deliberately), and `off-grid` show what the standard looks like when met: each differs from every sibling on at least three of the five axes.

## Enforcement

Two guards keep this standard from regressing:

- `packages/theme-foundation/tests/Unit/ThemeCatalogueTest.php` validates catalogue completeness — every theme package must have a well-formed `docs/themes.json` entry with tier, family, lane, sections, customisation surfaces, and overlap metadata.
- A companion test asserts that no two theme stylesheets are byte-identical, so the magazine-trio failure mode (identical CSS shipped under three premium keys) is caught in CI rather than in a marketplace side-by-side.

When a theme is merged or re-laned per the review above, update `docs/themes.json` in the same change; the catalogue test treats it as the source of truth.
