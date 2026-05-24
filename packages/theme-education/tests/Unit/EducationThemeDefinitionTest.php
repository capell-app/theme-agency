<?php

declare(strict_types=1);

use Capell\ThemeStudio\Education\EducationThemeServiceProvider;

it('defines the Education theme contract', function (): void {
    $definition = EducationThemeServiceProvider::definition();

    expect($definition->key)->toBe('education')
        ->and($definition->package)->toBe('capell-app/theme-education')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});
