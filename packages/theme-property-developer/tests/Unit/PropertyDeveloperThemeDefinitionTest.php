<?php

declare(strict_types=1);

use Capell\ThemeStudio\PropertyDeveloper\Health\ThemePropertyDeveloperHealthCheck;
use Capell\ThemeStudio\PropertyDeveloper\PropertyDeveloperThemeServiceProvider;

it('defines the property-developer renderer contract', function (): void {
    $definition = PropertyDeveloperThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-property-developer')
        ->and($definition->key)->toBe(PropertyDeveloperThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Property Developer')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('property-developer')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePropertyDeveloperHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
