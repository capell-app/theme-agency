<?php

declare(strict_types=1);

namespace Capell\Experiments\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Experiments\Enums\ResourceEnum;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAllocation;
use Capell\Experiments\Models\ExperimentAudienceRule;
use Capell\Experiments\Models\ExperimentGoal;
use Capell\Experiments\Models\ExperimentGoalEvent;
use Capell\Experiments\Models\ExperimentVariant;
use Override;
use Spatie\LaravelPackageTools\Package;

final class ExperimentsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-experiments';

    public static string $packageName = 'capell-app/experiments';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-experiments')
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_31_000001_create_experiments_table',
                '2026_05_31_000002_create_experiment_variants_table',
                '2026_05_31_000003_create_experiment_goals_table',
                '2026_05_31_000004_create_experiment_audience_rules_table',
                '2026_05_31_000005_create_experiment_allocations_table',
                '2026_05_31_000006_create_experiment_goal_events_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerProtectedTables()
                ->registerAdminResources();
        });
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            Experiment::class,
            ExperimentVariant::class,
            ExperimentGoal::class,
            ExperimentAudienceRule::class,
            ExperimentAllocation::class,
            ExperimentGoalEvent::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable(fn (): string => config('capell-experiments.tables.experiments', 'experiments'));
        CapellCore::registerProtectedTable(fn (): string => config('capell-experiments.tables.variants', 'experiment_variants'));
        CapellCore::registerProtectedTable(fn (): string => config('capell-experiments.tables.allocations', 'experiment_allocations'));
        CapellCore::registerProtectedTable(fn (): string => config('capell-experiments.tables.goals', 'experiment_goals'));
        CapellCore::registerProtectedTable(fn (): string => config('capell-experiments.tables.goal_events', 'experiment_goal_events'));
        CapellCore::registerProtectedTable(fn (): string => config('capell-experiments.tables.audience_rules', 'experiment_audience_rules'));

        return $this;
    }

    private function registerAdminResources(): self
    {
        if (! class_exists(CapellAdmin::class) || ! class_exists(AdminSurfaceContributionData::class)) {
            return $this;
        }

        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }
}
