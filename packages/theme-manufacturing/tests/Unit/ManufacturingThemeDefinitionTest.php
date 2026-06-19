<?php

declare(strict_types=1);

use Capell\ThemeStudio\Manufacturing\Health\ThemeManufacturingHealthCheck;
use Capell\ThemeStudio\Manufacturing\ManufacturingThemeServiceProvider;

it('defines the manufacturing renderer contract', function (): void {
    $definition = ManufacturingThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-manufacturing')
        ->and($definition->key)->toBe(ManufacturingThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Manufacturing')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('manufacturing')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeManufacturingHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
