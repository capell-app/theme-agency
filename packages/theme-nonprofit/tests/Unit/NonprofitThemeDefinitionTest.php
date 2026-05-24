<?php

declare(strict_types=1);

use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;

it('defines the Nonprofit theme contract', function (): void {
    $definition = NonprofitThemeServiceProvider::definition();

    expect($definition->key)->toBe('nonprofit')
        ->and($definition->package)->toBe('capell-app/theme-nonprofit')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});
