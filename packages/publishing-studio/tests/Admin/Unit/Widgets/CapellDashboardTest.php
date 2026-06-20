<?php

declare(strict_types=1);

use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Filament\Pages\CapellDashboard;
use Capell\Admin\Filament\Widgets\Dashboard\CapellInfoFilamentWidget;
use Capell\Admin\Filament\Widgets\Dashboard\ListPagesFilamentWidget;
use Capell\Admin\Filament\Widgets\Dashboard\MyWorkQueueFilamentWidget;
use Capell\Admin\Filament\Widgets\Dashboard\RecentlyPublishedFilamentWidget;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\LoginAudit\Filament\Widgets\LoginAuditsFilamentWidget;
use Capell\PublishingStudio\Filament\Widgets\WorkspaceActivityFilamentWidget;

it('getColumns returns the responsive dashboard grid columns', function (): void {
    $dashboard = new CapellDashboard;
    expect($dashboard->getColumns())->toBeIn([
        12,
        2,
        [
            'default' => 1,
            'lg' => 2,
        ],
    ]);
});

it('getWidgets contains all expected widget classes', function (): void {
    Site::factory()->has(SiteDomain::factory()->default())->create();

    $dashboard = new CapellDashboard;
    $widgets = $dashboard->getWidgets();

    expect($widgets)
        ->toContain(WorkspaceActivityFilamentWidget::class)
        ->toContain(MyWorkQueueFilamentWidget::class)
        ->toContain(RecentlyPublishedFilamentWidget::class)
        ->toContain(CapellInfoFilamentWidget::class);
});

it('registers workspace-owned admin widgets when publishing-studio are installed', function (): void {
    expect(CapellAdmin::getDashboardFilamentWidgets(DashboardEnum::Main))
        ->toContain(MyWorkQueueFilamentWidget::class)
        ->toContain(RecentlyPublishedFilamentWidget::class);
});

it('getWidgets does not contain dropped widgets', function (): void {
    $dashboard = new CapellDashboard;
    $widgets = $dashboard->getWidgets();

    expect($widgets)
        ->not->toContain(LoginAuditsFilamentWidget::class)
        ->not->toContain('Capell\\Admin\\Filament\\Widgets\\Health\\TotalAccessLogsWidget')
        ->not->toContain(ListPagesFilamentWidget::class);
});
