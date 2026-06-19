<?php

declare(strict_types=1);

use Capell\ThemeStudio\PersonalDev\Health\ThemePersonalDevHealthCheck;
use Capell\ThemeStudio\PersonalDev\PersonalDevThemeServiceProvider;

it('defines the personal-dev renderer contract', function (): void {
    $definition = PersonalDevThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-personal-dev')
        ->and($definition->key)->toBe(PersonalDevThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Personal Dev')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('personal-dev')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePersonalDevHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
