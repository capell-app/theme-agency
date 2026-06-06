# SEO Suite

Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Bundle: **search-seo** · Contexts: **admin, frontend, console** · Product group: **Capell Search & SEO**

This page is the consolidated implementation overview for the SEO Suite package. It is extracted from the package README, service providers, migrations, config files, routes, resources, models, actions, and the shared Capell ERD notes where available.

## What This Package Adds

SEO Suite adds metadata panels, AI Discovery outputs, structured data, broken link tracking, Search Console insights, AI-assisted content briefs, generated-output diagnostics, and publish checks.

- Page and site SEO schema extenders, including the page editor SEO settings tab, report-backed edit audit widget, and Pages-list audit overview widget.
- Page SEO reports cover every enum-backed check: metadata, canonical URL, robots, schema, social image, image alt text, internal links, broken links, redirect opportunities, translation coverage, sitemap URL availability, `llms.txt` eligibility, and Search Console status.
- SEO audit, AI Discovery, broken links, not-found URLs, and translation coverage pages.
- AI creator actions for briefs, images, layouts, metadata suggestions, and draft application.
- AI Discovery generation for `llms.txt`, optional `llms-full.txt`, per-page Markdown views, `robots.txt` AI crawler rules, and page-readiness audit signals.
- AI Discovery admin management for browsing pages, filling summaries, toggling page inclusion, previewing Markdown, and reviewing readiness issue counts.
- Search Console sync and dashboard reports.
- Generated-output diagnostics, route ownership checks, AI Discovery coverage, sitemap parity, public-output leak scanning, structured data reporting, and stale-output regeneration controls.

## Developer Notes

Exposes SEO work as actions, contracts, data objects, settings schemas, and extenders that connect to core pages, sites, translations, routes, and optional AI providers.

- SeoSuiteServiceProvider registers settings, pages, extenders, commands, routes, and views.
- Config files: capell-seo-suite.php and exchanger.php.
- Migrations create broken links, page SEO snapshots, Search Console metrics, AI creator contexts, AI histories, AI sessions, AI Discovery site profiles, page profiles, crawler rules, and snapshots.
- Commands cover install, setup, AI cache, AI usage, and OpenAI connection testing.
- Controllers: LlmsTxtController, LlmsFullTxtController, PageMarkdownController.

## AI Discovery

AI Discovery is an optional SEO Suite surface for AI-readable public content. It uses Capell's page, translation, URL, Site Discovery, robots, and SEO metadata rather than reverse-converting anonymous frontend HTML.

- `llms.txt` is generated per active site and language from public discoverable pages.
- `llms-full.txt` is opt-in per site/language and is bounded by page count and byte limits.
- Page Markdown output is available at `index.md` and `{url}.md`; the controller can use the active frontend context or resolve the site/language from the request URL.
- `Accept: text/markdown` rendering is separate from `.md` routes and must be enabled with the site/language `accept_markdown_enabled` control.
- `robots.txt` includes configurable AI crawler rules. Site-specific rows override global rows with the same provider, user-agent, and path, including disabling a global default for one site.
- Site profiles control `llms.txt`, `llms-full.txt`, Markdown pages, default include behavior, cache TTL, default section, limits, intro Markdown, and enabled/disabled state.
- Page profiles control include/exclude, summary, section, priority, optional Markdown override, generated Markdown state, and exclusion reason.
- Page editor quick-fill fields live in the SEO settings tab under AI Discovery and sync into page profiles.
- Site/language quick-fill fields live in site translation SEO metadata under AI Discovery and sync into site profiles.
- Snapshot records track generated output hashes, byte sizes, cache keys, expiry, status, and page/site context.
- Cache invalidation marks snapshots stale and forgets cached documents on page save/delete events and AI Discovery profile changes.
- Crawler rules seed from `capell-seo-suite.ai_discovery.default_crawler_rules`, can be shaped by the `ai_discovery_crawler_policy` setting, and render robots snippets for OAI-SearchBot, GPTBot, ChatGPT-User, ClaudeBot, Claude-SearchBot, Claude-User, PerplexityBot, Google-Extended, and CCBot.
- Full implementation notes live in [AI Discovery](ai-discovery.md).

## Operational Notes

Gives editors and site operators practical checks before publishing and operational dashboard reports after launch.

- Adds SEO and AI-related tables/settings.
- Extends page and site admin form-builder; page-level SEO fields live in this package rather than the core admin sidebar settings.
- Adds SEO admin pages and widgets, including the Pages-list overview widget through `PageResourceWidgetExtender`.
- Adds `llms.txt`, `llms-full.txt`, `robots.txt`, and page Markdown frontend output.
- Requires Site Discovery for public page discovery and sitemap outputs.
- Adds config for AI provider/model, image model, Search Console, publish gates, and prompts.
- Manifest metadata declares the SEO Suite settings classes, admin permissions, PageSpeed audit/digest capabilities, supported Blog/Publishing Studio/URL Manager integrations, and cache invalidation sources for generated AI Discovery output.

## Diagnostics

`capell:seo-suite-doctor` verifies route ownership, installed dependencies, generated document status codes, content types, cache headers, crawler policy, Site Discovery availability, and common web-server interception symptoms.

The doctor and dashboard actions expose AI Discovery coverage, excluded reasons, missing summaries, stale snapshots, Markdown availability, noindex conflicts, public-output leak scanning, sitemap XML validity, unsafe sitemap URLs, structured data reports, stale Markdown regeneration controls, and crawler previews for sitemap XML, robots, `llms.txt`, `llms-full.txt`, page Markdown, and schema output.

Structured data reporting also includes marketplace freshness warnings for Product/Offer prices and AggregateRating metadata, including missing or expired `priceValidUntil` values and stale or undated rating data.

## Edit Page Audits

The package contributes one edit-page header widget, `EditPageAuditTabsWidget`, which groups the SEO audit and PageSpeed audit into lazy tabs. Lightweight Livewire badges show current issue counts for each tab, while the heavier audit bodies load only for the active tab. The original standalone SEO and PageSpeed widgets still render as full Filament widget sections when used outside the tab container.

## Remaining Roadmap

- Build a dedicated crawler-preview diagnostics tab if the existing doctor and AI Discovery admin surfaces need a richer UI.
- Add the next tier of scored content analysis: focus keyword grading, heading structure, readability, and bulk metadata workflows.

## Data And Retention

- broken_links stores page, target URL, HTTP status, and last check time.
- page_seo_snapshots store page SEO report state.
- search_console_url_metrics store imported Search Console values.
- ai_creator_contexts, ai_generation_histories, and ai_creator_sessions store AI workflow state.
- ai_discovery_site_profiles, ai_discovery_page_profiles, ai_discovery_crawler_rules, and ai_discovery_snapshots store AI Discovery configuration, robots controls, and generated document state.
- SEO data connects to sites, pages, languages, users, and publishing-studio.

## Content Graph

SEO Suite contributes content graph edges from page SEO snapshots and broken-link records back to their pages. SEO snapshots use weak `DescribesPage` edges, and broken links use weak `FoundOnPage` edges. These records show up in impact previews and diagnostics without blocking ordinary page deletes as strong dependencies.

## Screenshot Plan

- `seo-audit-page.png`: `SeoAuditPage` with a seeded `page_seo_snapshots` row.
- `broken-links-page.png`: `BrokenLinksPage` with a seeded `broken_links` row.
- `translation-coverage-page.png`: `TranslationCoveragePage` with seeded site/page/language data.
- `page-seo-panel.png`: core Page edit screen with the SEO Suite tab/panel.
- `search-console-insights-panel.png`: admin dashboard with seeded `search_console_url_metrics`.
- `ai-creator-action-modal.png`: core Page edit screen with the SEO Suite AI Creator action modal.
- `sitemap-page.png`: sitemap coverage surface with seeded URL state.

Optional follow-up captures remain declared in `docs/screenshots.json` but are not promoted until the runner has the needed fixture support: `not-found-urls-page.png`, `ai-discovery-page.png`, `seo-settings-page.png`, `llms-txt-output.png`, `robots-txt-output.png`, and `page-markdown-output.png`.

## Screenshots

![SEO audit page](screenshots/seo-audit-page.png)

![Broken links diagnostics](screenshots/broken-links-page.png)

![Translation coverage settings](screenshots/translation-coverage-page.png)

![Page SEO panel](screenshots/page-seo-panel.png)

![AI Creator action modal](screenshots/ai-creator-action-modal.png)

![Sitemap coverage](screenshots/sitemap-page.png)

## Pitfalls

- `capell:seo-suite-install` must publish all SEO Suite schema migrations. The screenshot pass caught missing AI creator, broken link, page snapshot, and Search Console tables; the install command now publishes the complete set.
- SEO Suite depends on Insights for `NotFoundUrlsPage` and dashboard widgets. In a disposable app, install and migrate `capell-app/insights` before capturing those surfaces.
- Regenerate Filament Shield permissions after installing SEO Suite in a demo app: `php artisan shield:generate --all --panel=admin`.
- Do not enable AI creator without checking provider credentials and review workflow.
- Search Console requires credentials and property URL.
- Publish gates can block publishing when required metadata is missing.
- Site Discovery owns sitemap output; regenerate it after route or content changes.
- Keep AI Discovery page summaries specific. Thin summaries, duplicate entity names, no canonical URL, no schema, no server-rendered text, disabled Markdown views, and noindex pages are reported by the AI-readiness audit action.
- Review crawler defaults before publishing robots output; search crawlers and training crawlers are deliberately configurable separately.
- Use the SEO Suite settings crawler policy as the default posture, then use crawler rule rows when a site needs a provider-specific override.
- Prism provider telemetry treats missing usage data as zero tokens, and the circuit breaker is scoped by configured provider.
- PageSpeed digest default recipients use the user role relation when available instead of loading all users into memory.

## Verification

- Run `vendor/bin/pest packages/seo-suite/tests --configuration=phpunit.xml`.
- Run the relevant host-app migration or package install flow in a disposable database.
- Open the listed admin or frontend surface and compare it with the screenshot plan.

## Package Manifest

- Composer name: `capell-app/seo-suite`
- Product group: Capell Search & SEO
- Kind: package
- Tier: premium
- Bundle: search-seo
- Contexts: `admin`, `frontend`, `console`
- Requires: `capell-app/admin`, `capell-app/frontend`, `capell-app/insights`, `capell-app/site-discovery`
- Optional dependencies: None listed.

SEO Suite relies on Site Discovery for public page discovery and sitemap outputs.

## Admin Surfaces

- BrokenLinksPage (packages/seo-suite/src/Filament/Pages/BrokenLinksPage.php, slug `broken-links`)
- AiDiscoveryPage (packages/seo-suite/src/Filament/Pages/AiDiscoveryPage.php, slug `ai-discovery`)
- NotFoundUrlsPage (packages/seo-suite/src/Filament/Pages/NotFoundUrlsPage.php, slug `missing-pages`)
- SeoAuditPage (packages/seo-suite/src/Filament/Pages/SeoAuditPage.php, slug `seo-audit`)
- TranslationCoveragePage (packages/seo-suite/src/Filament/Pages/TranslationCoveragePage.php, slug `translation-coverage`)

## Commands

- `capell:admin-clear-ai-cache` (packages/seo-suite/src/Console/Commands/ClearAiCacheCommand.php)
- `capell:seo-suite-install` (packages/seo-suite/src/Console/Commands/InstallCommand.php)
- `capell:admin-monitor-ai-usage` (packages/seo-suite/src/Console/Commands/MonitorAiUsageCommand.php)
- `capell:seo-suite-setup` (packages/seo-suite/src/Console/Commands/SetupCommand.php)
- `capell:admin-test-openai` (packages/seo-suite/src/Console/Commands/TestOpenAiConnectionCommand.php)

## Routes And Config

- Config: packages/seo-suite/config/capell-seo-suite.php
- Config: packages/seo-suite/config/exchanger.php

## Permissions And Gates

- Policy: AiCreatorPolicy (packages/seo-suite/src/Policies/AiCreatorPolicy.php)
- Gate: AiMetricsWidgetAbstract: `developer`, `admin`, `super_admin`
- Gate: BrokenLinksPage: Filament Shield page permissions
- Gate: AiDiscoveryPage: Filament Shield page permissions
- Gate: NotFoundUrlsPage: Filament Shield page permissions
- Gate: SeoAuditPage: Filament Shield page permissions
- Gate: TranslationCoveragePage: Filament Shield page permissions

## Migrations

- Migration: 2026_04_18_000002_create_ai_creator_contexts_table.php
- Migration: 2026_04_18_000003_create_ai_generation_histories_table.php
- Migration: 2026_04_18_000004_create_ai_creator_sessions_table.php
- Migration: create_ai_discovery_crawler_rules_table.php
- Migration: create_ai_discovery_page_profiles_table.php
- Migration: create_ai_discovery_site_profiles_table.php
- Migration: create_ai_discovery_snapshots_table.php
- Migration: create_broken_links_table.php
- Migration: create_page_seo_snapshots_table.php
- Migration: create_search_console_url_metrics_table.php
- Settings migration: 2026_04_18_000001_update_ai-orchestrator_settings_add_ai_creator.php
- Settings migration: create_ai-orchestrator_settings.php

## ERD Excerpt

```mermaid
erDiagram
    SITES ||--o{ BROKEN_LINKS : scans
    PAGES ||--o{ BROKEN_LINKS : contains
    SITES ||--o{ AI_CREATOR_CONTEXTS : scopes
    SITES ||--o{ AI_CREATOR_SESSIONS : scopes
    USERS ||--o{ AI_CREATOR_SESSIONS : runs
    AI_GENERATION_HISTORIES ||--o{ AI_CREATOR_SESSIONS : supports
    AI_CREATOR_SESSIONS ||--o{ AI_GENERATION_HISTORIES : produces
    LANGUAGES ||--o{ AI_GENERATION_HISTORIES : localizes
    PAGES ||..o{ AI_GENERATION_HISTORIES : pageable_context

    BROKEN_LINKS {
        bigint id PK
        bigint page_id FK
        string target_url
        int http_status
        timestamp last_checked_at
    }

    AI_CREATOR_SESSIONS {
        bigint id PK
        bigint site_id FK
        bigint user_id FK
        bigint ai_history_id FK
        bigint workspace_id FK
        string status
        json generated_output
    }
```

## Screenshot Automation

Deployment should read [screenshots.json](screenshots.json), install the package with demo data, resolve each admin surface or frontend URL, and write images to `packages/seo-suite/docs/screenshots`.

- Page SEO panel.
- SEO audit page.
- Broken links page.
- AI Creator action modal.
- Sitemap page.
- Translation coverage page.
- Search Console insights panel.
- Optional follow-up captures: AI Discovery page, SEO Suite settings page, not-found URLs, and public AI Discovery outputs (`/llms.txt`, `/robots.txt`, and `/index.md`).
