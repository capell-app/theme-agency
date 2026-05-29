<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets;

use Capell\Diagnostics\Actions\DashboardReports\BuildQueueOperationsStatsAction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Override;

final class QueueOperationsStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected int|array|null $columns = [
        'default' => 2,
        'lg' => 4,
    ];

    /**
     * @return array<int, Stat>
     */
    #[Override]
    protected function getStats(): array
    {
        $stats = BuildQueueOperationsStatsAction::run();

        return [
            Stat::make(__('capell-diagnostics::package.queue_operations_total_jobs'), Number::format($stats->totalJobs))
                ->chart($stats->dailyTotals)
                ->color('primary'),
            Stat::make(__('capell-diagnostics::package.queue_operations_succeeded_jobs'), Number::format($stats->succeededJobs))
                ->color('success'),
            Stat::make(__('capell-diagnostics::package.queue_operations_failed_jobs'), Number::format($stats->failedJobs))
                ->chart($stats->dailyFailures)
                ->color($stats->failedJobs > 0 ? 'danger' : 'gray'),
            Stat::make(__('capell-diagnostics::package.queue_operations_pending_jobs'), Number::format($stats->pendingJobs))
                ->description(__('capell-diagnostics::package.queue_operations_average_runtime', [
                    'seconds' => Number::format($stats->averageRuntimeSeconds),
                ]))
                ->color($stats->runningJobs > 0 ? 'warning' : 'gray'),
        ];
    }
}
