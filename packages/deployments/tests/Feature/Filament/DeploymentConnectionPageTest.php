<?php

declare(strict_types=1);

use Capell\Deployments\Filament\Pages\DeploymentConnectionPage;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(CreatesAdminUser::class);

it('moves deployment repository connections into the deployments package', function (): void {
    expect(class_exists(DeploymentConnectionPage::class))->toBeTrue();
});

it('DeploymentConnectionPage class exists and has correct slug', function (): void {
    expect(class_exists(DeploymentConnectionPage::class))->toBeTrue();

    $property = new ReflectionProperty(DeploymentConnectionPage::class, 'slug');

    expect($property->getValue())->toBe('deployment-connection');
});

it('returns a Filament compatible navigation icon', function (): void {
    expect(DeploymentConnectionPage::getNavigationIcon())->not->toBeNull();
});

it('uses clear deployment repository navigation labels', function (): void {
    expect(DeploymentConnectionPage::getNavigationLabel())->toBe('Deployment Repository')
        ->and(DeploymentConnectionPage::getNavigationGroup())->toBe('System')
        ->and((new DeploymentConnectionPage)->getTitle())->toBe('Deployment Repository');
});

it('builds provider oauth urls from named callback routes', function (): void {
    Permission::findOrCreate('Manage:DeploymentConnectionPage', 'web');
    test()->actingAs(test()->createUserWithPermission('Manage:DeploymentConnectionPage'));

    config()->set('capell-deployments.oauth.github.client_id', 'github-client-id');
    config()->set('capell-deployments.oauth.gitlab.client_id', 'gitlab-client-id');
    config()->set('capell-deployments.oauth.bitbucket.client_id', 'bitbucket-client-id');

    $page = new DeploymentConnectionPage;

    expect($page->getGitHubOAuthUrl())->toContain(urlencode(route('capell-deployments.oauth.github')))
        ->and($page->getGitLabOAuthUrl())->toContain(urlencode(route('capell-deployments.oauth.gitlab')))
        ->and($page->getBitbucketOAuthUrl())->toContain('client_id=bitbucket-client-id')
        ->and($page->getGitHubOAuthUrl())->toContain('state=')
        ->and($page->getGitLabOAuthUrl())->toContain('state=')
        ->and($page->getBitbucketOAuthUrl())->toContain('state=');
});

it('does not fail when the deployment connections table has not been migrated yet', function (): void {
    Schema::dropIfExists('deployment_connections');

    expect((new DeploymentConnectionPage)->getConnections())->toBe([]);
});

it('limits access to users with the deployment connection page permission', function (): void {
    Permission::findOrCreate('View:DeploymentConnectionPage', 'web');
    Permission::findOrCreate('Manage:DeploymentConnectionPage', 'web');

    expect(DeploymentConnectionPage::canAccess())->toBeFalse();

    test()->actingAsUser();

    expect(DeploymentConnectionPage::canAccess())->toBeFalse();

    test()->actingAs(test()->createUserWithPermission('View:DeploymentConnectionPage'));

    expect(DeploymentConnectionPage::canAccess())->toBeTrue();

    test()->actingAs(test()->createUserWithPermission('Manage:DeploymentConnectionPage'));

    expect(DeploymentConnectionPage::canAccess())->toBeTrue()
        ->and(DeploymentConnectionPage::canManageConnections())->toBeTrue();
});

it('hides connection mutation controls from read-only viewers', function (): void {
    Permission::findOrCreate('View:DeploymentConnectionPage', 'web');
    $user = test()->createUserWithPermission('View:DeploymentConnectionPage');
    $connection = DeploymentConnection::factory()->github()->create(['is_active' => true]);

    test()->actingAs($user);

    Livewire::test(DeploymentConnectionPage::class)
        ->assertSee($connection->repoCoordinate())
        ->assertDontSee(__('capell-deployments::plugins.deployment_connection.connect_github'))
        ->assertDontSee(__('capell-deployments::plugins.deployment_connection.disconnect'));

    expect(fn (): mixed => (new DeploymentConnectionPage)->disconnect((int) $connection->getKey()))
        ->toThrow(HttpException::class);

    expect(DeploymentConnection::query()->whereKey($connection->getKey())->exists())->toBeTrue();
});

it('allows connection managers to disconnect active connections', function (): void {
    Permission::findOrCreate('Manage:DeploymentConnectionPage', 'web');
    $connection = DeploymentConnection::factory()->github()->create(['is_active' => true]);

    test()->actingAs(test()->createUserWithPermission('Manage:DeploymentConnectionPage'));

    (new DeploymentConnectionPage)->disconnect((int) $connection->getKey());

    expect(DeploymentConnection::query()->whereKey($connection->getKey())->exists())->toBeFalse();
});
