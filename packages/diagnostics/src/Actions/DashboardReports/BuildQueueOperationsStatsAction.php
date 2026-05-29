<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Data\QueueOperationsStatsData;
use Capell\Diagnostics\Models\QueueMonitor;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Action;
use Throwable;

final class BuildQueueOperationsStatsAction extends Action
{
    public function handle(?int $days = null): QueueOperationsStatsData
    {
        $days = max(1, $days ?? (int) config('capell-diagnostics.queue_monitor.trend_days', 7));
        $model = new QueueMonitor;

        if (! Schema::connection($model->getConnectionName())->hasTable($model->getTable())) {
            return new QueueOperationsStatsData(0, 0, 0, 0, 0, 0, array_fill(0, $days, 0), array_fill(0, $days, 0));
        }

        $summary = QueueMonitor::query()
            ->selectRaw('COUNT(*) as total_jobs')
            ->selectRaw('SUM(CASE WHEN finished_at IS NOT NULL AND failed = 0 THEN 1 ELSE 0 END) as succeeded_jobs')
            ->selectRaw('SUM(CASE WHEN finished_at IS NOT NULL AND failed = 1 THEN 1 ELSE 0 END) as failed_jobs')
            ->selectRaw('SUM(CASE WHEN finished_at IS NULL THEN 1 ELSE 0 END) as running_jobs')
            ->selectRaw($this->averageRuntimeExpression($model) . ' as average_runtime_seconds')
            ->first();

        return new QueueOperationsStatsData(
            totalJobs: (int) ($summary?->getAttribute('total_jobs') ?? 0),
            succeededJobs: (int) ($summary?->getAttribute('succeeded_jobs') ?? 0),
            failedJobs: (int) ($summary?->getAttribute('failed_jobs') ?? 0),
            runningJobs: (int) ($summary?->getAttribute('running_jobs') ?? 0),
            pendingJobs: $this->pendingJobs(),
            averageRuntimeSeconds: (int) ceil((float) ($summary?->getAttribute('average_runtime_seconds') ?? 0)),
            dailyTotals: $this->dailyTrend($days, 'total'),
            dailyFailures: $this->dailyTrend($days, 'failed'),
        );
    }

    private function averageRuntimeExpression(QueueMonitor $model): string
    {
        return match ($model->getConnection()->getDriverName()) {
            'pgsql' => 'AVG(EXTRACT(EPOCH FROM finished_at) - EXTRACT(EPOCH FROM started_at))',
            'sqlite' => "AVG(strftime('%s', finished_at) - strftime('%s', started_at))",
            default => 'AVG(TIMESTAMPDIFF(SECOND, started_at, finished_at))',
        };
    }

    /**
     * @return array<int, int>
     */
    private function dailyTrend(int $days, string $mode): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $dateExpression = 'DATE(created_at)';
        $valueExpression = $mode === 'failed'
            ? 'SUM(CASE WHEN finished_at IS NOT NULL AND failed = 1 THEN 1 ELSE 0 END)'
            : 'COUNT(*)';

        $rows = QueueMonitor::query()
            ->where('created_at', '>=', $start)
            ->selectRaw($dateExpression . ' as monitor_date')
            ->selectRaw($valueExpression . ' as monitor_count')
            ->groupByRaw($dateExpression)
            ->pluck('monitor_count', 'monitor_date');

        $trend = [];

        for ($dayOffset = $days - 1; $dayOffset >= 0; $dayOffset--) {
            $date = now()->subDays($dayOffset)->toDateString();
            $trend[] = (int) ($rows[$date] ?? 0);
        }

        return $trend;
    }

    private function pendingJobs(): int
    {
        $total = 0;

        foreach (DiscoverQueueMonitorQueuesAction::run() as $queue) {
            try {
                $total += Queue::size($queue);
            } catch (Throwable) {
                continue;
            }
        }

        return $total;
    }
}
