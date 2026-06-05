<?php

declare(strict_types=1);

use Capell\UrlManager\Actions\ResolveRedirectRuleAction;
use Capell\UrlManager\Health\UrlManagerHealthCheck;
use Capell\UrlManager\Providers\UrlManagerServiceProvider;
use Illuminate\Support\Facades\Schema;

it('reports real URL Manager health diagnostics', function (): void {
    expect(UrlManagerHealthCheck::runDiagnostics())->toHaveCount(3)
        ->and(UrlManagerHealthCheck::passed())->toBeTrue();
});

it('fails URL Manager health when a redirect table is missing', function (): void {
    Schema::drop('url_manager_redirect_hits');

    $check = new UrlManagerHealthCheck;

    expect($check->storageTablesCheck()->passed)->toBeFalse()
        ->and($check->missingTables())->toContain('url_manager_redirect_hits')
        ->and(UrlManagerHealthCheck::passed())->toBeFalse();
});

it('fails URL Manager health when action metadata is missing', function (): void {
    $check = new UrlManagerHealthCheck([
        'providers' => [
            'runtime' => [UrlManagerServiceProvider::class],
            'admin' => [UrlManagerServiceProvider::class],
        ],
        'database' => [
            'requiredTables' => [
                'url_manager_redirect_rules',
                'url_manager_redirect_hits',
                'url_manager_not_found_opportunities',
            ],
        ],
    ]);

    expect($check->actionClassesCheck()->passed)->toBeFalse()
        ->and($check->actionClassesCheck()->message)->toBe('URL Manager actions are not declared in capell.json.');
});

it('fails URL Manager health when provider metadata is missing', function (): void {
    $check = new UrlManagerHealthCheck([
        'actions' => [
            'resolveRedirectRule' => ResolveRedirectRuleAction::class,
        ],
        'providers' => [
            'runtime' => [UrlManagerServiceProvider::class],
        ],
        'database' => [
            'requiredTables' => [
                'url_manager_redirect_rules',
                'url_manager_redirect_hits',
                'url_manager_not_found_opportunities',
            ],
        ],
    ]);

    expect($check->providerMetadataCheck()->passed)->toBeFalse()
        ->and($check->providerMetadataCheck()->message)->toContain('admin');
});
