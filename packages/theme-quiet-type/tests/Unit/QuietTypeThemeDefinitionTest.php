<?php

declare(strict_types=1);

use Capell\ThemeStudio\QuietType\Health\ThemeQuietTypeHealthCheck;
use Capell\ThemeStudio\QuietType\QuietTypeThemeServiceProvider;

it('defines the quiet-type renderer contract', function (): void {
    $definition = QuietTypeThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-quiet-type')
        ->and($definition->key)->toBe(QuietTypeThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Quiet Type')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('quiet-type')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeQuietTypeHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
