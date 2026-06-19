<?php

declare(strict_types=1);

use Capell\ThemeStudio\PackagingSupplier\Health\ThemePackagingSupplierHealthCheck;
use Capell\ThemeStudio\PackagingSupplier\PackagingSupplierThemeServiceProvider;

it('defines the packaging-supplier renderer contract', function (): void {
    $definition = PackagingSupplierThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-packaging-supplier')
        ->and($definition->key)->toBe(PackagingSupplierThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Packaging Supplier')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('packaging-supplier')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePackagingSupplierHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
