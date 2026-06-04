<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Data\Dashboard;

use Spatie\LaravelData\Data;

final class ResolvedDashboardReportsSettingsData extends Data
{
    public function __construct(
        public int $stalePageThresholdDays,
    ) {}
}
