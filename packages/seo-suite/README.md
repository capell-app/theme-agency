# SEO Suite

<!-- prettier-ignore-start -->

## What This Plugin Adds

SEO Suite is an **Available**, **Schema-owning** Capell package in the **Capell Search & SEO** product group. It ships as `capell-app/seo-suite` and extends these surfaces: admin, frontend, console.

Ship search-ready pages by default: live SEO scoring, schema.org structured data, broken-link and redirect management, Google Search Console insights, and first-class AI-discovery output (`llms.txt`, page Markdown, AI-crawler controls) - all inside the Capell page editor.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: search-seo
- Composer package: `capell-app/seo-suite`
- Namespace: `Capell\SeoSuite`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Ship search-ready pages by default: live SEO scoring, schema.org structured data, broken-link and redirect management, Google Search Console insights, and first-class AI-discovery output (`llms.txt`, page Markdown, AI-crawler controls) - all inside the Capell page editor.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Page SEO panel (admin, required).
- SEO audit page (admin, required).
- Search Console insights panel (admin, required).
- Broken links page (admin, required).
- AI Creator action modal (admin, required).
- Sitemap page (admin, required).
- Translation coverage page (admin, required).

## Technical Shape

- Service providers: `Capell\SeoSuite\Providers\SeoSuiteServiceProvider`.
- Config files: `packages/seo-suite/config/capell-seo-suite.php`, `packages/seo-suite/config/exchanger.php`.
- Migrations: `packages/seo-suite/database/migrations/2026_05_10_190870_01_create_ai_creator_contexts_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_02_create_ai_generation_histories_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_03_create_ai_creator_sessions_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_04_create_ai_discovery_crawler_rules_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_05_create_ai_discovery_page_profiles_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_06_create_ai_discovery_site_profiles_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_07_create_ai_discovery_snapshots_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_08_create_broken_links_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_09_create_page_seo_snapshots_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_10_create_search_console_url_metrics_table.php`, `packages/seo-suite/database/migrations/2026_05_10_190870_11_create_search_console_query_metrics_table.php`, `packages/seo-suite/database/migrations/2026_05_29_000001_create_page_speed_audit_runs_table.php`, `packages/seo-suite/database/migrations/2026_05_29_000002_create_page_speed_audit_results_table.php`, `packages/seo-suite/database/migrations/2026_06_08_000001_add_cost_fields_to_ai_generation_histories_table.php`.
- Settings migrations: `packages/seo-suite/database/settings/2026_05_10_190871_01_create_ai-orchestrator_settings.php`, `packages/seo-suite/database/settings/2026_05_10_190871_03_create_seo_suite_settings.php`, `packages/seo-suite/database/settings/2026_05_29_000001_add_pagespeed_seo_suite_settings.php`, `packages/seo-suite/database/settings/2026_06_07_000001_add_seo_authoring_quality_gate_settings.php`.
- Settings classes: `AIOrchestratorSettings`, `SeoSuiteSettings`.
- Models: `AIGenerationHistory`, `AiCreatorContext`, `AiCreatorSession`, `AiDiscoveryCrawlerRule`, `AiDiscoveryPageProfile`, `AiDiscoverySiteProfile`, `AiDiscoverySnapshot`, `BrokenLink`, `PageSeoSnapshot`, `PageSpeedAuditResult`, `PageSpeedAuditRun`, `SearchConsoleQueryMetric`, `SearchConsoleUrlMetric`.
- Filament classes: `AiContentBriefAction`, `AiCreatorAction`, `AiImageGeneratorAction`, `CreateRedirectFromBrokenLinkAction`, `PageSeoPanel`, `TranslationSeoMetaSchema`, `SearchMetaDataSection`, `MetaSchema`, `TranslationMetaSchema`, `PageSeoPanelSchemaExtender`, `PageSeoSettingsTabExtender`, `SearchMetaSchemaExtender`, `and 34 more`.
- Policies: `AiCreatorPolicy`.
- Events: `AiGenerationCompleted`, `AiGenerationFailed`, `AiGenerationStarted`.
- Listeners: `ClearAiDiscoveryCacheOnPageDeleted`, `ClearAiDiscoveryCacheOnPageSaved`, `SeedAiCrawlerRulesOnSiteCreated`, `LogAiGeneration`, `NotifyAiFailure`, `RecordBrokenLink`.
- Actions: `EstimateAiGenerationCostAction`, `RecordAiGenerationAction`, `AnalyzePageContentAction`, `ApplyAiDraftAction`, `BaseAction`, `BreadcrumbsSchemaAction`, `BuildAiDiscoveryPageEntriesAction`, `BuildAiReadinessAuditAction`, `BuildAiRobotsTxtRulesAction`, `BuildCrawlerPreviewReportAction`, `BuildDecliningSearchConsolePagesAction`, `BuildMarketplaceStructuredDataFreshnessWarningsAction`, `and 65 more`.
- Data objects: `AiGenerationInputData`, `AiGenerationResultData`, `AiContentBriefData`, `AiDiscoveryAuditData`, `AiDiscoveryCrawlerRuleData`, `AiDiscoveryPageEntryData`, `AiDiscoveryRenderContextData`, `AiReadinessIssueData`, `CrawlerPreviewData`, `AiMetricsData`, `SearchConsoleDashboardStatsData`, `InternalLinkSuggestionData`, `and 23 more`.
- Jobs: `RunPageSpeedAuditJob`.
- Command signatures: `capell:admin-clear-ai-cache`, `capell:seo-suite-doctor`, `capell:seo-suite-install`, `capell:admin-monitor-ai-usage`, `capell:seo-suite:pagespeed-audit`, `capell:seo-suite:refresh-ai-discovery-markdown`, `capell:seo-suite-setup`, `capell:seo-suite-sync-search-console`, `capell:admin-test-openai`.
- Console command classes: `ClearAiCacheCommand`, `DoctorCommand`, `InstallCommand`, `MonitorAiUsageCommand`, `PageSpeedAuditCommand`, `RefreshAiDiscoveryMarkdownCommand`, `SetupCommand`, `SyncSearchConsoleCommand`, `TestOpenAiConnectionCommand`.
- Manifest contributions: `admin-page: Capell\SeoSuite\Manifest\AiDiscoveryPageContribution`, `admin-page: Capell\SeoSuite\Manifest\BrokenLinksPageContribution`, `admin-page: Capell\SeoSuite\Manifest\NotFoundUrlsPageContribution`, `admin-page: Capell\SeoSuite\Manifest\SearchRankingsPageContribution`, `admin-page: Capell\SeoSuite\Manifest\SeoAuditPageContribution`, `admin-page: Capell\SeoSuite\Manifest\TranslationCoveragePageContribution`, `route: Capell\SeoSuite\Manifest\AiDiscoveryRoutesContribution`, `model: Capell\SeoSuite\Manifest\SeoSuiteModelsContribution`, `dashboard-widget: Capell\SeoSuite\Manifest\SeoSuiteDashboardWidgetsContribution`, `scheduled-job: Capell\SeoSuite\Manifest\SeoSuitePageSpeedScheduleContribution`, `console-command: Capell\SeoSuite\Manifest\SeoSuiteConsoleCommandsContribution`, `setting: Capell\SeoSuite\Manifest\SeoSuiteSettingsContribution`, `health-check: Capell\SeoSuite\Manifest\SeoSuiteHealthContribution`.
- Health checks: `Capell\SeoSuite\Health\SeoSuiteHealthCheck`.
- Blade views: `packages/seo-suite/resources/views/components/schema/breadcrumb.blade.php`, `packages/seo-suite/resources/views/components/schema/graph.blade.php`, `packages/seo-suite/resources/views/components/schema/image.blade.php`, `packages/seo-suite/resources/views/components/schema/organization.blade.php`, `packages/seo-suite/resources/views/components/schema/webpage.blade.php`, `packages/seo-suite/resources/views/components/schema/website.blade.php`, `packages/seo-suite/resources/views/filament/components/page-seo-panel.blade.php`, `packages/seo-suite/resources/views/filament/fields/image-preview.blade.php`, `packages/seo-suite/resources/views/filament/widgets/ai-metrics.blade.php`, `packages/seo-suite/resources/views/filament/widgets/ai-usage.blade.php`, `packages/seo-suite/resources/views/filament/widgets/edit-page-audit-tabs.blade.php`, `packages/seo-suite/resources/views/filament/widgets/pagespeed-audit-edit.blade.php`, `and 10 more`.
- Cache tags: `seo-suite`.

## Data Model

- Required tables: `ai_creator_contexts`, `ai_creator_sessions`, `ai_discovery_crawler_rules`, `ai_discovery_page_profiles`, `ai_discovery_site_profiles`, `ai_discovery_snapshots`, `ai_generation_histories`, `broken_links`, `page_seo_snapshots`, `page_speed_audit_results`, `page_speed_audit_runs`, `search_console_query_metrics`, `search_console_url_metrics`.
- Models: `AIGenerationHistory`, `AiCreatorContext`, `AiCreatorSession`, `AiDiscoveryCrawlerRule`, `AiDiscoveryPageProfile`, `AiDiscoverySiteProfile`, `AiDiscoverySnapshot`, `BrokenLink`, `PageSeoSnapshot`, `PageSpeedAuditResult`, `PageSpeedAuditRun`, `SearchConsoleQueryMetric`, `SearchConsoleUrlMetric`.
- Migration files: `2026_05_10_190870_01_create_ai_creator_contexts_table.php`, `2026_05_10_190870_02_create_ai_generation_histories_table.php`, `2026_05_10_190870_03_create_ai_creator_sessions_table.php`, `2026_05_10_190870_04_create_ai_discovery_crawler_rules_table.php`, `2026_05_10_190870_05_create_ai_discovery_page_profiles_table.php`, `2026_05_10_190870_06_create_ai_discovery_site_profiles_table.php`, `2026_05_10_190870_07_create_ai_discovery_snapshots_table.php`, `2026_05_10_190870_08_create_broken_links_table.php`, `2026_05_10_190870_09_create_page_seo_snapshots_table.php`, `2026_05_10_190870_10_create_search_console_url_metrics_table.php`, `2026_05_10_190870_11_create_search_console_query_metrics_table.php`, `2026_05_29_000001_create_page_speed_audit_runs_table.php`, `2026_05_29_000002_create_page_speed_audit_results_table.php`, `2026_06_08_000001_add_cost_fields_to_ai_generation_histories_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:SeoAuditPage`, `View:BrokenLinksPage`, `Manage:BrokenLinks`, `View:NotFoundUrlsPage`, `Manage:NotFoundUrls`, `View:AiDiscoveryPage`, `Manage:AiDiscovery`, `View:SearchRankingsPage`, `View:TranslationCoveragePage`, `Manage:SeoSuiteSettings`, `Use:AiCreator`.
- Public routes: AI Discovery output routes for `llms.txt`, `llms-full.txt`, `robots.txt`, `index.md`, and per-page Markdown.
- Database changes: package migrations are declared.
- Settings: `Capell\SeoSuite\Settings\AIOrchestratorSettings`, `Capell\SeoSuite\Settings\SeoSuiteSettings`.
- Queues or schedules: schedules `capell:seo-suite:pagespeed-audit --notify` weekly when PageSpeed audits and digest settings are enabled.
- Cache tags: `seo-suite`.
- Commands: `capell:seo-suite-install`, `capell:seo-suite-setup`, `capell:seo-suite-doctor`, `capell:seo-suite:pagespeed-audit`, `capell:seo-suite:refresh-ai-discovery-markdown`, `capell:seo-suite-sync-search-console`, `capell:admin-clear-ai-cache`, `capell:admin-monitor-ai-usage`, `capell:admin-test-openai`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/seo-suite`.
2. Run host migrations and the package setup command from the installed Capell app.
3. Open the related Capell admin surface and verify SEO Suite appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Insights](../insights/README.md), [Site Discovery](../site-discovery/README.md), [Blog](../blog/README.md), [Media Ai](../media-ai/README.md), [Media Library](../media-library/README.md), [Publishing Studio](../publishing-studio/README.md), [Url Manager](../url-manager/README.md).
- Focused tests: `vendor/bin/pest packages/seo-suite/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
