<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\UserFactory;
use Capell\Diagnostics\Data\Dashboard\ContentHealthData;
use Capell\Diagnostics\Data\Dashboard\ContentHealthIssueData;
use Capell\Diagnostics\Filament\Pages\SystemHealthPage;
use Capell\Diagnostics\Health\DiagnosticsHealthCheck;
use Filament\Facades\Filament;
use Spatie\LaravelData\DataCollection;

it('denies system health access when disabled or unauthenticated', function (): void {
    config(['capell.dashboard.system_health_enabled' => false]);

    expect(SystemHealthPage::canAccess())->toBeFalse();

    config(['capell.dashboard.system_health_enabled' => true]);
    auth()->logout();

    expect(SystemHealthPage::canAccess())->toBeFalse();
});

it('exposes system health page labels, route and layout metadata', function (): void {
    expect(SystemHealthPage::getNavigationLabel())->toBeString()
        ->and(SystemHealthPage::getNavigationGroup())->toBeString()
        ->and(SystemHealthPage::getRoutePath(Filament::getPanel('admin')))->toBe('/system-health');

    $page = new SystemHealthPage;

    expect($page->getColumns())->toBe(['default' => 1, 'md' => 3])
        ->and($page->getWidgets())->toBeArray();
});

it('returns false when authenticated user cannot resolve super admin role checks', function (): void {
    auth()->login(UserFactory::new()->create());

    expect(SystemHealthPage::canAccess())->toBeFalse();
});

it('exposes diagnostics health compatibility and content health data boundaries', function (): void {
    $issue = new ContentHealthIssueData(
        id: 'missing-meta',
        label: 'Missing meta descriptions',
        count: 3,
        filterUrl: '/admin/pages?filter=missing-meta',
    );
    $data = new ContentHealthData(ContentHealthIssueData::collect([$issue], DataCollection::class));
    $firstIssue = diagnosticsContentHealthIssue(collect($data->issues->items())->first());

    expect(DiagnosticsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($data->issues)->toHaveCount(1)
        ->and($firstIssue->id)->toBe('missing-meta')
        ->and($firstIssue->count)->toBe(3);
});
