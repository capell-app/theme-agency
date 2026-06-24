<?php

declare(strict_types=1);

namespace Capell\AiCreator\Health;

use Capell\AiCreator\Providers\AiCreatorServiceProvider;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class AiCreatorHealthCheck implements ChecksExtensionHealth
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
        return collect([
            self::packageInstalledCheck(),
            self::sessionTableCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()->every(
            static fn (DoctorCheckResultData $check): bool => $check->passed,
        );
    }

    private static function packageInstalledCheck(): DoctorCheckResultData
    {
        $installed = CapellCore::isPackageInstalled(AiCreatorServiceProvider::$packageName);

        return new DoctorCheckResultData(
            label: 'AI Creator package installed',
            passed: $installed,
            message: $installed
                ? 'AI Creator is marked as installed.'
                : 'AI Creator is not marked as installed.',
            remediation: $installed ? null : 'Install AI Creator before opening package surfaces.',
        );
    }

    private static function sessionTableCheck(): DoctorCheckResultData
    {
        try {
            $hasTable = Schema::hasTable('capell_ai_creator_sessions');
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'AI Creator session table',
                passed: false,
                message: 'Unable to check the AI Creator session table.',
                remediation: $throwable->getMessage(),
            );
        }

        return new DoctorCheckResultData(
            label: 'AI Creator session table',
            passed: $hasTable,
            message: $hasTable
                ? 'The AI Creator session table is present.'
                : 'The AI Creator session table is missing.',
            remediation: $hasTable ? null : 'Run host migrations through the package install flow.',
        );
    }
}
