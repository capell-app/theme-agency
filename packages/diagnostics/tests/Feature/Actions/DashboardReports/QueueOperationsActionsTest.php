<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\DashboardReports\BuildQueueOperationsStatsAction;
use Capell\Diagnostics\Actions\DashboardReports\DeletePendingQueueJobAction;
use Capell\Diagnostics\Actions\DashboardReports\DiscoverQueueMonitorQueuesAction;
use Capell\Diagnostics\Actions\DashboardReports\PruneQueueMonitorsAction;
use Capell\Diagnostics\Actions\DashboardReports\RetryFailedJobAction;
use Capell\Diagnostics\Actions\DashboardReports\RetrySelectedFailedJobsAction;
use Capell\Diagnostics\Models\FailedJob;
use Capell\Diagnostics\Models\PendingQueueJob;
use Capell\Diagnostics\Models\QueueMonitor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    $failedJobSchema = Schema::connection((new FailedJob)->getConnectionName());
    $pendingJobSchema = Schema::connection((new PendingQueueJob)->getConnectionName());

    $failedJobSchema->dropIfExists('failed_jobs');
    $failedJobSchema->create('failed_jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('uuid')->unique();
        $table->text('connection');
        $table->text('queue');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();
    });

    $pendingJobSchema->dropIfExists('jobs');
    $pendingJobSchema->create('jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    QueueMonitor::query()->delete();
});

it('discovers configured Capell queue names', function (): void {
    config([
        'capell-diagnostics.queue_monitor.queues' => ['default', 'critical'],
        'capell-email-studio.queue' => 'mail',
        'capell-newsletter.sync.queue' => null,
        'capell-public-actions.queue' => 'webhooks',
        'migration-assistant.queue.name' => 'imports',
    ]);

    expect(DiscoverQueueMonitorQueuesAction::run())->toBe([
        'default',
        'critical',
        'mail',
        'webhooks',
        'imports',
    ]);
});

it('builds queue operation stats from grouped monitor data', function (): void {
    QueueMonitor::query()->create([
        'job_id' => 'job-1',
        'name' => 'ImportSiteJob',
        'queue' => 'imports',
        'started_at' => now()->subMinutes(5),
        'finished_at' => now()->subMinutes(3),
        'failed' => false,
        'attempt' => 1,
        'progress' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    QueueMonitor::query()->create([
        'job_id' => 'job-2',
        'name' => 'SendEmailJob',
        'queue' => 'mail',
        'started_at' => now()->subMinutes(2),
        'finished_at' => now()->subMinute(),
        'failed' => true,
        'attempt' => 2,
        'progress' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    QueueMonitor::query()->create([
        'job_id' => 'job-3',
        'name' => 'WebhookJob',
        'queue' => 'webhooks',
        'started_at' => now(),
        'finished_at' => null,
        'failed' => false,
        'attempt' => 1,
        'progress' => 25,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $stats = BuildQueueOperationsStatsAction::run(3);

    expect($stats->totalJobs)->toBe(3)
        ->and($stats->succeededJobs)->toBe(1)
        ->and($stats->failedJobs)->toBe(1)
        ->and($stats->runningJobs)->toBe(1)
        ->and($stats->dailyTotals)->toHaveCount(3)
        ->and($stats->dailyTotals[2])->toBe(3)
        ->and($stats->dailyFailures[2])->toBe(1)
        ->and($stats->averageRuntimeSeconds)->toBeGreaterThan(0);
});

it('retries individual and selected failed jobs by uuid', function (): void {
    FailedJob::query()->create([
        'uuid' => 'failed-job-1',
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'ImportSiteJob'], JSON_THROW_ON_ERROR),
        'exception' => 'Failed',
        'failed_at' => now(),
    ]);
    FailedJob::query()->create([
        'uuid' => 'failed-job-2',
        'connection' => 'database',
        'queue' => 'mail',
        'payload' => json_encode(['displayName' => 'SendEmailJob'], JSON_THROW_ON_ERROR),
        'exception' => 'Failed',
        'failed_at' => now(),
    ]);

    expect(RetryFailedJobAction::run('failed-job-1'))->toBe('failed-job-1');

    expect(RetrySelectedFailedJobsAction::run(['failed-job-2']))->toBe([
        'failed-job-2',
    ]);
});

it('rejects retry requests for missing failed jobs', function (): void {
    expect(fn (): string => RetryFailedJobAction::run('missing-job'))
        ->toThrow(RuntimeException::class, 'Failed job could not be found.');
});

it('deletes pending jobs and prunes old monitor records', function (): void {
    $pendingJob = PendingQueueJob::query()->create([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'PendingJob'], JSON_THROW_ON_ERROR),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $oldMonitor = QueueMonitor::query()->create([
        'job_id' => 'old-job',
        'name' => 'OldJob',
        'queue' => 'default',
        'started_at' => now()->subDays(20),
        'finished_at' => now()->subDays(20),
        'failed' => false,
        'attempt' => 1,
        'progress' => 100,
        'created_at' => now()->subDays(20),
        'updated_at' => now()->subDays(20),
    ]);

    $freshMonitor = QueueMonitor::query()->create([
        'job_id' => 'fresh-job',
        'name' => 'FreshJob',
        'queue' => 'default',
        'started_at' => now(),
        'finished_at' => now(),
        'failed' => false,
        'attempt' => 1,
        'progress' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DeletePendingQueueJobAction::run($pendingJob))->toBe((int) $pendingJob->getKey())
        ->and(PendingQueueJob::query()->whereKey($pendingJob->getKey())->exists())->toBeFalse()
        ->and(PruneQueueMonitorsAction::run(14))->toBe(1)
        ->and(QueueMonitor::query()->whereKey($oldMonitor->getKey())->exists())->toBeFalse()
        ->and(QueueMonitor::query()->whereKey($freshMonitor->getKey())->exists())->toBeTrue();
});
