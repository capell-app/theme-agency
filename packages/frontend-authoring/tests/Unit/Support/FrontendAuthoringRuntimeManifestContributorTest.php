<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Contracts\FrontendContextReader;
use Capell\Frontend\Data\FrontendRuntimeManifestData;
use Capell\Frontend\Enums\RenderingStrategyEnum;
use Capell\FrontendAuthoring\Providers\FrontendAuthoringServiceProvider;
use Capell\FrontendAuthoring\Support\FrontendAuthoringRuntimeManifestContributor;
use Illuminate\Support\Facades\Config;

function frontendAuthoringRuntimeManifest(): FrontendRuntimeManifestData
{
    return FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly);
}

function frontendAuthoringContextReader(): FrontendContextReader
{
    return Mockery::mock(FrontendContextReader::class);
}

it('enables the frontend beacon runtime when frontend authoring is enabled', function (): void {
    Config::set('capell-frontend-authoring.enabled', true);

    $manifest = frontendAuthoringRuntimeManifest();

    resolve(FrontendAuthoringRuntimeManifestContributor::class)->contribute(
        frontendAuthoringContextReader(),
        $manifest,
    );

    expect($manifest->usesBeacon)->toBeTrue()
        ->and($manifest->modules)->toHaveKey('frontend-authoring')
        ->and($manifest->modules['frontend-authoring'])->toBeTrue();
});

it('leaves the frontend beacon runtime disabled when frontend authoring is disabled', function (): void {
    Config::set('capell-frontend-authoring.enabled', false);

    $manifest = frontendAuthoringRuntimeManifest();

    resolve(FrontendAuthoringRuntimeManifestContributor::class)->contribute(
        frontendAuthoringContextReader(),
        $manifest,
    );

    expect($manifest->usesBeacon)->toBeFalse()
        ->and($manifest->modules)->not->toHaveKey('frontend-authoring');
});

it('leaves the frontend beacon runtime disabled when the package is not installed', function (): void {
    Config::set('capell-frontend-authoring.enabled', true);
    CapellCore::forcePackageInstalled(FrontendAuthoringServiceProvider::$packageName, false);

    $manifest = frontendAuthoringRuntimeManifest();

    resolve(FrontendAuthoringRuntimeManifestContributor::class)->contribute(
        frontendAuthoringContextReader(),
        $manifest,
    );

    expect($manifest->usesBeacon)->toBeFalse()
        ->and($manifest->modules)->not->toHaveKey('frontend-authoring');
});
