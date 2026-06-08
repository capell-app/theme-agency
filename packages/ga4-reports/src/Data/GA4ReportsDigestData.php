<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Data;

use Spatie\LaravelData\Data;

final class GA4ReportsDigestData extends Data
{
    /**
     * @param  list<GA4ReportsTrendPointData>  $trend
     * @param  list<GA4ReportsTopPageData>  $topPages
     */
    public function __construct(
        public readonly GA4ReportsWindowData $window,
        public readonly GA4ReportsOverviewData $overview,
        public readonly array $trend,
        public readonly array $topPages,
    ) {}
}
