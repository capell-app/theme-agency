# Theme Knowledge — Improvement & Growth Plan

> Package: capell-app/theme-knowledge · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

`KnowledgeThemeServiceProvider` registers the `knowledge` theme key with a single Blade layout (`capell-theme-knowledge::page`, `src/KnowledgeThemeServiceProvider.php:100`) and one preset, extending `default` (the foundation theme — `FoundationThemeServiceProvider::THEME_KEY = 'default'`, so this is correct, not a mismatch). `definition()->includedSections` lists 16 keys; `navigation` and `footer` are explicitly delegated back to foundation via `isFoundationSection()`, leaving 14 theme-owned section views plus `page.blade.php`. The demo command `capell:theme-knowledge-demo` exists (`src/Console/Commands/DemoCommand.php`) and delegates to `InstallKnowledgeThemeDemoAction`, which just calls `ThemeDemoPageInstaller::run($data, 'knowledge', 'Knowledge')` — there are no bespoke demo layouts; demo content is the generic foundation page installer. The theme overrides hero/features/proof/content sections and the page shell; it inherits navigation, footer, and all chrome from foundation-theme.

Marketplace and Composer copy are buyer-facing, but `capell.json` now promotes only the extension card because the **9** committed route-backed PNG captures declared by `docs/screenshots.json` were generated from the generic Capell runner `theme-gallery` fixture instead of the actual Theme Knowledge shell.

## Completed Improvement Slices

- **2026-06-03:** Added buyer-facing marketplace and Composer copy, replaced the stub Diagnostics health check with real theme registration/view/vendor-asset probes, and limited marketplace screenshots to committed preview assets.
- **2026-06-06:** Captured all 9 `docs/screenshots.json` route-backed targets through the Capell runner, then demoted the gallery after verifying the route renders the generic `theme-gallery` fixture rather than the package theme shell.
- **2026-06-04:** Moved author bench and topic hub defaults into translations, made both sections accept hydrated item data, removed optional package installation checks from public newsletter/search Blade, and added tests for those public rendering contracts.
- **2026-06-05:** Added a first-class `doc-article` section renderer with hydrated breadcrumbs, category sidebar, article metadata, constrained article body, and sticky table-of-contents layout, plus registry coverage and docs/manifest reconciliation.
- **2026-06-06:** Added code-block/prose styling for technical articles, constrained long-form measures with calmer heading scale, tokenized theme colour utilities, dark-mode tokens, a reduced-motion guard for decorative grid overlays, and KB article feedback/version/freshness affordances.

**Headline:** the package is positioned as a knowledge-base / documentation / help-site theme, and the core documentation layout now exists. The remaining shipped-feature gaps are a real Theme Knowledge screenshot fixture, dark-mode capture, and render-budget coverage; the current light and dark captures are blocked on a Capell runner fixture that renders the actual Theme Knowledge shell/CSS instead of the generic `theme-gallery` route.

## 2. Improvements (existing functionality)

Prioritized. Real templates only.

1. **Topic hubs are translated and data-driven.** — `topic-hubs` now reads `$section->items`/`$items` when supplied and falls back to translated default hub cards, so real taxonomy labels and summaries can be passed from render data. — `resources/views/sections/topic-hubs.blade.php`, `resources/lang/en/generic.php`, `tests/Unit/KnowledgeThemeDefinitionTest.php` — **M**

2. **Author bench is translated and data-driven.** — `authors` now reads `$section->items`/`$items` when supplied and falls back to translated author cards, removing hard-coded public English from the Blade. — `resources/views/sections/authors.blade.php`, `resources/lang/en/generic.php`, `tests/Unit/KnowledgeThemeDefinitionTest.php` — **M**

3. **Blocked by runner fixture: add dark mode screenshot.** Theme Knowledge now defines dark-mode token overrides under `prefers-color-scheme: dark` and normalizes white/slate utility surfaces inside `.knowledge-shell`. The dark screenshot remains open because the current Capell runner route `/screenshot-fixtures/theme-gallery/knowledge/*` returns hard-coded generic HTML/CSS rather than the package theme shell, so its attempted dark capture was light and was not promoted. — `resources/css/theme-knowledge.css`, `docs/screenshots.json` — **L**

4. **Done/Shipped: tokenize hardcoded hex colors.** Theme views now use `.knowledge-shell` CSS custom properties such as `--site-theme-primary`, `--site-theme-accent`, `--site-theme-surface`, `--site-theme-heading`, and ink/code variants instead of inline arbitrary hex utilities. The literal hex values are centralized in `resources/css/theme-knowledge.css` token definitions so Theme Studio presets and future dark-mode overrides have one palette boundary. — `resources/views/**/*.blade.php`, `resources/css/theme-knowledge.css` — **L**

5. **Done/Shipped: honor reduced motion on the animated grid.** `.knowledge-hero::before`, `.knowledge-search-console::before`, and `.knowledge-cta::before` now sit behind a `prefers-reduced-motion: reduce` guard that disables decorative grid backgrounds and shortens animation/transition durations inside the theme shell. — `resources/css/theme-knowledge.css` — **S**

6. **Give skeleton/placeholder bars semantic fallback** — **What:** `content-listing`, `search-listing`, `topic-hubs` render decorative `<span>`/`<div>` bars (`h-3 bg-[#f59e0b]`, etc.) as filler. Most are `aria-hidden="true"` (good), but the `search-listing` "source" panel and the `content-listing` image fallback rely on coloured bars as the only content when no data is present. — why: empty-state polish; a docs theme with no content should still read as intentional. — `resources/views/sections/search-listing.blade.php`, `content-listing.blade.php` — **S**

7. **Done/Shipped: heading scale / readability pass for long-form.** Long-form doc/article and content-listing surfaces now get a 68ch reading measure for prose/header text, balanced article headings, and a scoped heading weight override that calms the previous all-`font-black` treatment without rewriting section templates. — `resources/css/theme-knowledge.css` — **M**

## 3. Missing Features (gaps)

`capabilities[]` = `["theme-knowledge", "theme-knowledge-frontend"]`. For the knowledge/docs/help vertical, the current feature status is:

- **Done/Shipped: Doc/article layout (table stakes, differentiator-critical).** `doc-article` is now a first-class included section with a left category sidebar, breadcrumb trail, article metadata, constrained article body, and right sticky table of contents backed by hydrated renderer data and translated fallbacks. — `src/KnowledgeThemeServiceProvider.php`, `resources/views/sections/doc-article.blade.php`, `resources/lang/en/generic.php`, `tests/Unit/KnowledgeThemeDefinitionTest.php`
- **Persistent sidebar / category navigation.** The shipped `doc-article` section renders a hydrated first-level category sidebar. Multi-level collapsible sidebar trees remain deeper follow-up work.
- **Table of contents (in-page anchor nav).** The shipped `doc-article` section renders hydrated sticky TOC links. Automatic heading extraction remains future work.
- **Breadcrumbs.** The shipped `doc-article` section renders hydrated breadcrumb trails with translated fallbacks.
- **Done/Shipped: Functional search surface.** `search-listing.blade.php` now renders a translated `GET` search form with renderer-supplied availability/action data and query input. Deeper Search package result-page integration remains follow-up work.
- **Done/Shipped: code-block / syntax styling.** `.knowledge-doc-prose` now styles inline code, scrollable `pre` blocks, lists, and H2/H3 rhythm for both section-rendered articles and Knowledge Base article views. Full syntax highlighting remains future package/app integration depth.
- **Done/Shipped: article feedback ("Was this helpful?").** The Theme Knowledge KB article view renders the package feedback form, submitted-state message, and helpfulness aggregate summary using the Knowledge Base public payload and translated KB strings. A reusable partial can remain future cleanup, but the buyer-facing affordance is present.
- **Done/Shipped: versioning / "last updated" freshness affordance.** The theme doc-article section accepts a hydrated `version`, and the KB article view displays the current public article version plus last-modified date from the Knowledge Base article payload. A full version switcher remains future product depth.
- **Doc-article hero/header partial** (title + category + reading time + updated date). The generic `hero` is marketing-shaped.

**Vs siblings:** `theme-healthcare` ships dedicated `blog/article.blade.php` + `blog/index.blade.php` views and a `partials/` directory; this theme ships neither a blog article view nor any partials, despite "Editorial/Resources" positioning. **Cross-sell:** `resource-library`→`capell-app/blog`, `search-listing`/`topic-index`→`capell-app/search`, `newsletter`→`capell-app/newsletter` are already wired as optional flags, and `seo-suite` is in `supports[]` — but none of these are surfaced as buyer-facing "better together" value, and the search integration produces no functional UI. **Differentiator vs table-stakes:** sidebar nav + TOC + functional search + code blocks are table stakes for the vertical and currently missing; versioning and article feedback are the differentiators.

## 4. Issues / Risks

- **Optional-package checks removed from public Blade.** Newsletter and search listing views now rely on renderer-provided `newsletterAvailable`/`searchAvailable` flags and default to `false` when rendered in isolation. `PublicOutputSafetyTest` guards against `CapellCore::` and `isPackageInstalled(` in public views. — `resources/views/sections/newsletter.blade.php`, `search-listing.blade.php`, `tests/Unit/PublicOutputSafetyTest.php`
- **Author hard-coded copy fixed.** Author default cards now live in translations and the view accepts hydrated item data. — `resources/views/sections/authors.blade.php`, `resources/lang/en/generic.php`
- **Health check shipped.** `ThemeKnowledgeHealthCheck` now probes Theme Studio registration, required views, and vendor asset registration; tests cover passing diagnostics and an unregistered theme failure. — `src/Health/ThemeKnowledgeHealthCheck.php`, `tests/Unit/ThemeKnowledgeHealthCheckTest.php`
- **Stub contribution class.** `src/Manifest/ThemeManagementPageContribution.php` is contract-only (`compatibleCapellApiVersion()` only); confirm the management page actually mounts for `themeKey: knowledge` rather than relying on an empty contract impl. — `src/Manifest/ThemeManagementPageContribution.php`
- **Screenshot contract reopened.** `docs/screenshots.json` declares 9 render entries and every path exists under `docs/screenshots/`, but those PNGs are generic `theme-gallery` fixture captures rather than real Theme Knowledge shell output. `capell.json` now promotes only the package card until a Capell runner route renders the actual Theme Knowledge shell. — `docs/screenshots.json`, `capell.json`, `docs/screenshots/`
- **Marketplace copy shipped, with code-block caveat.** Marketplace/Composer copy is buyer-facing, and the manifest caveat now correctly narrows the remaining technical-docs promise gap to code-block styling. — `capell.json`
- **`{!! $content !!}` trust boundary.** `page.blade.php:12` echoes pre-rendered section HTML unescaped. This is the standard foundation pattern (sections render server-side via the trusted renderer), so it is acceptable — but it means section template safety is the only guard; `PublicOutputSafetyTest` now guards against authoring markers, database calls, and public Blade `CapellCore::` package checks. Broader untranslated-literal detection remains open. — `resources/views/page.blade.php`, `tests/Unit/PublicOutputSafetyTest.php`
- **Performance budget.** `capell.json` `performance.frontendRenderBudgetMs = 20`, `adminQueryBudget = 0`, `cacheSafety.cacheable = false` (varies by site, locale). No test asserts the render budget, and there is no LCP guidance for `hero-desktop.jpg` (698 KB)/`hero-mobile.jpg` (493 KB) demo assets — large hero media with no documented `loading`/`fetchpriority` strategy risks the LCP path. — `capell.json`, `docs/assets/marketplace/*`
- **Test gaps.** `tests/` covers theme definition, manifest requirements, package-aware rendering, health diagnostics, translated/data-driven author and topic hub defaults, the doc/article layout renderer, and public-output leak strings including `CapellCore::` checks. Not covered: route-backed screenshot capture, dark mode, broader untranslated-literal detection, CSS visual assertions for code blocks/reduced motion, and render budget. — `tests/Unit/`, `tests/Feature/Commands/DemoCommandTest.php`

## 5. Marketplace & Selling

**Critique.** Marketplace and Composer copy are now buyer-facing, and the technical-docs copy gap has narrowed now that code blocks have a dedicated prose treatment. The static visual story remains reopened because the 9 committed route-backed PNG captures show the generic runner `theme-gallery` fixture rather than the actual Theme Knowledge shell; real light and dark captures need a runner fixture that loads the Theme Knowledge shell/CSS.

**Improved 1-sentence summary:**

> A premium knowledge-base and documentation theme for Capell — sidebar-navigated articles, in-page table of contents, prominent search, and readable long-form layouts out of the box.

**Improved 3–4 sentence description:**

> Theme Knowledge turns a Capell site into a polished documentation and help centre. It pairs a category sidebar, sticky table of contents, code-friendly prose, and breadcrumb trails with a prominent search experience and readable long-form typography, so visitors find answers fast. Resource libraries, author bios, topic hubs, and newsletter capture round out a full knowledge-marketing surface, with optional Blog, Search, and Newsletter integrations lighting up automatically when those packages are installed. Built on the Capell foundation theme with a configurable colour palette and accessible focus states.

**Screenshot/media status.** The 9 light renders are committed to `docs/screenshots/`, but they are demoted from marketplace media because they show the generic runner fixture rather than real Theme Knowledge output. Light and dark captures remain open until the Capell screenshot runner fixture can render the actual Theme Knowledge CSS instead of the generic theme-gallery CSS; the current package repo has no route-backed fixture to capture without changing the external runner app.

**Differentiation / target buyer.** Today the theme is hard to distinguish from a generic editorial/marketing theme. Target buyer: documentation/help-centre owners, dev-tool/SaaS teams, and internal-knowledge-base operators who want a docs site without a separate static-site generator. The wedge is "docs site inside your CMS" — sidebar + TOC + search + versioning — which no sibling theme currently fills.

**8–12 keywords/tags:** knowledge base, documentation, help center, docs theme, technical docs, sidebar navigation, table of contents, search, code blocks, resource library, developer docs, editorial.

## 6. Prioritized Roadmap

| Item                                                                                                     | Bucket | Effort | Impact | Section ref |
| -------------------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Done/Shipped: Add doc/article layout (sidebar + TOC + breadcrumbs) backing `knowledge-docs-layout.svg`   | Done   | L      | High   | §3          |
| Make `search-listing` a real, prominent search form (cross-sell `capell-app/search`)                     | Done   | M      | High   | §3, §2.6    |
| Replace generic `theme-gallery` fixture PNGs with real Theme Knowledge renderer captures                  | Next   | M      | High   | §4, §5      |
| Done/Shipped: Add code-block / `pre`/`code` prose styling                                                | Done   | M      | High   | §3          |
| Blocked by runner fixture: capture a real dark screenshot through a Theme Knowledge CSS fixture          | Next   | M      | High   | §2.3, §5    |
| Done/Shipped: Tokenize hardcoded hex to preset `--site-theme-*` variables                                | Done   | L      | Med    | §2.4        |
| Done/Shipped: Constrained prose measure + heading-scale readability pass                                 | Done   | M      | Med    | §2.7        |
| Done/Shipped: `prefers-reduced-motion` guard on animated grid `::before`                                 | Done   | S      | Low    | §2.5        |
| Remove `CapellCore::isPackageInstalled` from `newsletter`/`search-listing` Blade; rely on renderer flags | Done   | S      | Med    | §4          |
| Fix untranslated/hardcoded copy in `authors` (and data-drive it)                                         | Done   | M      | Med    | §2.2, §4    |
| Data-drive `topic-hubs` instead of fixed 4 labels                                                        | Done   | M      | Med    | §2.1        |
| Rewrite marketplace `summary`; tighten `description`                                                     | Done   | S      | High   | §5          |
| Implement real `ThemeKnowledgeHealthCheck` logic (views/renderer/assets)                                 | Done   | S      | Med    | §4          |
| Done/Shipped: Article feedback ("Was this helpful?") affordance                                          | Done   | M      | Med    | §3          |
| Done/Shipped: Versioning / "last updated" freshness affordance                                           | Done   | L      | Med    | §3          |
| Extend `PublicOutputSafetyTest`: broader untranslated-literal detection; assert render budget            | Later  | M      | Med    | §4          |
