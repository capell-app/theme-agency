<?php

declare(strict_types=1);

use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\SeoSuite\Filament\Settings\SeoSettingsSchema;
use Capell\SeoSuite\Handlers\ClearCircuitBreakerHandler;
use Capell\SeoSuite\Providers\SeoSuiteServiceProvider;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Capell\SeoSuite\Targets\FlatJsonTarget;

it('autoloads seo tools provider dependencies from their PSR-4 paths', function (): void {
    expect(class_exists(FlatJsonTarget::class))->toBeTrue()
        ->and(class_exists(ClearCircuitBreakerHandler::class))->toBeTrue();
});

it('registers seo suite settings and extension settings surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass('seo_suite'))->toBe(SeoSuiteSettings::class)
        ->and($settingsRegistry->getSchemas('seo_suite'))->toContain(SeoSettingsSchema::class);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(SeoSuiteServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe('seo_suite');
});
