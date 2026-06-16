<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Providers;

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Contracts\DashboardSettingsContributor;
use Capell\Admin\Contracts\Extenders\PageTableExtender;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\Dashboard\NullContentHealthDataProvider;
use Capell\Core\Facades\CapellCore;
use Capell\DashboardReports\Filament\Extenders\DashboardReportsPageTableExtender;
use Capell\DashboardReports\Filament\Settings\Contributors\DashboardReportsDashboardSettingsContributor;
use Capell\DashboardReports\Support\Dashboard\DashboardReportsContentHealthDataProvider;
use Capell\DashboardReports\Support\Dashboard\DashboardReportWidgetRegistry;
use Illuminate\Support\ServiceProvider;

final class AdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerDashboardDataProviders()
            ->registerPageTableExtender()
            ->registerDashboardSettingsContributor()
            ->registerDashboardWidgets();
    }

    private function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(DashboardReportsServiceProvider::$packageName);
    }

    private function registerDashboardDataProviders(): self
    {
        if (! $this->app->bound(ContentHealthDataProvider::class)) {
            $this->app->singleton(ContentHealthDataProvider::class, DashboardReportsContentHealthDataProvider::class);

            return $this;
        }

        $contentHealthDataProvider = $this->app->make(ContentHealthDataProvider::class);

        if ($contentHealthDataProvider instanceof NullContentHealthDataProvider) {
            $this->app->forgetInstance(ContentHealthDataProvider::class);
            $this->app->singleton(ContentHealthDataProvider::class, DashboardReportsContentHealthDataProvider::class);
        }

        return $this;
    }

    private function registerDashboardSettingsContributor(): self
    {
        $this->app->tag([DashboardReportsDashboardSettingsContributor::class], DashboardSettingsContributor::TAG);

        return $this;
    }

    private function registerPageTableExtender(): self
    {
        $this->app->tag([DashboardReportsPageTableExtender::class], PageTableExtender::TAG);

        return $this;
    }

    private function registerDashboardWidgets(): self
    {
        app(DashboardReportWidgetRegistry::class)
            ->registrations()
            ->each(function (array $registration): void {
                CapellAdmin::registerDashboardWidget($registration['widget'], ...$registration['dashboards']);
            });

        return $this;
    }
}
