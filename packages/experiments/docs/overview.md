# Experiments

<!-- prettier-ignore-start -->

## What This Plugin Adds

Experiments is an **Available**, **Schema-owning** Capell package in the **Capell Growth** product group. It ships as `capell-app/experiments` and extends these surfaces: admin.

Run server-side A/B tests on Capell pages or campaigns. Target visitors by path, UTM, referrer, query, or custom segments, choose sticky or per-request weighted allocation, and track goal conversions with statistically gated winner reports.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: growth
- Composer package: `capell-app/experiments`
- Namespace: `Capell\Experiments`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Run statistically gated server-side A/B tests on Capell pages or campaigns - audience targeting, weighted allocation, and goal-tracked winner reports.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Experiments admin index (admin, required).
- Experiment variants admin index (admin, required).
- Experiment goals admin index (admin, required).
- Experiment audience rules admin index (admin, required).

## Technical Shape

- Service providers: `Capell\Experiments\Providers\ExperimentsServiceProvider`.
- Config files: `packages/experiments/config/capell-experiments.php`.
- Migrations: `packages/experiments/database/migrations/2026_05_31_000001_create_experiments_table.php`, `packages/experiments/database/migrations/2026_05_31_000002_create_experiment_variants_table.php`, `packages/experiments/database/migrations/2026_05_31_000003_create_experiment_goals_table.php`, `packages/experiments/database/migrations/2026_05_31_000004_create_experiment_audience_rules_table.php`, `packages/experiments/database/migrations/2026_05_31_000005_create_experiment_allocations_table.php`, `packages/experiments/database/migrations/2026_05_31_000006_create_experiment_goal_events_table.php`, `packages/experiments/database/migrations/2026_06_07_000001_add_idempotency_unique_to_experiment_goal_events_table.php`.
- Models: `Experiment`, `ExperimentAllocation`, `ExperimentAudienceRule`, `ExperimentGoal`, `ExperimentGoalEvent`, `ExperimentVariant`.
- Filament classes: `ExperimentAudienceRuleResource`, `CreateExperimentAudienceRule`, `EditExperimentAudienceRule`, `ListExperimentAudienceRules`, `ExperimentGoalResource`, `CreateExperimentGoal`, `EditExperimentGoal`, `ListExperimentGoals`, `ExperimentVariantResource`, `CreateExperimentVariant`, `EditExperimentVariant`, `ListExperimentVariants`, `and 5 more`.
- Actions: `AllocateVariantAction`, `BuildWinnerReportAction`, `CreateExperimentAction`, `DeclareExperimentWinnerAction`, `EvaluateAudienceRulesAction`, `RecordGoalEventAction`, `ResolveExperimentVariantForContextAction`, `SyncExperimentStatusesAction`.
- Data objects: `ExperimentAudienceRuleData`, `ExperimentContextData`, `ExperimentData`, `ExperimentGoalData`, `ExperimentGoalEventData`, `ExperimentStatusSyncResultData`, `ExperimentVariantData`, `ResolvedExperimentVariantData`, `VariantAllocationData`, `WinnerReportData`, `WinnerVariantReportData`.
- Console command classes: `SyncExperimentStatusesCommand`.
- Manifest contributions: `admin-resource: Capell\Experiments\Manifest\ExperimentAudienceRuleResourceContribution`, `admin-resource: Capell\Experiments\Manifest\ExperimentGoalResourceContribution`, `admin-resource: Capell\Experiments\Manifest\ExperimentResourceContribution`, `admin-resource: Capell\Experiments\Manifest\ExperimentVariantResourceContribution`, `model: Capell\Experiments\Manifest\ExperimentsModelsContribution`.
- Health checks: `Capell\Experiments\Health\ExperimentsHealthCheck`.
- Blade views: `packages/experiments/resources/views/filament/experiments/results-page.blade.php`, `packages/experiments/resources/views/filament/experiments/results.blade.php`.
- Cache tags: `experiments`.

## Data Model

- Required tables: `experiments`, `experiment_variants`, `experiment_goals`, `experiment_audience_rules`, `experiment_allocations`, `experiment_goal_events`.
- Models: `Experiment`, `ExperimentAllocation`, `ExperimentAudienceRule`, `ExperimentGoal`, `ExperimentGoalEvent`, `ExperimentVariant`.
- Migration files: `2026_05_31_000001_create_experiments_table.php`, `2026_05_31_000002_create_experiment_variants_table.php`, `2026_05_31_000003_create_experiment_goals_table.php`, `2026_05_31_000004_create_experiment_audience_rules_table.php`, `2026_05_31_000005_create_experiment_allocations_table.php`, `2026_05_31_000006_create_experiment_goal_events_table.php`, `2026_06_07_000001_add_idempotency_unique_to_experiment_goal_events_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:Experiment`, `Create:Experiment`, `Update:Experiment`, `Delete:Experiment`, `View:ExperimentVariant`, `Create:ExperimentVariant`, `Update:ExperimentVariant`, `Delete:ExperimentVariant`, `View:ExperimentGoal`, `Create:ExperimentGoal`, `Update:ExperimentGoal`, `Delete:ExperimentGoal`, `View:ExperimentAudienceRule`, `Create:ExperimentAudienceRule`, `Update:ExperimentAudienceRule`, `Delete:ExperimentAudienceRule`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `experiments`.
- Commands: console command classes detected: `SyncExperimentStatusesCommand`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |

## Quick Start

1. Install the package: `composer require capell-app/experiments`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Experiments appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Campaign Studio](../../campaign-studio/README.md), [Frontend Optimizer](../../frontend-optimizer/README.md), [Html Cache](../../html-cache/README.md), [Form Builder](../../form-builder/README.md), [Insights](../../insights/README.md).
- Focused tests: `vendor/bin/pest packages/experiments/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
