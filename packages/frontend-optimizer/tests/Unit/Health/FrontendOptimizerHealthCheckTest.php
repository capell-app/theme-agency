<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Frontend\Contracts\FrontendAssetManifestRenderer;
use Capell\Frontend\Support\Assets\DefaultFrontendAssetManifestRenderer;
use Capell\FrontendOptimizer\Health\FrontendOptimizerHealthCheck;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
    Config::set('queue.default', 'database');
});

it('reports a compatible capell api version', function (): void {
    expect(FrontendOptimizerHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = FrontendOptimizerHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(5)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the renderer, storage paths, generator, and queue are ready', function (): void {
    $results = FrontendOptimizerHealthCheck::runDiagnostics();

    expect(FrontendOptimizerHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the renderer binding check when the default renderer is bound', function (): void {
    app()->instance(FrontendAssetManifestRenderer::class, resolve(DefaultFrontendAssetManifestRenderer::class));

    $check = new FrontendOptimizerHealthCheck;

    expect($check->optimizerRendererIsBound())->toBeFalse()
        ->and($check->rendererBindingCheck()->passed)->toBeFalse()
        ->and(FrontendOptimizerHealthCheck::passed())->toBeFalse();
});

it('passes the storage disk check when the local disk is writable', function (): void {
    $check = new FrontendOptimizerHealthCheck;

    expect($check->storageDiskIsWritable())->toBeTrue()
        ->and($check->manifestStorageWritableCheck()->passed)->toBeTrue()
        ->and($check->criticalCssStorageWritableCheck()->passed)->toBeTrue();
});

it('passes the generator readiness check when node, script, and playwright declaration are present', function (): void {
    $check = new FrontendOptimizerHealthCheck;

    expect($check->nodeBinaryCanRun())->toBeTrue()
        ->and($check->generatorScriptExists())->toBeTrue()
        ->and($check->playwrightDependencyIsDeclared())->toBeTrue()
        ->and($check->generatorReadinessCheck()->passed)->toBeTrue();
});

it('fails the generator readiness check when the configured script is missing', function (): void {
    Config::set('capell-frontend-optimizer.playwright.script', __DIR__ . '/missing-generate-critical-css.mjs');

    $check = new FrontendOptimizerHealthCheck;

    expect($check->generatorScriptExists())->toBeFalse()
        ->and($check->generatorIsReady())->toBeFalse()
        ->and($check->generatorReadinessCheck()->passed)->toBeFalse()
        ->and(FrontendOptimizerHealthCheck::passed())->toBeFalse();
});

it('fails the generation queue check when generation is enabled on the sync queue', function (): void {
    Config::set('queue.default', 'sync');
    Config::set('queue.connections.sync.driver', 'sync');

    $check = new FrontendOptimizerHealthCheck;

    expect($check->generationRunsOnSyncQueue())->toBeTrue()
        ->and($check->generationQueueDriverCheck()->passed)->toBeFalse()
        ->and(FrontendOptimizerHealthCheck::passed())->toBeFalse();
});

it('passes the generation queue check when automatic generation is disabled even on the sync queue', function (): void {
    Config::set('queue.default', 'sync');
    Config::set('queue.connections.sync.driver', 'sync');
    Config::set('capell-frontend-optimizer.enabled', false);

    $check = new FrontendOptimizerHealthCheck;

    expect($check->generationRunsOnSyncQueue())->toBeFalse()
        ->and($check->generationQueueDriverCheck()->passed)->toBeTrue();
});
