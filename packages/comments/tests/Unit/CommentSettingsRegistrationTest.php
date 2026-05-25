<?php

declare(strict_types=1);

use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Comments\Filament\Settings\CommentSettingsSchema;
use Capell\Comments\Providers\CommentsServiceProvider;
use Capell\Comments\Settings\CommentSettings;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;

it('registers comments settings and extension settings surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass('comments'))->toBe(CommentSettings::class)
        ->and($settingsRegistry->getSchemas('comments'))->toContain(CommentSettingsSchema::class);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(CommentsServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe('comments');
});
