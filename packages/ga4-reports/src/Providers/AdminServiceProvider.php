<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Providers;

use Capell\Admin\Contracts\DashboardSettingsContributor;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\GA4Reports\Actions\BuildGA4ReportsOverviewAction;
use Capell\GA4Reports\Console\Commands\SyncGA4ReportsCommand;
use Capell\GA4Reports\Data\GA4ReportsOverviewData;
use Capell\GA4Reports\Filament\Pages\GA4ReportsPage;
use Capell\GA4Reports\Filament\Settings\Contributors\GA4ReportsDashboardSettingsContributor;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsSetupStatusWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTopPagesWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTrafficTrendWidget;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

final class AdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerDashboardSettingsContributor()
            ->registerCommands()
            ->registerPages()
            ->registerOverviewStats()
            ->registerDashboardWidgets()
            ->registerSchedule();
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(GA4ReportsServiceProvider::$packageName);
    }

    private function registerDashboardSettingsContributor(): self
    {
        $this->app->tag([GA4ReportsDashboardSettingsContributor::class], DashboardSettingsContributor::TAG);

        return $this;
    }

    private function registerCommands(): self
    {
        if (! $this->app->runningInConsole()) {
            return $this;
        }

        $this->commands([SyncGA4ReportsCommand::class]);

        return $this;
    }

    private function registerPages(): self
    {
        CapellAdmin::registerExtensionPage(GA4ReportsServiceProvider::$packageName, GA4ReportsPage::class);

        return $this;
    }

    private function registerDashboardWidgets(): self
    {
        CapellAdmin::registerDashboardWidget(GA4ReportsTrafficTrendWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardWidget(GA4ReportsTopPagesWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardWidget(GA4ReportsSetupStatusWidget::class, DashboardEnum::Main);

        return $this;
    }

    private function registerOverviewStats(): self
    {
        CapellAdmin::registerOverviewStat(
            key: 'ga4_reports_overview',
            label: fn (): string => __('capell-ga4-reports::widgets.screen_page_views'),
            value: fn (): int => $this->ga4Overview()->screenPageViews,
            group: fn (): string => __('capell-ga4-reports::settings.fieldset'),
            sort: 120,
            settingsLabel: fn (): string => __('capell-ga4-reports::widgets.overview'),
        );

        CapellAdmin::registerOverviewStat(
            key: 'ga4_reports_overview.sessions',
            label: fn (): string => __('capell-ga4-reports::widgets.sessions'),
            value: fn (): int => $this->ga4Overview()->sessions,
            group: fn (): string => __('capell-ga4-reports::settings.fieldset'),
            sort: 121,
            settingsKey: 'ga4_reports_overview',
            settingsLabel: fn (): string => __('capell-ga4-reports::widgets.overview'),
        );

        CapellAdmin::registerOverviewStat(
            key: 'ga4_reports_overview.engagement_rate',
            label: fn (): string => __('capell-ga4-reports::widgets.engagement_rate'),
            value: fn (): string => number_format($this->ga4Overview()->engagementRate * 100, 1) . '%',
            group: fn (): string => __('capell-ga4-reports::settings.fieldset'),
            sort: 122,
            settingsKey: 'ga4_reports_overview',
            settingsLabel: fn (): string => __('capell-ga4-reports::widgets.overview'),
        );

        return $this;
    }

    private function ga4Overview(): GA4ReportsOverviewData
    {
        static $overview = null;

        if ($overview instanceof GA4ReportsOverviewData) {
            return $overview;
        }

        $overview = BuildGA4ReportsOverviewAction::run();

        return $overview;
    }

    private function registerSchedule(): self
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('ga4-reports:sync')->daily();
        });

        return $this;
    }
}
