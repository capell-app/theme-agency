<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Spatie\LaravelData\Data;

final class PageSpeedAuditSummaryData extends Data
{
    /**
     * @param  list<PageSpeedAuditDigestFindingData>  $worstMobileResults
     * @param  list<PageSpeedAuditDigestFindingData>  $worstDesktopResults
     * @param  list<PageSpeedAuditDigestFindingData>  $biggestDrops
     * @param  list<PageSpeedAuditDigestFindingData>  $belowThresholdResults
     */
    public function __construct(
        public readonly PageSpeedAuditRun $run,
        public readonly int $auditedPages,
        public readonly int $successfulResults,
        public readonly int $failedResults,
        public readonly int $poorResults,
        public readonly array $worstMobileResults = [],
        public readonly array $worstDesktopResults = [],
        public readonly array $biggestDrops = [],
        public readonly array $belowThresholdResults = [],
    ) {}
}
