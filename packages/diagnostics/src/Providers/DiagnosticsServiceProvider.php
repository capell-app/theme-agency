<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Providers;

use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Diagnostics\Actions\DashboardReports\DiscoverQueueMonitorQueuesAction;
use Capell\Diagnostics\Console\Commands\RunDiagnosticsHealthCommand;
use Capell\Diagnostics\Models\QueueMonitor;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use Croustibat\FilamentJobsMonitor\Models\QueueMonitor as BaseQueueMonitor;
use Spatie\LaravelPackageTools\Package;

final class DiagnosticsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-diagnostics';

    public static string $packageName = 'capell-app/diagnostics';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile(self::$name)
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasCommand(RunDiagnosticsHealthCommand::class)
            ->hasMigrations([
                '2026_05_10_190846_01_create_command_palette_runs_table',
                '2026_05_29_000001_create_queue_monitors_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->bind(BaseQueueMonitor::class, QueueMonitor::class);
    }

    public function packageBooted(): void
    {
        $this->configureUpstreamQueueMonitor();
    }

    private function configureUpstreamQueueMonitor(): void
    {
        // croustibat/filament-jobs-monitor remains the telemetry collector; Capell owns the Diagnostics UX.
        config([
            'filament-jobs-monitor.resources.enabled' => false,
            'filament-jobs-monitor.resources.navigation_count_badge' => false,
            'filament-jobs-monitor.resources.navigation_group' => null,
            'filament-jobs-monitor.pruning.enabled' => (bool) config('capell-diagnostics.queue_monitor.prune_enabled', true),
            'filament-jobs-monitor.pruning.retention_days' => (int) config('capell-diagnostics.queue_monitor.retention_days', 14),
            'filament-jobs-monitor.queues' => DiscoverQueueMonitorQueuesAction::run(),
        ]);

        FilamentJobsMonitorPlugin::get()
            ->enableNavigation(false)
            ->navigationCountBadge(false);
    }
}
