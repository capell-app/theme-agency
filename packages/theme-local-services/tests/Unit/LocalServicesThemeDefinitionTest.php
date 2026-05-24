<?php

declare(strict_types=1);

use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;

it('defines the Local Services theme contract', function (): void {
    $definition = LocalServicesThemeServiceProvider::definition();

    expect($definition->key)->toBe('local-services')
        ->and($definition->package)->toBe('capell-app/theme-local-services')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});
