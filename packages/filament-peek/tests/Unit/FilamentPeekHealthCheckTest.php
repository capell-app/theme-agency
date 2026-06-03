<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FilamentPeek\Health\FilamentPeekHealthCheck;

it('passes when the preview route, actions, upstream plugin, and cache store are healthy', function (): void {
    $diagnostics = FilamentPeekHealthCheck::runDiagnostics();

    expect($diagnostics)->toHaveCount(4)
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and(FilamentPeekHealthCheck::passed())->toBeTrue();
});

it('fails when the configured preview cache store cannot be resolved', function (): void {
    config()->set('capell-filament-peek.preview.cache_store', 'does-not-exist');

    $diagnostics = FilamentPeekHealthCheck::runDiagnostics();

    $cacheStoreResult = $diagnostics->firstWhere('label', 'Filament Peek preview cache store');

    expect($cacheStoreResult)->not->toBeNull()
        ->and($cacheStoreResult->passed)->toBeFalse()
        ->and($cacheStoreResult->remediation)->not->toBeNull()
        ->and(FilamentPeekHealthCheck::passed())->toBeFalse();
});

it('does not leak snapshot tokens or cache keys in any diagnostic output', function (): void {
    $diagnostics = FilamentPeekHealthCheck::runDiagnostics();

    $diagnostics->each(function (DoctorCheckResultData $result): void {
        expect($result->message)->not->toContain('snapshot:')
            ->and($result->message)->not->toContain('capell-filament-peek:snapshot');
    });
});
