# Theme Knowledge — Improvement & Growth Plan
> Package: capell-app/theme-knowledge · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

`KnowledgeThemeServiceProvider` registers the `knowledge` theme key with a single Blade layout (`capell-theme-knowledge::page`, `src/KnowledgeThemeServiceProvider.php:100`) and one preset, extending `default` (the foundation theme — `FoundationThemeServiceProvider::THEME_KEY = 'default'`, so this is correct, not a mismatch). `definition()->includedSections` lists 16 keys; `navigation` and `footer` are explicitly delegated back to foundation via `isFoundationSection()`, leaving 14 theme-owned section views plus `page.blade.php`. The demo command `capell:theme-knowledge-demo` exists (`src/Console/Commands/DemoCommand.php`) and delegates to `InstallKnowledgeThemeDemoAction`, which just calls `ThemeDemoPageInstaller::run($data, 'knowledge', 'Knowledge')` — there are no bespoke demo layouts; demo content is the generic foundation page installer. The theme overrides hero/features/proof/content sections and the page shell; it inherits navigation, footer, and all chrome from foundation-theme.

Current marketplace summary (verbatim): **"Research and archive theme screenshots from route-backed demo layouts."** Marketplace screenshots declared in `capell.json`: 6 (`extension-card.jpg`, `knowledge-docs-layout.svg`, `knowledge-homepage-layout.svg`, `knowledge-search-layout.svg`, `hero-desktop.jpg`, `hero-mobile.jpg`); 6 present on disk — but 3 are ~3 KB placeholder SVG mockups and there is no rendered-page screenshot or dark variant. Separately, `docs/screenshots.json` declares **9** QA render entries writing to `docs/screenshots/`, a directory that **does not exist** (0 committed).

**Headline:** the package is positioned as a knowledge-base / documentation / help-site theme, but the shipped section suite is editorial-marketing (hero, features, proof, cta, newsletter, authors, topic-hubs). There is no doc/article layout, no sidebar/category navigation, no table of contents, no breadcrumbs, no code-block styling, and the "search" section is a non-functional visual mock.

## 2. Improvements (existing functionality)

Prioritized. Real templates only.

1. **Make `topic-hubs` data-driven** — **What:** `resources/views/sections/topic-hubs.blade.php` hardcodes a fixed 4-item loop over `topic_hub_strategy/design/operations/growth` and ignores any passed `$items`/`$section`. Every site renders identical "Strategy/Design/Operations/Growth" hubs. — why: a knowledge theme's category hubs must reflect the site's real taxonomy. — `resources/views/sections/topic-hubs.blade.php` — **M**

2. **Replace hardcoded author bench in `authors`** — **What:** `resources/views/sections/authors.blade.php` ends with four literal, untranslated cards ("Editorial Team", "Design Staff", "Research", "Growth Ops" + English descriptions) instead of iterating `$section->items`. — why: untranslated copy in public output and non-editable content (see also Issue #2). — `resources/views/sections/authors.blade.php` — **M**

3. **Add dark mode** — **What:** zero `dark:` utilities and no dark tokens anywhere (`resources/css/theme-knowledge.css` hardcodes `color: #111827` and re-declares the preset hexes as `--site-theme-*`). Sibling `theme-agency` ships dark screenshots. — why: docs/KB sites are heavily read in dark mode; absence is a competitive gap and undercuts the premium tier. — `resources/css/theme-knowledge.css` + all `resources/views/sections/*.blade.php` — **L**

4. **Tokenize hardcoded hex colors** — **What:** section blades are saturated with inline arbitrary hex (`bg-[#07111f]`, `text-[#1d4ed8]`, `bg-[#f59e0b]`, `text-[#172033]`, `bg-[#f8fafc]`, etc.) repeated across 14 files; the preset exposes `primaryColor/accentColor/neutralColor/surfaceColor/foregroundColor` but the views ignore them. — why: changing the preset palette does not change the rendered theme, defeating Theme Studio customization; also makes dark mode (#3) far harder. — all `resources/views/sections/*.blade.php`, `resources/css/theme-knowledge.css` — **L**

5. **Honor reduced motion on the animated grid** — **What:** `.knowledge-hero::before / .knowledge-search-console::before / .knowledge-cta::before` paint an animated/overlay grid with no `@media (prefers-reduced-motion: reduce)` guard. — why: WCAG 2.3.3 / motion sensitivity; the preset advertises `motionIntensity: subtle`. — `resources/css/theme-knowledge.css` — **S**

6. **Give skeleton/placeholder bars semantic fallback** — **What:** `content-listing`, `search-listing`, `topic-hubs` render decorative `<span>`/`<div>` bars (`h-3 bg-[#f59e0b]`, etc.) as filler. Most are `aria-hidden="true"` (good), but the `search-listing` "source" panel and the `content-listing` image fallback rely on coloured bars as the only content when no data is present. — why: empty-state polish; a docs theme with no content should still read as intentional. — `resources/views/sections/search-listing.blade.php`, `content-listing.blade.php` — **S**

7. **Heading scale / readability pass for long-form** — **What:** body sections cap at `max-w-5xl`/`max-w-6xl` full-bleed grids; there is no constrained prose measure (~65ch) anywhere, and every heading is `font-black`. — why: documentation is long-form reading; `font-black` H2/H3 throughout fights legibility and the lack of a prose column hurts article comprehension. — `resources/views/sections/*.blade.php`, `resources/css/theme-knowledge.css` — **M**

## 3. Missing Features (gaps)

`capabilities[]` = `["theme-knowledge", "theme-knowledge-frontend"]`. For the knowledge/docs/help vertical, the following are absent in code:

- **Doc/article layout (table stakes, differentiator-critical).** Only `page.blade.php` exists — a single generic shell rendering `{!! $content !!}`. There is no second layout for an article/doc page with a left category sidebar + right table of contents. The marketplace asset `knowledge-docs-layout.svg` advertises exactly this layout, which does not exist in `resources/views`. This is the single biggest gap.
- **Persistent sidebar / category navigation.** Navigation is delegated wholesale to foundation-theme (`isFoundationSection()`), so there is no docs-style multi-level collapsible sidebar tree — the defining UI of a KB theme.
- **Table of contents (in-page anchor nav).** Nothing renders headings into a sticky TOC.
- **Breadcrumbs.** No breadcrumb section/partial anywhere.
- **Functional search.** `search-listing.blade.php` is a static mock: the "input" is a `<div>` placeholder, the button is `type="button"`, results are seeded translation strings. It never renders a real `<form>`/`<input>` even when `searchAvailable` is true. A KB theme's search must be prominent and real (it should cross-sell `capell-app/search`).
- **Code-block / syntax styling.** No `pre`/`code` styling in CSS or any prose component — essential for developer docs.
- **Article feedback ("Was this helpful?").** No feedback widget/partial.
- **Versioning / "last updated" / version switcher.** No version or freshness affordance.
- **Doc-article hero/header partial** (title + category + reading time + updated date). The generic `hero` is marketing-shaped.

**Vs siblings:** `theme-healthcare` ships dedicated `blog/article.blade.php` + `blog/index.blade.php` views and a `partials/` directory; this theme ships neither a blog article view nor any partials, despite "Editorial/Resources" positioning. **Cross-sell:** `resource-library`→`capell-app/blog`, `search-listing`/`topic-index`→`capell-app/search`, `newsletter`→`capell-app/newsletter` are already wired as optional flags, and `seo-suite` is in `supports[]` — but none of these are surfaced as buyer-facing "better together" value, and the search integration produces no functional UI. **Differentiator vs table-stakes:** sidebar nav + TOC + functional search + code blocks are table stakes for the vertical and currently missing; versioning and article feedback are the differentiators.

## 4. Issues / Risks

- **Optional-package checks executed in public Blade (contradicts the package's own rule).** `resources/views/sections/newsletter.blade.php:2-4` and `search-listing.blade.php:2-4` run `CapellCore::isPackageInstalled(...)` inside `@php`. The README Maintenance Notes explicitly say "Keep optional package checks inside the service provider/renderer layer, not public Blade," and the provider already passes `newsletterAvailable`/`searchAvailable` via `ViewSectionRenderer` (`src/KnowledgeThemeServiceProvider.php:155-163`). These inline fallbacks are redundant and move app-state logic into the view layer. — `resources/views/sections/newsletter.blade.php`, `search-listing.blade.php`
- **Untranslated hard-coded copy in public output.** `resources/views/sections/authors.blade.php` emits "Editorial Team / Design Staff / Research / Growth Ops" and four English sentences literally. Breaks i18n on non-EN sites and violates the translation convention. Not caught by `PublicOutputSafetyTest` (it only scans for leak markers, not for untranslated literals). — `resources/views/sections/authors.blade.php`
- **Stub health check marked `critical`.** `src/Health/ThemeKnowledgeHealthCheck.php` implements only `compatibleCapellApiVersion()` and performs no health logic, yet `capell.json` `healthChecks[0].severity = "critical"`. Diagnostics will report "healthy" regardless of whether views/renderer actually resolve. — `src/Health/ThemeKnowledgeHealthCheck.php`, `capell.json`
- **Stub contribution class.** `src/Manifest/ThemeManagementPageContribution.php` is contract-only (`compatibleCapellApiVersion()` only); confirm the management page actually mounts for `themeKey: knowledge` rather than relying on an empty contract impl. — `src/Manifest/ThemeManagementPageContribution.php`
- **Manifest screenshot drift.** `docs/screenshots.json` declares 9 render entries writing to `docs/screenshots/`, which does not exist (0 committed); `capell.json` declares 6 marketplace images (present, but 3 are placeholder SVGs). `docs/overview.md` "Screenshot Plan" describes "homepage, directory, detail, contact, conversion CTA" states — vocabulary that matches a marketing theme, not the KB sections actually shipped. Three sources, three different screenshot stories. — `docs/screenshots.json`, `capell.json`, `docs/overview.md`
- **Marketplace summary is a developer note, not a sell line.** "Research and archive theme screenshots from route-backed demo layouts." describes the screenshot pipeline, not the product. — `capell.json`
- **`{!! $content !!}` trust boundary.** `page.blade.php:12` echoes pre-rendered section HTML unescaped. This is the standard foundation pattern (sections render server-side via the trusted renderer), so it is acceptable — but it means section template safety is the only guard; the existing `PublicOutputSafetyTest` is the right place and should be extended (it currently does not assert against untranslated literals or `CapellCore::` calls in Blade). — `resources/views/page.blade.php`, `tests/Unit/PublicOutputSafetyTest.php`
- **Performance budget.** `capell.json` `performance.frontendRenderBudgetMs = 20`, `adminQueryBudget = 0`, `cacheSafety.cacheable = false` (varies by site, locale). No test asserts the render budget, and there is no LCP guidance for `hero-desktop.jpg` (698 KB)/`hero-mobile.jpg` (493 KB) demo assets — large hero media with no documented `loading`/`fetchpriority` strategy risks the LCP path. — `capell.json`, `docs/assets/marketplace/*`
- **Test gaps.** `tests/` covers theme definition, manifest requirements, package-aware rendering, and public-output leak strings. Not covered: a real article/doc layout (doesn't exist yet), search-section form behaviour, dark mode, untranslated-literal detection, the `CapellCore::isPackageInstalled` in-Blade smell, and the `topic-hubs`/`authors` hardcoded-content regression. — `tests/Unit/`, `tests/Feature/Commands/DemoCommandTest.php`

## 5. Marketplace & Selling

**Critique.** The `capell.json` `summary` ("Research and archive theme screenshots from route-backed demo layouts.") is an internal QA note and tells a buyer nothing. The composer/`capell.json` `description` ("Editorial and resource-library theme for knowledge bases, publishers, and content-led teams.") is decent and on-vertical, but over-indexes on "editorial/publisher" while under-promising the docs/KB capabilities a buyer in this vertical expects (and which the theme does not yet ship — see §3). The visual story is weak for a premium theme: 3 of 6 marketplace images are 3 KB placeholder SVGs, there is no real rendered-page screenshot in `docs/screenshots/`, and no dark-mode shot.

**Improved 1-sentence summary:**
> A premium knowledge-base and documentation theme for Capell — sidebar-navigated articles, in-page table of contents, prominent search, and clean code blocks out of the box.

**Improved 3–4 sentence description:**
> Theme Knowledge turns a Capell site into a polished documentation and help centre. It pairs a category sidebar, sticky table of contents, and breadcrumb trails with a prominent search experience and readable long-form typography, so visitors find answers fast. Resource libraries, author bios, topic hubs, and newsletter capture round out a full knowledge-marketing surface, with optional Blog, Search, and Newsletter integrations lighting up automatically when those packages are installed. Built on the Capell foundation theme with a configurable colour palette and accessible focus states. (Note: the sidebar/TOC/breadcrumb/code-block/functional-search claims require the §3 work before this copy is truthful.)

**Screenshot/media gaps.** Run `docs/screenshots.json` to generate the 9 declared renders and commit them to `docs/screenshots/` (currently empty); replace the 3 placeholder SVG mockups with real rendered layouts; add a dark-mode variant (parity with `theme-agency`); add a doc-article layout screenshot once §3 lands so `knowledge-docs-layout.svg` is backed by a real layout.

**Differentiation / target buyer.** Today the theme is hard to distinguish from a generic editorial/marketing theme. Target buyer: documentation/help-centre owners, dev-tool/SaaS teams, and internal-knowledge-base operators who want a docs site without a separate static-site generator. The wedge is "docs site inside your CMS" — sidebar + TOC + search + versioning — which no sibling theme currently fills.

**8–12 keywords/tags:** knowledge base, documentation, help center, docs theme, technical docs, sidebar navigation, table of contents, search, code blocks, resource library, developer docs, editorial.

## 6. Prioritized Roadmap

| Item | Bucket | Effort | Impact | Section ref |
| --- | --- | --- | --- | --- |
| Add doc/article layout (sidebar + TOC + breadcrumbs) backing `knowledge-docs-layout.svg` | Now | L | High | §3 |
| Make `search-listing` a real, prominent search form (cross-sell `capell-app/search`) | Now | M | High | §3, §2.6 |
| Remove `CapellCore::isPackageInstalled` from `newsletter`/`search-listing` Blade; rely on renderer flags | Now | S | Med | §4 |
| Fix untranslated/hardcoded copy in `authors` (and data-drive it) | Now | M | Med | §2.2, §4 |
| Data-drive `topic-hubs` instead of fixed 4 labels | Now | M | Med | §2.1 |
| Rewrite marketplace `summary`; tighten `description` | Now | S | High | §5 |
| Generate + commit the 9 `docs/screenshots.json` renders; replace placeholder SVGs | Now | M | High | §4, §5 |
| Add code-block / `pre`/`code` prose styling | Next | M | High | §3 |
| Implement real `ThemeKnowledgeHealthCheck` logic (views/renderer resolve) | Next | S | Med | §4 |
| Add dark mode (tokens + `dark:` variants) and a dark screenshot | Next | L | High | §2.3, §5 |
| Tokenize hardcoded hex to preset `--site-theme-*` variables | Next | L | Med | §2.4 |
| Constrained prose measure + heading-scale readability pass | Next | M | Med | §2.7 |
| `prefers-reduced-motion` guard on animated grid `::before` | Next | S | Low | §2.5 |
| Article feedback ("Was this helpful?") partial | Later | M | Med | §3 |
| Versioning / "last updated" / version switcher | Later | L | Med | §3 |
| Extend `PublicOutputSafetyTest`: ban in-Blade `CapellCore::`, untranslated literals; assert render budget | Later | M | Med | §4 |
