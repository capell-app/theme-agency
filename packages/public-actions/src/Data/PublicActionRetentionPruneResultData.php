<?php

declare(strict_types=1);

namespace Capell\PublicActions\Data;

use Carbon\CarbonImmutable;

final readonly class PublicActionRetentionPruneResultData
{
    public function __construct(
        public int $retentionDays,
        public CarbonImmutable $cutoff,
        public int $matchedSubmissions,
        public int $matchedDispatchAttempts,
        public int $deletedSubmissions,
        public bool $dryRun,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'retention_days' => $this->retentionDays,
            'cutoff' => $this->cutoff->toIso8601String(),
            'matched_submissions' => $this->matchedSubmissions,
            'matched_dispatch_attempts' => $this->matchedDispatchAttempts,
            'deleted_submissions' => $this->deletedSubmissions,
            'dry_run' => $this->dryRun,
        ];
    }
}
