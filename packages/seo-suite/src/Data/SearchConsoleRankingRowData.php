<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

class SearchConsoleRankingRowData extends Data
{
    public function __construct(
        public int $siteId,
        public string $query,
        public string $url,
        public CarbonInterface $windowStart,
        public CarbonInterface $windowEnd,
        public int $clicks,
        public int $impressions,
        public float $ctr,
        public float $averagePosition,
        public int $previousClicks,
        public int $previousImpressions,
        public float $previousCtr,
        public float $previousAveragePosition,
        public int $clickDelta,
        public int $impressionDelta,
        public float $positionDelta,
    ) {}
}
