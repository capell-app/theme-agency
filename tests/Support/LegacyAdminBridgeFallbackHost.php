<?php

declare(strict_types=1);

namespace Capell\Tests\Support;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Enums\DashboardEnum;

final class LegacyAdminBridgeFallbackHost
{
    /** @var list<AdminSurfaceContributionData> */
    public array $surfaceContributions = [];

    /** @var array<class-string, list<DashboardEnum>> */
    public array $dashboardFilamentWidgets = [];

    /** @var array<string, list<class-string>> */
    public array $extensionPages = [];

    public function contributeToAdminSurface(AdminSurfaceContributionData $contribution): void
    {
        $this->surfaceContributions[] = $contribution;
    }

    /**
     * @param  class-string  $widget
     */
    public function registerDashboardFilamentWidget(string $widget, DashboardEnum ...$dashboards): void
    {
        $this->dashboardFilamentWidgets[$widget] = array_values($dashboards);
    }

    /**
     * @param  class-string  $page
     */
    public function registerExtensionPage(string $packageName, string $page): void
    {
        $this->extensionPages[$packageName] ??= [];
        $this->extensionPages[$packageName][] = $page;

        $this->contributeToAdminSurface(AdminSurfaceContributionData::page($page));
    }
}
