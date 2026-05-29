<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Models\QueueMonitor;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Action;

final class PruneQueueMonitorsAction extends Action
{
    public function handle(?int $retentionDays = null): int
    {
        $model = new QueueMonitor;

        if (! Schema::connection($model->getConnectionName())->hasTable($model->getTable())) {
            return 0;
        }

        $retentionDays = max(1, $retentionDays ?? (int) config('capell-diagnostics.queue_monitor.retention_days', 14));

        return QueueMonitor::query()
            ->where('created_at', '<', now()->subDays($retentionDays))
            ->delete();
    }
}
