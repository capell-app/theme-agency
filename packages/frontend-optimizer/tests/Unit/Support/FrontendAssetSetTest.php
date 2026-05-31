<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Enums\AssetKind;
use Capell\FrontendOptimizer\Enums\AssetLoadingStrategy;
use Capell\FrontendOptimizer\Enums\AssetSlot;
use Capell\FrontendOptimizer\Support\FrontendAssetSet;

it('collects css and js assets with loading strategies', function (): void {
    $assets = FrontendAssetSet::make()
        ->css(
            handle: 'hero',
            path: 'vendor/theme/hero.css',
            loadingStrategy: AssetLoadingStrategy::Preload,
            slot: AssetSlot::AboveFold,
            criticalEligible: true,
            packageName: 'capell-app/theme',
        )
        ->js(
            handle: 'carousel',
            path: 'vendor/layout/carousel.js',
            loadingStrategy: AssetLoadingStrategy::Lazy,
        )
        ->all();

    $javascriptAsset = $assets->get(0);
    $stylesheetAsset = $assets->get(1);

    throw_if($javascriptAsset === null || $stylesheetAsset === null, RuntimeException::class, 'Expected frontend asset set to contain css and js assets.');

    expect($assets)->toHaveCount(2)
        ->and($javascriptAsset->kind)->toBe(AssetKind::Js)
        ->and($javascriptAsset->loadingStrategy)->toBe(AssetLoadingStrategy::Lazy)
        ->and($stylesheetAsset->criticalEligible)->toBeTrue()
        ->and($stylesheetAsset->slot)->toBe(AssetSlot::AboveFold);
});

it('rejects critical javascript assets', function (): void {
    FrontendAssetSet::make()->js(
        handle: 'bad',
        path: 'bad.js',
        loadingStrategy: AssetLoadingStrategy::Critical,
    );
})->throws(InvalidArgumentException::class, 'JavaScript assets cannot use the critical loading strategy.');
