<?php

declare(strict_types=1);

use Capell\Deployments\Filament\Pages\DeploymentConnectionPage;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Models\DeploymentPublication;
use Capell\Deployments\Services\GitProvider\GitHubProvider;
use Capell\Deployments\Tests\Fixtures\Autoload\FakeComposerPublisher;
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

    expect($property->getValue())->toBe('deployments/deployment-connection');
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
    $page->repoOwner = 'capell';
    $page->repoName = 'app';

    expect($page->getGitHubOAuthUrl())->toContain(urlencode(route('capell-deployments.oauth.github')))
        ->and($page->getGitLabOAuthUrl())->toContain(urlencode(route('capell-deployments.oauth.gitlab')))
        ->and($page->getBitbucketOAuthUrl())->toContain('client_id=bitbucket-client-id')
        ->and($page->getGitHubOAuthUrl())->toContain('state=')
        ->and($page->getGitLabOAuthUrl())->toContain('state=')
        ->and($page->getBitbucketOAuthUrl())->toContain('state=');
});

it('requires repository coordinates before building oauth urls', function (): void {
    Permission::findOrCreate('Manage:DeploymentConnectionPage', 'web');
    test()->actingAs(test()->createUserWithPermission('Manage:DeploymentConnectionPage'));

    config()->set('capell-deployments.oauth.github.client_id', 'github-client-id');

    expect(fn (): string => (new DeploymentConnectionPage)->getGitHubOAuthUrl())
        ->toThrow(HttpException::class);
});

it('disables connect providers until repository and oauth client configuration are present', function (): void {
    Permission::findOrCreate('Manage:DeploymentConnectionPage', 'web');
    test()->actingAs(test()->createUserWithPermission('Manage:DeploymentConnectionPage'));

    config()->set('capell-deployments.oauth.github.client_id', 'github-client-id');
    config()->set('capell-deployments.oauth.gitlab.client_id');
    config()->set('capell-deployments.oauth.bitbucket.client_id');

    $page = new DeploymentConnectionPage;

    expect(collect($page->getConnectProviders())->pluck('url')->all())->toBe([null, null, null]);

    $page->repoOwner = 'capell';
    $page->repoName = 'app';

    $providers = $page->getConnectProviders();

    expect($providers[0]['url'])->toContain('github.com/login/oauth/authorize')
        ->and($providers[1]['url'])->toBeNull()
        ->and($providers[1]['disabledReason'])->toBe('GitLab OAuth is not configured.');
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

it('allows connection managers to cancel pending pull request publications', function (): void {
    Permission::findOrCreate('Manage:DeploymentConnectionPage', 'web');
    $provider = new FakeComposerPublisher;
    app()->instance(GitHubProvider::class, $provider);
    $connection = DeploymentConnection::factory()->github()->create(['is_active' => true]);
    $publication = DeploymentPublication::query()->create([
        'deployment_connection_id' => $connection->id,
        'provider' => $connection->provider,
        'repo_owner' => $connection->repo_owner,
        'repo_name' => $connection->repo_name,
        'composer_package' => 'capell/cancel-me',
        'constraint' => '^1.0',
        'branch_name' => 'capell/add-extension-cancel-me',
        'commit_sha' => 'commit-sha',
        'pull_request_id' => 789,
        'pull_request_url' => 'https://github.test/pull/789',
        'status' => 'pending',
        'dry_run' => false,
        'status_checked_at' => null,
    ]);

    test()->actingAs(test()->createUserWithPermission('Manage:DeploymentConnectionPage'));

    (new DeploymentConnectionPage)->cancelPublication((int) $connection->getKey(), (int) $publication->getKey());

    expect($publication->refresh()->status)->toBe('cancelled')
        ->and($provider->closedPullRequestIds)->toBe([789]);
});
