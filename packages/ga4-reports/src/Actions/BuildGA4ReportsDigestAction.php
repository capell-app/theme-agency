<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Data\GA4ReportsDigestData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildGA4ReportsDigestAction
{
    use AsAction;

    public function handle(?GA4ReportsWindowData $window = null, int $topPageLimit = 10): ?GA4ReportsDigestData
    {
        $resolvedWindow = $window ?? BuildGA4ReportsWindowAction::run();

        if (! $resolvedWindow instanceof GA4ReportsWindowData) {
            return null;
        }

        return new GA4ReportsDigestData(
            window: $resolvedWindow,
            overview: BuildGA4ReportsOverviewAction::run($resolvedWindow),
            trend: BuildGA4ReportsTrendAction::run($resolvedWindow),
            topPages: BuildTopGA4ReportsPagesAction::run($resolvedWindow, $topPageLimit),
        );
    }
}
