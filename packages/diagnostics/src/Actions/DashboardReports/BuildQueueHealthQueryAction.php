<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Models\FailedJob;
use Capell\Diagnostics\Models\PendingQueueJob;
use Capell\Diagnostics\Models\QueueMonitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Action;

final class BuildQueueHealthQueryAction extends Action
{
    /**
     * @return Builder<Model>
     */
    public function handle(?string $activeTab = null): Builder
    {
        return match ($activeTab) {
            'failed' => $this->failedJobsQuery(),
            'pending' => $this->pendingJobsQuery(),
            default => $this->monitorHistoryQuery(),
        };
    }

    /**
     * @return Builder<Model>
     */
    private function monitorHistoryQuery(): Builder
    {
        $model = new QueueMonitor;

        return QueueMonitor::query()
            ->when(
                Schema::connection($model->getConnectionName())->hasTable($model->getTable()) === false,
                fn (Builder $query): Builder => $query->whereRaw('1 = 0'),
            )
            ->latest('started_at');
    }

    /**
     * @return Builder<Model>
     */
    private function failedJobsQuery(): Builder
    {
        $model = new FailedJob;

        if (! Schema::connection($model->getConnectionName())->hasTable($model->getTable())) {
            return $this->monitorHistoryQuery()->whereRaw('1 = 0');
        }

        return FailedJob::query()->latest('failed_at');
    }

    /**
     * @return Builder<Model>
     */
    private function pendingJobsQuery(): Builder
    {
        $model = new PendingQueueJob;

        if (
            config('queue.default') !== 'database'
            || ! (bool) config('capell-diagnostics.queue_monitor.pending_jobs_enabled', true)
            || ! Schema::connection($model->getConnectionName())->hasTable($model->getTable())
        ) {
            return $this->monitorHistoryQuery()->whereRaw('1 = 0');
        }

        return PendingQueueJob::query()
            ->forConfiguredQueues(DiscoverQueueMonitorQueuesAction::run())
            ->latest('created_at');
    }
}
