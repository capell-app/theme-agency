<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\GA4Reports\Actions\ResolveGA4ReportsConfigAction;
use Capell\GA4Reports\Contracts\GA4ReportsDataClientInterface;
use Capell\GA4Reports\Data\GA4ReportsConfigData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Capell\GA4Reports\Models\GA4ReportsDailyMetric;
use Capell\GA4Reports\Models\GA4ReportsPageMetric;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class Ga4ReportsHealthCheck implements ChecksExtensionHealth
{
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
            $check->modelAvailabilityCheck(),
            $check->configurationCheck(),
            $check->lastSyncRecencyCheck(),
            $check->dataClientReachabilityCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts every snapshot storage table the sync writes into exists.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'GA4 Reports storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'The sync runs, daily metrics, and page metrics tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the GA4 Reports storage tables.',
        );
    }

    /**
     * Asserts the package models and minimum storage columns are available.
     */
    public function modelAvailabilityCheck(): DoctorCheckResultData
    {
        $missingModels = $this->missingModelClasses();
        $missingColumns = $this->missingStorageColumns();
        $passed = $missingModels === [] && $missingColumns === [];

        return new DoctorCheckResultData(
            label: 'GA4 Reports models and schema',
            passed: $passed,
            message: $passed
                ? 'The GA4 Reports models are registered and their required storage columns are present.'
                : 'Missing model/schema pieces: ' . implode(', ', [...$missingModels, ...$missingColumns]) . '.',
            remediation: $passed
                ? null
                : 'Confirm the GA4 Reports service provider is loaded and the package migrations have run.',
        );
    }

    /**
     * Asserts the integration is configured enough to sync, without disclosing
     * the GA4 property ID or the service-account credentials path.
     */
    public function configurationCheck(): DoctorCheckResultData
    {
        $config = $this->resolveConfig();

        if (! $config instanceof GA4ReportsConfigData) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports configuration',
                passed: false,
                message: 'GA4 Reports settings could not be resolved.',
                remediation: 'Confirm the GA4 Reports settings group is installed and migrated.',
            );
        }

        if (! $config->enabled) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports configuration',
                passed: true,
                message: 'GA4 Reports is disabled; no GA4 sync will run.',
            );
        }

        $missingSettings = $this->missingConfigurationKeys($config);

        if ($missingSettings !== []) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports configuration',
                passed: false,
                message: 'GA4 Reports is enabled but missing required settings: ' . implode(', ', $missingSettings) . '.',
                remediation: 'Set the GA4 property ID and service-account credentials path in the GA4 Reports settings.',
            );
        }

        if (! $this->credentialsFileIsReadable($config->credentialsPath)) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports configuration',
                passed: false,
                message: 'GA4 Reports is enabled but the configured service-account credentials file is not readable.',
                remediation: 'Confirm the service-account JSON exists at the configured path and is readable by the application.',
            );
        }

        if (! $this->credentialsFileHasServiceAccountValues($config->credentialsPath)) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports configuration',
                passed: false,
                message: 'GA4 Reports is enabled but the configured service-account credentials file is not valid service-account JSON.',
                remediation: 'Replace the configured file with a Google service-account JSON containing client_email and private_key values.',
            );
        }

        return new DoctorCheckResultData(
            label: 'GA4 Reports configuration',
            passed: true,
            message: 'GA4 Reports is enabled with a property ID and valid service-account credential fields.',
        );
    }

    /**
     * Asserts enabled installs have completed a successful sync recently enough
     * for dashboard snapshots to be trusted.
     */
    public function lastSyncRecencyCheck(): DoctorCheckResultData
    {
        $config = $this->resolveConfig();

        if (! $config instanceof GA4ReportsConfigData) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports last successful sync',
                passed: false,
                message: 'GA4 Reports settings could not be resolved, so sync recency cannot be checked.',
                remediation: 'Confirm the GA4 Reports settings group is installed and migrated.',
            );
        }

        if (! $config->enabled) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports last successful sync',
                passed: true,
                message: 'GA4 Reports is disabled; sync recency is not required.',
            );
        }

        $syncRunsTable = (new GA4ReportsSyncRun)->getTable();

        if (! Schema::hasTable($syncRunsTable)) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports last successful sync',
                passed: false,
                message: 'The sync runs table is missing, so sync recency cannot be checked.',
                remediation: 'Run the Capell migrations to create the GA4 Reports sync runs table.',
            );
        }

        $latestFinishedAt = $this->latestSuccessfulSyncFinishedAt();

        if (! $latestFinishedAt instanceof CarbonImmutable) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports last successful sync',
                passed: false,
                message: 'No successful GA4 Reports sync run has completed yet.',
                remediation: 'Run the GA4 Reports sync command after configuring the integration.',
            );
        }

        $maxAgeHours = $this->maxSuccessfulSyncAgeHours();
        $staleBefore = CarbonImmutable::instance(Date::now())->subHours($maxAgeHours);
        $fresh = $latestFinishedAt->greaterThanOrEqualTo($staleBefore);

        return new DoctorCheckResultData(
            label: 'GA4 Reports last successful sync',
            passed: $fresh,
            message: $fresh
                ? 'The latest successful GA4 Reports sync completed within the expected freshness window.'
                : 'The latest successful GA4 Reports sync is older than the expected freshness window.',
            remediation: $fresh
                ? null
                : 'Inspect the GA4 Reports scheduler/queue and run the sync command to refresh snapshots.',
        );
    }

    /**
     * Asserts the configured data client can be resolved and, when enabled, can
     * attempt a tiny GA4 Data API read without exposing credential details.
     */
    public function dataClientReachabilityCheck(): DoctorCheckResultData
    {
        $config = $this->resolveConfig();

        if (! $config instanceof GA4ReportsConfigData) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports data client',
                passed: false,
                message: 'GA4 Reports settings could not be resolved, so the data client cannot be checked.',
                remediation: 'Confirm the GA4 Reports settings group is installed and migrated.',
            );
        }

        if (! $config->enabled) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports data client',
                passed: true,
                message: 'GA4 Reports is disabled; the GA4 Data API probe was skipped.',
            );
        }

        if (
            $this->missingConfigurationKeys($config) !== []
            || ! $this->credentialsFileIsReadable($config->credentialsPath)
            || ! $this->credentialsFileHasServiceAccountValues($config->credentialsPath)
        ) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports data client',
                passed: false,
                message: 'GA4 Reports is enabled but local credentials are not ready for a data-client probe.',
                remediation: 'Set the GA4 property ID and a readable service-account JSON path in the GA4 Reports settings.',
            );
        }

        try {
            /** @var GA4ReportsDataClientInterface $client */
            $client = app()->make(GA4ReportsDataClientInterface::class);
        } catch (Throwable) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports data client',
                passed: false,
                message: 'The GA4 Reports data client could not be resolved from the container.',
                remediation: 'Confirm GA4ReportsServiceProvider is loaded and binds GA4ReportsDataClientInterface.',
            );
        }

        if (! $client->isConfigured()) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports data client',
                passed: false,
                message: 'The GA4 Reports data client resolved but reports that it is not configured.',
                remediation: 'Confirm the service-account settings are loaded before the data client singleton is resolved.',
            );
        }

        try {
            $client->dailyMetrics($this->apiProbeWindow($config));
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'GA4 Reports data client',
                passed: false,
                message: $this->apiProbeFailureMessage($throwable),
                remediation: 'Confirm the service account can access the GA4 property and that Google Analytics Data API requests are not blocked or quota-limited.',
            );
        }

        return new DoctorCheckResultData(
            label: 'GA4 Reports data client',
            passed: true,
            message: 'The GA4 Reports data client is configured and accepted a read probe.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect($this->storageTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingModelClasses(): array
    {
        return array_values(collect($this->modelClasses())
            ->reject(static fn (string $modelClass): bool => is_subclass_of($modelClass, Model::class))
            ->merge(collect($this->modelClasses())
                ->reject(static fn (string $modelClass): bool => in_array($modelClass, CapellCore::getModels(), true))
                ->map(static fn (string $modelClass): string => $modelClass . ' registration'))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingStorageColumns(): array
    {
        $missingColumns = [];

        foreach ($this->requiredStorageColumns() as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($tableName, $column)) {
                    $missingColumns[] = $tableName . '.' . $column;
                }
            }
        }

        return $missingColumns;
    }

    /**
     * @return list<string>
     */
    public function missingConfigurationKeys(GA4ReportsConfigData $config): array
    {
        $missing = [];

        if ($config->propertyId === '') {
            $missing[] = 'property ID';
        }

        if ($config->credentialsPath === '') {
            $missing[] = 'credentials path';
        }

        return $missing;
    }

    public function credentialsFileIsReadable(string $credentialsPath): bool
    {
        return $credentialsPath !== '' && is_readable($credentialsPath);
    }

    public function credentialsFileHasServiceAccountValues(string $credentialsPath): bool
    {
        if (! $this->credentialsFileIsReadable($credentialsPath)) {
            return false;
        }

        try {
            $contents = file_get_contents($credentialsPath);

            if (! is_string($contents)) {
                return false;
            }

            $credentials = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return false;
        }

        if (! is_array($credentials)) {
            return false;
        }

        $clientEmail = $credentials['client_email'] ?? null;
        $privateKey = $credentials['private_key'] ?? null;

        return is_string($clientEmail)
            && trim($clientEmail) !== ''
            && is_string($privateKey)
            && trim($privateKey) !== '';
    }

    public function latestSuccessfulSyncFinishedAt(): ?CarbonImmutable
    {
        if (! Schema::hasTable((new GA4ReportsSyncRun)->getTable())) {
            return null;
        }

        $finishedAt = GA4ReportsSyncRun::query()
            ->where('status', 'succeeded')
            ->whereNotNull('finished_at')
            ->latest('finished_at')
            ->value('finished_at');

        if ($finishedAt instanceof CarbonImmutable) {
            return $finishedAt;
        }

        if ($finishedAt instanceof DateTimeInterface) {
            return CarbonImmutable::instance($finishedAt);
        }

        if (is_string($finishedAt) && $finishedAt !== '') {
            return CarbonImmutable::parse($finishedAt);
        }

        return null;
    }

    public function resolveConfig(): ?GA4ReportsConfigData
    {
        try {
            return ResolveGA4ReportsConfigAction::run();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return list<string>
     */
    private function storageTableNames(): array
    {
        return [
            (new GA4ReportsSyncRun)->getTable(),
            (new GA4ReportsDailyMetric)->getTable(),
            (new GA4ReportsPageMetric)->getTable(),
        ];
    }

    /**
     * @return list<class-string<Model>>
     */
    private function modelClasses(): array
    {
        return [
            GA4ReportsSyncRun::class,
            GA4ReportsDailyMetric::class,
            GA4ReportsPageMetric::class,
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredStorageColumns(): array
    {
        return [
            (new GA4ReportsSyncRun)->getTable() => [
                'property_id',
                'status',
                'started_at',
                'finished_at',
            ],
            (new GA4ReportsDailyMetric)->getTable() => [
                'property_id',
                'metric_date',
                'total_users',
                'sessions',
                'screen_page_views',
            ],
            (new GA4ReportsPageMetric)->getTable() => [
                'property_id',
                'metric_date',
                'page_path',
                'total_users',
                'sessions',
                'screen_page_views',
            ],
        ];
    }

    private function maxSuccessfulSyncAgeHours(): int
    {
        $configuredHours = config('capell-ga4-reports.health.max_successful_sync_age_hours', 48);

        return is_numeric($configuredHours) ? max(1, (int) $configuredHours) : 48;
    }

    private function apiProbeWindow(GA4ReportsConfigData $config): GA4ReportsWindowData
    {
        $probeDate = CarbonImmutable::instance(Date::now())->subDay()->startOfDay();

        return new GA4ReportsWindowData(
            startsAt: $probeDate,
            endsAt: $probeDate,
            propertyId: $config->propertyId,
        );
    }

    private function apiProbeFailureMessage(Throwable $throwable): string
    {
        return 'The GA4 Reports data client could not complete the read probe.';
    }
}
