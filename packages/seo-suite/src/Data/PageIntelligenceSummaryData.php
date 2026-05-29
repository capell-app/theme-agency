<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

class PageIntelligenceSummaryData extends Data
{
    /**
     * @param  list<string>  $targetKeywords
     * @param  list<SearchConsoleRankingRowData>  $rankingRows
     * @param  list<SeoOpportunityRowData>  $opportunities
     */
    public function __construct(
        public array $targetKeywords,
        public array $rankingRows,
        public array $opportunities,
    ) {}
}
