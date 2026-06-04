<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Health;

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Contracts\DashboardSettingsContributor;
use Capell\Admin\Contracts\Extenders\PageTableExtender;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\Dashboard\NullContentHealthDataProvider;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\DashboardReports\Filament\Extenders\DashboardReportsPageTableExtender;
use Capell\DashboardReports\Filament\Settings\Contributors\DashboardReportsDashboardSettingsContributor;
use Capell\DashboardReports\Filament\Widgets\ContentHealthWidget;
use Capell\DashboardReports\Filament\Widgets\PublishingTrendChartWidget;
use Capell\DashboardReports\Providers\DashboardReportsServiceProvider;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Collection;
use Throwable;

final class DashboardReportsHealthCheck implements ChecksExtensionHealth
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
            $check->packageInstalledCheck(),
            $check->contentHealthProviderCheck(),
            $check->dashboardWidgetsCheck(),
            $check->dashboardSettingsContributorCheck(),
            $check->pageTableFilterExtenderCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function packageInstalledCheck(): DoctorCheckResultData
    {
        $installed = CapellCore::isPackageInstalled(DashboardReportsServiceProvider::$packageName);

        return new DoctorCheckResultData(
            label: $this->translation('capell-dashboard-reports::dashboard.health_package_installed_label'),
            passed: $installed,
            message: $installed
                ? $this->translation('capell-dashboard-reports::dashboard.health_package_installed_passed')
                : $this->translation('capell-dashboard-reports::dashboard.health_package_installed_failed'),
            remediation: $installed
                ? null
                : $this->translation('capell-dashboard-reports::dashboard.health_package_installed_remediation'),
        );
    }

    public function contentHealthProviderCheck(): DoctorCheckResultData
    {
        $providerAvailable = $this->contentHealthProviderAvailable();

        return new DoctorCheckResultData(
            label: $this->translation('capell-dashboard-reports::dashboard.health_content_health_provider_label'),
            passed: $providerAvailable,
            message: $providerAvailable
                ? $this->translation('capell-dashboard-reports::dashboard.health_content_health_provider_passed')
                : $this->translation('capell-dashboard-reports::dashboard.health_content_health_provider_failed'),
            remediation: $providerAvailable
                ? null
                : $this->translation('capell-dashboard-reports::dashboard.health_content_health_provider_remediation'),
        );
    }

    public function dashboardWidgetsCheck(): DoctorCheckResultData
    {
        $missingWidgets = $this->missingDashboardWidgets();

        return new DoctorCheckResultData(
            label: $this->translation('capell-dashboard-reports::dashboard.health_dashboard_widgets_label'),
            passed: $missingWidgets === [],
            message: $missingWidgets === []
                ? $this->translation('capell-dashboard-reports::dashboard.health_dashboard_widgets_passed')
                : $this->translation('capell-dashboard-reports::dashboard.health_dashboard_widgets_failed', ['widgets' => implode(', ', $missingWidgets)]),
            remediation: $missingWidgets === []
                ? null
                : $this->translation('capell-dashboard-reports::dashboard.health_dashboard_widgets_remediation'),
        );
    }

    public function dashboardSettingsContributorCheck(): DoctorCheckResultData
    {
        $registered = $this->tagContains(DashboardSettingsContributor::TAG, DashboardReportsDashboardSettingsContributor::class);

        return new DoctorCheckResultData(
            label: $this->translation('capell-dashboard-reports::dashboard.health_dashboard_settings_contributor_label'),
            passed: $registered,
            message: $registered
                ? $this->translation('capell-dashboard-reports::dashboard.health_dashboard_settings_contributor_passed')
                : $this->translation('capell-dashboard-reports::dashboard.health_dashboard_settings_contributor_failed'),
            remediation: $registered
                ? null
                : $this->translation('capell-dashboard-reports::dashboard.health_dashboard_settings_contributor_remediation'),
        );
    }

    public function pageTableFilterExtenderCheck(): DoctorCheckResultData
    {
        $registered = $this->tagContains(PageTableExtender::TAG, DashboardReportsPageTableExtender::class);

        return new DoctorCheckResultData(
            label: $this->translation('capell-dashboard-reports::dashboard.health_page_table_filter_extender_label'),
            passed: $registered,
            message: $registered
                ? $this->translation('capell-dashboard-reports::dashboard.health_page_table_filter_extender_passed')
                : $this->translation('capell-dashboard-reports::dashboard.health_page_table_filter_extender_failed'),
            remediation: $registered
                ? null
                : $this->translation('capell-dashboard-reports::dashboard.health_page_table_filter_extender_remediation'),
        );
    }

    public function contentHealthProviderAvailable(): bool
    {
        if (! app()->bound(ContentHealthDataProvider::class)) {
            return false;
        }

        try {
            $provider = app()->make(ContentHealthDataProvider::class);

            return $this->isContentHealthProviderAvailable($provider);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    public function missingDashboardWidgets(): array
    {
        $registeredWidgets = CapellAdmin::getDashboardWidgets(DashboardEnum::Main);
        $requiredWidgets = [
            PublishingTrendChartWidget::class,
            ContentHealthWidget::class,
        ];

        return array_values(array_filter(
            $requiredWidgets,
            static fn (string $widget): bool => ! in_array($widget, $registeredWidgets, true),
        ));
    }

    private function isContentHealthProviderAvailable(ContentHealthDataProvider $provider): bool
    {
        return ! $provider instanceof NullContentHealthDataProvider;
    }

    /**
     * @param  class-string  $class
     */
    private function tagContains(string $tag, string $class): bool
    {
        return collect(app()->tagged($tag))
            ->contains(static fn (object $entry): bool => $entry instanceof $class);
    }

    /**
     * @param  array<string, string>  $replace
     */
    private function translation(string $key, array $replace = []): string
    {
        $translation = app(Translator::class)->get($key, $replace);

        return is_string($translation) ? $translation : $key;
    }
}
