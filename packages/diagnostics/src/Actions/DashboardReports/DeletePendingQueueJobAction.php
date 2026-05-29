<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Models\PendingQueueJob;
use Lorisleiva\Actions\Action;
use RuntimeException;

final class DeletePendingQueueJobAction extends Action
{
    public function handle(PendingQueueJob|int $job): int
    {
        $pendingJob = $job instanceof PendingQueueJob ? $job : PendingQueueJob::query()->find($job);

        throw_unless($pendingJob instanceof PendingQueueJob, RuntimeException::class, 'Pending queue job could not be found.');

        $id = (int) $pendingJob->getKey();
        $pendingJob->delete();

        return $id;
    }
}
