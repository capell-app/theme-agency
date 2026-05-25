<?php

declare(strict_types=1);

use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\FrontendOptimizer\Contracts\CriticalCssGenerator;
use Capell\FrontendOptimizer\Filament\Settings\FrontendOptimizerSettingsSchema;
use Capell\FrontendOptimizer\Providers\FrontendOptimizerServiceProvider;
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

it('registers frontend optimizer settings and extension settings surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass(FrontendOptimizerSettings::group()))
        ->toBe(FrontendOptimizerSettings::class)
        ->and($settingsRegistry->getSchemas(FrontendOptimizerSettings::group()))
        ->toContain(FrontendOptimizerSettingsSchema::class);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(FrontendOptimizerServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe(FrontendOptimizerSettings::group());
});
