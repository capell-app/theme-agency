<?php

declare(strict_types=1);

use Capell\ThemeStudio\FitnessWellness\FitnessWellnessThemeServiceProvider;
use Capell\ThemeStudio\FitnessWellness\Health\ThemeFitnessWellnessHealthCheck;

it('defines the fitness-wellness renderer contract', function (): void {
    $definition = FitnessWellnessThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-fitness-wellness')
        ->and($definition->key)->toBe(FitnessWellnessThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Fitness & Wellness')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('fitness-wellness')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFitnessWellnessHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
