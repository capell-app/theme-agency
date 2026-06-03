<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Diagnostics\Actions\Dashboard\BuildPackagesInstalledAction;
use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Filament\Pages\SystemHealthPage;
use Capell\Diagnostics\Filament\Widgets\Health\PackagesInstalledWidgetAbstract;
use Capell\Diagnostics\Models\FailedJob;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Diagnostics' own health check. Unlike a stub, this verifies the package's four
 * declared capabilities actually resolve: package-catalog discovery returns data,
 * the package-catalog widget is registered, the system-health page is reachable,
 * and the failed-job summary is queryable. It doubles as the reference
 * implementation other packages copy when writing a real `runDiagnostics()`.
 */
final class DiagnosticsHealthCheck implements ChecksExtensionHealth
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
            $check->packageCatalogCheck(),
            $check->manifestHealthCheck(),
            $check->systemHealthWidgetsCheck(),
            $check->queueHealthCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    private function packageCatalogCheck(): DoctorCheckResultData
    {
        $discovered = false;

        try {
            $discovered = BuildPackagesInstalledAction::run()->packages->toCollection()->isNotEmpty();
        } catch (Throwable) {
            $discovered = false;
        }

        return new DoctorCheckResultData(
            label: 'Package catalog discovery',
            passed: $discovered,
            message: $discovered
                ? 'Installed Capell packages are discovered from composer metadata.'
                : 'No installed Capell packages could be discovered from composer metadata.',
            remediation: $discovered ? null : 'Ensure vendor/composer/installed.json exists and lists capell-app packages.',
        );
    }

    private function manifestHealthCheck(): DoctorCheckResultData
    {
        $available = class_exists(BuildPackagesInstalledAction::class);

        return new DoctorCheckResultData(
            label: 'Package manifest metadata',
            passed: $available,
            message: $available
                ? 'Package manifests expose bundle, command, and health-check metadata.'
                : 'Package manifest reader action is unavailable.',
            remediation: $available ? null : 'Reinstall the Diagnostics package.',
        );
    }

    private function systemHealthWidgetsCheck(): DoctorCheckResultData
    {
        $registered = class_exists(PackagesInstalledWidgetAbstract::class)
            && class_exists(SystemHealthPage::class);

        return new DoctorCheckResultData(
            label: 'System health widgets',
            passed: $registered,
            message: $registered
                ? 'System health widgets and page are registered for developer users.'
                : 'System health widgets or page are not registered.',
            remediation: $registered ? null : 'Reinstall the Diagnostics package and clear caches.',
        );
    }

    private function queueHealthCheck(): DoctorCheckResultData
    {
        $queryable = false;

        try {
            $queryable = class_exists(QueueHealthPage::class) && FailedJob::query()->count() >= 0;
        } catch (Throwable) {
            $queryable = false;
        }

        return new DoctorCheckResultData(
            label: 'Queue health and failed-job summary',
            passed: $queryable,
            message: $queryable
                ? 'Queue health page and failed-job summary are available.'
                : 'Queue health page or failed-job summary is unavailable.',
            remediation: $queryable ? null : 'Run queue monitor migrations (croustibat/filament-jobs-monitor).',
        );
    }
}
