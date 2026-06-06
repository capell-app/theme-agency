<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Widgets\Concerns;

use Capell\Admin\Filament\Concerns\HasDashboardDateRange;
use Capell\GA4Reports\Actions\BuildGA4ReportsWindowAction;
use Capell\GA4Reports\Data\GA4ReportsWindowData;

trait BuildsGA4ReportsDashboardWindow
{
    use HasDashboardDateRange;

    private function getGA4ReportsWindow(): ?GA4ReportsWindowData
    {
        [$rangeStart, $rangeEnd] = $this->getDashboardDateRange();

        return BuildGA4ReportsWindowAction::run($rangeStart, $rangeEnd);
    }

    private function getPreviousGA4ReportsWindow(GA4ReportsWindowData $window): ?GA4ReportsWindowData
    {
        $days = (int) $window->startsAt->startOfDay()->diffInDays($window->endsAt->startOfDay()) + 1;
        $previousEnd = $window->startsAt->subDay()->endOfDay();
        $previousStart = $previousEnd->subDays($days - 1)->startOfDay();

        return BuildGA4ReportsWindowAction::run($previousStart, $previousEnd);
    }
}
