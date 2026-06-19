<?php

declare(strict_types=1);

use Capell\ThemeStudio\BeautySpa\BeautySpaThemeServiceProvider;
use Capell\ThemeStudio\BeautySpa\Health\ThemeBeautySpaHealthCheck;

it('defines the beauty-spa renderer contract', function (): void {
    $definition = BeautySpaThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-beauty-spa')
        ->and($definition->key)->toBe(BeautySpaThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Beauty & Spa')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('beauty-spa')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeBeautySpaHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
