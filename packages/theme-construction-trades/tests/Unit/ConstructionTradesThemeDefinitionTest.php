<?php

declare(strict_types=1);

use Capell\ThemeStudio\ConstructionTrades\ConstructionTradesThemeServiceProvider;
use Capell\ThemeStudio\ConstructionTrades\Health\ThemeConstructionTradesHealthCheck;

it('defines the construction-trades renderer contract', function (): void {
    $definition = ConstructionTradesThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-construction-trades')
        ->and($definition->key)->toBe(ConstructionTradesThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Construction Trades')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('construction-trades')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeConstructionTradesHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
