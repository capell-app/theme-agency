<?php

declare(strict_types=1);

use Capell\ThemeStudio\QuantTrading\Health\ThemeQuantTradingHealthCheck;
use Capell\ThemeStudio\QuantTrading\QuantTradingThemeServiceProvider;

it('defines the quant-trading renderer contract', function (): void {
    $definition = QuantTradingThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-quant-trading')
        ->and($definition->key)->toBe(QuantTradingThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Quant Trading')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('quant-trading')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeQuantTradingHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
