<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\GA4Reports\Actions\ResolveGA4ReportsConfigAction;
use Capell\GA4Reports\Data\GA4ReportsConfigData;
use Capell\GA4Reports\Models\GA4ReportsDailyMetric;
use Capell\GA4Reports\Models\GA4ReportsPageMetric;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Illuminate\Support\Collection;
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
            $check->configurationCheck(),
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
     * Asserts the integration is configured enough to sync, without disclosing
     * the GA4 property ID or the service-account credentials path.
     */
    public function configurationCheck(): DoctorCheckResultData
    {
        $config = $this->resolveConfig();

        if ($config === null) {
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
                remediation: null,
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

        return new DoctorCheckResultData(
            label: 'GA4 Reports configuration',
            passed: true,
            message: 'GA4 Reports is enabled with a property ID and a readable service-account credentials file.',
            remediation: null,
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
}
