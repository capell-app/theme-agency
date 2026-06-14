<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Capell\GA4Reports\Actions\ResolveGA4ReportsConfigAction;
use Capell\GA4Reports\Contracts\GA4ReportsDataClientInterface;
use Capell\GA4Reports\Filament\Settings\GA4ReportsSettingsSchema;
use Capell\GA4Reports\Models\GA4ReportsDailyMetric;
use Capell\GA4Reports\Models\GA4ReportsPageMetric;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Capell\GA4Reports\Settings\GA4ReportsSettings;
use Capell\GA4Reports\Settings\GA4ReportsSettingsMigrationProvider;
use Capell\GA4Reports\Support\Insights\GA4ReportsDataClient;
use Capell\GA4Reports\Support\Insights\NullGA4ReportsDataClient;
use Filament\Support\Icons\Heroicon;
use Override;
use Spatie\LaravelPackageTools\Package;

final class GA4ReportsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-ga4-reports';

    public static string $packageName = 'capell-app/ga4-reports';

    private bool $packageSurfacesRegistered = false;

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-ga4-reports')
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasMigrations([
                '2026_05_10_190852_03_create_ga4_reports_sync_runs_table',
                '2026_05_10_190852_01_create_ga4_reports_daily_metrics_table',
                '2026_05_10_190852_02_create_ga4_reports_page_metrics_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->register(AdminServiceProvider::class);
    }

    public function packageRegistered(): void
    {
        $this
            ->registerSettingsMigrations()
            ->bindGA4ReportsClient();

        $this->registerInstalledPackageSurfacesWhenReady();
    }

    public function packageBooted(): void
    {
        $this->registerInstalledPackageSurfaces();

        if (! $this->isPackageInstalled() || ! $this->app->runningInConsole()) {
            return;
        }

        /** @var GA4ReportsSettingsMigrationProvider $provider */
        $provider = $this->app->make(GA4ReportsSettingsMigrationProvider::class);

        $this->publishes([
            $provider->path() . '/2026_05_10_190853_01_create_ga4_reports_settings.php' => database_path('settings/2026_05_10_190853_01_create_ga4_reports_settings.php'),
        ], 'capell-ga4-reports-settings');
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            GA4ReportsSyncRun::class,
            GA4ReportsDailyMetric::class,
            GA4ReportsPageMetric::class,
        ]);

        return $this;
    }

    private function registerInstalledPackageSurfacesWhenReady(): void
    {
        if ($this->app->isBooted()) {
            $this->registerInstalledPackageSurfaces();

            return;
        }

        $this->app->booting(function (): void {
            $this->registerInstalledPackageSurfaces();
        });

        $this->app->booted(function (): void {
            $this->registerInstalledPackageSurfaces();
        });
    }

    private function registerInstalledPackageSurfaces(): void
    {
        if ($this->packageSurfacesRegistered || ! $this->isPackageInstalled()) {
            return;
        }

        $this->packageSurfacesRegistered = true;

        $this
            ->registerModels()
            ->registerSettings()
            ->registerProtectedTables();
    }

    private function registerSettings(): self
    {
        $this->surface()->settingsClass('ga4_reports', GA4ReportsSettings::class);
        $this->surface()->settingsMetadata(new SettingsGroupMetadata(
            group: 'ga4_reports',
            label: 'capell-ga4-reports::settings.title',
            icon: Heroicon::OutlinedChartBarSquare,
            navigationGroup: 'capell-admin::navigation.group_monitoring',
            navigationSort: 90,
            packageName: self::$packageName,
        ));
        $this->surface()->settingsSchema('ga4_reports', GA4ReportsSettingsSchema::class);

        return $this;
    }

    private function registerSettingsMigrations(): self
    {
        $this->app->singleton(GA4ReportsSettingsMigrationProvider::class);

        return $this;
    }

    private function bindGA4ReportsClient(): self
    {
        $this->app->singleton(GA4ReportsDataClientInterface::class, function (): GA4ReportsDataClientInterface {
            $resolvedConfig = ResolveGA4ReportsConfigAction::run();

            if (! $resolvedConfig->enabled || $resolvedConfig->propertyId === '' || $resolvedConfig->credentialsPath === '') {
                return new NullGA4ReportsDataClient;
            }

            return new GA4ReportsDataClient([
                'enabled' => $resolvedConfig->enabled,
                'property_id' => $resolvedConfig->propertyId,
                'credentials_path' => $resolvedConfig->credentialsPath,
                'http_timeout' => config('capell-ga4-reports.http_timeout', 20),
                'http_retry_times' => config('capell-ga4-reports.http_retry_times', 3),
                'http_retry_delay_ms' => config('capell-ga4-reports.http_retry_delay_ms', 250),
                'http_retry_max_delay_ms' => config('capell-ga4-reports.http_retry_max_delay_ms', 5000),
            ]);
        });

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable(fn (): string => config('capell-ga4-reports.tables.sync_runs', 'ga4_reports_sync_runs'));
        CapellCore::registerProtectedTable(fn (): string => config('capell-ga4-reports.tables.daily_metrics', 'ga4_reports_daily_metrics'));
        CapellCore::registerProtectedTable(fn (): string => config('capell-ga4-reports.tables.page_metrics', 'ga4_reports_page_metrics'));

        return $this;
    }
}
