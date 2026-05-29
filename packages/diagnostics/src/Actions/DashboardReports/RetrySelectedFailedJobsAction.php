<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Models\FailedJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Action;
use RuntimeException;

final class RetrySelectedFailedJobsAction extends Action
{
    /**
     * @param  Collection<int, FailedJob>|array<int, FailedJob|string>|string  $jobs
     * @return list<string>
     */
    public function handle(Collection|array|string $jobs): array
    {
        $uuids = collect(is_string($jobs) ? [$jobs] : $jobs)
            ->map(static fn (FailedJob|string $job): ?string => $job instanceof FailedJob ? $job->uuid : $job)
            ->filter(static fn (?string $uuid): bool => is_string($uuid) && $uuid !== '')
            ->values()
            ->all();

        if ($uuids === []) {
            throw new RuntimeException('No failed jobs were selected for retry.');
        }

        if (! Schema::connection((new FailedJob)->getConnectionName())->hasTable((new FailedJob)->getTable())) {
            throw new RuntimeException('One or more failed jobs could not be found.');
        }

        $existing = FailedJob::query()
            ->whereIn('uuid', $uuids)
            ->pluck('uuid')
            ->all();

        if (count($existing) !== count($uuids)) {
            throw new RuntimeException('One or more failed jobs could not be found.');
        }

        Artisan::call('queue:retry', ['id' => $uuids]);

        return array_values($existing);
    }
}
