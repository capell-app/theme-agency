<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Contracts\CriticalCssGenerator;
use Capell\FrontendOptimizer\Settings\FrontendOptimizerSettings;
use Capell\FrontendOptimizer\Support\LayoutAssetRegistry;
use Capell\FrontendOptimizer\Support\PlaywrightCriticalCssGenerator;
use Capell\FrontendOptimizer\Support\WidgetAssetRegistry;

it('binds optimizer registries and the required playwright generator', function (): void {
    expect(resolve(LayoutAssetRegistry::class))->toBeInstanceOf(LayoutAssetRegistry::class)
        ->and(resolve(WidgetAssetRegistry::class))->toBeInstanceOf(WidgetAssetRegistry::class)
        ->and(resolve(CriticalCssGenerator::class))->toBeInstanceOf(PlaywrightCriticalCssGenerator::class);
});

it('exposes frontend optimizer settings defaults', function (): void {
    $settings = new FrontendOptimizerSettings;

    expect($settings->enable_critical_css)->toBeTrue()
        ->and($settings->automatic_generation)->toBeTrue()
        ->and($settings->profile_scope)->toBe('layout')
        ->and($settings->viewports)->toHaveCount(2)
        ->and(FrontendOptimizerSettings::group())->toBe('frontend_optimizer');
});
