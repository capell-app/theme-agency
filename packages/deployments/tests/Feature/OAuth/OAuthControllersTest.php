<?php

declare(strict_types=1);

use Capell\Deployments\Actions\ConnectDeploymentAction;
use Capell\Deployments\Actions\OAuth\CreateOAuthStateAction;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Http\Controllers\OAuth\BitbucketCallbackController;
use Capell\Deployments\Http\Controllers\OAuth\GitHubCallbackController;
use Capell\Deployments\Http\Controllers\OAuth\GitLabCallbackController;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    Permission::findOrCreate('View:DeploymentConnectionPage', 'web');
    Permission::findOrCreate('Manage:DeploymentConnectionPage', 'web');

    test()->actingAs(test()->createUserWithPermission('Manage:DeploymentConnectionPage'));
});

it('ConnectDeploymentAction class exists', function (): void {
    expect(class_exists(ConnectDeploymentAction::class))->toBeTrue();
});

it('GitHub callback controller class exists', function (): void {
    expect(class_exists(GitHubCallbackController::class))->toBeTrue();
});

it('GitLab callback controller class exists', function (): void {
    expect(class_exists(GitLabCallbackController::class))->toBeTrue();
});

it('Bitbucket callback controller class exists', function (): void {
    expect(class_exists(BitbucketCallbackController::class))->toBeTrue();
});

it('rejects github oauth callbacks without a valid session state', function (): void {
    CreateOAuthStateAction::run(GitProviderType::GitHub);

    $this->get(route('capell-deployments.oauth.github', [
        'code' => 'github-code',
        'state' => 'forged-state',
    ]))
        ->assertSessionHasErrors();
});

it('forbids oauth callbacks for read-only deployment viewers', function (): void {
    test()->actingAs(test()->createUserWithPermission('View:DeploymentConnectionPage'));

    $state = CreateOAuthStateAction::run(GitProviderType::GitHub);

    $this->get(route('capell-deployments.oauth.github', [
        'code' => 'github-code',
        'state' => $state,
    ]))
        ->assertForbidden();

    expect(DeploymentConnection::query()->count())->toBe(0);
});

it('connects github only after oauth state validation passes', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitHub, 'capell-owner', 'capell-app');
    config()->set('capell-deployments.http_timeout', 7);
    $timeouts = [];

    Http::fake(function (ClientRequest $request, array $options) use (&$timeouts): PromiseInterface {
        $timeouts[] = $options['timeout'] ?? null;

        return match ($request->url()) {
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'github-access-token',
            ]),
            'https://api.github.com/user' => Http::response([
                'id' => 123,
                'login' => 'capell-owner',
            ]),
            default => Http::response([], 404),
        };
    });

    $this->get(route('capell-deployments.oauth.github', [
        'code' => 'github-code',
        'state' => $state,
    ]))
        ->assertRedirect(route('filament.admin.pages.deployment-connection'));

    expect(DeploymentConnection::query()->where([
        'provider' => GitProviderType::GitHub->value,
        'repo_owner' => 'capell-owner',
        'repo_name' => 'capell-app',
    ])->exists())->toBeTrue()
        ->and($timeouts)->toBe([7, 7]);
});

it('connects gitlab after oauth state validation passes', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitLab, 'capell-group/platform', 'capell-app');
    config()->set('capell-deployments.http_timeout', 8);
    $timeouts = [];

    Http::fake(function (ClientRequest $request, array $options) use (&$timeouts): PromiseInterface {
        $timeouts[] = $options['timeout'] ?? null;

        return match ($request->url()) {
            'https://gitlab.com/oauth/token' => Http::response([
                'access_token' => 'gitlab-access-token',
                'refresh_token' => 'gitlab-refresh-token',
                'expires_in' => 7200,
            ]),
            'https://gitlab.com/api/v4/user' => Http::response([
                'id' => 456,
                'username' => 'gitlab-owner',
            ]),
            default => Http::response([], 404),
        };
    });

    $this->get(route('capell-deployments.oauth.gitlab', [
        'code' => 'gitlab-code',
        'state' => $state,
    ]))
        ->assertRedirect(route('filament.admin.pages.deployment-connection'));

    $connection = DeploymentConnection::query()->where([
        'provider' => GitProviderType::GitLab->value,
        'repo_owner' => 'capell-group/platform',
        'repo_name' => 'capell-app',
    ])->first();

    expect($connection)->not->toBeNull()
        ->and($connection?->token_expires_at)->not->toBeNull()
        ->and($timeouts)->toBe([8, 8]);
});

it('connects bitbucket after oauth state validation passes', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::Bitbucket, 'capell-workspace', 'capell-app');
    config()->set('capell-deployments.http_timeout', 9);
    $timeouts = [];

    Http::fake(function (ClientRequest $request, array $options) use (&$timeouts): PromiseInterface {
        $timeouts[] = $options['timeout'] ?? null;

        return match ($request->url()) {
            'https://bitbucket.org/site/oauth2/access_token' => Http::response([
                'access_token' => 'bitbucket-access-token',
                'refresh_token' => 'bitbucket-refresh-token',
                'expires_in' => 7200,
            ]),
            'https://api.bitbucket.org/2.0/user' => Http::response([
                'account_id' => 'bitbucket-account-id',
                'username' => 'bitbucket-owner',
            ]),
            default => Http::response([], 404),
        };
    });

    $this->get(route('capell-deployments.oauth.bitbucket', [
        'code' => 'bitbucket-code',
        'state' => $state,
    ]))
        ->assertRedirect(route('filament.admin.pages.deployment-connection'));

    $connection = DeploymentConnection::query()->where([
        'provider' => GitProviderType::Bitbucket->value,
        'repo_owner' => 'capell-workspace',
        'repo_name' => 'capell-app',
    ])->first();

    expect($connection)->not->toBeNull()
        ->and($connection?->token_expires_at)->not->toBeNull()
        ->and($timeouts)->toBe([9, 9]);
});

it('redirects safely when github oauth token exchange times out', function (): void {
    $state = CreateOAuthStateAction::run(GitProviderType::GitHub);

    Http::fake(function (ClientRequest $request, array $options): PromiseInterface {
        throw new ConnectionException('Connection timed out.');
    });

    $this->get(route('capell-deployments.oauth.github', [
        'code' => 'github-code',
        'state' => $state,
    ]))
        ->assertSessionHasErrors();

    expect(DeploymentConnection::query()->count())->toBe(0);
});
