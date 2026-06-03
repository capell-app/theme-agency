<?php

declare(strict_types=1);

namespace Capell\Experiments\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAllocation;
use Capell\Experiments\Models\ExperimentAudienceRule;
use Capell\Experiments\Models\ExperimentGoal;
use Capell\Experiments\Models\ExperimentGoalEvent;
use Capell\Experiments\Models\ExperimentVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class ExperimentsHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<class-string<Model>>
     */
    private const array MODELS = [
        Experiment::class,
        ExperimentVariant::class,
        ExperimentGoal::class,
        ExperimentAudienceRule::class,
        ExperimentAllocation::class,
        ExperimentGoalEvent::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->storageTablesCheck(),
            $check->modelTableResolutionCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts every experiment storage table exists.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'Experiments storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'All experiment, variant, goal, audience rule, allocation, and goal event tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the experiments storage tables.',
        );
    }

    /**
     * Asserts every experiment model resolves to an existing table so the
     * allocation and reporting surfaces are queryable.
     */
    public function modelTableResolutionCheck(): DoctorCheckResultData
    {
        $unresolvedModels = $this->modelsWithMissingTables();

        return new DoctorCheckResultData(
            label: 'Experiments model table resolution',
            passed: $unresolvedModels === [],
            message: $unresolvedModels === []
                ? 'Experiment models resolve to existing tables for allocation and reporting.'
                : 'Models without a backing table: ' . implode(', ', $unresolvedModels) . '.',
            remediation: $unresolvedModels === []
                ? null
                : 'Check capell-experiments.tables configuration and run the Capell migrations.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect($this->requiredTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function modelsWithMissingTables(): array
    {
        return array_values(collect(self::MODELS)
            ->reject(static function (string $modelClass): bool {
                /** @var Model $model */
                $model = new $modelClass;

                return Schema::hasTable($model->getTable());
            })
            ->map(static fn (string $modelClass): string => class_basename($modelClass))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    private function requiredTableNames(): array
    {
        $tables = config('capell-experiments.tables', []);

        $resolve = static function (string $key, string $fallback) use ($tables): string {
            $value = is_array($tables) ? ($tables[$key] ?? null) : null;

            return is_string($value) && $value !== '' ? $value : $fallback;
        };

        return [
            $resolve('experiments', 'experiments'),
            $resolve('variants', 'experiment_variants'),
            $resolve('goals', 'experiment_goals'),
            $resolve('audience_rules', 'experiment_audience_rules'),
            $resolve('allocations', 'experiment_allocations'),
            $resolve('goal_events', 'experiment_goal_events'),
        ];
    }
}
