<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Support\Dashboard;

use Capell\DashboardReports\Actions\Dashboard\BuildDefaultContentHealthAction;
use Capell\DashboardReports\Data\Dashboard\ResolvedDashboardReportsSettingsData;

final class DashboardReportsSettingsResolver
{
    private const int MIN_STALE_PAGE_THRESHOLD_DAYS = 1;

    private const int MAX_STALE_PAGE_THRESHOLD_DAYS = 3650;

    public function settings(): ResolvedDashboardReportsSettingsData
    {
        return new ResolvedDashboardReportsSettingsData(
            stalePageThresholdDays: $this->stalePageThresholdDays(),
        );
    }

    private function stalePageThresholdDays(): int
    {
        $configured = config('capell-dashboard-reports.stale_page_threshold_days');

        if (! is_numeric($configured)) {
            return BuildDefaultContentHealthAction::DEFAULT_STALE_DAYS;
        }

        return max(
            self::MIN_STALE_PAGE_THRESHOLD_DAYS,
            min(self::MAX_STALE_PAGE_THRESHOLD_DAYS, (int) $configured),
        );
    }
}
