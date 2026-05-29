<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Models\FailedJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Action;
use RuntimeException;

final class RetryFailedJobAction extends Action
{
    public function handle(FailedJob|string $job): string
    {
        $uuid = $job instanceof FailedJob ? $job->uuid : $job;

        if (! is_string($uuid) || $uuid === '') {
            throw new RuntimeException('Failed job UUID is missing.');
        }

        if (! Schema::connection((new FailedJob)->getConnectionName())->hasTable((new FailedJob)->getTable())) {
            throw new RuntimeException('Failed job could not be found.');
        }

        if (! FailedJob::query()->where('uuid', $uuid)->exists()) {
            throw new RuntimeException('Failed job could not be found.');
        }

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return $uuid;
    }
}
