<?php

declare(strict_types=1);

use Capell\UrlManager\Health\UrlManagerHealthCheck;
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
