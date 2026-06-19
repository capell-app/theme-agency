<?php

declare(strict_types=1);

use Capell\ThemeStudio\ProductStudio\Health\ThemeProductStudioHealthCheck;
use Capell\ThemeStudio\ProductStudio\ProductStudioThemeServiceProvider;

it('defines the product-studio renderer contract', function (): void {
    $definition = ProductStudioThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-product-studio')
        ->and($definition->key)->toBe(ProductStudioThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Product Studio')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('product-studio')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeProductStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
