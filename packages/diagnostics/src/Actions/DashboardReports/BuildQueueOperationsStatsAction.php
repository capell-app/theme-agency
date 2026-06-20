<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Data\QueueOperationsStatsData;
use Capell\Diagnostics\Models\PendingQueueJob;
use Capell\Diagnostics\Models\QueueMonitor;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Action;
use Throwable;

/**
 * @method static QueueOperationsStatsData run(?int $days = null)
 */
final class BuildQueueOperationsStatsAction extends Action
{
    public function handle(?int $days = null): QueueOperationsStatsData
    {
        $configuredTrendDays = config('capell-diagnostics.queue_monitor.trend_days', 7);
        $days = max(1, $days ?? (is_numeric($configuredTrendDays) ? (int) $configuredTrendDays : 7));
        $model = new QueueMonitor;

        if (! Schema::connection($model->getConnectionName())->hasTable($model->getTable())) {
            return new QueueOperationsStatsData(0, 0, 0, 0, 0, null, 'idle', 0, array_fill(0, $days, 0), array_fill(0, $days, 0));
        }

        $summary = QueueMonitor::query()
            ->selectRaw('COUNT(*) as total_jobs')
            ->selectRaw('SUM(CASE WHEN finished_at IS NOT NULL AND failed = 0 THEN 1 ELSE 0 END) as succeeded_jobs')
            ->selectRaw('SUM(CASE WHEN finished_at IS NOT NULL AND failed = 1 THEN 1 ELSE 0 END) as failed_jobs')
            ->selectRaw('SUM(CASE WHEN finished_at IS NULL THEN 1 ELSE 0 END) as running_jobs')
            ->selectRaw($this->averageRuntimeExpression($model) . ' as average_runtime_seconds')
            ->first();

        $runningJobs = (int) ($summary?->getAttribute('running_jobs') ?? 0);
        $pendingStats = $this->pendingJobStats();

        return new QueueOperationsStatsData(
            totalJobs: (int) ($summary?->getAttribute('total_jobs') ?? 0),
            succeededJobs: (int) ($summary?->getAttribute('succeeded_jobs') ?? 0),
            failedJobs: (int) ($summary?->getAttribute('failed_jobs') ?? 0),
            runningJobs: $runningJobs,
            pendingJobs: $pendingStats['count'],
            oldestPendingJobAgeSeconds: $pendingStats['oldest_age_seconds'],
            queueLivenessStatus: $this->queueLivenessStatus($runningJobs, $pendingStats['count'], $pendingStats['oldest_age_seconds']),
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

    /**
     * @return array{count: int, oldest_age_seconds: int|null}
     */
    private function pendingJobStats(): array
    {
        $pendingModel = new PendingQueueJob;
        $connection = $pendingModel->getConnectionName();
        $table = $pendingModel->getTable();
        $queues = DiscoverQueueMonitorQueuesAction::run();

        if (Schema::connection($connection)->hasTable($table)) {
            $query = PendingQueueJob::query()
                ->forConfiguredQueues($queues)
                ->whereNull('reserved_at');

            $oldestCreatedAt = (clone $query)->min('created_at');

            return [
                'count' => (int) $query->count(),
                'oldest_age_seconds' => $this->ageInSeconds($oldestCreatedAt),
            ];
        }

        return [
            'count' => $this->pendingJobsFromQueueSize($queues),
            'oldest_age_seconds' => null,
        ];
    }

    /**
     * @param  list<string>  $queues
     */
    private function pendingJobsFromQueueSize(array $queues): int
    {
        $total = 0;

        foreach ($queues as $queue) {
            try {
                $total += Queue::size($queue);
            } catch (Throwable) {
                continue;
            }
        }

        return $total;
    }

    private function queueLivenessStatus(int $runningJobs, int $pendingJobs, ?int $oldestPendingJobAgeSeconds): string
    {
        if ($runningJobs > 0) {
            return 'active';
        }

        if ($pendingJobs === 0) {
            return 'idle';
        }

        $configuredStaleSeconds = config('capell-diagnostics.queue_monitor.stale_pending_seconds', 300);
        $staleAfterSeconds = max(1, is_numeric($configuredStaleSeconds) ? (int) $configuredStaleSeconds : 300);

        return $oldestPendingJobAgeSeconds !== null && $oldestPendingJobAgeSeconds >= $staleAfterSeconds
            ? 'stale'
            : 'waiting';
    }

    private function ageInSeconds(mixed $value): ?int
    {
        if (is_numeric($value)) {
            return max(0, now()->getTimestamp() - (int) $value);
        }

        if ($value instanceof CarbonInterface) {
            return max(0, (int) now()->diffInSeconds($value, true));
        }

        if (is_string($value) && trim($value) !== '') {
            return max(0, (int) now()->diffInSeconds(CarbonImmutable::parse($value), true));
        }

        return null;
    }
}
