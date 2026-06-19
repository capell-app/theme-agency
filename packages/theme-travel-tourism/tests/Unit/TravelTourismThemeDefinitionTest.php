<?php

declare(strict_types=1);

use Capell\ThemeStudio\TravelTourism\Health\ThemeTravelTourismHealthCheck;
use Capell\ThemeStudio\TravelTourism\TravelTourismThemeServiceProvider;

it('defines the travel-tourism renderer contract', function (): void {
    $definition = TravelTourismThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-travel-tourism')
        ->and($definition->key)->toBe(TravelTourismThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Travel & Tourism')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('travel-tourism')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeTravelTourismHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
