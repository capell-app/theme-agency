# Screenshot Audit — 2026-07-05

Companion to Wave 0.5 in `theme-improvement-programme-2026-h2.md`. Method: viewed all 19 committed homepage captures (`packages/theme-*/docs/screenshots/*-homepage.png`, the desktop/light variant of each) and cross-referenced them against `docs/themes.json` `notes` and the Part 2 catalogue. This is a visual/content review — it does not re-litigate the token/overlap findings from Wave 0.3, which already match what's on screen. It surfaces what a code-level review doesn't catch: repeated source imagery, leftover placeholder copy, and page-rhythm sameness that survives even where tokens differ.

## 1. Shared stock photography pool (fleet-wide, the headline finding)

One photoshoot — a glass-walled office corridor with black steel mullions, a wood floor, hanging pendant lights, and (in wider crops) a wood conference table — supplies the hero image, and most grid-thumbnail imagery, across the large majority of the catalogue. This is presumably a shared `ThemeDemoMedia` fixture pool, not a per-theme choice.

| Usage                                     | Themes                                                                                                                                      | Count |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- | ----- |
| Exact same photo/crop as hero             | liquid-glass, wild-card, reel-room, off-grid, gold-rush, open-studio, field-guide, launch-pad, first-light, one-take, front-row, soft-focus | 12    |
| Same photoshoot, different crop/treatment | night-shift (moodier dark crop), quiet-type (conference-table crop), art-paper (oxblood duotone crop)                                       | 3     |
| No large stock photo in the hero          | ink-press (news-ticker box instead), far-field (abstract letter-plate "A" instead), foundation (no photo — UI-chrome demo)                  | 3     |
| Genuinely different photography           | deep-bench (desert/canyon road)                                                                                                             | 1     |

12 of 19 themes — including ones with otherwise strong token/type differentiation like off-grid's mono-zine identity and liquid-glass's only-glassmorphism token — literally share one photograph. For families whose entire premise is showcasing creative/visual work (portfolio-gallery ×8, archive-directory ×4), this undercuts the sales pitch more than any token or copy difference: a buyer comparing two of these side by side in the marketplace sees the same picture twice.

**The fix pattern already exists in the fleet, it's just not systematised:** off-grid desaturates the shared photo to grayscale, art-paper applies an oxblood duotone, and deep-bench simply sources different photography. None of this needed new photography sourcing (off-grid/art-paper) or is expensive to do fleet-wide (deep-bench's approach doesn't scale to 19 themes, but the CSS-filter approach does). See the `photo-treatment-filter` primitive proposed in the main doc's 2.7.

## 2. Demo-content copy artifacts ("looks unfinished")

Two themes render copy that reads as a leftover placeholder rather than deliberate fictional-brand copy — both are cheap, mechanical fixes, not redesign work:

- **open-studio** — nav brand, footer brand, and hero eyebrow all render the literal string "Case Study Platform Demo." The word "Demo" is visible in what's supposed to be a premium marketplace screenshot. Check the theme's `DemoContent` provider for a hardcoded placeholder brand.
- **front-row** — hero eyebrow reads "INDEX GALLERY · PREMIUM PORTFOLIO COLLECTION." Every other theme invents a plausible fictional brand (The Meridian Review, Atrium & Field, Northwind System, Raw Index, Scoreboard Awards, Frame Index); this one instead surfaces what reads like an internal product-tier label. Worth checking whether `premium-portfolio-collection` (the pre-rename package name folded into front-row per its `themes.json` note) leaked into the copy verbatim.

## 3. Portfolio-gallery page rhythm repeats even where tokens don't

Independent of the photo-reuse issue, six to eight of the portfolio-gallery cluster (field-guide, launch-pad, first-light, one-take, deep-bench, front-row, soft-focus, open-studio) share an almost identical section order and density: hero (text + single image) → three-card feature/stat row → content grid ("latest"/"featured"/"pinned") → proof or stat band → dark CTA band. The Part 2 headline-mechanic work (Wave 4c) targets the _content_ of these sections; it doesn't by itself vary the _order and rhythm_ they appear in. A buyer scrolling two of these themes back to back will feel the same page even after the mechanics differentiate the middle content, because the top-to-bottom shape is identical.

The archive-directory pair reel-room/gold-rush shows the same issue at the widget level today: both render a "criteria list + horizontal progress bar + percentage" scoring pattern that looks nearly identical, even though the planned mechanics (jury-score-matrix vs. a conic-gradient voting-status-gauge) are meant to diverge. This is good evidence Wave 4b's reel-room/gold-rush work is correctly prioritised — it's fixing a real, currently-visible overlap, not a theoretical one.

## 4. Generic connective-tissue vocabulary

Beyond the signature sections, the boilerplate furniture around them — search-bar placeholder copy, filter-chip labels, "Featured this week" / "Latest" headings, three-stat rows — reads nearly identically across the same portfolio-gallery/archive-directory themes. Once Part 2's signature mechanics ship, these surrounding sections will still read as templated unless they also get a per-family voice pass. This is a copy problem, not a widget problem — cheap to fix alongside the Wave 4a–4c level-up work rather than a new wave.

## 5. Dark-mode capture gap (confirms Wave 2.4's target precisely)

Exactly 4 of 19 themes have no `-dark` homepage capture today: **art-paper, far-field, ink-press, foundation**. Quiet-type (the fourth editorial-publishing theme) already has one, so this isn't a whole-family gap — it's these four specifically. Gives Wave 2.4 a concrete, checkable done-state ahead of the dark-mode parity test landing.

## 6. Foundation's demo homepage is self-referential

Foundation's only committed homepage capture (`foundation-homepage-layout.png`) shows copy about the theme itself — heading "Homepage layout," subhead "A flexible public homepage with hero, feature grid, proof, CTA, and footer rhythm," a "Layout contract" card listing "Public-safe Blade output / Theme tokens applied / Optional package note visible." This is Storybook-style meta-documentation, not a plausible real site. Next to the other 18 themes' fictional-but-real-feeling brands, Foundation's marketplace listing reads as unfinished by comparison. Recommend a neutral, plausible generic-site demo (small business, community group, or blog) consistent with its "starter sites / general publishing" positioning in `docs/theme-catalogue-guide.md`.

## Positive proof points worth reusing, not just criticising

- **art-paper**'s oxblood duotone treatment is the strongest existing example of turning shared/generic photography into something that reads as a deliberate visual signature.
- **off-grid**'s desaturation is a second, cheaper example of the same idea.
- **deep-bench**'s canyon photography proves unique-per-theme sourcing is already happening somewhere in the fleet — just not everywhere.
- **quiet-type** remains the fleet's clearest genuinely-distinct theme: only serif-first token set, only theme reusing Foundation chrome deliberately, no stock-photo dependency in its hero at all. _(Caveat added in Round 2, §7: this holds for its homepage hero only — its directory page reuses the shared photo like everyone else.)_
- **ink-press** and **far-field** show that a strong text-first or abstract-graphic hero treatment is a complete alternative to photography — neither looks cheaper for skipping it.

## Per-theme quick reference

| Theme                | Family               | Hero image                         | Dark capture | Copy artifact                       |
| -------------------- | -------------------- | ---------------------------------- | ------------ | ----------------------------------- |
| default (Foundation) | foundation-free      | none (UI-chrome demo)              | no           | self-referential copy (§6)          |
| liquid-glass         | foundation-free      | shared corridor photo              | yes          | —                                   |
| night-shift          | product-saas         | same shoot, different crop         | yes          | —                                   |
| ink-press            | editorial-publishing | none (news-ticker box)             | no           | —                                   |
| art-paper            | editorial-publishing | same shoot, oxblood duotone        | no           | —                                   |
| quiet-type           | editorial-publishing | none                               | yes          | —                                   |
| far-field            | editorial-publishing | none (letter-plate "A")            | no           | —                                   |
| wild-card            | archive-directory    | shared corridor photo              | yes          | —                                   |
| reel-room            | archive-directory    | shared corridor photo              | yes          | —                                   |
| off-grid             | archive-directory    | shared corridor photo, desaturated | yes          | —                                   |
| gold-rush            | archive-directory    | shared corridor photo              | yes          | —                                   |
| open-studio          | portfolio-gallery    | shared corridor photo              | yes          | "Case Study Platform Demo" (§2)     |
| field-guide          | portfolio-gallery    | shared corridor photo              | yes          | —                                   |
| launch-pad           | portfolio-gallery    | shared corridor photo              | yes          | —                                   |
| first-light          | portfolio-gallery    | shared corridor photo              | yes          | —                                   |
| one-take             | portfolio-gallery    | shared corridor photo              | yes          | —                                   |
| deep-bench           | portfolio-gallery    | unique (desert/canyon)             | yes          | —                                   |
| front-row            | portfolio-gallery    | shared corridor photo              | yes          | "PREMIUM PORTFOLIO COLLECTION" (§2) |
| soft-focus           | portfolio-gallery    | shared corridor photo              | yes          | —                                   |

## Feeds into the main programme

- Wave 0.6 — the two copy-artifact fixes (§2), cheap enough to land ahead of any redesign wave.
- Wave 2.7 — new `photo-treatment-filter` shared primitive (§1).
- Theme-bar criterion 8 — section rhythm and connective-tissue copy (§3, §4).
- Risks & dependencies 7–8 — shared demo photography tracked as an explicit risk; print-stylesheet scope flagged for a keep/drop decision (carried from the pre-2026-H2 next-tier plan, currently unassigned to any wave).

---

## Round 2 — Interior pages (2026-07-05)

Extends the audit above from homepages to the other DemoContent surfaces: directory/listing, detail/landing, contact, empty, not-found. Sampled 19 interior screenshots across families, plus both themes that have empty/not-found captured.

### 7. Photo reuse is denser on interior grid pages than on homepages

The homepage audit found the shared stock photo reused _across_ themes. On interior "index of many things" pages it's also reused _within a single page_, which is a sharper version of the same problem because the page's whole premise is "here are N different indexed items":

- **field-guide**'s directory page ("Twelve thousand captures behind four facets") shows the identical desk-with-iMac-and-skyline photo 4–5 times, captioned each time as a different indexed capture ("SaaS dashboard," "Editorial portfolio," "Pastel onboarding," "Fintech docs").
- **quiet-type**'s directory page ("An index of essays built to be read") reuses its shared corridor/conference-table photo 3× across "More from the archive," captioned as 3 different essays.
- **wild-card** and **reel-room**'s detail pages each reuse their hero photo again in a "related work" grid, appearing twice on the same page.
- **deep-bench** is internally consistent (always its own canyon photo) but still repeats that single photo 3× on its one detail page — a milder version of the same root cause: a too-small per-theme media pool, not just a too-small fleet-wide one.

**Correction to the Round 1 quiet-type proof point:** its homepage hero genuinely has no stock-photo dependency, but its directory page does reuse the shared photo — see the footnote added above. The "no stock-photo dependency" finding holds for quiet-type's hero only, not the whole theme.

### 8. Duplicate fictional project name across themes

wild-card's and reel-room's detail pages each independently invented a demo project called **"Tidal States"** — same name, different theme, different fictional studio. Harmless in isolation, but a buyer comparing screenshots side by side (exactly how a marketplace gallery gets browsed) would notice two "different" award-winning projects sharing a name.

### 9. open-studio's detail page: the sharpest "looks unfinished" tell in the fleet

Its detail page doesn't show a fictional case study at all — it's literally _about_ the concept of a detail page: heading "Detail / page story," subhead "Article-style page data for long-form previews," and bullet copy describing its own field structure ("A wide cover image, a title, and the studio behind the build"). Worse, its footer nav renders literal internal surface names as if they were public site links: **"Empty state"** and **"404"** appear as clickable footer items under "Content" and "Support." No real business site links to its own 404 page from the footer. Fold into the Wave 0.6 fix.

### 10. Screenshot-manifest surface coverage is thin fleet-wide

Excluding the homepage, only **2 of 19 themes** (liquid-glass, quiet-type) have screenshots matching the DemoContent contract's surface names (`directory`/`detail`/`contact`/`empty`/`not-found`/`cta`). The other 17 use an unrelated ad hoc set (`landing`, `listing`, `search`, `contact`) — `landing` reads as the `detail` surface and `listing` as `directory` by content, but neither `empty`, `not-found`, nor `cta` is captured for any of them. Net effect: **89% of the catalogue has no visible empty state or 404 page in its marketplace screenshots**, and this audit could only inspect what liquid-glass and quiet-type actually do there.

**Positive proof point:** where empty/404 states are visible, they're good — both themes keep their type/token identity, reuse a consistent "recovery action" pattern (liquid-glass's "Layered site chrome" preview panel serves both surfaces; quiet-type offers real back-issue links), and neither looks like a generic framework default. This is reassuring evidence the other 17 themes' empty/404 states are _probably_ fine — the DemoContent contract (Wave 3.1) requires all 7 surfaces to render — but "probably fine because it's not visible" is exactly the gap Wave 3.4's manifest-completeness check should close.

### 11. Foundation's meta-documentation voice is systemic

Its `standard-page-layout` capture repeats the exact same pattern as its homepage: "FOUNDATION THEME" eyebrow, a heading naming the page type itself ("Standard content page"), and the identical "Layout contract" card. This isn't a one-off homepage choice — it's how every Foundation demo page talks. Reinforces §6 above; the fix (a plausible generic-site demo) should cover all of Foundation's surfaces, not just its homepage.

## Feeds into the main programme (Round 2)

- Wave 0.6 (expanded) — open-studio's footer nav labels and the wild-card/reel-room name collision, added to the existing fix item.
- Wave 0.7 — this round's summary, sharpening Wave 3.4/3.5 with the exact manifest-coverage gap (2/19).
