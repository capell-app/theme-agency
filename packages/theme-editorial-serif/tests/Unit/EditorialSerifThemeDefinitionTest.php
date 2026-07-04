<?php

declare(strict_types=1);

use Capell\ThemeStudio\EditorialSerif\EditorialSerifThemeServiceProvider;
use Capell\ThemeStudio\EditorialSerif\Health\ThemeEditorialSerifHealthCheck;

it('defines the editorial-serif renderer contract', function (): void {
    $definition = EditorialSerifThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-editorial-serif')
        ->and($definition->key)->toBe(EditorialSerifThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Editorial Serif')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('editorial-serif')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeEditorialSerifHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
