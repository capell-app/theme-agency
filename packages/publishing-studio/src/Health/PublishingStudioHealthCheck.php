<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PublishingStudio\Checks\PublishCheck;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

final class PublishingStudioHealthCheck implements ChecksExtensionHealth
{
    /**
     * Storage tables that must exist for the publishing workflow,
     * versioning, and durable scheduler to operate.
     *
     * @var list<string>
     */
    private const array REQUIRED_TABLES = [
        'workspaces',
        'publishing_revisions',
        'publishing_scheduler_events',
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
            $check->publishChecksResolvableCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts every workflow, versioning, and scheduler storage table exists.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'Publishing Studio storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'Workspace, revision, and scheduler tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the Publishing Studio storage tables.',
        );
    }

    /**
     * Asserts at least one publish-readiness check is configured and resolvable,
     * so the advertised publish gating is not a silent no-op.
     */
    public function publishChecksResolvableCheck(): DoctorCheckResultData
    {
        $resolvableCheckCount = $this->resolvablePublishCheckCount();

        return new DoctorCheckResultData(
            label: 'Publishing Studio publish-readiness checks',
            passed: $resolvableCheckCount > 0,
            message: $resolvableCheckCount > 0
                ? $resolvableCheckCount . ' publish-readiness check(s) are configured and resolvable.'
                : 'No publish-readiness checks are configured; publishes run without readiness gating.',
            remediation: $resolvableCheckCount > 0
                ? null
                : 'Populate capell.publishing-studio.publish_checks with one or more PublishCheck classes.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return collect(self::REQUIRED_TABLES)
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all();
    }

    /**
     * Counts the configured publish checks that resolve to a real
     * {@see PublishCheck} implementation.
     */
    public function resolvablePublishCheckCount(): int
    {
        $checkClasses = Config::array('capell.publishing-studio.publish_checks', []);

        return collect($checkClasses)
            ->filter(static fn (mixed $checkClass): bool => is_string($checkClass)
                && class_exists($checkClass)
                && is_subclass_of($checkClass, PublishCheck::class))
            ->count();
    }
}
