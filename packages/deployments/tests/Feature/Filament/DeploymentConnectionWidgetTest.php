<?php

declare(strict_types=1);

use Capell\Deployments\Filament\Widgets\DeploymentConnectionWidget;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

it('DeploymentConnectionWidget class exists', function (): void {
    expect(class_exists(DeploymentConnectionWidget::class))->toBeTrue();
});

it('hides deployment connection widget data from users without page access', function (): void {
    $connection = DeploymentConnection::factory()->github()->create(['is_active' => true]);

    expect(DeploymentConnectionWidget::canView())->toBeFalse()
        ->and((new DeploymentConnectionWidget)->getConnection())->toBeNull();

    Livewire::test(DeploymentConnectionWidget::class)
        ->assertDontSee($connection->repoCoordinate())
        ->assertDontSee($connection->provider->getLabel());

    test()->actingAsUser();

    expect(DeploymentConnectionWidget::canView())->toBeFalse()
        ->and((new DeploymentConnectionWidget)->getConnection())->toBeNull();

    Livewire::test(DeploymentConnectionWidget::class)
        ->assertDontSee($connection->repoCoordinate())
        ->assertDontSee($connection->provider->getLabel());
});

it('allows deployment connection widget data for deployment page viewers', function (): void {
    Permission::findOrCreate('View:DeploymentConnectionPage', 'web');
    $connection = DeploymentConnection::factory()->github()->create(['is_active' => true]);

    test()->actingAs(test()->createUserWithPermission('View:DeploymentConnectionPage'));

    expect(DeploymentConnectionWidget::canView())->toBeTrue()
        ->and((new DeploymentConnectionWidget)->getConnection()?->is($connection))->toBeTrue();

    Livewire::test(DeploymentConnectionWidget::class)
        ->assertSee($connection->repoCoordinate())
        ->assertSee($connection->provider->getLabel());
});

it('does not fail when the deployment connection widget renders before migrations', function (): void {
    Permission::findOrCreate('View:DeploymentConnectionPage', 'web');
    test()->actingAs(test()->createUserWithPermission('View:DeploymentConnectionPage'));

    Schema::dropIfExists('deployment_connections');

    expect((new DeploymentConnectionWidget)->getConnection())->toBeNull();
});
