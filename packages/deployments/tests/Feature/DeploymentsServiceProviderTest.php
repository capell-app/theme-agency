<?php

declare(strict_types=1);

use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\Deployments\Contracts\PublishesComposerChanges;
use Capell\Deployments\Filament\Pages\DeploymentConnectionPage;
use Capell\Deployments\Filament\Widgets\DeploymentConnectionWidget;
use Capell\Deployments\Providers\DeploymentsServiceProvider;
use Illuminate\Support\Facades\Route;

it('registers the deployments package metadata', function (): void {
    expect(CapellCore::hasPackage(DeploymentsServiceProvider::$packageName))->toBeTrue()
        ->and(CapellCore::getPackage(DeploymentsServiceProvider::$packageName)->serviceProviderClass)
        ->toBe(DeploymentsServiceProvider::class);
});

it('registers the deployment connections admin page', function (): void {
    expect(CapellAdmin::getAdminSurfaceRegistry()->pages())->toContain(DeploymentConnectionPage::class)
        ->and(CapellAdmin::getDashboardWidgets(DashboardEnum::SystemHealth))->toContain(DeploymentConnectionWidget::class)
        ->and(DeploymentConnectionPage::getNavigationLabel())->toBe('Deployment Repository')
        ->and(app()->bound(PublishesComposerChanges::class))->toBeTrue();
});

it('registers authenticated oauth callback routes', function (): void {
    foreach ([
        'capell-deployments.oauth.github',
        'capell-deployments.oauth.gitlab',
        'capell-deployments.oauth.bitbucket',
    ] as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull()
            ->and($route?->gatherMiddleware())->toContain('web', 'auth');
    }
});
