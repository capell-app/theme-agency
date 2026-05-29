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

        throw_if(! is_string($uuid) || $uuid === '', RuntimeException::class, 'Failed job UUID is missing.');

        throw_unless(Schema::connection((new FailedJob)->getConnectionName())->hasTable((new FailedJob)->getTable()), RuntimeException::class, 'Failed job could not be found.');

        throw_unless(FailedJob::query()->where('uuid', $uuid)->exists(), RuntimeException::class, 'Failed job could not be found.');

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return $uuid;
    }
}
