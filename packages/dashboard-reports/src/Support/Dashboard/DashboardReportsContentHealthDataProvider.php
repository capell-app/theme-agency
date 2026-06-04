<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Support\Dashboard;

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Data\Dashboard\ContentHealthData;
use Capell\DashboardReports\Actions\Dashboard\BuildDefaultContentHealthAction;

final class DashboardReportsContentHealthDataProvider implements ContentHealthDataProvider
{
    public function build(): ContentHealthData
    {
        $settings = resolve(DashboardReportsSettingsResolver::class)->settings();

        return BuildDefaultContentHealthAction::run($settings->stalePageThresholdDays);
    }
}
