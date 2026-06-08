<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Data;

use Capell\PublishingStudio\Checks\PublishCheckResult;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

final class PublishReadinessData extends Data
{
    /**
     * @param  array<class-string<Model>, int>  $rowCounts
     * @param  array<int, array{site_id: int, language_id: int, url: string}>  $collisions
     * @param  array<int, PublishCheckResult>  $checkResults
     * @param  list<string>  $blockingIssues
     */
    public function __construct(
        public readonly int $workspaceId,
        public readonly bool $wouldPublish,
        public readonly int $totalRows,
        public readonly array $rowCounts,
        public readonly array $collisions,
        public readonly int $conflictCount,
        public readonly array $checkResults,
        public readonly ?string $failureMessage,
        public readonly array $blockingIssues,
        public readonly int $blockingIssueCount,
    ) {}
}
