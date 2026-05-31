<?php

declare(strict_types=1);

use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Filament\Pages\CapellDashboard;
use Capell\Admin\Filament\Widgets\Dashboard\CapellFilamentInfoWidget;
use Capell\Admin\Filament\Widgets\Dashboard\ListPagesWidget;
use Capell\Admin\Filament\Widgets\Dashboard\MyWorkQueueWidget;
use Capell\Admin\Filament\Widgets\Dashboard\RecentlyPublishedWidget;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\LoginAudit\Filament\Widgets\LoginAuditsWidget;
use Capell\PublishingStudio\Filament\Widgets\WorkspaceActivityWidgetAbstract;

it('getColumns returns the responsive dashboard grid columns', function (): void {
    $dashboard = new CapellDashboard;
    expect($dashboard->getColumns())->toBeIn([
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
        ->toContain(WorkspaceActivityWidgetAbstract::class)
        ->toContain(MyWorkQueueWidget::class)
        ->toContain(RecentlyPublishedWidget::class)
        ->toContain(CapellFilamentInfoWidget::class);
});

it('registers workspace-owned admin widgets when publishing-studio are installed', function (): void {
    expect(CapellAdmin::getDashboardWidgets(DashboardEnum::Main))
        ->toContain(MyWorkQueueWidget::class)
        ->toContain(RecentlyPublishedWidget::class);
});

it('getWidgets does not contain dropped widgets', function (): void {
    $dashboard = new CapellDashboard;
    $widgets = $dashboard->getWidgets();

    expect($widgets)
        ->not->toContain(LoginAuditsWidget::class)
        ->not->toContain('Capell\\Admin\\Filament\\Widgets\\Health\\TotalAccessLogsWidget')
        ->not->toContain(ListPagesWidget::class);
});
