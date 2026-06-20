<?php

declare(strict_types=1);

use Capell\Deployments\Filament\Widgets\DeploymentConnectionFilamentWidget;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

it('DeploymentConnectionFilamentWidget class exists', function (): void {
    expect(class_exists(DeploymentConnectionFilamentWidget::class))->toBeTrue();
});

it('hides deployment connection widget data from users without page access', function (): void {
    $connection = DeploymentConnection::factory()->github()->create(['is_active' => true]);

    expect(DeploymentConnectionFilamentWidget::canView())->toBeFalse()
        ->and((new DeploymentConnectionFilamentWidget)->getConnection())->toBeNull();

    Livewire::test(DeploymentConnectionFilamentWidget::class)
        ->assertDontSee($connection->repoCoordinate())
        ->assertDontSee($connection->provider->getLabel());

    test()->actingAsUser();

    expect(DeploymentConnectionFilamentWidget::canView())->toBeFalse()
        ->and((new DeploymentConnectionFilamentWidget)->getConnection())->toBeNull();

    Livewire::test(DeploymentConnectionFilamentWidget::class)
        ->assertDontSee($connection->repoCoordinate())
        ->assertDontSee($connection->provider->getLabel());
});

it('allows deployment connection widget data for deployment page viewers', function (): void {
    Permission::findOrCreate('View:DeploymentConnectionPage', 'web');
    $connection = DeploymentConnection::factory()->github()->create(['is_active' => true]);

    test()->actingAs(test()->createUserWithPermission('View:DeploymentConnectionPage'));

    expect(DeploymentConnectionFilamentWidget::canView())->toBeTrue()
        ->and((new DeploymentConnectionFilamentWidget)->getConnection()?->is($connection))->toBeTrue();

    Livewire::test(DeploymentConnectionFilamentWidget::class)
        ->assertSee($connection->repoCoordinate())
        ->assertSee($connection->provider->getLabel());
});

it('does not fail when the deployment connection widget renders before migrations', function (): void {
    Permission::findOrCreate('View:DeploymentConnectionPage', 'web');
    test()->actingAs(test()->createUserWithPermission('View:DeploymentConnectionPage'));

    Schema::dropIfExists('deployment_connections');

    expect((new DeploymentConnectionFilamentWidget)->getConnection())->toBeNull();
});
