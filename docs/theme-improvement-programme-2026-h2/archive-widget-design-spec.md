# Capell Theme Signature Widgets & Primitives

**Author**: Claude Code | **Status**: Plan (Design Phase)
**Scope**: Task A (Archive-Directory × 4 themes, 5-6 widgets each) + Task B (Liquid-Glass, Foundation Default, +2 Foundation Primitives)

---

## ARCHITECTURE FOUNDATION

Each theme extends `theme-foundation`, shares canonical token schema (`primaryColor`, `accentColor`, `neutralColor`, `surfaceColor`, `foregroundColor`, `headingFont`, `bodyFont`, `spacing`, `cardStyle`, `layoutPresentation`, `motionIntensity`, `mediaTreatment`, `radius`, `headingScale`, `cardDensity`, + `overlayTreatment` for liquid-glass only).

**Rendering**: Layout-builder containers (theme-liquid-glass) OR VariantViewSectionRenderer (archive-directory, foundation). No DB queries from public Blade; filtering/sorting payload-driven or client-side.

**Progressive Enhancement**: Container queries, `:has()`, subgrid, scroll-snap, sticky positioning, CSS counters, scroll-driven animations. Respect `prefers-reduced-motion`.

---

## TASK A: ARCHIVE-DIRECTORY THEMES

### 🎰 **WILD-CARD** (Playful Collection Browsing, Arcade Energy)
*Burnt-orange accent, bone surface, condensed/system pairing, feature-slab media, zero-radius cards, dense grid*

#### 1. **featured-carousel**
- **Concept**: Shuffle-deck of rapid-fire featured entries with swipe & hover preview overlay
- **Payload**: `items[]` (title, image, tags, href), `currentIndex`, `autoplayInterval`
- **Layout**: CSS scroll-snap container (touch-friendly), carousel cards full-bleed on mobile → 3-col grid desktop, feature-slab images with burnt-orange border accent
- **Motion** (by `motionIntensity`):
  - Low: fade-in on swipe
  - Medium: slide + color pulse on hover
  - High: parallax image shift + staggered tag reveal
- **Variants**: (1) auto-rotate with pause-on-hover, (2) manual swipe only, (3) paginated with dot nav
- **Mobile**: Single column, full-width swipe
- **Data**: Payload-driven; client-side carousel logic
- **Why wild-card**: Playful energy, arcade-velocity rhythm — other themes need calm, steady navigation

---

#### 2. **metadata-facet-wall**
- **Concept**: Filterable wall of sticky category pills (type, year, status, size, color) with live result count
- **Payload**: `facets[]` (label, count, isActive, slug), `resultCount`, `filterUrl`
- **Layout**: Horizontal scroll on mobile, flex wrap on tablet/desktop with CSS Grid for density; pills as tight clusters (2px gap), zero-radius borders matching card style
- **Motion**:
  - Low: color transition on select
  - Medium: count badge scale-up when facet activates
  - High: facet → result card stagger-in
- **Variants**: (1) multi-select with clear-all, (2) single-select only, (3) collapsible sections (Type / Year / Status)
- **Mobile**: Horizontal scroll carousel, sticky to viewport top
- **Data**: Payload-driven; filtering via `filterUrl` query params or client JS that re-renders payload
- **Why wild-card**: Arcade cabinets have dense controls — facets are visceral, instant feedback

---

#### 3. **card-shuffle-grid**
- **Concept**: Masonry grid with "randomize" button; each card reveals hidden metadata on hover (artist, likes, submission date) via CSS-only reveal
- **Payload**: `cards[]` (title, image, hiddenMeta{artist, date, likeCount}, href), `gridColumns`
- **Layout**: CSS Grid with auto-fit columns (8rem card base, `cardDensity` token scales), zero-radius cards, slight rotation via `transform: rotate(var(--card-angle))`
- **Motion**:
  - Low: metadata slide-up on hover
  - Medium: card lift (box-shadow expand) + metadata fade
  - High: card tilt perspective + metadata parallax + confetti CSS animation on randomize
- **Variants**: (1) strict grid, (2) masonry, (3) rotate-random (each card gets unique angle)
- **Mobile**: Single column or 2-col, no rotation
- **Data**: Client-side shuffle (Array.sort randomization); card angles stored as CSS custom properties
- **Why wild-card**: Playful accidents, serendipity — tangible tactile card-flip sensation

---

#### 4. **featured-today-banner**
- **Concept**: Full-width hero slot (image + overlay text + CTA) with today's "featured" entry; counts down to next feature
- **Payload**: `entry{title, image, excerpt, ctaLabel, ctaHref}`, `nextChangeTimestamp`, `backgroundColor` (use accentColor)
- **Layout**: Full-bleed image with semi-transparent dark overlay (use `surfaceColor` @ 0.7 opacity), text centered or bottom-left (layout token), CTA button floating
- **Motion**:
  - Low: static feature
  - Medium: image zoom on load, CTA pulse
  - High: scroll-driven image parallax + countdown timer + "swapping in X hours" ticker (CSS counter)
- **Variants**: (1) image-lead, (2) text-lead, (3) quote style (testimonial from featured artist)
- **Mobile**: Text overlay stacked bottom, image reduced aspect ratio
- **Data**: Payload-driven
- **Why wild-card**: "Today's pick" has arcade FOMO/lotto energy; countdown = behavioral draw

---

#### 5. **winners-ledger-table**
- **Concept**: Sortable table of winners (name, category, year, score) with inline sparkline-ish CSS bar (width = normalized score) and expand-to-details pattern
- **Payload**: `rows[]` (name, category, year, score, details{bio, project, image}), `sortKey`, `sortOrder`
- **Layout**: Horizontal scroll on mobile, full table on desktop; rows as tight striped bands (alternating `surfaceColor` / surface+10%), zero-radius cells, sparkline bar via `<span style="width: calc(var(--score) * 1%)">`
- **Motion**:
  - Low: row highlight on hover
  - Medium: sparkline bar fill animation on load
  - High: row expand with nested details (flex-column reveal)
- **Variants**: (1) compact ledger, (2) card-per-row (for mobile-first), (3) with photo thumbnails
- **Mobile**: Card layout, sparklines full-width
- **Data**: Client-side sort (payload re-mapped via JS or `:has()` + CSS-only sort via data attributes if payload is pre-sorted)
- **Why wild-card**: Gameboard/scoreboard viscerality; ledger format = institutional credibility + playful density

---

#### 6. **submission-pulse-indicator**
- **Concept**: Real-time submission counter with visual "heartbeat" pulse when new submissions arrive; stacked bar chart of submission rate by hour (last 24h)
- **Payload**: `totalCount`, `recentCount`, `lastUpdateTimestamp`, `chartData[]` (hour, count)`
- **Layout**: Header stat block (large number + "new" flash) + mini bar chart (8 bars = 3-hour blocks) below, all in card wrapper
- **Motion**:
  - Low: number update with color flash
  - Medium: bar chart animates in staggered
  - High: pulse animation (scale + opacity) at `recentCount` update, sparkline bars grow with cubic-bezier ease
- **Variants**: (1) counter only, (2) chart only, (3) full combo with live WebSocket update (if backend supports)
- **Mobile**: Stacked layout, bars full-width
- **Data**: Payload-driven; if live updates, subscribe to WebSocket / Server-Sent Events
- **Why wild-card**: Energy meter = play-state feedback; real-time = FOMO loop

---

---

### 🎬 **REEL-ROOM** (Media/Video Archive, Cinema Archive Energy)
*Concrete-grey surface, signal-orange accent, condensed/system pairing, motion-preview treatment, zero-radius cards, dense grid*

#### 1. **video-preview-grid**
- **Concept**: Grid of video cards with play icon + duration + thumbnail; hover reveals preview video (lazy-loaded `<video>` or iframe embed) in lightbox; category badge
- **Payload**: `videos[]` (id, title, duration, thumbnail, previewUrl, category, views), `gridColumns`
- **Layout**: Responsive grid (3-4 cols desktop, 2-col tablet, 1-col mobile); cards aspect-ratio 16/9, zero-radius, category badge top-right with orange accent, duration bottom-right in monospace
- **Motion**:
  - Low: play icon opacity on hover
  - Medium: thumbnail blur-up on hover, icon scale-in
  - High: thumbnail pan (zoom + translate) + blur effect, play icon spins slightly
- **Variants**: (1) thumbnail-only, (2) with inline preview (hover shows video snippet), (3) with jury score badge
- **Mobile**: Single column, preview opens in modal
- **Data**: Payload-driven; preview video loads on click (lazy-load iframe or HTML5 `<video>`)
- **Why reel-room**: Video archives = cinema; motion-preview is signature treatment

---

#### 2. **date-filter-rail**
- **Concept**: Horizontal scrollable timeline of years/decades; click selects year, content below re-filters; sticky to top on scroll
- **Payload**: `dates[]` (year, count, isActive)`, `selectedYear`, `onSelectUrl`
- **Layout**: Horizontal scroll carousel, year labels in monospace, active year has orange underline + larger text, counts in smaller grey text below
- **Motion**:
  - Low: text color change on select
  - Medium: active underline slides smoothly
  - High: content below cross-fade when year changes
- **Variants**: (1) years only, (2) decades with expansion, (3) month-by-month
- **Mobile**: Full-width scroll, labels larger for finger tap
- **Data**: Payload-driven; URL update or client-side state
- **Why reel-room**: Archive exhibits organize by time; timeline = curation narrative

---

#### 3. **featured-project-showcase**
- **Concept**: Large media hero (video or image) with overlaid metadata (title, jury intro, score, credits) and "next/prev" nav; sticky scroll behavior
- **Payload**: `project{id, title, media{type, src, thumbnail}, jury{quote, members[]}, score, credits}`, `allProjects[]` (for nav context)
- **Layout**: Full-width hero container (60vh) with media fill, dark gradient overlay (black fade from bottom), text block bottom-left/right, nav arrows positioned absolutely, credits small caption
- **Motion**:
  - Low: text fade-in on load
  - Medium: media parallax on scroll, text slides up
  - High: prev/next arrows animate in staggered, media cross-fade on nav
- **Variants**: (1) video-lead, (2) image-lead, (3) quote-first (large jury quote then media)
- **Mobile**: Media reduced height (40vh), text bottom-full-width
- **Data**: Payload-driven
- **Why reel-room**: Cinema-style feature presentation; immersive media = awards showcase

---

#### 4. **jury-score-matrix**
- **Concept**: Matrix table (projects × jurors) with color-coded score cells (heatmap: low=grey, mid=orange, high=white); click cell to see justification note
- **Payload**: `projects[]` (id, title), `jurors[]` (id, name), `scores[]` ({projectId, jurorId, score, note})`, `colorScale`
- **Layout**: Sticky header (juror names vertical text or compact initials), project names as row labels, score cells as colored squares (aspect-ratio 1/1), expand-on-click for note detail
- **Motion**:
  - Low: cell highlight on hover
  - Medium: cell background animates in staggered load
  - High: heatmap "fills" with timed animation per cell
- **Variants**: (1) compact grid, (2) with mini sparklines (trend within juror), (3) aggregated rows (avg score + range)
- **Mobile**: Horizontal scroll, sticky first column (project names)
- **Data**: Payload-driven
- **Why reel-room**: Jury/critic credibility = institutional transparency; matrix = dense reference tool

---

#### 5. **archive-wall-index**
- **Concept**: Staggered grid of archive "drawers" (visual cards that look like filing boxes) with year range, entry count, and "open" action
- **Payload**: `archives[]` (id, label, startYear, endYear, count, color, href)`, `groupBy` (optional: category)
- **Layout**: Staggered CSS Grid (masonry-like) with zero-radius "box" cards, year-range label in monospace top-left, count badge top-right (orange circle), slight rotation per card (CSS custom var)
- **Motion**:
  - Low: card highlight on hover
  - Medium: box shadow expands, text scales slightly
  - High: card rotates back to level on hover (opposite of rest state), count badge spins
- **Variants**: (1) strict grid, (2) masonry, (3) with category headers
- **Mobile**: Single column or 2-col
- **Data**: Payload-driven
- **Why reel-room**: Archive physicality; filing cabinet metaphor = curation narrative

---

#### 6. **media-credits-sidebar**
- **Concept**: Sticky sidebar (or bottom sheet on mobile) listing all media assets in current project/archive: photographer, music, footage credits with links
- **Payload**: `credits[]` (type, name, credit, url, license)`, `projectTitle`
- **Layout**: Vertical scrollable list in sidebar or modal, entries as cards with type label (e.g., "Photography"), grey text, links in orange
- **Motion**:
  - Low: static
  - Medium: list items fade-in staggered on load
  - High: scroll-driven sticky header
- **Variants**: (1) sidebar, (2) collapsible drawer at bottom, (3) inline at end of content
- **Mobile**: Bottom sheet / modal
- **Data**: Payload-driven
- **Why reel-room**: Media archives = institutional responsibility for attribution; sidebar = secondary-but-visible info

---

---

### 📋 **OFF-GRID** (Spare Reference Lists, Field Station Energy)
*Monospace typeface, raw card style, hyperlink-blue accent on grey, zero-radius, irregular-index media, dense layout*

#### 1. **rough-links-directory**
- **Concept**: Unglamorous plain-text hyperlinks organized by category; links render in monospace, URL visible (not just label), no images
- **Payload**: `categories[]` ({name, items[]({title, url, date, tags[]})})`, `searchQuery`
- **Layout**: Vertical list or 2-col layout, each link as minimal card (monospace title, tiny grey URL text, date right-aligned), categories as section headers (same font), zero padding
- **Motion**:
  - Low: link color change on hover (hyperlink blue)
  - Medium: URL text reveals/hides on hover
  - High: category expand/collapse with CSS `:has(:checked)` toggle
- **Variants**: (1) flat list, (2) category collapse, (3) with annotation quotes (tiny grey text under link)
- **Mobile**: Single column, URL always visible
- **Data**: Client-side search/filter via JS on monospace text
- **Why off-grid**: Brutalism; links = knowledge without decoration; monospace = hacker/archive aesthetic

---

#### 2. **irregular-index-grid**
- **Concept**: Freeform grid of index entries: varying text sizes, all monospace, some entries larger (hot picks), some tiny (references); arranged in a seemingly chaotic but legible grid
- **Payload**: `entries[]` ({text, size ('small'|'medium'|'large'), href, isHot})`, `seed` (for layout randomization)
- **Layout**: CSS Grid with rows & columns variable-height; use `grid-auto-flow: dense` to fill gaps; entries as minimal labels (no background, just text + underline on hover)
- **Motion**:
  - Low: underline on hover
  - Medium: text size pulse on hover
  - High: entries fade-in staggered from top-left to bottom-right
- **Variants**: (1) random seed per session, (2) fixed seed (consistent layout), (3) with background boxes (minimal grey bg)
- **Mobile**: Simplified layout, single row-height
- **Data**: Payload-driven (with optional seed for deterministic layout)
- **Why off-grid**: Field guides = scattered reference; irregular = intentional curation, not algorithm

---

#### 3. **archive-wall-text**
- **Concept**: Wall of archive dates (year-month) as a dense text grid; hover shows count or featured entry from that period
- **Payload**: `archiveEntries[]` ({date, count, title, excerpt})`, `startDate`, `endDate`
- **Layout**: CSS Grid of dates in monospace, zero-radius no-background style, dates in single-color text (hyperlink blue or accent color), responsive grid (8 cols desktop → 4 cols tablet → 3 cols mobile)
- **Motion**:
  - Low: date color on hover
  - Medium: count badge appears on hover
  - High: featured entry card slides in from side
- **Variants**: (1) dates only, (2) with count, (3) with featured entry preview
- **Mobile**: Vertical list format (dates + counts), no grid
- **Data**: Payload-driven
- **Why off-grid**: Timeline = historical record; text-only = archival aesthetic

---

#### 4. **submission-markers-list**
- **Concept**: Stark list of submissions with marker status (new, featured, archived) as tiny symbol/icon; each row is a submission entry with title, date, status
- **Payload**: `submissions[]` ({id, title, date, status ('new'|'featured'|'archived'), href})`
- **Layout**: Vertical list, minimal cards, status marker as tiny square or symbol left of title (blue for new, accent for featured, grey for archived), date right-aligned in monospace, zero styling except borders
- **Motion**:
  - Low: row hover background
  - Medium: marker icon animates (blink for new)
  - High: new submissions glow briefly on load
- **Variants**: (1) flat list, (2) with category grouping, (3) collapsible by date range
- **Mobile**: Single column, status marker larger
- **Data**: Payload-driven
- **Why off-grid**: Markers = field notes; list = inventory aesthetic

---

#### 5. **zine-annotations-sidebar**
- **Concept**: Floating sidebar or callout of editorial annotations/notes on current archive section (handwriting-like style, via serif font or literal annotation marks)
- **Payload**: `annotations[]` ({text, author, date, context})`
- **Layout**: Sidebar or right-margin area with zero-radius minimal cards, annotations in serif (contrast to monospace headers), tiny author+date, annotation marks (✗ or →) as visual separators
- **Motion**:
  - Low: static
  - Medium: annotations fade-in staggered
  - High: scroll-driven sticky annotation (sticks to scroll position)
- **Variants**: (1) sidebar, (2) margin notes (inline), (3) hoverable popover
- **Mobile**: Drawer or bottom sheet
- **Data**: Payload-driven
- **Why off-grid**: Zine = handmade feel; annotations = curatorial voice

---

#### 6. **irregular-archive-calendar**
- **Concept**: Calendar grid of archive activity (heatmap of posts per day), dates as clickable cells, varying opacity/color by intensity
- **Payload**: `calendarData[]` ({date, count, entries[]})`, `month`, `year`
- **Layout**: Standard calendar grid (7 cols × 6 rows), cells sized square, background opacity or color intensity by count (dark when active), date number in monospace, zero borders
- **Motion**:
  - Low: cell highlight on hover
  - Medium: count label appears on hover
  - High: cells fill-in staggered on load (top-left → bottom-right)
- **Variants**: (1) heatmap color, (2) opacity-only, (3) with mini sparklines (cell internal)
- **Mobile**: Vertical date list (one per row) instead of grid
- **Data**: Payload-driven
- **Why off-grid**: Calendar = temporal archive; sparse = intentional gaps

---

---

### 🏆 **GOLD-RUSH** (Dense Cataloguing, Assay Office Energy)
*Rust accent on sand surface, scoreboard card style, compact density, voting/jury section vocabulary, zero-radius*

#### 1. **score-criteria-table**
- **Concept**: Table of judging criteria (category name, description, weight, examples); criteria rows expand to show full rubric on click
- **Payload**: `criteria[]` ({id, name, description, weight, scale(1-10), examples[]})`, `totalWeight`
- **Layout**: Horizontal-scroll table on mobile, full table desktop; sticky left column (criteria name + icon), compact cells, weight percentage right-aligned (mini bar), zero-radius cell borders
- **Motion**:
  - Low: row highlight on hover
  - Medium: weight bar fills in staggered load
  - High: row expand with nested rubric cards
- **Variants**: (1) table-only, (2) with expand details, (3) with score distribution histogram per criterion
- **Mobile**: Stacked cards format
- **Data**: Payload-driven; sorting via client-side or URL params
- **Why gold-rush**: Awards need transparent criteria; scoreboard energy = public accountability

---

#### 2. **voting-status-gauge**
- **Concept**: Large gauge/progress widget showing voting progress (e.g., 73% of votes counted); animated fill on load
- **Payload**: `votesReceived`, `votesTotal`, `votingDeadline`, `currentLeader`, `tieStatus`
- **Layout**: Centered large circular gauge (SVG or CSS conic-gradient) with percentage center, small label below, countdown timer below that
- **Motion**:
  - Low: static gauge
  - Medium: gauge fill animates in on load with easing
  - High: gauge updates with number counter, deadline timer counts down, alert color if approaching deadline
- **Variants**: (1) circular gauge, (2) linear progress bar, (3) with mini pie chart of category breakdown
- **Mobile**: Smaller gauge, text larger
- **Data**: Payload-driven
- **Why gold-rush**: Voting energy = suspense/momentum; gauge = real-time drama

---

#### 3. **newest-nominees-carousel**
- **Concept**: Horizontal carousel of newest nominee entries (3-4 visible), each card shows thumbnail, name, category, "votes so far" counter
- **Payload**: `nominees[]` ({id, name, image, category, voteCount, href})`, `sortBy` (newest/most-voted)
- **Layout**: Scroll-snap carousel, cards compact (2-col mobile, 4-col desktop), zero-radius images, vote count badge bottom-right in rust accent, category label top-left
- **Motion**:
  - Low: card highlight on hover
  - Medium: vote count badge scale up on page load
  - High: carousel smooth-scroll, vote counter animates when votes update
- **Variants**: (1) newest first, (2) most-voted first, (3) with live vote ticker
- **Mobile**: Single-col scroll
- **Data**: Payload-driven; vote count updates can be periodic or WebSocket-driven
- **Why gold-rush**: Fresh momentum; carousel = browsing pleasure

---

#### 4. **previous-winners-ledger**
- **Concept**: Sortable, searchable table of past winners (years): winner name, category, year, vote count, link to project page
- **Payload**: `winners[]` ({id, name, category, year, voteCount, href})`, `sortKey`, `search`
- **Layout**: Table (horizontal scroll on mobile), sticky header, rows as striped bands, year right-aligned in rust, vote count as mini bar/number
- **Motion**:
  - Low: row hover
  - Medium: bars animate in on sort change
  - High: rows fade-in staggered on load
- **Variants**: (1) list view, (2) card grid, (3) with category facets
- **Mobile**: Card layout with vertical stacking
- **Data**: Client-side sort/search or payload-driven (with pre-sorted/filtered data)
- **Why gold-rush**: Winners = institutional record; ledger = authority + transparency

---

#### 5. **nominee-heat-map**
- **Concept**: Matrix of nominees × categories with color-coded popularity heatmap (scale: few votes → many votes)
- **Payload**: `nominees[]` (id, name), `categories[]` (id, name), `heatData[]` ({nomineeId, categoryId, voteCount})`, `maxVotes`
- **Layout**: Matrix grid (nominees × categories), color cells from light (low votes) to rust (high votes), clickable cells expand to detail card, responsive to density token
- **Motion**:
  - Low: cell hover highlight
  - Medium: cells fill in staggered on load (random order)
  - High: cells update real-time if votes change, mini number counter animates
- **Variants**: (1) heatmap only, (2) with row/col totals, (3) with trend sparklines
- **Mobile**: Horizontal scroll, collapsed category headers
- **Data**: Payload-driven
- **Why gold-rush**: Heatmaps = data transparency; rust+sand = warm authority

---

#### 6. **award-countdown-ticker**
- **Concept**: Large countdown timer to awards ceremony; shows hours:minutes:seconds, updates in real-time, visual alert when <24h remaining
- **Payload**: `ceremonyDate`, `ceremonyTitle`, `ceremonyUrl`
- **Layout**: Centered large monospace numbers, ceremony title above, date below, background shifts color when deadline near (alert orange), no card border
- **Motion**:
  - Low: static display
  - Medium: numbers update smoothly
  - High: numbers blink or scale briefly on each second, background pulses if <24h
- **Variants**: (1) hh:mm:ss only, (2) with date, (3) with "Time to vote" label
- **Mobile**: Larger text for readability
- **Data**: Payload-driven; updates via interval (`setInterval`) or WebSocket
- **Why gold-rush**: Urgency = voting drives; countdown = behavioral loop

---

---

## TASK B: FOUNDATION THEMES

### 💎 **LIQUID-GLASS** (Glassmorphism, Modern Product Energy)
*Teal/orange palette, xl-radius elevated cards, manrope headings, framed media, glassmorphic overlays, backdrop-filter effects*

**Note**: Liquid-Glass is layout-native (renders via layout-builder containers, not section renderers). Widgets work within those containers.

#### 1. **glass-feature-card**
- **Concept**: Elevated card with frosted-glass effect (backdrop-filter blur + transparency) layered over blurred background image; foreground text has text-shadow for legibility
- **Payload**: `title`, `description`, `backgroundImage`, `overlayColor` (teal or orange from palette), `ctaLabel`, `ctaHref`
- **Layout**: Fixed aspect-ratio (16:9 or 4:3), xl-radius corners (2rem), backdrop-filter blur (10px), semi-transparent surface color overlay (0.1 opacity), text centered or bottom-left
- **Motion**:
  - Low: text fade-in on load
  - Medium: background image zoom on hover, glass effect intensifies
  - High: parallax image on scroll, glass blur increases on scroll-down
- **Variants**: (1) centered text, (2) bottom-left layout, (3) with quote style
- **Mobile**: Full-width, reduced height, text larger
- **Data**: Payload-driven
- **Why liquid-glass**: Glassmorphism is signature token; backdrop-filter + depth creates visual hierarchy

---

#### 2. **translucent-stat-band**
- **Concept**: Stats display (3-5 numbers: users, products, uptime) in horizontal band with glassmorphic background; numbers count-up on scroll-into-view
- **Payload**: `stats[]` ({label, value, unit, icon?})`, `triggerOnScroll`
- **Layout**: Horizontal flex row of stat items, each item glass card (smaller, xl-radius), semi-transparent background with backdrop-filter, number large (use headingScale token) and orange, label small and teal-ish
- **Motion**:
  - Low: static display
  - Medium: numbers count up (0 → value) on load or scroll-into-view
  - High: count-up with cubic-bezier ease, stat card scale-in staggered
- **Variants**: (1) count-up on load, (2) count-up on scroll, (3) with animated icons
- **Mobile**: Stacked vertical, full-width glass cards
- **Data**: Payload-driven; counter animation via JS `Intl.NumberFormat` or simple increment loop
- **Why liquid-glass**: Glass cards + motion = luxury-product aesthetic; stats validate positioning

---

#### 3. **layered-depth-hero**
- **Concept**: Hero section with 3-4 stacked glass layers (parallax at different rates), top layer has CTA; background is blurred video or image
- **Payload**: `backgroundMedia` (image or video), `layers[]` ({text, depth, ctaLabel?})`, `headlineFont` (use manrope)
- **Layout**: Full viewport height, layered cards at different `transform: translateZ()` pseudo-depths, top layer full-width CTA button, glass effect on each layer
- **Motion**:
  - Low: static layers
  - Medium: layers fade-in staggered
  - High: parallax depth on mouse move (JavaScript 3D perspective) or on scroll
- **Variants**: (1) parallax on scroll, (2) parallax on mouse move, (3) with animated background
- **Mobile**: Reduced height, single layer (or 2), no mouse parallax
- **Data**: Payload-driven; parallax via JS or CSS scroll-driven animations
- **Why liquid-glass**: Depth tokens + layering = glassmorphism hallmark; hero = product launch energy

---

#### 4. **refraction-grid**
- **Concept**: Grid of framed images (with glass borders and subtle blur) that appear to "refract" light; background behind cards blurs when cards in focus
- **Payload**: `images[]` ({src, caption, refraction-intensity (0-1)})`, `gridCols`
- **Layout**: Responsive grid (3-4 cols desktop, 2-col tablet, 1-col mobile), each image wrapped in xl-radius glass frame with backdrop-filter, slight blur on image itself (intensity by data), border-color teal or orange
- **Motion**:
  - Low: border glow on hover
  - Medium: image blur changes on hover (reverse: blur decreases)
  - High: background behind card darkens/blurs on hover, card lifts (shadow + transform)
- **Variants**: (1) blur-on-hover, (2) blur-static, (3) with captions
- **Mobile**: Single column, simpler glass effect
- **Data**: Payload-driven
- **Why liquid-glass**: Refraction metaphor (light bending through glass) = visual signature

---

#### 5. **floating-glass-nav**
- **Concept**: Floating navigation sidebar or top bar with glass effect; nav items scroll smoothly; backdrop-filter blurs content behind
- **Payload**: `navItems[]` ({label, href, isActive})`, `position` ('side'|'top')
- **Layout**: Glass container with rounded corners (xl-radius), semi-transparent background, sticky/fixed positioning, nav links in manrope, active link in orange accent
- **Motion**:
  - Low: link color on hover
  - Medium: active link underline slides in smoothly
  - High: nav appears from side (slide-in) or top (fade-in + drop-shadow)
- **Variants**: (1) sidebar, (2) top nav, (3) floating center
- **Mobile**: Top nav (not side), full-width, horizontal scroll
- **Data**: Payload-driven
- **Why liquid-glass**: Floating = weightless; glass = modern tech aesthetic

---

### 📚 **FOUNDATION DEFAULT** (Pristine General-Purpose)
*Structured layout, balanced spacing, subtle cards, natural media, Inter throughout*

#### 1. **pristine-pricing-table**
- **Concept**: 2-4 pricing tiers side-by-side; each tier is a subtle card with features list, price large, CTA button bold, highlight one tier (popular)
- **Payload**: `tiers[]` ({name, price, billingPeriod, description, features[], ctaLabel, ctaHref, isPopular})`, `currency`
- **Layout**: Responsive grid (1-col mobile, 2-col tablet, 3-4 cols desktop), each tier card with soft shadow (subtle card token), popular tier has slightly larger or tinted background, features as checkmark list
- **Motion**:
  - Low: tier highlight on hover
  - Medium: popular tier scales up slightly on load
  - High: checkmarks animate in staggered on load
- **Variants**: (1) standard 3-tier, (2) 2-tier comparison, (3) with annual/monthly toggle
- **Mobile**: Single column (or carousel), CTA button full-width
- **Data**: Payload-driven
- **Why foundation**: Pricing validates SaaS positioning; table is pure utility, no flash needed

---

#### 2. **accordion-faq-has**
- **Concept**: FAQ section with accordion (expand/collapse) built on CSS `:has()` without JavaScript; each Q&A pair is a subtle card
- **Payload**: `faqs[]` ({question, answer, category?})`
- **Layout**: Vertical stack of cards, each card has `<input type="checkbox">` (hidden) + label (question) + content area, `:has(:checked) > content` shows answer
- **Motion**:
  - Low: chevron icon rotates on expand
  - Medium: answer slides down on expand (max-height transition)
  - High: answer fades in as it slides
- **Variants**: (1) single-expand (only one open at a time, radio input), (2) multi-expand (checkbox), (3) with categories (collapsible sections)
- **Mobile**: Question text wraps, answer readable
- **Data**: Payload-driven; no database queries needed (all in HTML)
- **Why foundation**: `:has()` is modern CSS win; FAQ = essential utility; no JS = accessible + performant

---

#### 3. **changelog-stream**
- **Concept**: Vertical timeline of changes/updates; entries are dated cards with version badge, summary, and expandable detail
- **Payload**: `entries[]` ({date, version, summary, details, breaking?, category?})`, `sortOrder` ('newest-first' default)
- **Layout**: Vertical stream with timeline line (CSS `::before` pseudo), each entry offset left/right alternating, card with version badge top-left
- **Motion**:
  - Low: entry card highlight on hover
  - Medium: entries fade-in staggered on load
  - High: timeline line draws in (CSS animation), entries slide-in from sides
- **Variants**: (1) simple list, (2) with full details expand, (3) with category filter (e.g., Features/Fixes/Breaking)
- **Mobile**: Single-side timeline (all left or all right), entries full-width
- **Data**: Payload-driven
- **Why foundation**: Changelog = transparency + documentation; timeline = easy scanning

---

#### 4. **stats-display-band**
- **Concept**: Horizontal band of key stats (users, revenue, uptime) with simple styling; numbers in large type, labels below in smaller grey text
- **Payload**: `stats[]` ({label, value, unit, trend?, color?})`
- **Layout**: Horizontal flex row of stat blocks, centered align, each block with number (use headingScale), label, optional trend indicator (⬆️ green, ⬇️ red)
- **Motion**:
  - Low: static
  - Medium: numbers count-up on load or scroll-into-view
  - High: trend icon animates in alongside number
- **Variants**: (1) plain numbers, (2) with trend indicators, (3) with mini sparklines per stat
- **Mobile**: Stacked vertical, numbers larger for readability
- **Data**: Payload-driven
- **Why foundation**: Stats validate product; simple = honest

---

---

## TASK C: THEME-FOUNDATION PRIMITIVES (Shared Across All Themes)

### 1. **responsive-table-to-cards-contract**
- **Concept**: Base table component that intelligently switches from `<table>` (desktop) to card layout (mobile) without duplicate markup; uses CSS `:has()` for layout selection
- **Implementation Pattern**:
  ```
  @media (max-width: 48rem) {
    table { display: block; }
    tr { display: grid; grid-template-columns: label 1fr; gap: 1rem; }
    td::before { content: attr(data-label); font-weight: bold; grid-column: 1; }
    td { grid-column: 2; }
  }
  ```
- **Payload**: Table rows as JSON, each row has `{cells: [{label, value}, ...]}`, table renders via Blade loop with `data-label` attributes
- **Use Cases**: Pricing tables, comparison matrices, staff directories, results tables
- **Why theme-foundation**: Every archive-directory theme uses dense tables; shared contract saves 3-4 custom solutions

---

### 2. **count-up-stat-component**
- **Concept**: Reusable stat counter (number animates from 0 to final value on scroll-into-view or load)
- **Implementation**: `<x-stat-counter value="1234" label="Users" animateOnLoad=true />` Blade component; uses Intersection Observer or simple scroll listener + `Intl.NumberFormat`
- **Payload**: `value` (final number), `label` (text), `unit` (%, K, M, etc.), `duration` (animation time in ms), `animateOnLoad` (bool)
- **Customization**: CSS custom properties for font-size (via headingScale), color (via accent or primary color), spacing
- **Why theme-foundation**: Used in all themes' stat displays; reusable JS logic = DRY, testable, performant

---

### 3. **scroll-driven-animation-base**
- **Concept**: Shared CSS/JS patterns for scroll-driven animations (parallax, fade-in-on-scroll, sticky headers, scroll-snap carousels)
- **Implementation**: Base CSS utility classes + small vanilla JS helper (Intersection Observer + `requestAnimationFrame`):
  ```
  .scroll-fade-in { opacity: 0; animation: fadeIn auto linear forwards; animation-timeline: view(); }
  .scroll-parallax { transform: translateY(var(--offset)); }
  .scroll-snap-carousel { display: flex; overflow-x: scroll; scroll-snap-type: x mandatory; }
  ```
- **Why theme-foundation**: 80% of widgets need scroll behavior; shared base = consistency + easier tuning per theme (motionIntensity token)

---

---

## SUMMARY TABLE

| **Theme** | **Widget Family** | **Count** | **Key Differentiator** |
|-----------|-------------------|-----------|------------------------|
| **Wild-Card** | Arcade/Playful | 6 | Shuffle, randomize, confetti, FOMO loops |
| **Reel-Room** | Cinema/Archive | 6 | Motion-preview, jury transparency, timeline curation |
| **Off-Grid** | Brutalist/Field Guide | 6 | Monospace, hyperlink blue, scattered layout, annotation |
| **Gold-Rush** | Awards/Scoreboard | 6 | Heatmaps, voting gauges, counts, transparent criteria |
| **Liquid-Glass** | Glassmorphism | 5 | Backdrop-filter, depth, refraction, layering |
| **Foundation Default** | Pristine/Utility | 4 | Pricing, FAQ, changelog, stats—no flash |
| **Theme-Foundation** | Primitives | 3 | Table→Cards, count-up, scroll-driven anim |

---

## ARCHITECTURE NOTES

### Data Handling
- **Payload-Driven**: All widgets receive structured data from Layout Builder; no DB queries in public Blade.
- **Client-Side Logic**: Filtering, sorting, carousel swipes, accordions via vanilla JS or CSS-only (`:has()`, `scroll-snap`).
- **Real-Time Updates**: Where voting/counts matter (gold-rush, wild-card), websocket or SSE can feed updates; all widgets accept fresh payload via JS state updates.

### CSS & Theming
- **Token System**: Every widget respects `primaryColor`, `accentColor`, `cardStyle`, `cardDensity`, `headingScale`, `motionIntensity`, `mediaTreatment`, `radius`, `overlayTreatment` (liquid-glass only).
- **Dark Mode**: Light + dark token sets; all widgets inherit via CSS custom properties.
- **Responsive Design**: Mobile-first breakpoints; containers queries for card-density responsiveness.

### Motion Tiers
- **Low** (`motionIntensity: subtle`): Color transitions, essential hover states only
- **Medium** (`motionIntensity: balanced`): Slide/fade, scale on load, stagger animations
- **High** (`motionIntensity: dynamic`): Parallax, scroll-driven, 3D transforms, complex easing

### Accessibility
- **prefers-reduced-motion**: All animations disabled; static fallback states identical to animated states.
- **Keyboard Navigation**: Carousels, modals, accordions fully keyboard-accessible.
- **ARIA Labels**: Cards, buttons, live regions properly marked.

---

## NEXT STEPS (Implementation Roadmap)

1. **Phase 1**: Archive-Directory foundation (wild-card 3 widgets, reel-room 3 widgets) + theme-foundation primitives
2. **Phase 2**: Complete archive-directory (6 each) + off-grid + gold-rush sampling
3. **Phase 3**: Liquid-Glass showcase widgets; foundation default pricing/FAQ
4. **Phase 4**: Accessibility audit, dark mode testing, performance optimization (lazy-load media, JS bundle split)
