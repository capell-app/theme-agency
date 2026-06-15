<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Manifest\ManifestValidator;
use Capell\GA4Reports\Console\Commands\SyncGA4ReportsCommand;
use Capell\GA4Reports\Contracts\GA4ReportsDataClientInterface;
use Capell\GA4Reports\Filament\Pages\GA4ReportsPage;
use Capell\GA4Reports\Filament\Settings\GA4ReportsSettingsSchema;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsOverviewStatsWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsSetupStatusWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTopPagesTableWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTopPagesWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTrafficTrendWidget;
use Capell\GA4Reports\Health\Ga4ReportsHealthCheck;
use Capell\GA4Reports\Manifest\GA4ReportsAdminPageContribution;
use Capell\GA4Reports\Manifest\GA4ReportsConsoleCommandsContribution;
use Capell\GA4Reports\Manifest\GA4ReportsDashboardWidgetsContribution;
use Capell\GA4Reports\Manifest\GA4ReportsHealthContribution;
use Capell\GA4Reports\Manifest\GA4ReportsModelsContribution;
use Capell\GA4Reports\Manifest\GA4ReportsOverviewStatsContribution;
use Capell\GA4Reports\Manifest\GA4ReportsScheduledSyncContribution;
use Capell\GA4Reports\Manifest\GA4ReportsSettingsContribution;
use Capell\GA4Reports\Models\GA4ReportsDailyMetric;
use Capell\GA4Reports\Models\GA4ReportsPageMetric;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Capell\GA4Reports\Providers\GA4ReportsServiceProvider;
use Capell\GA4Reports\Settings\GA4ReportsSettings;
use Capell\GA4Reports\Settings\GA4ReportsSettingsMigrationProvider;
use Capell\GA4Reports\Support\Insights\GA4ReportsDataClient;
use Capell\GA4Reports\Support\Insights\NullGA4ReportsDataClient;
use Capell\GA4Reports\Tests\GA4ReportsTestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(GA4ReportsTestCase::class);

it('registers package metadata when installed', function (): void {
    $package = CapellCore::getPackage(GA4ReportsServiceProvider::$packageName);

    expect($package->name)->toBe(GA4ReportsServiceProvider::$packageName)
        ->and($package->isInstalled())->toBeTrue();
});

it('creates GA4 reporting tables', function (): void {
    expect(Schema::hasTable((new GA4ReportsSyncRun)->getTable()))->toBeTrue()
        ->and(Schema::hasTable((new GA4ReportsDailyMetric)->getTable()))->toBeTrue()
        ->and(Schema::hasTable((new GA4ReportsPageMetric)->getTable()))->toBeTrue();
});

it('keeps package manifest requirements aligned with composer requirements', function (): void {
    $manifest = File::json(dirname(__DIR__, 3) . '/capell.json');
    $composer = File::json(dirname(__DIR__, 3) . '/composer.json');

    $composerPackageRequirements = array_values(array_filter(
        array_keys($composer['require'] ?? []),
        fn (int|string $packageName): bool => is_string($packageName) && str_starts_with($packageName, 'capell-app/'),
    ));

    sort($composerPackageRequirements);

    $manifestRequirements = $manifest['dependencies']['requires'] ?? [];
    sort($manifestRequirements);

    expect($composerPackageRequirements)->toBe($manifestRequirements)
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['database']['settings'])->toBeTrue()
        ->and($manifest['database']['requiredTables'])->toBe([
            'ga4_reports_sync_runs',
            'ga4_reports_daily_metrics',
            'ga4_reports_page_metrics',
        ])
        ->and($manifest['settings'])->toBe([GA4ReportsSettings::class])
        ->and($manifest['providers']['runtime'])->toContain(GA4ReportsServiceProvider::class);
});

it('declares implemented GA4 reporting contributions and validates manifest contracts', function (): void {
    $manifest = File::json(dirname(__DIR__, 3) . '/capell.json');
    $composer = File::json(dirname(__DIR__, 3) . '/composer.json');
    $contributions = collect($manifest['contributes']);

    (new ManifestValidator)->validate($manifest, $composer, 'capell-app/ga4-reports', 'ga4-reports test manifest');

    expect($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-page'
            && ($contribution['class'] ?? null) === GA4ReportsAdminPageContribution::class
            && ($contribution['pageClass'] ?? null) === GA4ReportsPage::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'dashboard-widget'
            && ($contribution['class'] ?? null) === GA4ReportsDashboardWidgetsContribution::class
            && in_array(GA4ReportsTrafficTrendWidget::class, $contribution['widgetClasses'] ?? [], true)
            && in_array(GA4ReportsTopPagesWidget::class, $contribution['widgetClasses'] ?? [], true)
            && in_array(GA4ReportsSetupStatusWidget::class, $contribution['widgetClasses'] ?? [], true)))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'dashboard-widget'
            && ($contribution['class'] ?? null) === GA4ReportsDashboardWidgetsContribution::class
            && in_array(GA4ReportsOverviewStatsWidget::class, $contribution['widgetClasses'] ?? [], true)
            && in_array(GA4ReportsTopPagesTableWidget::class, $contribution['widgetClasses'] ?? [], true)))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'overview-stat'
            && ($contribution['class'] ?? null) === GA4ReportsOverviewStatsContribution::class
            && ($contribution['keys'] ?? []) === [
                'ga4_reports_overview',
                'ga4_reports_overview.sessions',
                'ga4_reports_overview.engagement_rate',
            ]))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'model'
            && ($contribution['class'] ?? null) === GA4ReportsModelsContribution::class
            && ($contribution['modelClasses'] ?? []) === [
                GA4ReportsDailyMetric::class,
                GA4ReportsPageMetric::class,
                GA4ReportsSyncRun::class,
            ]))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'scheduled-job'
            && ($contribution['class'] ?? null) === GA4ReportsScheduledSyncContribution::class
            && ($contribution['command'] ?? null) === 'capell:ga4-reports-sync'
            && ($contribution['frequencyConfig'] ?? null) === 'capell-ga4-reports.sync_cron'))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'console-command'
            && ($contribution['class'] ?? null) === GA4ReportsConsoleCommandsContribution::class
            && in_array('capell:ga4-reports-sync', $contribution['commands'] ?? [], true)
            && in_array(SyncGA4ReportsCommand::class, $contribution['commandClasses'] ?? [], true)))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'setting'
            && ($contribution['class'] ?? null) === GA4ReportsSettingsContribution::class
            && ($contribution['settingsClass'] ?? null) === GA4ReportsSettings::class
            && ($contribution['schemaClass'] ?? null) === GA4ReportsSettingsSchema::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'health-check'
            && ($contribution['class'] ?? null) === GA4ReportsHealthContribution::class
            && ($contribution['checkClass'] ?? null) === Ga4ReportsHealthCheck::class))->toBeTrue();
});

it('exposes default settings and setting migrations', function (): void {
    $settings = new GA4ReportsSettings;
    $provider = new GA4ReportsSettingsMigrationProvider;

    expect($settings->enabled)->toBeFalse()
        ->and($settings->property_id)->toBe('')
        ->and($settings->credentials_path)->toBe('')
        ->and($settings->sync_days)->toBe(30)
        ->and($settings->sync_cron)->toBe('0 2 * * *')
        ->and($settings->route_slug)->toBe('ga4-reports')
        ->and($provider->getSettingMigrations())->toBe(['2026_05_10_190853_01_create_ga4_reports_settings']);
});

it('binds the null GA4 client until settings are configured', function (): void {
    expect(resolve(GA4ReportsDataClientInterface::class))
        ->toBeInstanceOf(NullGA4ReportsDataClient::class);

    $settings = new GA4ReportsSettings;
    $settings->enabled = true;
    $settings->property_id = '123456789';
    $settings->credentials_path = '/tmp/ga4-reports.json';
    $settings->sync_days = 30;
    $settings->sync_cron = '0 2 * * *';
    $settings->route_slug = 'ga4-reports';

    app()->instance(GA4ReportsSettings::class, $settings);
    app()->forgetInstance(GA4ReportsDataClientInterface::class);

    expect(resolve(GA4ReportsDataClientInterface::class))
        ->toBeInstanceOf(GA4ReportsDataClient::class);
});
