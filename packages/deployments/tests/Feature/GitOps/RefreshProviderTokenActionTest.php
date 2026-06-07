<?php

declare(strict_types=1);

use Capell\Deployments\Actions\RefreshProviderTokenAction;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Models\DeploymentConnection;
use Capell\Deployments\Services\GitProvider\BitbucketProvider;
use Capell\Deployments\Services\GitProvider\GitLabProvider;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;

it('refreshes expired GitLab OAuth tokens before provider requests', function (): void {
    config()->set('capell-deployments.oauth.gitlab.client_id', 'gitlab-client-id');
    config()->set('capell-deployments.oauth.gitlab.client_secret', 'gitlab-client-secret');

    $connection = DeploymentConnection::factory()->gitlab()->create([
        'repo_owner' => 'acme',
        'repo_name' => 'app',
        'access_token_encrypted' => 'expired-access-token',
        'refresh_token_encrypted' => 'refresh-token',
        'token_expires_at' => now()->subMinute(),
    ]);

    Http::fake([
        'gitlab.com/oauth/token' => Http::response([
            'access_token' => 'fresh-access-token',
            'refresh_token' => 'fresh-refresh-token',
            'expires_in' => 7200,
        ]),
        'gitlab.com/api/v4/projects/*/repository/branches/main' => Http::response([
            'commit' => ['id' => 'fresh-branch-sha'],
        ]),
    ]);

    expect(resolve(GitLabProvider::class)->getBranchCommitSha($connection, 'main'))->toBe('fresh-branch-sha');

    $connection->refresh();

    expect($connection->access_token_encrypted)->toBe('fresh-access-token')
        ->and($connection->refresh_token_encrypted)->toBe('fresh-refresh-token')
        ->and($connection->token_expires_at)->not->toBeNull();

    Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://gitlab.com/oauth/token');
});

it('does not refresh unexpired Bitbucket OAuth tokens before provider requests', function (): void {
    $connection = DeploymentConnection::factory()->bitbucket()->create([
        'repo_owner' => 'acme',
        'repo_name' => 'app',
        'access_token_encrypted' => 'current-access-token',
        'refresh_token_encrypted' => 'refresh-token',
        'token_expires_at' => now()->addHour(),
    ]);

    Http::fake([
        'api.bitbucket.org/2.0/repositories/acme/app/refs/branches/main' => Http::response([
            'target' => ['hash' => 'current-branch-sha'],
        ]),
    ]);

    expect(resolve(BitbucketProvider::class)->getBranchCommitSha($connection, 'main'))->toBe('current-branch-sha');

    Http::assertNotSent(fn (ClientRequest $request): bool => $request->url() === 'https://bitbucket.org/site/oauth2/access_token');
});

it('keeps the existing token when refresh response is invalid', function (): void {
    $connection = DeploymentConnection::factory()->create([
        'provider' => GitProviderType::GitLab,
        'access_token_encrypted' => 'expired-access-token',
        'refresh_token_encrypted' => 'refresh-token',
        'token_expires_at' => now()->subMinute(),
    ]);

    Http::fake([
        'gitlab.com/oauth/token' => Http::response(['error' => 'invalid_grant'], 400),
    ]);

    $refreshed = RefreshProviderTokenAction::run($connection);

    expect($refreshed->access_token_encrypted)->toBe('expired-access-token')
        ->and($refreshed->refresh_token_encrypted)->toBe('refresh-token');
});
