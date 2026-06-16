<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data;

use Spatie\LaravelData\Data;

final class QueueOperationsStatsData extends Data
{
    /**
     * @param  array<int, int>  $dailyTotals
     * @param  array<int, int>  $dailyFailures
     */
    public function __construct(
        public readonly int $totalJobs,
        public readonly int $succeededJobs,
        public readonly int $failedJobs,
        public readonly int $runningJobs,
        public readonly int $pendingJobs,
        public readonly ?int $oldestPendingJobAgeSeconds,
        public readonly string $queueLivenessStatus,
        public readonly int $averageRuntimeSeconds,
        public readonly array $dailyTotals,
        public readonly array $dailyFailures,
    ) {}
}
