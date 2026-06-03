<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Deployments\Enums\GitProviderType;
use Capell\Deployments\Health\DeploymentsHealthCheck;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(DeploymentsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = DeploymentsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(2)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the storage table exists and a provider is configured', function (): void {
    Config::set('capell-deployments.oauth.github.client_id', 'github-client-id');

    expect(DeploymentsHealthCheck::passed())->toBeTrue();
});

it('fails the storage table check when the connections table is missing', function (): void {
    Schema::drop('deployment_connections');

    $check = new DeploymentsHealthCheck;

    expect($check->hasConnectionTable())->toBeFalse()
        ->and($check->storageTableCheck()->passed)->toBeFalse()
        ->and(DeploymentsHealthCheck::passed())->toBeFalse();
});

it('fails the oauth provider configuration check when no client id is configured', function (): void {
    Config::set('capell-deployments.oauth.github.client_id', null);
    Config::set('capell-deployments.oauth.gitlab.client_id', null);
    Config::set('capell-deployments.oauth.bitbucket.client_id', null);

    $check = new DeploymentsHealthCheck;

    expect($check->configuredProviderNames())->toBe([])
        ->and($check->oauthProviderConfigurationCheck()->passed)->toBeFalse()
        ->and(DeploymentsHealthCheck::passed())->toBeFalse();
});

it('reports configured provider labels without exposing credential values', function (): void {
    Config::set('capell-deployments.oauth.github.client_id', 'github-client-id');
    Config::set('capell-deployments.oauth.gitlab.client_id', null);
    Config::set('capell-deployments.oauth.bitbucket.client_id', 'bitbucket-client-id');

    $check = new DeploymentsHealthCheck;
    $result = $check->oauthProviderConfigurationCheck();

    expect($check->configuredProviderNames())->toBe([
        GitProviderType::GitHub->getLabel(),
        GitProviderType::Bitbucket->getLabel(),
    ])
        ->and($result->passed)->toBeTrue()
        ->and($result->message)->not->toContain('github-client-id')
        ->and($result->message)->not->toContain('bitbucket-client-id');
});
