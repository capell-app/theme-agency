<?php

declare(strict_types=1);

use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\Newsletter\Filament\Settings\NewsletterSettingsSchema;
use Capell\Newsletter\Providers\NewsletterServiceProvider;
use Capell\Newsletter\Settings\NewsletterSettings;

it('registers newsletter settings and extension settings surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass('newsletter'))->toBe(NewsletterSettings::class)
        ->and($settingsRegistry->getSchemas('newsletter'))->toContain(NewsletterSettingsSchema::class);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(NewsletterServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe('newsletter');
});
