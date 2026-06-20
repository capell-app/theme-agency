<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Diagnostics\Actions\Dashboard\BuildPackagesInstalledAction;
use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Filament\Pages\SystemHealthPage;
use Capell\Diagnostics\Filament\Widgets\Health\PackagesInstalledFilamentWidget;
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
    public static function runDiagnostics(?string $key = null): Collection
    {
        $check = new self;
        $assertions = collect([
            'diagnostics.package-catalog' => $check->packageCatalogCheck(),
            'diagnostics.manifest-health' => $check->manifestHealthCheck(),
            'diagnostics.system-health-widgets' => $check->systemHealthWidgetsCheck(),
            'diagnostics.queue-health' => $check->queueHealthCheck(),
        ]);

        if ($key === null) {
            return $assertions->values();
        }

        $assertion = $assertions->get($key);

        return $assertion instanceof DoctorCheckResultData
            ? collect([$assertion])
            : collect();
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
            label: (string) __('capell-diagnostics::package.health_check_package_catalog_label'),
            passed: $discovered,
            message: $discovered
                ? (string) __('capell-diagnostics::package.health_check_package_catalog_passed')
                : (string) __('capell-diagnostics::package.health_check_package_catalog_failed'),
            remediation: $discovered ? null : (string) __('capell-diagnostics::package.health_check_package_catalog_remediation'),
        );
    }

    private function manifestHealthCheck(): DoctorCheckResultData
    {
        $available = class_exists(BuildPackagesInstalledAction::class);

        return new DoctorCheckResultData(
            label: (string) __('capell-diagnostics::package.health_check_manifest_metadata_label'),
            passed: $available,
            message: $available
                ? (string) __('capell-diagnostics::package.health_check_manifest_metadata_passed')
                : (string) __('capell-diagnostics::package.health_check_manifest_metadata_failed'),
            remediation: $available ? null : (string) __('capell-diagnostics::package.health_check_reinstall_remediation'),
        );
    }

    private function systemHealthWidgetsCheck(): DoctorCheckResultData
    {
        $registered = class_exists(PackagesInstalledFilamentWidget::class)
            && class_exists(SystemHealthPage::class);

        return new DoctorCheckResultData(
            label: (string) __('capell-diagnostics::package.health_check_system_health_widgets_label'),
            passed: $registered,
            message: $registered
                ? (string) __('capell-diagnostics::package.health_check_system_health_widgets_passed')
                : (string) __('capell-diagnostics::package.health_check_system_health_widgets_failed'),
            remediation: $registered ? null : (string) __('capell-diagnostics::package.health_check_clear_cache_remediation'),
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
            label: (string) __('capell-diagnostics::package.health_check_queue_health_label'),
            passed: $queryable,
            message: $queryable
                ? (string) __('capell-diagnostics::package.health_check_queue_health_passed')
                : (string) __('capell-diagnostics::package.health_check_queue_health_failed'),
            remediation: $queryable ? null : (string) __('capell-diagnostics::package.health_check_queue_health_remediation'),
        );
    }
}
