# SEO Suite — Improvement & Growth Plan

> Package: capell-app/seo-suite · Kind: package · Tier: premium · Product group: Capell Search & SEO · Bundle: search-seo · Status: Draft

## 1. Snapshot

SEO Suite is the premium search/SEO package for the Capell CMS. It bolts metadata panels, schema.org structured data, broken-link tracking, Google Search Console insights, PageSpeed audits, AI-assisted content/metadata generation (via Prism), and an "AI Discovery" surface (`llms.txt`, `llms-full.txt`, per-page Markdown, AI-crawler `robots.txt`) onto core pages and sites. Domain logic is correctly concentrated in ~90 Actions under `src/Actions`, with admin pages/widgets/extenders delegating to them, Data objects at boundaries, and public Blade/controllers driven by view composers and cached responses.

- **Surfaces:** `admin`, `frontend`, `console` (manifest `surfaces`).
- **Key Actions:** `BuildPageSeoReportAction`, `CalculateSeoScoreAction`, `SuggestInternalLinksAction`, `BuildSeoSuiteDoctorReportAction`, `GenerateLlmsTxtAction`, `BuildRobotsTxtAction`, `RunPageSpeedAuditAction`, `SyncSearchConsoleInsightsAction`, `ResolvePageStructuredDataAction`, `SchemaGraphAction`.
- **Models/tables (13 required):** `broken_links`, `page_seo_snapshots`, `page_speed_audit_runs`, `page_speed_audit_results`, `search_console_url_metrics`, `search_console_query_metrics`, `ai_generation_histories`, `ai_creator_contexts`, `ai_creator_sessions`, `ai_discovery_site_profiles`, `ai_discovery_page_profiles`, `ai_discovery_crawler_rules`, `ai_discovery_snapshots`.
- **Dependencies — requires:** `capell-app/admin`, `capell-app/frontend`, `capell-app/insights`, `capell-app/site-discovery`, plus `prism-php/prism ^0.100`. **supports:** none declared; composer `suggest`s `capell-app/publishing-studio`. **conflicts:** none.
- **Marketplace summary (verbatim):** "SEO Suite adds metadata panels, structured data, broken link tracking, Search Console insights, AI-assisted content briefs, AI Discovery outputs, crawler policy controls, and generated-output diagnostics."
- **Screenshots in manifest:** 1 (`docs/assets/marketplace/extension-card.jpg`). The repo separately holds 14 light/dark admin screenshots under `docs/screenshots/` that are NOT referenced by the marketplace block.

## 2. Improvements (existing functionality)

- **Wire up the 11 dormant SEO checks.** `SeoCheckKeyEnum` defines 15 cases (Canonical, Schema, ImageAltText, InternalLinks, BrokenLinks, Redirects, SocialImage, TranslationCoverage, Sitemap, LlmsTxt, SearchConsole, …) but `BuildPageSeoReportAction::enabledChecks()` only emits MetaTitle, MetaDescription, DuplicateTitle, Robots. Canonical/schema/alt/links never become issues or passed-checks, so the page score is blind to them. This is the single biggest credibility gap vs Yoast/RankMath. `src/Actions/BuildPageSeoReportAction.php`, `src/Enums/SeoCheckKeyEnum.php`. **Effort: L**

- **Make `SeoSuiteHealthCheck` actually check something.** The class implements only `compatibleCapellApiVersion()`; the 4 manifest `healthChecks` (page-report critical, schema-graph, broken-links, ai-discovery) all point at this one no-op class. Health labels promise coverage that is never asserted at runtime. Add per-key probe methods (e.g. report builds without throwing, schema graph non-empty for a sample page, AI Discovery route resolves). `src/Health/SeoSuiteHealthCheck.php`, `capell.json` (healthChecks). **Effort: M**

- **Weight the SEO score instead of flat severity penalties.** `CalculateSeoScoreAction` is `100 − Σ severity penalty` (Critical 25 / Warning 10 / Notice 3). A page missing only canonical scores identically to one missing alt text; there is no per-check weight or category breakdown. Introduce check-category weights and return a structured breakdown (on-page / technical / structured-data / links) so the widget can show sub-scores. `src/Actions/CalculateSeoScoreAction.php`, `src/Enums/SeoIssueSeverityEnum.php`. **Effort: M**

- **Strengthen the public-output leak scanner.** `BuildSeoSuiteDoctorReportAction::leakMatches()` only greps for `signature=` and `expires=`. The declared capability `seo-suite-public-output-leak-scanning` and the README promise detection of admin URLs, model IDs, field paths, Livewire internals, unpublished content, and editor metadata. Expand the match set (admin route prefix, `wire:`, `livewire`, `meta->`, numeric model-id patterns in unexpected places, draft markers). `src/Actions/BuildSeoSuiteDoctorReportAction.php` (~L485-491), `src/Actions/BuildCrawlerPreviewReportAction.php` (`leakWarnings`). **Effort: M**

- **Upgrade internal-link suggestions beyond ≥4-char token overlap.** `SuggestInternalLinksAction` tokenises titles/content and scores by literal token intersection, capped at 5. The package already ships an AI/embedding stack (Prism, `AiTokenCounter`) yet links ignore it. Offer an optional embedding/semantic-similarity strategy and surface anchor-text suggestions, not just target pages. `src/Actions/SuggestInternalLinksAction.php`, `src/Support/InternalLinks/InternalLinkCandidateRepository.php`. **Effort: L**

- **Make `defaultPageSpeedDigestRecipients()` query-efficient.** It calls `$userModel::query()->get()` and filters every user in PHP via `hasRole`/`isGlobalAdmin`. On a large user table this loads the whole table into memory each schedule resolve. Push the role filter into the query (`whereHas('roles', …)` / role scope). `src/Providers/SeoSuiteServiceProvider.php` (L849-868). **Effort: S**

- **Persist AI cost, not just tokens.** `RecordAiGenerationAction` stores `prompt_tokens`/`completion_tokens`/`total_tokens`/`duration` but no monetary cost and no model-price table; `PrismProvider::chat()` logs token metrics at `debug` only. Buyers expect a spend dashboard. Add a cost column/derivation and roll it into `AiUsageWidget`. `src/Actions/Ai/RecordAiGenerationAction.php`, `src/Support/PrismProvider.php`. **Effort: M**

- **Scope the AI circuit breaker per provider/site.** `PrismProvider` uses a single global cache key `ai_circuit_breaker_state`; one site's provider outage trips AI generation for every site and provider. Key it by provider (and optionally site). `src/Support/PrismProvider.php` (`CIRCUIT_BREAKER_KEY`). **Effort: S**

- **Index the duplicate-title check.** `BuildPageSeoReportAction::duplicateTitleExists()` runs `whereHas('translations', … where 'meta->title' = ?)`, a JSON-path scan per audited page with no supporting index. At scale this is slow and runs inside the synchronous report. Consider a generated/indexed title column or a cached title set. `src/Actions/BuildPageSeoReportAction.php`. **Effort: M**

- **Surface crawler-preview reports in the admin UI.** Both `docs/overview.md` ("Remaining Roadmap") and the action `BuildCrawlerPreviewReportAction` exist, but the README still lists "Wire crawler-preview reports into the admin diagnostics UI" as outstanding — the report has no admin page/tab. `src/Actions/BuildCrawlerPreviewReportAction.php`, `src/Filament/Pages/`. **Effort: M**

## 3. Missing Features (gaps)

- **On-page content analysis (H1 presence/uniqueness, heading order, word count, keyword in title/URL/first paragraph, keyword density, readability).** Absent entirely — the audit never inspects rendered body content. Ties to capability `seo-suite` / `seo-suite-structured-data-audit` adjacency. **Table-stakes** (core Yoast/RankMath feature).

- **Focus-keyword workflow.** There is a `NormalizeTargetKeywordsAction` and keyword plumbing into prompts, but no per-page "focus keyword + scored analysis" loop that the audit grades against. **Table-stakes.**

- **OG/Twitter card validation + live preview correctness.** `BuildSocialMetaAction`/`SocialMetaData` build tags and the report has a `socialPreview`, but `socialPreview.imageUrl` is hard-coded `null` in `BuildPageSeoReportAction`, and there is no validation (missing OG image, wrong dimensions, missing `twitter:card`). **Table-stakes.**

- **Redirect manager UI.** `CreateRedirectForBrokenLinkAction` and `BuildRedirectOpportunityReportAction` exist, but there is no first-class redirect CRUD/import surface (regex/410/bulk) — redirect creation is only a reaction to broken links. **Table-stakes** for an "SEO suite."

- **XML sitemap ownership clarity / sitemap index + image/video/news sitemaps.** Sitemaps are delegated to `capell-app/site-discovery`; SEO Suite only reports parity. Buyers expect the SEO product to own sitemap segmentation, priority, and submission. **Differentiator** if pulled in, otherwise document the boundary loudly.

- **AI-answer / GEO visibility tracking.** The package writes `llms.txt` and AI-crawler rules but never measures whether the site is cited by ChatGPT/Perplexity/AI Overviews. Given the AI Discovery investment this is the natural premium **differentiator**.

- **Search Console: query→page opportunity surfacing (CTR/position striking-distance).** Metrics are imported (`search_console_query_metrics`) and there are movement/decline actions, but no "position 11-20 striking-distance" or "high-impressions/low-CTR" opportunity report tied to the page editor. **Differentiator.**

- **Bulk metadata editing / CSV export-import of titles & descriptions.** AI suggests them one page at a time; there is no grid bulk-edit or import. **Table-stakes** at premium tier.

- **User-level authorization for AI generation.** `AiCreatorPolicy` only answers "is the feature enabled for this site" — there is no per-user gate on who may spend AI budget. **Table-stakes** (cost-control / security).

- **Scheduled site-wide re-crawl / broken-link sweep.** Broken links are recorded reactively via `UrlVisitFailed`; there is no scheduled crawler that proactively re-checks known URLs. **Table-stakes.**

## 4. Issues / Risks

- **Public-output safety is enforced at runtime, not in tests.** `leakMatches()` in `BuildSeoSuiteDoctorReportAction.php` is the only leak gate and is a 2-substring grep. There is no feature/arch test asserting that anonymous GETs of `/llms.txt`, `/llms-full.txt`, `/robots.txt`, `/index.md`, `/{url}.md` are free of admin URLs, signed URLs, model IDs, field paths, or Livewire internals. This mirrors the project's documented Redis-cluster postmortem failure mode (a guard that only the happy path exercises). The Capell skill explicitly requires tests proving anonymous + non-admin safety for rendering/cache output. **`tests/` (missing), `src/Actions/BuildSeoSuiteDoctorReportAction.php`.**

- **Manifest `cacheSafety` is inaccurate.** `capell.json` declares `cacheable: false`, `invalidationSources: []`. In reality `LlmsTxtController`/`PageMarkdownController` use `Cache::remember(...)` with `Cache-Control: public, max-age` and `ETag`, and `SeoSuiteServiceProvider` registers real invalidation on `PageSaved`/`PageDeleted` and AI Discovery profile saves. The manifest under-reports what is cached and lists no invalidation sources, which misleads cache-safety review. **`capell.json` (performance.cacheSafety), `src/Http/Controllers/*`, `src/Providers/SeoSuiteServiceProvider.php`.**

- **`frontendRenderBudgetMs: 20` is at risk for schema rendering.** `WebsiteSchemaComposer`/`SchemaComponentComposer` run during frontend render and call `SiteMetaSchemaAction`/`PageMetaSchemaAction`/`BreadcrumbsSchemaAction` synchronously; `BuildPageImageSchemaAction` triggers `loadMissing(['image','media'])` via `registerFrontendContextHydration`. With multiple schema components on a page, hitting a 20 ms budget without view caching is optimistic. Add a render benchmark/guard. **`src/View/Composers/`, `src/Actions/SiteMetaSchemaAction.php`, `capell.json` (performance).**

- **Manifest under-declares shipped surfaces.** `permissions: []` and `settings: []` are empty even though `AiCreatorPolicy`, three settings schemas, and `SeoSuiteSettings`/`AIOrchestratorSettings` exist; `SearchRankingsPage` and the entire PageSpeed feature (2 tables, schedule, digest notification) are absent from `capabilities`/`healthChecks`/`contributes` PageSpeed wiring. Marketplace/automation reading the manifest will misrepresent the package. **`capell.json`.**

- **Empty CHANGELOG.** `CHANGELOG.md` has only a placeholder "Prepared package metadata…" line. A premium first-party paid package with priority support needs real release notes. **`CHANGELOG.md`.**

- **Docs drift.** `docs/overview.md` lists migrations dated `2026_04_18_*` and omits `RobotsTxtController` in one place / PageSpeed tables; actual migrations are `2026_05_10_*` and `2026_05_29_*`. The screenshot plan references files (`seo-settings-page.png`, `llms-txt-output.png`, `ai-discovery-page.png`) that are not all committed. **`docs/overview.md`, `docs/screenshots.json`.**

- **i18n completeness unverified for new strings.** Only `resources/lang/en/` exists. Every new check label, doctor message, and PageSpeed/notification string must resolve via `__()` keys (`capell-seo-suite::generic.seo_check_*`); there is no second locale to prove keys exist and no test asserting all `SeoCheckKeyEnum` labels have translations. **`resources/lang/en/*`, `src/Enums/SeoCheckKeyEnum.php`.**

- **AI robustness edge: token-usage assumption.** `PrismProvider::chat()` reads `$response->usage->promptTokens + completionTokens` and `$response->usage` unconditionally for the debug log; a provider returning null usage (some Prism providers / Ollama) would throw inside the success path after a successful generation. Guard usage access. **`src/Support/PrismProvider.php`.**

- **`SeoSuiteHealthCheck` no-op (repeat from §2 as a risk):** ships as a passing health check that proves nothing, giving false assurance in the extension health dashboard. **`src/Health/SeoSuiteHealthCheck.php`.**

## 5. Marketplace & Selling

**Critique of current copy.** The `capell.json` marketplace `summary` and composer `description` diverge. Composer says "SEO tools for Capell (metadata panels, structured data, social meta, AI ai-orchestrator)" — note the literal, unpolished token "AI ai-orchestrator", which also leaks into the config prompt strings ("You are a helpful ai-orchestrator…"). The manifest summary is a flat, comma-spliced feature list ("adds X, Y, Z, and diagnostics") with no benefit, no audience, and no hook — it reads like a changelog, not a pitch.

**Improved 1-sentence summary:**

> Ship search-ready pages by default: live SEO scoring, schema.org structured data, broken-link and redirect management, Google Search Console insights, and first-class AI-discovery output (`llms.txt`, page Markdown, AI-crawler controls) — all inside the Capell page editor.

**Improved 3–4 sentence listing description:**

> SEO Suite turns Capell into a search-and-AI optimisation platform. Editors get an in-context SEO panel that scores every page, previews Google and social results, and suggests internal links and metadata with optional AI assistance, while operators get broken-link tracking, redirect opportunities, Search Console dashboards, and PageSpeed audits. It is the only Capell package built for the AI-search era: generate and govern `llms.txt`, `llms-full.txt`, and per-page Markdown, and control exactly which AI crawlers (GPTBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot) may train on or surface your content. A built-in doctor verifies route ownership, content types, cache headers, sitemap parity, and public-output safety so generated SEO output never leaks admin internals.

**Screenshot/media gaps.** The marketplace block ships **one** generic preview image while 14 polished light/dark captures already sit unused in `docs/screenshots/`. Promote the strongest to the manifest: the page SEO panel (the core value), the AI Discovery page, the SEO audit page, the Search Console insights panel, and a real `/llms.txt` output. Missing and high-converting: an animated GIF of the AI metadata-suggestion flow, a before/after SEO-score shot, and the AI-crawler policy toggle screen.

**Pricing/tier/bundle positioning.** `premium` + `search-seo` bundle + `paid`/`first-party`/`priority`/private-docs is appropriate — this is a deep, schema-owning, AI-integrated package, not an add-on. Tighten cross-sell: the manifest `supports` array is empty; it should explicitly support `capell-app/publishing-studio` (already in composer `suggest`, and there's a real `SeoPublishReportProviderAdapter`) and `capell-app/blog` (an `ArticleSchemaTemplate` ships). Position AI Discovery + Search Console as the upsell hook over a hypothetical free/basic metadata tier, and bundle naturally with an Insights/Analytics Extension Suite.

**Top differentiators / value props.** (1) AI-search/GEO readiness as a native, governed feature — rare in CMS SEO plugins. (2) Public-output safety doctor — a trust feature no Yoast competitor markets. (3) One editor surface spanning metadata, schema, links, Search Console, and PageSpeed. **Target buyer persona:** the Capell agency/site-operator who manages multi-site, multi-language properties and is being asked by clients "are we visible in ChatGPT/AI Overviews?".

**Suggested keywords/tags:** `seo`, `structured-data`, `schema.org`, `llms.txt`, `ai-seo`, `generative-engine-optimization`, `search-console`, `pagespeed`, `broken-links`, `redirects`, `meta-tags`, `open-graph`.

## 6. Prioritized Roadmap

| Item                                                                                           | Bucket | Effort | Impact | Section ref |
| ---------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add anonymous/non-admin public-output safety tests for `/llms.txt`, `/index.md`, `/robots.txt` | Now    | M      | High   | §4          |
| Wire up the 11 dormant `SeoCheckKeyEnum` checks (canonical, schema, alt, links…)               | Now    | L      | High   | §2          |
| Make `SeoSuiteHealthCheck` perform real per-key probes                                         | Now    | M      | High   | §2/§4       |
| Strengthen `leakMatches()` beyond `signature=`/`expires=`                                      | Now    | M      | High   | §2/§4       |
| Correct manifest `cacheSafety`, `permissions`, `settings`, PageSpeed capabilities              | Now    | S      | Med    | §4          |
| Fix `defaultPageSpeedDigestRecipients()` to filter in-query                                    | Now    | S      | Med    | §2          |
| Scope AI circuit breaker per provider; guard null Prism usage                                  | Now    | S      | Med    | §2/§4       |
| Weighted SEO score with category breakdown                                                     | Next   | M      | High   | §2          |
| On-page content analysis (H1, headings, word count, keyword density)                           | Next   | L      | High   | §3          |
| Focus-keyword workflow graded by the audit                                                     | Next   | M      | High   | §3          |
| Redirect manager UI (bulk/regex/410)                                                           | Next   | L      | High   | §3          |
| User-level AI-generation authorization + cost persistence/dashboard                            | Next   | M      | Med    | §2/§3       |
| Promote real screenshots + rewrite marketplace summary/description                             | Next   | S      | High   | §5          |
| Embedding/semantic internal-link + anchor-text suggestions                                     | Later  | L      | Med    | §2/§3       |
| AI-answer/GEO visibility tracking (ChatGPT/Perplexity/AI Overviews citations)                  | Later  | L      | High   | §3          |
| Search Console striking-distance opportunity report in editor                                  | Later  | M      | Med    | §3          |
