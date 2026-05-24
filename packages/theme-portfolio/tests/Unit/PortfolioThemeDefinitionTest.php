<?php

declare(strict_types=1);

use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;

it('defines the Portfolio theme contract', function (): void {
    $definition = PortfolioThemeServiceProvider::definition();

    expect($definition->key)->toBe('portfolio')
        ->and($definition->package)->toBe('capell-app/theme-portfolio')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});
