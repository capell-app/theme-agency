<?php

declare(strict_types=1);

namespace Capell\UrlManager\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class UrlManagerHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_TABLES = [
        'url_manager_redirect_rules',
        'url_manager_redirect_hits',
        'url_manager_not_found_opportunities',
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
            $check->actionClassesCheck(),
            $check->providerMetadataCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts every redirect and 404-opportunity storage table exists.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'URL Manager redirect tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'Redirect rules, redirect hits, and not-found opportunity tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the URL Manager redirect tables.',
        );
    }

    /**
     * Asserts manifest-declared domain actions are autoloadable.
     */
    public function actionClassesCheck(): DoctorCheckResultData
    {
        $missingActions = $this->missingActionClasses();

        return new DoctorCheckResultData(
            label: 'URL Manager action classes',
            passed: $missingActions === [],
            message: $missingActions === []
                ? 'All manifest-declared URL Manager actions are autoloadable.'
                : 'Missing action classes: ' . implode(', ', $missingActions) . '.',
            remediation: $missingActions === []
                ? null
                : 'Check capell.json actions and the URL Manager PSR-4 autoload mapping.',
        );
    }

    /**
     * Asserts provider metadata and required-table metadata are discoverable.
     */
    public function providerMetadataCheck(): DoctorCheckResultData
    {
        $missingProviders = $this->missingProviderClasses();
        $manifestTables = $this->manifestRequiredTables();
        $missingTableMetadata = array_values(array_diff(self::REQUIRED_TABLES, $manifestTables));
        $passed = $missingProviders === [] && $missingTableMetadata === [];

        return new DoctorCheckResultData(
            label: 'URL Manager provider metadata',
            passed: $passed,
            message: $passed
                ? 'Provider classes and required redirect table metadata are declared in capell.json.'
                : 'Missing providers or required table metadata: ' . implode(', ', [
                    ...$missingProviders,
                    ...$missingTableMetadata,
                ]) . '.',
            remediation: $passed
                ? null
                : 'Restore URL Manager provider entries and requiredTables in capell.json.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        $missingTables = [];

        foreach (self::REQUIRED_TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                $missingTables[] = $tableName;
            }
        }

        return $missingTables;
    }

    /**
     * @return list<string>
     */
    public function missingActionClasses(): array
    {
        $actions = $this->manifest()['actions'] ?? [];

        if (! is_array($actions)) {
            return [];
        }

        $missingActions = [];

        foreach ($actions as $actionClass) {
            if (is_string($actionClass) && ! class_exists($actionClass)) {
                $missingActions[] = $actionClass;
            }
        }

        return $missingActions;
    }

    /**
     * @return list<string>
     */
    public function missingProviderClasses(): array
    {
        $providers = $this->manifest()['providers'] ?? [];

        if (! is_array($providers)) {
            return [];
        }

        $missingProviders = [];

        foreach (['runtime', 'admin'] as $providerGroup) {
            $providerClasses = $providers[$providerGroup] ?? [];

            if (! is_array($providerClasses)) {
                continue;
            }

            foreach ($providerClasses as $providerClass) {
                if (is_string($providerClass) && ! class_exists($providerClass)) {
                    $missingProviders[] = $providerClass;
                }
            }
        }

        return array_values(array_unique($missingProviders));
    }

    /**
     * @return list<string>
     */
    public function manifestRequiredTables(): array
    {
        $tables = $this->manifest()['database']['requiredTables'] ?? [];

        if (! is_array($tables)) {
            return [];
        }

        return array_values(array_filter($tables, is_string(...)));
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(): array
    {
        $manifestPath = dirname(__DIR__, 2) . '/capell.json';

        try {
            $contents = file_get_contents($manifestPath);

            if ($contents === false) {
                return [];
            }

            $manifest = json_decode($contents, associative: true, flags: JSON_THROW_ON_ERROR);

            return is_array($manifest) ? $manifest : [];
        } catch (Throwable) {
            return [];
        }
    }
}
