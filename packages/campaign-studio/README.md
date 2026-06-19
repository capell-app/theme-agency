# Campaign Studio

<!-- prettier-ignore-start -->

## What This Plugin Adds

Campaign Studio is an **Available**, **Schema-owning** Capell package in the **Capell Growth** product group. It ships as `capell-app/campaign-studio` and extends these surfaces: admin, frontend.

Launch, target, and measure marketing campaigns inside Capell - build landing-page variants, drop in CTA and lead-capture widgets, and track UTM-attributed conversions and funnels without bolting on a separate analytics tool.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: growth
- Composer package: `capell-app/campaign-studio`
- Namespace: `Capell\CampaignStudio`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Launch, target, and measure marketing campaigns inside Capell - build landing-page variants, drop in CTA and lead-capture widgets, and track UTM-attributed conversions and funnels without bolting on a separate analytics tool.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Campaign groups index (admin, required).
- Campaign landing pages index (admin, required).
- Campaign conversion goals form (admin, required).
- CTA widget form (admin, required).
- Campaign dashboard widgets (admin, required).
- Frontend landing page with campaign widgets (frontend, required).

## Technical Shape

- Service providers: `Capell\CampaignStudio\Providers\CampaignStudioServiceProvider`, `Capell\CampaignStudio\Providers\AdminServiceProvider`, `Capell\CampaignStudio\Providers\FrontendServiceProvider`.
- Config files: `packages/campaign-studio/config/capell-campaign-studio.php`.
- Migrations: `packages/campaign-studio/database/migrations/2026_05_10_190843_01_create_campaign_groups_table.php`, `packages/campaign-studio/database/migrations/2026_05_10_190843_02_create_campaign_conversion_goals_table.php`, `packages/campaign-studio/database/migrations/2026_05_10_190843_03_create_campaign_landing_pages_table.php`, `packages/campaign-studio/database/migrations/2026_05_10_190843_04_create_campaign_cta_widgets_table.php`, `packages/campaign-studio/database/migrations/2026_05_10_190843_05_create_campaign_conversions_table.php`.
- Models: `CampaignConversion`, `CampaignConversionGoal`, `CampaignCtaWidget`, `CampaignGroup`, `CampaignLandingPage`.
- Filament classes: `CampaignCtaWidgetWidgetConfigurator`, `CampaignHeroWidgetConfigurator`, `CampaignLeadFormWidgetConfigurator`, `CampaignPageSchemaExtender`, `CampaignConversionGoalResource`, `CreateCampaignConversionGoal`, `EditCampaignConversionGoal`, `ListCampaignConversionGoals`, `CampaignConversionGoalForm`, `CampaignConversionGoalsTable`, `CampaignCtaWidgetResource`, `CreateCampaignCtaWidget`, `and 19 more`.
- Route files: `packages/campaign-studio/routes/web.php`.
- Policies: `AbstractCampaignStudioResourcePolicy`, `CampaignConversionGoalPolicy`, `CampaignCtaWidgetPolicy`, `CampaignGroupPolicy`, `CampaignLandingPagePolicy`.
- Events: `CampaignConverted`.
- Listeners: `RecordFormSubmissionConversion`, `SyncCampaignLandingPageFromPage`.
- Actions: `ApplyCampaignPageDefaultsAction`, `BuildCampaignConversionFunnelAction`, `BuildCampaignExperimentResultsAction`, `BuildCampaignLandingPageVariantsAction`, `BuildCampaignOverviewStatsAction`, `BuildCampaignUrlAction`, `BuildConversionAttributionAction`, `BuildTopCampaignStudioQueryAction`, `BuildTopLandingPagesQueryAction`, `CaptureCampaignConversionAction`, `GetCampaignStudioTrackerScriptAction`, `InstallCampaignLayoutsAction`, `and 11 more`.
- Data objects: `AudienceTargetData`, `CampaignConversionCaptureData`, `CampaignCtaActionData`, `CampaignExperimentResultsData`, `CampaignExperimentVariantResultData`, `ConversionAttributionData`, `CampaignConversionSummaryData`, `CampaignLandingPageSummaryData`, `LandingPageVariantData`, `LandingPageVariantSelectionData`, `UtmData`.
- Command signatures: `capell:campaign-studio-sync-statuses`.
- Console command classes: `InstallCampaignLayoutsCommand`, `SyncCampaignStatusesCommand`.
- Manifest contributions: `admin-resource: Capell\CampaignStudio\Manifest\CampaignStudioAdminResourcesContribution`, `configurator: Capell\CampaignStudio\Manifest\CampaignWidgetConfiguratorsContribution`, `console-command: Capell\CampaignStudio\Manifest\CampaignStudioConsoleCommandsContribution`, `dashboard-widget: Capell\CampaignStudio\Manifest\CampaignStudioDashboardWidgetsContribution`, `frontend-component: Capell\CampaignStudio\Manifest\CampaignWidgetComponentsContribution`, `model: Capell\CampaignStudio\Manifest\CampaignStudioModelsContribution`, `overview-stat: Capell\CampaignStudio\Manifest\CampaignOverviewStatsContribution`, `route: Capell\CampaignStudio\Manifest\CampaignStudioConversionRouteContribution`, `scheduled-job: Capell\CampaignStudio\Manifest\CampaignStudioStatusScheduleContribution`, `schema-extender: Capell\CampaignStudio\Manifest\CampaignPageSchemaExtenderContribution`.
- Health checks: `Capell\CampaignStudio\Health\CampaignStudioHealthCheck`.
- Blade views: `packages/campaign-studio/resources/views/components/tracking/attributes.blade.php`, `packages/campaign-studio/resources/views/components/widget/campaign-cta-widget.blade.php`, `packages/campaign-studio/resources/views/components/widget/campaign-hero.blade.php`, `packages/campaign-studio/resources/views/components/widget/campaign-lead-form.blade.php`, `packages/campaign-studio/resources/views/tracker.blade.php`.
- Cache tags: `campaign-studio`.

## Data Model

- Required tables: `campaign_groups`, `campaign_landing_pages`, `campaign_cta_widgets`, `campaign_conversion_goals`, `campaign_conversions`.
- Models: `CampaignConversion`, `CampaignConversionGoal`, `CampaignCtaWidget`, `CampaignGroup`, `CampaignLandingPage`.
- Migration files: `2026_05_10_190843_01_create_campaign_groups_table.php`, `2026_05_10_190843_02_create_campaign_conversion_goals_table.php`, `2026_05_10_190843_03_create_campaign_landing_pages_table.php`, `2026_05_10_190843_04_create_campaign_cta_widgets_table.php`, `2026_05_10_190843_05_create_campaign_conversions_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `campaign-studio`.
- Commands: `capell:campaign-studio-sync-statuses`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/campaign-studio`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Campaign Studio appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Form Builder](../form-builder/README.md), [Insights](../insights/README.md), [Layout Builder](../layout-builder/README.md), [Experiments](../experiments/README.md), [Seo Suite](../seo-suite/README.md).
- Focused tests: `vendor/bin/pest packages/campaign-studio/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
