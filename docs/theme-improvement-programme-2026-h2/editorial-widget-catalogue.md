# Editorial Widget Catalogue: Signature Content Display Widgets
## Four-Theme Publishing Family

**Context:** Design tokens available across all themes: primaryColor, accentColor, neutralColor, surfaceColor, foregroundColor, headingFont, bodyFont, spacing, alignment, cardStyle, navigationStyle, layoutPresentation, motionIntensity, mediaTreatment, radius, headingScale, cardDensity. VariantViewSectionRenderer supports named variants per section. Progressive enhancement via CSS (scroll-driven animations, view transitions, container queries, :has(), subgrid, scroll-snap, text-wrap: balance) preferred over JS; small vanilla JS allowed; must respect prefers-reduced-motion.

---

## INK PRESS (News/Analysis) — 6 Signature Widgets
**Lane:** News publishers, policy journals, analysis desks, regional newspapers, editorial membership sites
**Visual DNA:** Newsprint red accent on white; photographic media; bordered cards; sora/inter pairing; dense layout rhythm
**Print Heritage:** Broadsheet front-page systems, live ticker furniture, editorial columns with bylines, multi-column layouts

### 1. `breaking-news-ribbon`
**Concept:** Sticky horizontal news ticker with live-update pulse, editorial alerts stacked vertically; emulates print masthead liveness.

**Payload:** `{ items: [{ label, headline, timestamp, priority, section?, link? }], displayMode, updateInterval }`

**Layout Mechanics:**
- Sticky to top or inline in feed; fixed height ~60px
- Horizontal scroll-snap (desktop) or vertical stack (mobile)
- Borders on left/top/right per `cardStyle`; thin red left-edge accent line
- Grid: `auto-flow dense` for variable-width update badges
- Responsive: mobile collapses to single-line ticker with scroll

**Motion:**
- `motionIntensity: reduced` → static only, no fade-in
- `motionIntensity: standard` → gentle slide-in for new items from left
- `motionIntensity: high` → pulse indicator on live items, fade-in-up for alerts
- Accessibility: aria-live="polite" for screen readers

**Variants:**
- `compact`: Single-line headline only, timestamp icon
- `expanded`: Headline + 1-line summary, byline, category badge
- `alert-mode`: Bright accent background, larger text, dismissible per item

**Mobile:** Single-column vertical list, scroll horizontally on desktop, full-width on small screens

**Why Ink Press Only:** News rhythm demands live editorial updates; other themes serve long-form or visual-first content with slower publish cadence.

---

### 2. `opinion-grid-with-bylines`
**Concept:** Columnists/opinion writers arranged in an opinionated 3-4 column layout with large byline portraits and date metadata; evokes op-ed page visual hierarchy.

**Payload:** `{ columns: [{ author, title, excerpt, publishedAt, avatar, accent?, articles? }], columnCount, fallbackLayout }`

**Layout Mechanics:**
- CSS Grid: `grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))`
- Each column: portrait image (square/circle toggle) at top, large serif heading for author name, title, short bio, "Latest" or date
- Subgrid for multi-article stacks within column
- Borders/frames per `cardStyle`; accent color horizontal rule under author name
- Sticky column header on scroll (container queries `@container (min-height: 600px)`)

**Motion:**
- `motionIntensity: reduced` → static
- `motionIntensity: standard` → stagger-in on scroll (each column 100ms apart)
- `motionIntensity: high` → parallax scroll on portrait images, hover lift with shadow
- Parallax uses CSS `scroll-behavior: smooth` + CSS anchor positions

**Variants:**
- `editorial-board`: Larger portraits, full biographies, links to archives
- `rotating-voices`: Show 1-2 featured, rest behind modal/accordion
- `timeline-columns`: Date-driven, one column per week/issue

**Mobile:** Single column, stack all authors vertically with full metadata visible

**Why Ink Press Only:** Editorial voice and opinion are central to news outlets; other themes don't foreground individual writer authority.

---

### 3. `live-event-timeline`
**Concept:** Chronological event feed (breaking news, game scores, court decisions) with micro-update cards and scroll-driven progress bar at side.

**Payload:** `{ events: [{ time, headline, category, context?, media?, updates }], liveIndicator, autoScroll }`

**Layout Mechanics:**
- Timeline spine on left (desktop) or top (mobile); events card-stack perpendicular
- Cards: `min-width: 300px`, inline with timestamp/icon; media previews in cards
- Scroll-driven animation: progress bar fills as user scrolls through events
- Accent color for category badges (politics/sports/markets)
- Responsive: spine becomes horizontal rule on mobile, cards stack below

**Motion:**
- `motionIntensity: reduced` → static spine, no animation
- `motionIntensity: standard` → fade-in as cards enter viewport
- `motionIntensity: high` → scroll-driven progress bar, hover card lift, live pulse on top event
- View transition on click (navigate to full story)

**Variants:**
- `sports-scoreboard`: Compact score format, team logos
- `political-tracker`: Policy vote counts, amendment flags
- `market-live`: Ticker-style numbers, color-coded up/down

**Mobile:** Vertical timeline, single card column, full-width

**Why Ink Press Only:** Real-time event tracking is native to news workflows; rarely used by design/culture or long-form journals.

---

### 4. `byline-fact-boxes`
**Concept:** Reporter metadata + key fact boxes inlined mid-article (author photo, beats, related stories, at-a-glance facts); responsive sidebars collapse inline on mobile.

**Payload:** `{ author, beats, photo, facts: [{ label, value }], relatedStories }`

**Layout Mechanics:**
- Desktop: sticky right sidebar (width: 20-25vw, max 300px) or use `@supports (display: grid)` for subgrid integration into parent article
- Facts in 2-column grid within box
- Byline section top, facts middle, related stories bottom
- Mobile: full-width card between paragraphs, not sticky
- Borders; heading font for fact labels, body font for values
- Use `:has(> .fact-box)` to adjust article width on large screens

**Motion:**
- `motionIntensity: reduced` → static
- `motionIntensity: standard` → fade-in on scroll, sticky on desktop only
- `motionIntensity: high` → parallax on scroll (fact values slightly faster), smooth stick/unstick
- `@media (prefers-reduced-motion)` removes parallax, keeps fade-in

**Variants:**
- `condensed`: Photo + author name only, facts pop on hover
- `expanded`: Full bio, social links, newsletter signup
- `interview-sidebar`: Q&A format instead of facts

**Mobile:** Full-width between content blocks, no sticky behavior

**Why Ink Press Only:** Contextualizes reporter expertise and live fact updates; news-specific pattern.

---

### 5. `related-analysis-carousel`
**Concept:** Horizontal scroll carousel of related analysis pieces (cross-topic/regional stories), left-aligned, with topic tags and reading time.

**Payload:** `{ articles: [{ title, excerpt, topic, author, readingTime, image, link }], orientation }`

**Layout Mechanics:**
- CSS scroll-snap-x: `scroll-snap-type: x mandatory`, `scroll-snap-align: start`
- Cards: `flex-basis: 280px`, gap per spacing token
- Tags/metadata below title in smaller type
- Image aspect: 16:9 or 4:3 per `mediaTreatment`
- Desktop: show 3-4 cards, mobile: 1.5 cards visible + scroll indicator
- Borders per `cardStyle`

**Motion:**
- `motionIntensity: reduced` → static scroll, no momentum animation
- `motionIntensity: standard` → smooth scroll, fade prev/next cards at edges
- `motionIntensity: high` → momentum scroll snap, hover card lift, scroll velocity parallax on images
- View transition on click

**Variants:**
- `analysis-deep-dive`: Larger cards, longer excerpts, byline emphasis
- `regional-wire`: Compact, topic badges only, rapid fire
- `explainer-series`: 3-part indicator, progress badges

**Mobile:** Full-width horizontal scroll, 1 card visible

**Why Ink Press Only:** Cross-linking and analysis depth are central to news narrative; other families prioritize visual discovery or essay flow.

---

### 6. `reading-progress-with-markers`
**Concept:** Article progress bar (top or side) with chapter markers, estimated reading time remaining, and estimated time live (if breaking).

**Payload:** `{ sections: [{ title, offset }], totalTime, publishedAt, updatedAt }`

**Layout Mechanics:**
- Horizontal bar (top) or vertical spine (side); position: sticky
- Markers at section boundaries, clickable (scroll-to-section)
- Text overlay: "X minutes remaining" or "Published YY minutes ago"
- Uses scroll-driven animation to fill bar
- Accent color for markers; neutral for bar background
- Mobile: compact horizontal bar top only

**Motion:**
- `motionIntensity: reduced` → static bar fill per scroll position
- `motionIntensity: standard` → smooth bar fill, labels fade in/out
- `motionIntensity: high` → animated counter (minutes remaining), hover marker highlight, smooth section jump
- Disable on `prefers-reduced-motion`

**Variants:**
- `chapter-navigator`: Large clickable section names, expand on hover
- `live-ticker-mode`: "Updated X mins ago" badge pulses, replaces reading time
- `print-edition`: Show print edition page numbers where applicable

**Mobile:** Compact top bar, no side spine

**Why Ink Press Only:** Reading continuity and time-context critical for news consumption; helps readers gauge depth before commitment.

---

## ART PAPER (Design/Architecture/Interiors) — 6 Signature Widgets
**Lane:** Design magazines, architecture publishers, interiors studios, art and culture journals, fashion editorial
**Visual DNA:** Oxblood accent on warm paper; photographic media; airy card density; sora/inter pairing; gallery-driven layouts
**Print Heritage:** Art catalogue spreads, product photography systems, swatches/color grids, designer studio credits pages

### 1. `image-grid-with-captions`
**Concept:** Responsive image grid (2-4 columns) with lazy-loaded images, floated captions, and subtle hover reveal of metadata (photographer, location, technique).

**Payload:** `{ items: [{ src, caption, photographer, location, technique?, lqip? }], columnCount, aspect, captionPosition }`

**Layout Mechanics:**
- CSS Masonry (or subgrid fallback): `display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))`
- Images fill container, maintain aspect per `mediaTreatment`
- Captions: absolute positioned bottom-left or bottom-right, semi-transparent background over image
- Metadata (photographer/location) hidden by default, appear on hover (opacity 0 → 1)
- Uses `object-fit: cover` + `object-position` for intelligent crop
- Mobile: single column, captions always visible

**Motion:**
- `motionIntensity: reduced` → static, no hover effects
- `motionIntensity: standard` → fade-in captions on hover, smooth image load (fade-in)
- `motionIntensity: high` → parallax on image load, hover metadata slides up with backdrop blur
- Lazy-load with intersection observer

**Variants:**
- `product-grid`: Square images, consistent aspect, 3-4 columns always
- `editorial-gallery`: Variable aspect (portrait/landscape mixed), large captions
- `lookbook`: Aspect: 3:4, overlaid text for fashion/styling info

**Mobile:** Always single column, full-width, captions always visible

**Why Art Paper Only:** Photography primacy and cataloguing/curation is central to design publishing; other themes either need denser text or focus on long-form reading.

---

### 2. `designer-profile-card-cluster`
**Concept:** 2-3 designer/architect profiles shown side-by-side (or stacked on mobile) with studio photo, short bio, featured projects list, and contact link.

**Payload:** `{ designers: [{ name, studio, bio, photo, projects: [{ title, year }], contact }] }`

**Layout Mechanics:**
- Horizontal cluster desktop (3 cards, 1fr each), vertical stack mobile (1 card full-width)
- Card: border per `cardStyle`, heading + studio, paragraph bio, sub-grid for projects list
- Projects: 2-column list (title | year) with accent-color icon
- Photo: 1:1 or 4:5, corner radius per `radius` token
- Responsive: at `min-width: 768px`, switch to 3-up grid

**Motion:**
- `motionIntensity: reduced` → static
- `motionIntensity: standard` → stagger-in on scroll (each card 100ms apart)
- `motionIntensity: high` → hover card lift, parallax on photos, smooth expand on click for full bio
- Variant interaction: click card → modal/sidebar expands full bio + all projects

**Variants:**
- `studio-spotlight`: Large studio photo, full project list, awards/exhibitions section
- `contributor-voices`: Small photos, emphasis on bio quote, minimal projects
- `editorial-board`: Grid format, larger headings, formal credentials

**Mobile:** Full-width card stack, photo on top, 100% readable

**Why Art Paper Only:** Designer/architect attribution and portfolio links are core to design publishing; aligns with product-credits and lead-story sections.

---

### 3. `product-comparison-table`
**Concept:** Side-by-side product/design system comparison with image swatches, specifications, and interactive toggling of details (material, price, availability).

**Payload:** `{ products: [{ name, image, specs: { material, finish, price, availability }, description }], toggleCategories }`

**Layout Mechanics:**
- Desktop: 2-4 columns side-by-side, mobile: horizontal scroll-snap
- Column: large product image top, name heading, toggleable spec groups below (material, price, etc.)
- Specs: key-value pairs, 2-column grid
- Accent color for highlight cells or price/availability rows
- Borders between columns per `cardStyle`
- Uses container queries: `@container (min-width: 600px)` to expand spec details

**Motion:**
- `motionIntensity: reduced` → static tables, no toggles (all specs visible)
- `motionIntensity: standard` → smooth height transition when toggling spec group
- `motionIntensity: high` → hover image zoom, spec group slide-down/up animation
- Click spec label to toggle related items across all products (sync highlight)

**Variants:**
- `furniture-collection`: Large images, weight/dimensions emphasis, finish swatches inline
- `material-explorer`: Focus on material samples, toggle show/hide certifications
- `budget-comparison`: Price highlight, availability badges, financing info

**Mobile:** Single column, swipe between products, all specs visible

**Why Art Paper Only:** Product-centric comparison and design specification comparison is core to interiors/furniture/design publishing; aligns with product-credits section.

---

### 4. `trend-forecast-infographic`
**Concept:** Visual trend report using color swatches, icons, minimal text; upcoming design trends displayed as color palette + keyword grid with 1-line descriptions.

**Payload:** `{ trends: [{ name, color, season, keywords: [{ term, description }] }], layout }`

**Layout Mechanics:**
- Top row: 6-8 color swatches arranged horizontally (desktop) or 2 rows of 4 (mobile), each swatch clickable
- Click swatch → reveals trend name, 3-4 keywords + descriptions below
- Keywords in 2-column grid beneath swatch
- Minimal borders; primary visual is color + icon (svgs)
- Responsive: swatch size scales, text size adjusts per headingScale

**Motion:**
- `motionIntensity: reduced` → static, all details visible or in tabbed layout
- `motionIntensity: standard` → color swatch fade-in stagger, keyword descriptions slide-up
- `motionIntensity: high` → hover swatch enlarges, keywords animate with stagger, background shifts to trend color (low opacity)
- Scroll-driven: as user scrolls, active swatch indicator moves
- Prefers-reduced-motion: disable parallax/background shift, keep slide-up

**Variants:**
- `seasonal-palette`: Organize by season (Spring | Summer | Fall | Winter)
- `material-focus`: Swatch label includes material type (ceramic, wool, etc.)
- `interactive-survey`: Vote on trend, show poll results overlay

**Mobile:** Single-row swatches (scroll horizontally), expanded details below

**Why Art Paper Only:** Trend forecasting and color curation central to design publishing; visual-first, minimal text approach unique to Art Paper's gallery DNA.

---

### 5. `editorial-pull-quotes-with-author-photo`
**Concept:** Large blockquote with author photo, title, and org inlined; floats left/right in article (or stacked on mobile) with accent color border.

**Payload:** `{ quote, author, title, organization, photo, accentColor }`

**Layout Mechanics:**
- Desktop: float left or right, `width: 35-40%`, `margin: 2em 2em 1em 0`
- Border: thick left or top border per accent color
- Photo: 80-100px square, corner radius per `radius` token, positioned top-left of quote text
- Name heading (medium size), title + org in smaller gray text below photo
- Quote text in larger serif or heading font, `text-wrap: balance` for better breaks
- Responsive: on mobile, convert to full-width block (no float), maintain layout, image above quote

**Motion:**
- `motionIntensity: reduced` → static
- `motionIntensity: standard` → fade-in on scroll, border animates from left edge
- `motionIntensity: high` → hover parallax on photo (slightly shifts), quote text color shifts to accent (low opacity)
- Use intersection observer for scroll-trigger

**Variants:**
- `interview-response`: format as Q: ... A: ... with photo under interviewer question
- `expert-opinion`: smaller layout, badge "Expert Opinion" above quote
- `testimonial`: emphasis on organization logo instead of photo

**Mobile:** Full-width card, image top-center, centered quote text

**Why Art Paper Only:** Design interviews and designer voices prominent; unique to visual-first publications that need design-conscious attribution.

---

### 6. `process-documentation-timeline`
**Concept:** Vertical or horizontal timeline showing design process steps (research → concept → prototype → final), with images for each phase and toggle-able "behind-the-scenes" details.

**Payload:** `{ phases: [{ title, image, description, insights: [{ label, detail }] }] }`

**Layout Mechanics:**
- Timeline spine (left-side vertical on desktop, top horizontal on mobile)
- Phase cards positioned perpendicular to spine with connector line
- Image top of card (aspect per `mediaTreatment`), title, 1-line description below
- "Insights" section collapsed by default, expand on click to show grid of key learnings
- Use `@container` queries to adapt spacing at different viewport widths
- Mobile: horizontal spine, vertical card stack

**Motion:**
- `motionIntensity: reduced` → static, all insights visible or in accordion
- `motionIntensity: standard` → stagger fade-in as each phase enters viewport, smooth expand on click
- `motionIntensity: high` → scroll-driven progress marker on spine, hover card lift, parallax on images, insight details slide-up
- View transition between phases if navigating (mobile)

**Variants:**
- `client-case-study`: Add budget/timeline icons, client logo in header
- `materials-source`: Swatch/material sample images instead of process photos
- `architectural-process`: Detailed drawings (sketches → renderings → final), measurement annotations

**Mobile:** Horizontal timeline at top, cards stack below, full-width

**Why Art Paper Only:** Design process storytelling central to design magazines; showcases methodology and iteration unique to architectural/design publishing.

---

## QUIET TYPE (Long-Form Essays/Literary) — 6 Signature Widgets
**Lane:** Publications & journals, writers & essayists, design studios, editorial brands, print-grade serif voice
**Visual DNA:** Serif fraunces/newsreader pairing; dramatic heading scale; framed media; airy spacing; zero-radius flat cards; minimal navigation
**Print Heritage:** Literary journal spreads, essay anthologies, book jacket design, typographic essays, marginalia systems

### 1. `essay-with-dropcap-and-marginalia`
**Concept:** Article body text with large dropcap on first paragraph; optional margin notes (quotes, references, illustrations) floated beside text; framed image inserts.

**Payload:** `{ content: { heading, body, dropcapLetter }, marginNotes: [{ position, content, type }], images: [{ src, caption, credit }] }`

**Layout Mechanics:**
- Body: max-width 600-700px (readability), centered on large screens
- Dropcap: 3-4 line height, serif font, slightly darker color; uses CSS `float: left`, `margin-right`
- Margin notes: positioned `position: absolute` or use CSS `:not(:first-letter)` + flexbox layout, opacity 70%, smaller font size
- Framed images: border per `radius: 0` (sharp corners, per Quiet Type DNA), optional shadow for depth
- `text-wrap: balance` on all headings for beautiful line breaks
- Mobile: margin notes move below text or hide (show toggle button), dropcap smaller

**Motion:**
- `motionIntensity: reduced` → static only, all margin notes visible
- `motionIntensity: standard` → fade-in body text on scroll, margin notes appear as text scrolls past
- `motionIntensity: high` → stagger body line-by-line fade-in, margin notes appear with subtle slide, images fade in with small parallax
- Parallax uses CSS anchor positions (light, not intrusive)
- Disable all on `prefers-reduced-motion`

**Variants:**
- `annotated-essay`: Heavy margin notes, toggle show/hide annotations sidebar
- `literary-excerpt`: Emphasis on dropcap, minimal margin notes, pull-quotes styled differently
- `translator-notes`: Bilingual layout, margin notes are translation/cultural context

**Mobile:** Single column, no floating, margin notes as footnotes below, dropcap visible but smaller

**Why Quiet Type Only:** Marginalia and typographic sophistication central to literary/essay publishing; other themes lack print-heritage serif-first typography.

---

### 2. `author-bio-with-bibliography`
**Concept:** Author profile section at top/bottom of essay with portrait, bio, social links, and collapsible reading list (books, articles, prior essays by author).

**Payload:** `{ author: { name, bio, photo, social }, bibliography: [{ title, url, year, type }] }`

**Layout Mechanics:**
- Desktop: 2-column (photo left, bio + bibliography right) or single column with photo top
- Photo: 1:1 or 4:5 aspect, flat card (zero radius per Quiet Type), no shadow
- Bio: heading + 2-3 paragraph, body font in default size
- Bibliography: expandable section (click "Works Cited" or "Further Reading" heading), nested list or 2-column grid
- List items: title, publication, year (small caps if available), link underline on hover
- Mobile: full-width single column, photo centered

**Motion:**
- `motionIntensity: reduced` → static, bibliography always visible or fully collapsed
- `motionIntensity: standard` → fade-in on scroll, smooth expand/collapse bibliography
- `motionIntensity: high` → hover book/article title shows publish year as overlay, expand bibliography with smooth height transition
- Author name appears with view transition if clicking from article header

**Variants:**
- `translator-translator`: Add translator photo/info alongside author, joint bio
- `guest-editor-profile`: Larger bio, "This issue edited by" header, link to editor's other selections
- `collaborative-essay`: Multiple author photos in grid, each with name + institution

**Mobile:** Single column, photo top, full-width bio and bibliography

**Why Quiet Type Only:** Literary/essayist authority and bibliography curation central to long-form journals; aligns with essay-index and author-profiles sections.

---

### 3. `issue-contents-table-of-contents`
**Concept:** Table of contents displaying essays in issue with optional section groupings (essays, interviews, reviews), linked to page numbers or internal navigation.

**Payload:** `{ sections: [{ title, contents: [{ title, author, page }] }], currentIssue }`

**Layout Mechanics:**
- Hierarchy: Issue number/date header, section headings (optional), flat list of essay titles with authors and page numbers
- Typography: title in heading font, author in gray small caps, page number right-aligned in body font
- Borders: thin horizontal rule between entries, section grouping with left accent line
- Desktop: 2-column grid for long TOCs, mobile: single column
- All items clickable (navigate or scroll to section)
- Use `display: grid` with `grid-template-columns: 1fr auto` for title + page alignment

**Motion:**
- `motionIntensity: reduced` → static
- `motionIntensity: standard` → fade-in entries on scroll, underline animates on hover title
- `motionIntensity: high` → hover entry background shifts to accent color (10% opacity), smooth scroll-to on click
- Active entry (current position in issue) highlighted with left accent line fill
- Scroll-driven: update active highlight as user reads

**Variants:**
- `archive-index`: Multiple issues in columns (Spring | Summer | Fall | Winter), year headers
- `guest-issue`: Emphasis on guest editor name, "Guest-curated" badge, editor bio link
- `themed-issue`: Section titles are thematic (eg "On Sustainability"), larger section headings

**Mobile:** Single column, no page numbers (link to section instead), search box to filter titles

**Why Quiet Type Only:** Issue-centric publishing and essayist-driven curation central to literary journals; unique to long-form editorial brands.

---

### 4. `pull-quote-system-with-attribution`
**Concept:** Multiple pull-quote styles for essays: sidebar floats, full-width blockquotes, and margin-positioned quotes with source citations and optional icons.

**Payload:** `{ quote, source, attribution, context?, icon? }`

**Layout Mechanics:**
- Three layouts:
  1. Sidebar (desktop float, mobile full-width): 35% width, left border accent, photo of source top-left
  2. Full-width blockquote: max-width 600px, centered, larger type, attribution centered below
  3. Margin quote: positioned in margin (desktop only), smaller font, no background, semi-transparent
- All use serif font for quote text, body font for attribution
- Icon (optional): small svg next to attribution (emphasis, lightbulb, etc.)
- Borders: accent color bar (left or top)
- Responsive: sidebar converts to full-width card

**Motion:**
- `motionIntensity: reduced` → static
- `motionIntensity: standard` → fade-in on scroll, border animates in
- `motionIntensity: high` → parallax on margin quotes, hover source photo enlarges slightly, attribution color shifts to accent
- Disable on `prefers-reduced-motion`

**Variants:**
- `interview-response`: format as Q: "..." vs A: "..." with speaker name/title
- `margin-annotation`: smaller, semi-transparent, appears as text scrolls past
- `editorial-callout`: background color (soft accent), icon emphasis, slightly larger for key insights

**Mobile:** Full-width card, image centered top, quote centered text

**Why Quiet Type Only:** Literary sophistication in pull-quote design and margin-note system unique to essay publishing; emphasizes text quality over visual spectacle.

---

### 5. `reading-list-curated-sidebar`
**Concept:** Sticky sidebar (desktop) or collapsible section (mobile) showing curated reading list, recent essays by author, or recommended issues; updated per article context.

**Payload:** `{ listTitle, items: [{ title, author, link, type, year }] }`

**Layout Mechanics:**
- Sidebar: `position: sticky`, width 250-280px, right of main content
- List: simple links with metadata (author, year in small caps)
- Section heading with icon (book, leaf, etc.)
- Dividers between items or grouped by type (books | essays | archives)
- Zero-radius flat card per Quiet Type; subtle border
- Mobile: expand/collapse drawer or accordion, full-width

**Motion:**
- `motionIntensity: reduced` → static
- `motionIntensity: standard` → fade-in on scroll, hover link underlines
- `motionIntensity: high` → hover item background shifts, link icon animates on hover (arrow slide-right)
- View transition on click (navigate to reading item)

**Variants:**
- `issue-archive`: grouped by season/year, clickable issue cards
- `author-works`: all essays by author, organized by date descending
- `thematic-collection`: grouped by theme (subject tags), tagged reading list

**Mobile:** Full-width expandable drawer, single column list

**Why Quiet Type Only:** Curation and essayist archive exploration central to literary journals; supports long reading sessions and discovery.

---

### 6. `serialized-chapters-navigator`
**Concept:** Multi-part essay or serialized story shown with chapter navigator (current chapter highlighted, prev/next navigation, progress indicator).

**Payload:** `{ chapters: [{ number, title, date, preview }], currentChapter }`

**Layout Mechanics:**
- Chapter list: vertical (left sidebar on desktop) or horizontal tabs above content
- Current chapter: bold heading, background accent (low opacity)
- Prev/Next buttons: prominent, link to adjacent chapters
- Progress indicator: bar showing chapter position (e.g., "Chapter 3 of 5")
- Responsive: sidebar becomes tab list on mobile, full-width
- Desktop: sticky chapter list, updates as user scrolls through chapter content

**Motion:**
- `motionIntensity: reduced` → static, no tab animations
- `motionIntensity: standard` → fade-in tabs, smooth scroll-to chapter on click, progress bar fills smoothly
- `motionIntensity: high` → view transition between chapters (slide-left/right), hover chapter card lifts slightly, progress bar animates fill
- Keyboard navigation: left/right arrows jump chapters (a11y)

**Variants:**
- `serial-novel`: Chapters numbered, each with publish date, "Next episode in X days" timer
- `long-form-essay-parts`: Part 1: Introduction | Part 2: Historical Context, etc., chapter explorer at side
- `interactive-story`: Branching chapters, reader choices affect next chapter options

**Mobile:** Tab navigation at top, full-width, smooth swipe to next chapter (keyboard arrows)

**Why Quiet Type Only:** Serialized long-form reading and chapter-based navigation central to essayist/literary publishing; supports deeper editorial commitment.

---

## FAR FIELD (Travel/Culture/Global Affairs with Audio) — 6 Signature Widgets
**Lane:** Global magazines, culture publishers, travel editorial teams, city guide brands, premium media shops
**Visual DNA:** Brass-gold accent on parchment; photographic media; airy card density; sora/inter pairing; audio-visual integration
**Print Heritage:** Geographic magazines (photo essays, travel narratives), landscape photography monographs, cultural dispatches, radio/broadcast transcripts

### 1. `audio-embedded-with-transcript`
**Concept:** Audio player integrated with side-by-side (desktop) or tabbed (mobile) transcript; highlights current speaker/section as audio plays; key quotes featured.

**Payload:** `{ audioUrl, transcript: [{ speaker, startTime, endTime, text }], keyQuotes: [], duration }`

**Layout Mechanics:**
- Desktop: 2-column (player + controls left ~40%, transcript right ~60%)
- Player: custom controls (play/pause, progress bar, time remaining), accent color for progress
- Transcript: scrollable, speaker names in color, current speaker highlighted with background
- Sync: as audio plays, transcript scrolls to current speaker
- Key quotes in sidebar or pulled out in larger text
- Mobile: tabs (player | transcript | highlights), full-width, single column

**Motion:**
- `motionIntensity: reduced` → static player, transcript doesn't auto-scroll
- `motionIntensity: standard` → smooth progress bar animation, transcript auto-scrolls to current speaker, fade-in highlights
- `motionIntensity: high` → speaker names fade to accent color on play, progress bar glows, hover speaker name jumps to time
- Keyboard: spacebar play/pause, left/right arrows skip forward/back 10s

**Variants:**
- `multilingual-transcript`: language toggle (English | Spanish | French), tabs per language
- `interview-breakdown`: speaker photos above names, timestamp icons link to audio position
- `podcast-episode`: show/host info header, download button, shownotes as sidebar

**Mobile:** Stacked player on top, transcript below or in drawer, swipe between transcript/highlights

**Why Far Field Only:** Audio integration (radio-audio, lead-dispatch podcast tie-ins) and transcript/dialogue-based storytelling central to global affairs and travel broadcasting.

---

### 2. `destination-grid-with-guides`
**Concept:** Geographic destination cards (cities, regions) in interactive grid with image, 1-line description, and expandable "guide" link; can highlight featured/curated destinations.

**Payload:** `{ destinations: [{ name, region, image, summary, guides: [{ title, link }], featured }] }`

**Layout Mechanics:**
- Responsive grid: `grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))` (4 cols desktop, 2 mobile, 1 small)
- Each card: image (aspect 4:3) top, destination name, region (small caps), 1-line summary
- Hover: reveals "X guides" link or expandable list of associated guides
- Featured cards: slightly larger, accent border or badge
- Responsive: images scale, text stays readable
- Mobile: single column, full-width images

**Motion:**
- `motionIntensity: reduced` → static, guides always visible or in accordion
- `motionIntensity: standard` → fade-in cards on scroll (stagger), guides fade-in on hover
- `motionIntensity: high` → hover card lift with shadow, image parallax, guides slide-down, guide links animate arrow on hover
- Scroll-driven: cards appear as user scrolls

**Variants:**
- `region-explorer`: group destinations by continent, section headers with icon
- `city-guide-index`: emphasis on guide links, show count "5 guides", open guides in modal
- `travel-map`: hover pin on map appears, shows destination info

**Mobile:** Full-width cards, stacked vertically, guides always visible

**Why Far Field Only:** Geographic organization and city-guide cross-linking central to travel/culture publishing; aligns with city-guides and travel-culture sections.

---

### 3. `cultural-dispatch-timeline`
**Concept:** Vertical timeline of cultural events (performances, exhibitions, festivals) with date/time, location, media preview, and ticket/info links; can be filtered by type.

**Payload:** `{ events: [{ date, time, title, location, category, image, description, link }], filterOptions }`

**Layout Mechanics:**
- Timeline spine (left edge, gold accent color)
- Event cards perpendicular, with connector line to spine
- Card: date/time header, title (large), location (small), image (16:9), 2-line description, link
- Category badge (festival | performance | exhibition) with icon
- Filter buttons above timeline (All | Performances | Festivals, etc.)
- Responsive: on mobile, spine becomes horizontal rule, cards stack below
- Use `@container` for layout adjustments at smaller sizes

**Motion:**
- `motionIntensity: reduced` → static, cards don't animate in
- `motionIntensity: standard` → stagger fade-in cards on scroll, timeline fill-in animation
- `motionIntensity: high` → hover event card lifts, image parallax, scroll-driven timeline progress, smooth filter transition
- Category badge animates on hover (rotate/pulse)

**Variants:**
- `festival-countdown`: upcoming events emphasized, "Days until" badges, calendar view option
- `performance-archive`: past events searchable by date range, performer names emphasized
- `exhibition-tracker`: image gallery instead of single image, opening/closing dates prominent

**Mobile:** Horizontal timeline bar at top, event cards stack below, filter drawer

**Why Far Field Only:** Event-driven cultural calendar and performance/exhibition tracking central to culture publishing; travel narrative requires real-world context markers.

---

### 4. `photo-essay-with-lazy-captions`
**Concept:** Full-width or near-full-width photo essay with lazy-loaded images; captions appear on scroll or on hover; image count/position indicator (1/12, 2/12, etc.).

**Payload:** `{ images: [{ src, caption, lqip, photographer }], title, essayMeta }`

**Layout Mechanics:**
- Layout: single-column, max-width 1000px, full-width images (one per "page")
- Image: fills viewport height (or 60-70vh to allow caption peek)
- Caption: absolutely positioned bottom, semi-transparent dark background, white text, fade-in on scroll past image
- Counter: top-right "3/12" in small type, updates as user scrolls
- Lazy load with LQIP (low-quality image placeholder) for fast paint
- Mobile: full viewport images, landscape aspect-aware, captions below image (not overlay)

**Motion:**
- `motionIntensity: reduced` → static images, captions visible, no fade effects
- `motionIntensity: standard` → fade-in captions on scroll, smooth LQIP-to-full transition
- `motionIntensity: high` → parallax on images (slower scroll than text), caption slides up from bottom, counter animates on change
- View transition between images if clicking prev/next

**Variants:**
- `photo-journey`: map pins on side showing location of each photo, click pin scrolls to photo
- `documentary-series`: audio narration synced to images, play/pause controls
- `fashion-lookbook`: outfit details in expandable cards beside/below image, swatches linked

**Mobile:** Full viewport images, captions below, counter top-right, swipe between images

**Why Far Field Only:** Photography-driven narrative and travel/place-based storytelling central to global affairs and culture; visual primacy aligned with dispatch sections.

---

### 5. `reading-list-with-cultural-context`
**Concept:** Curated reading list (books, articles, essays) related to essay or travel destination, with category grouping, year/author info, and brief context quote from the source.

**Payload:** `{ title, resources: [{ title, author, year, type, link, contextQuote }] }`

**Layout Mechanics:**
- Grouped by type (books | articles | essays) with collapsible sections
- Each item: title (link), author (small caps), year, type badge (book icon, etc.), context quote (italic, small font)
- Responsive: 2-column grid (desktop) or single column (mobile)
- Accent color for section headers, quote marks visible around context quote
- Cards: flat style (zero radius), subtle borders

**Motion:**
- `motionIntensity: reduced` → static, all sections expanded or fully collapsed
- `motionIntensity: standard` → smooth expand/collapse sections, fade-in items
- `motionIntensity: high` → hover item lifts slightly, context quote color shifts to accent on hover, expand/collapse smooth height transition

**Variants:**
- `destination-bibliography`: grouped by place/region, "Essential Reading for [City]"
- `themed-collection`: reading list for essay theme, grouped by theme tags
- `editor-picks`: curated by editor, "Editor's Pick" badge, short editor note instead of context quote

**Mobile:** Single column, all sections expanded or drawer-based

**Why Far Field Only:** Cultural and geographical context curation central to travel/culture publishing; supports reader's deeper engagement with destinations.

---

### 6. `columnists-with-latest-essay-preview`
**Concept:** Columnist profile section showing regular contributors with photo, brief bio, and "Latest Essay" preview card linked; can toggle between columnists.

**Payload:** `{ columnists: [{ name, bio, photo, latestEssay: { title, date, excerpt, link } }] }`

**Layout Mechanics:**
- Horizontal layout (desktop): columnist photos/names left side, latest essay preview card larger on right
- Click/hover columnist name → switches preview to their latest essay
- Responsive: mobile stacks columnists vertically with essay preview below each
- Accent color for columnist name highlight, border on preview card
- Photo: square, flat card style; bio: 2-3 lines body font

**Motion:**
- `motionIntensity: reduced` → static, preview visible
- `motionIntensity: standard` → fade-in preview on click columnist, smooth transition
- `motionIntensity: high` → hover columnist name underlines in accent color, preview card fades in with slide-up, hover essay title color shift
- View transition between columnists

**Variants:**
- `rotating-voices`: cycle through columnists automatically (no clicks), preview auto-updates every 5s
- `archive-by-columnist`: click columnist → expand all their essays in timeline below
- `guest-contributor-grid`: show upcoming guest contributors with "coming soon" badge

**Mobile:** Full-width columnists list above essays, tap to expand essay preview

**Why Far Field Only:** Regular travel and culture columnist structure and expertise-based editorial voice unique to global affairs/culture magazines; aligns with columnists section.

---

---

## SHARED EDITORIAL PRIMITIVES (Foundation)
### Three components for all four editorial themes (to live in `theme-foundation`):

### 1. `byline-with-metadata` Component
**Purpose:** Standardized author/contributor attribution with photo, title, publication, and optional social links. Used by all four themes.

**Payload:** `{ name, title, organization, photo?, social: { twitter?, website? } }`

**Features:**
- Responsive: 1:1 photo left (desktop) or centered top (mobile)
- Name heading, title + org in small caps
- Social links (icons, optional)
- Flat style, no shadow, border toggle per `cardStyle`
- Motion: standard fade-in on scroll, high motion adds hover lift

**Why Foundation:**
- Ink Press uses in byline-fact-boxes, opinion-grid
- Art Paper uses in designer-profile-card-cluster
- Quiet Type uses in author-bio-with-bibliography
- Far Field uses in columnists-with-latest-essay-preview
- Shared metadata structure, unique context per theme

---

### 2. `related-items-carousel` Component
**Purpose:** Horizontal scroll carousel of related articles/items with title, excerpt, image, metadata. Used by all four themes for cross-linking.

**Payload:** `{ items: [{ title, excerpt, image, link, date?, category? }], orientation }`

**Features:**
- Responsive scroll-snap grid
- Cards: image top (aspect ratio per `mediaTreatment`), title, excerpt, metadata
- Accent color for category badges
- Mobile: full-width horizontal scroll
- Motion: standard fade-in cards, high motion adds parallax on images, hover card lift

**Why Foundation:**
- Ink Press: related-analysis-carousel (news analysis linking)
- Art Paper: trend-related items, designer-related works
- Quiet Type: related essays, author bibliography items
- Far Field: destination-related dispatches, cultural event connections
- Identical scroll behavior and responsive pattern; different context and metadata per theme

---

### 3. `timestamp-metadata-block` Component
**Purpose:** Flexible metadata display (author, date, read time, category, publication info). Used by all four themes for article headers and contextual info.

**Payload:** `{ author?, publishedAt?, updatedAt?, readingTime?, category?, tags? }`

**Features:**
- Single-line or multi-line format (toggle per variant)
- Small caps for labels, accent color for highlights
- Responsive: stack on mobile, inline on desktop
- Motion: fade-in on scroll, high motion color shift on hover
- Supports "Updated X minutes ago" for live content (Ink Press)

**Why Foundation:**
- Ink Press: live updates, reading time, section metadata
- Art Paper: publication/date, designer credits
- Quiet Type: publication, date, author, issue info
- Far Field: dispatch date, location, audio duration
- Shared metadata pattern; different emphasis per editorial lane

---

---

## IMPLEMENTATION CHECKLIST

### Per-Theme Widget Rollout:
- [ ] Create section blade templates in `packages/theme-{ink-press,art-paper,quiet-type,far-field}/resources/views/sections/`
- [ ] Register sections in theme config (capell.json) with variants
- [ ] Add design token overrides where needed (e.g., border styles, motion intensity defaults)
- [ ] Test VariantViewSectionRenderer with 2-3 variants per widget
- [ ] Ensure payload structure matches Layout Builder contract (no DB queries, no authoring metadata)
- [ ] Add CSS for progressive enhancement (scroll-driven, view transitions, container queries, :has())
- [ ] Vanilla JS (if needed) in `packages/theme-{name}/resources/js/`, respects prefers-reduced-motion
- [ ] Responsive design: test mobile/tablet/desktop
- [ ] Dark mode: verify CSS variables work in light + dark color schemes
- [ ] Accessibility: ARIA labels, keyboard navigation, screen reader friendly captions
- [ ] Lazy loading for images: LQIP support, intersection observer
- [ ] Performance: CSS only where possible, defer JS until needed

### Shared Foundation Primitives:
- [ ] Create 3 components in `packages/theme-foundation/resources/views/components/`
- [ ] Each component accepts design-token slots (color, font, spacing, motion)
- [ ] Document payload contract in README
- [ ] Provide variant examples for each
- [ ] Test light + dark mode rendering
- [ ] Update theme-foundation README with component usage guide

---

## DESIGN PHILOSOPHY SUMMARY

**Ink Press (News):** Dense, live-update rhythm; byline authority; real-time context. Signatures: breaking-news-ribbon (live ticker), live-event-timeline (chronological events), reading-progress-with-markers (engagement depth).

**Art Paper (Design/Interiors):** Visual primacy; curator voice; product/designer attribution. Signatures: image-grid-with-captions (photography curation), designer-profile-card-cluster (creative authority), product-comparison-table (specification excellence).

**Quiet Type (Essays/Literary):** Typographic sophistication; margin systems; author curation. Signatures: essay-with-dropcap-and-marginalia (print inheritance), serialized-chapters-navigator (long-form reading), reading-list-curated-sidebar (essayist discovery).

**Far Field (Travel/Culture/Global):** Geographic + audio narrative; cultural events; place-based discovery. Signatures: audio-embedded-with-transcript (broadcast integration), destination-grid-with-guides (place organization), photo-essay-with-lazy-captions (travel narrative).

**Each theme's widgets resist convergence:** No two themes share identical widget behavior or visual treatment. Editorial lane (news vs. design vs. essays vs. travel) determines signature widgets and interaction patterns.
