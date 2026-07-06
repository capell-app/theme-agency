# Appendix — Portfolio-Gallery Widget Catalogue (raw expert output, 2026-07-05)

> **Pre-critique material.** Where this file conflicts with `../theme-improvement-programme-2026-h2.md` Part 2 (§0 guardrails, §A decisions), the programme document wins. Known overrides: deep-bench drag-reorder is DROPPED (filters stay); all scatter/shuffle positions must be server-seeded from a payload hash (no client randomness); masonry means deterministic grid spans, not CSS masonry.

## Headline mechanics (verified orthogonal)

| Theme | Mechanic | Physics metaphor | Core interaction |
|-------|----------|------------------|------------------|
| open-studio | Viewport scrubbing | Film reel frame-by-frame | Scroll/hover reveals sequential project frames; scrub timeline |
| field-guide | Tessellation grid | Responsive honeycomb | Dense taxonomy; auto-reflow; overlaid filtering reshuffles grid |
| launch-pad | Stagger launch sequence | Rocket staging tiers | Timed cascade reveal; momentum scroll-snap; sequential unlock |
| first-light | Lightbox reel | Darkroom contact sheet | Grid reveals to lightbox; curated best-of strip; swipeable carousel |
| one-take | Dossier pages | Case-study binding | Pagination as document pages; margin notes; sticky TOC |
| deep-bench | Bench/roster sorting | Lineup cards by skill | Role-based filtering; live name/badge search (drag-reorder dropped) |
| front-row | Salon gallery wall | Museum hang/curation | Varied card sizes; featured works float above grid |
| soft-focus | Scatter light table | Photography light table | Seeded scatter; organic overlap; click to bring to front |

## Motion tiers (uniform)

- **none:** static form; intent via layering, depth, scale variance; static hover states.
- **minimal:** fade 300–400ms; scale hover 1.02–1.05; no parallax; stagger 30–50ms.
- **subtle:** slide 300–400ms cubic-bezier; stagger 50–100ms; hover lift 2–4px; parallax ≤20px.
- **energetic:** spring physics (overshoot); parallax 30–50px; rotation/skew; magnetic snap; floating bob.

All respect `prefers-reduced-motion`; "none" must still look intentional.

---

## open-studio — Viewport Scrubbing (filmstrip)

Creative case-study platforms need cinematic pacing; sequential discovery mirrors project workflow narrative.

### filmstrip-project-showcase
Hero-scale project card with frame-by-frame scrubbing via timeline scrub bar or arrow buttons.
- Payload: `{ items[], currentIndex, autoplay, layout }`; per item `image`, `imageUrl`, `imageAlt`, `title`, `meta`, `discipline`, `featured`
- Layout: full-width; featured image 16:9; overlay title + meta + discipline badges bottom-left; scrub bar with thumbnail timeline + prev/next
- Motion: none→fade swap; minimal→fade 400ms + scrub indicator slides; subtle→slide 400ms + parallax overlay; energetic→parallax depth + spring-snap scrubbing
- Variants: `autoplay`, `grid-peek`, `info-drawer`
- Mobile: single column; scrub bar becomes horizontal carousel; overlay below image

### discipline-carousel-browse
Sticky discipline tabs filter a grid of related projects; underline animates.
- Payload: `{ items[], discipline, availableDisciplines[] }`; per item `tags[]`, `url`, `discipline`
- Layout: sticky horizontal tab bar; grid beneath (3/2/1 cols); selected underline animates
- Variants: `pill-style`, `count-badge`, `featured-first`
- Mobile: scroll tabs; 2-col grid

### process-notes-timeline
Vertical process timeline; spine animates on scroll, cards stagger in.
- Payload: `{ steps: [{ title, description, image, date, stage }] }`
- Layout: left spine with milestone dots; alternating offset cards; spine highlight follows scroll
- Variants: `sticky-title`, `compact`, `detailed`
- Mobile: single column; spine left; cards right-aligned

### credits-grid-roster
Role/discipline filter pills + contributor cards (avatar, name, role, link).
- Payload: `{ credits: [{ name, role, image, url, disciplines[] }], filterRoles[] }`
- Layout: filter pills; 4/3/2-col card grid; circular avatar, name, role tag, link
- Variants: `avatar-name-only`, `role-badge-prominent`, `searchable`

### next-project-cta
Button pair: next project / browse all.
- Payload: `{ nextProjectTitle, nextProjectUrl, browseAllUrl, preset }`
- Variants: `inline-text`, `floating`, `carousel-controls`

---

## field-guide — Tessellation Grid

Inspiration libraries need exhaustive browsability; multi-taxonomy discovery requires dense, reshuffle-safe layout.

### taxonomy-grid-browser
Dense auto-fit grid with overlaid filter sidebar; filters reshuffle grid (client-side over ≤50 rendered items).
- Payload: `{ items[], taxonomies: [{ name, filters[] }], layout: 'auto|rigid' }`
- Layout: sidebar (bottom sheet on mobile) + grid `auto-fit minmax(180px, 1fr)`
- Variants: `compact-density`, `filter-pills`, `saved-collections`

### latest-designs-showcase
"Just added" grid with date badge + tag overflow; hover zooms image.
- Payload: `{ items[], displayLimit: 12, sortBy: 'date|popularity', tags[] }`
- Variants: `carousel`, `time-slider`, `featured-first`

### editor-picks-curated
Alternating image/text blocks with curator avatar + note + expandable details.
- Payload: `{ picks: [{ item, curatorNote, curatorAvatar, curatorName }] }`
- Variants: `vertical-spine`, `quote-style`, `grid-after-hero`

### faq-archives-accordion
Category tabs + searchable accordion (shared accordion module); highlight matches.
- Payload: `{ faqs: [{ category, question, answer, tags[] }], categories[] }`
- Variants: `always-open`, `search-highlights`, `linked-items`

### collection-cta-browse
Large feature block (background image + overlay) with collection name, count, browse CTA.
- Payload: `{ collectionName, itemCount, imageUrl, ctaUrl, preset }`
- Variants: `featured-image`, `card-stack`, `inline-gallery`

---

## launch-pad — Stagger Launch Sequence

Template/landing galleries benefit from momentum-building reveal; sequential unlock drives scroll engagement.

### launch-sequence-hero
Staggered text reveal (title → subtitle → CTA) + featured image fade-in.
- Payload: `{ title, subtitle, image, ctaText, ctaUrl, ctaSecondary }`
- Layout: 70vh; text left, image right; gradient background
- Variants: `centered`, `split-half`, `video-bg`

### category-navigation-grid
Category filter pills with icon + count; scroll-snap.
- Payload: `{ categories: [{ name, icon, count }] }`
- Variants: `grid-4col`, `featured-large`, `nested-subcategories`

### website-examples-grid
Featured template cards (image, title overlay, category tag, CTA).
- Payload: `{ items[], displayMode: 'grid|featured-first', itemsPerPage }`
- Variants: `carousel-horizontal`, `featured-hero`, `hover-preview`

### paid-templates-upsell
Premium template showcase with price badge + feature count.
- Payload: `{ templates: [{ image, name, price, license, featureCount, ctaUrl }] }`
- Variants: `comparison-table`, `license-modal`, `discount-badge`

### launch-cta-sequence
Final-stage CTA with momentum arrow.
- Payload: `{ primaryCta, secondaryCta, backgroundImage }`
- Variants: `full-bleed-bg`, `gradient-overlay`, `countdown-timer` (data-deadline pattern)

---

## first-light — Lightbox Reel

Daily curation needs rapid-fire browsing (grid) + deep inspection (lightbox); contact-sheet metaphor mirrors the inspiration-feed use-case.

### curation-feed-grid
Dense contact-sheet grid; click opens shared lightbox; hover shows date badge + border highlight.
- Payload: `{ items[], layout: 'dense|balanced', enableLightbox: true }`; per item `image`, `title`, `date`
- Layout: grid `auto-fit minmax(140px, 1fr)`; square aspect; minimal spacing
- Variants: `masonry-safe`, `rows-only`, `category-tabs`

### lightbox-carousel-viewer
Full-screen overlay via shared lightbox.js; swipe, keyboard (arrows + Esc), prev/next.
- Payload: `{ items[], currentIndex }`; metadata below (title, source, date)
- Variants: `full-bleed`, `thumbstrip-bottom`, `metadata-overlay`
- Mobile: full viewport; bottom-sheet controls; swipe-to-dismiss

### best-of-views-carousel
Curated best-of carousel; 1.5 cards visible; scroll-snap center.
- Payload: `{ items[], selectedCategory, curateBy: 'popular|featured|recent' }`
- Variants: `thumbnail-gallery`, `vote-system`, `auto-advance`

### source-metadata-credits
Attribution stack (app, designer, date) with link icons + category pills.
- Payload: `{ source, sourceUrl, appName, appUrl, designer, designerUrl, date, categories[] }`
- Variants: `inline-grid`, `icon-prominent`, `copyable-credits`

### next-item-lightbox-cta
Lightbox-footer navigation: next item / return to grid; progress counter ("3 of 12").
- Payload: `{ nextItemTitle, itemCount, currentIndex }`
- Variants: `arrow-only`, `progress-bar`, `auto-advance-timer`

---

## one-take — Dossier Pages

Document metaphor mirrors the designer mental model: comprehensive project information in print-like structured format.

### showcase-hero-dossier
Featured template as a document page; page number bottom-right; left margin-notes area.
- Payload: `{ title, subtitle, image, pageNumber, totalPages, tags[] }`
- Layout: print-like container (border + shadow); image top half; page number small type
- Motion: subtle→page-turn (scroll-snap; view transitions where supported, fade fallback)
- Variants: `margin-notes`, `bookmark-dog-ear`, `facing-pages`

### category-tabs-pagination
Sticky tab bar styled as document sections; tabs show page ranges ("Pages 3–8").
- Payload: `{ categories: [{ name, pageStart, pageEnd, count }] }`
- Variants: `page-range-visible`, `section-dividers`, `collapsible-sections`

### one-page-grid-showcase
Grid of one-page template cards with preview image + specs sidebar (fonts, colours, sections).
- Payload: `{ items[], displayMode: 'grid|list', columnCount }`
- Variants: `list-view`, `expanded-specs`, `comparison-mode`

### tools-sponsors-sidebar
Sticky side column of tools, sponsors, resources — like a book endpaper.
- Payload: `{ tools[], sponsors[], resources[] }`
- Variants: `floating-sticky`, `collapsible`, `grid-footer`

### build-resources-cta
Final dossier page: "Start Your Project" CTA + resource list + final page number.
- Payload: `{ ctaPrimary, ctaSecondary, resourceLinks[] }`
- Variants: `bonus-resources`, `license-selector`, `referral-link`

---

## deep-bench — Bench/Roster Sorting

Talent-directory browsing: filterable, sortable lineups. **Drag-reorder dropped by feasibility review** (no persistence target on a public site); multi-select filters and search stay.

### directory-hero-roster
Hero with role-count stats + browse CTA.
- Payload: `{ introText, totalCount, roleCounts: [{ role, count }], ctaText }`
- Motion: stats count-up via shared count-up module
- Variants: `featured-faces`, `scroll-cue`, `stat-breakdown`

### role-filters-toolbar
Multi-select role pills with per-role count badges; reset control.
- Payload: `{ availableRoles[], selectedRoles[] }`
- Variants: `search-input`, `collapsible-groups`, `show-unavailable`

### portfolio-grid-cards
4/3/2-col grid of talent cards: avatar, name, role badges, skill tags, featured-work thumb, profile link.
- Payload: `{ portfolios: [{ name, image, roles[], skills[], featuredWork, profileUrl }] }`
- Variants: `list-view`, `featured-work-modal`, `follow-button`

### resume-resources-sidebar
Sticky resource links grouped by category (resume tips, portfolio building, career guides).
- Payload: `{ resources: [{ title, url, category }] }`
- Variants: `floating-sticky`, `collapsible`, `downloadable-guide`

### curated-lists-cta
Grid of curated collections ("Best Logo Designers") driving to category pages.
- Payload: `{ curatedLists: [{ name, count, ctaUrl }] }`
- Variants: `featured-hero`, `vote-system`, `trending-indicator`

---

## front-row — Salon Gallery Wall

Awarded portfolios deserve salon curation: prestige through the museum-hang metaphor. Card-size variety = deterministic grid spans from payload flags (no CSS masonry dependency).

### featured-portfolios-hero
Gallery centerpiece; floats above the grid on scroll with parallax shift + depth shadow.
- Payload: `{ portfolio: { name, image, award, featuredWork[], url } }`
- Variants: `image-left-text-right`, `award-badge-prominent`, `carousel-within`
- Mobile: full-width, no parallax

### filter-taxonomies-grid
Multi-taxonomy pills (award, style, discipline); grid reflows on selection.
- Payload: `{ taxonomies: [{ name, options[] }], selectedFilters }`
- Variants: `sidebar-filters`, `hide-unavailable`, `saved-searches`

### portfolio-grid-gallery-wall
Varied card sizing (1x1, 1x2 featured, 2x1 wide) via payload-driven span classes; organic gaps; hover lift + title reveal.
- Payload: `{ portfolios[], layout: 'spans|strict', featureMode }`
- Variants: `strict-grid`, `featured-float`, `image-focal-point`

### awarded-profiles-spotlight
Award-winning profiles with gold/silver/bronze badge styling.
- Payload: `{ awardedProfiles: [{ profile, award, year, awardBadge }] }`
- Variants: `award-categories`, `year-filter`, `vote-count`

### education-upsell-cta
Feature card promoting learning resources; partner logos below.
- Payload: `{ heading, description, courseCount, ctaUrl, partnerLogos[] }`
- Variants: `featured-course`, `testimonial-carousel`, `free-vs-paid`

---

## soft-focus — Scatter Light Table

Calm inspiration galleries need organic, non-linear layout. **All scatter positions/rotations are server-seeded from a payload/page hash** and shipped as CSS custom properties — page-cache coherent, no client randomness.

### browse-panels-scatter
Floating, overlapping category cards in seeded scatter (rotate 2–8°, translate offsets); click brings a card to front (z-index via `:has(input:checked)` or data-attribute).
- Payload: `{ categories: [{ name, icon, itemCount, image }], seed }`
- Variants: `grid-toggle`, `pin-to-top`, `story-points`
- Mobile: scatter disabled; single column

### style-type-categories-scattered
Floating filter cards ("Minimalist", "Bold", "Pastel"); click filters visible items; soft glow on hover.
- Payload: `{ filterGroups: [{ name, options[] }], seed }`
- Variants: `nested-filters`, `multi-select-sticky`, `color-swatches`

### latest-showcase-organic
Latest items in seeded overlapping scatter; hover lift + glow + image zoom.
- Payload: `{ items[], displayCount: 12, scatterDensity: 'low|medium|high', seed }`
- Variants: `pin-favorites`, `grid-toggle`, `time-slider`

### sponsor-space-floating
Sponsor/partner logos in seeded scatter; click brings forward + enlarges.
- Payload: `{ sponsors: [{ name, logo, url }], seed }`
- Variants: `featured-sponsor`, `tier-based`, `sponsor-showcase-modal`

### random-best-of-cta
"Feeling lost? Let us surprise you" — best-of cards; "Shuffle" rotates through a server-seeded order (deterministic sequence, not random).
- Payload: `{ bestOfItems: [{ item, curatorPick }], seed }`
- Variants: `auto-shuffle-timer`, `share-button`, `discovery-streak`

---

## Shared media primitives (Foundation-owned; all 8 consume with different tokens)

### art-directed-picture
`<picture>` with breakpointed sources, focal point, aspect-ratio tokens per breakpoint, overlay treatment.
- Payload: `{ sources: [{ media, srcset, sizes }], fallback, alt, aspectRatios: { desktop, tablet, mobile }, focalPoint: { x, y }, overlayTreatment: 'none|dim|gradient|text', loading, decoding }`

### hover-video-poster
Image poster with hover/scroll-triggered video preview.
- Payload: `{ posterImage, videoUrl, videoType: 'embed|hosted', autoplayOnScroll, preloadVideo, duration }`

### card-frame-wrapper
Card wrapper applying theme styling via tokens.
- Payload: `{ slot, variant: 'elevated|outline|filled|minimal', hoverEffect: 'lift|shadow|scale|glow|none', motionClass }`
