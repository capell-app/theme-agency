<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Spatie\LaravelData\Data;

final class PageSpeedAuditSummaryData extends Data
{
    public function __construct(
        public readonly PageSpeedAuditRun $run,
        public readonly int $auditedPages,
        public readonly int $successfulResults,
        public readonly int $failedResults,
        public readonly int $poorResults,
    ) {}
}
