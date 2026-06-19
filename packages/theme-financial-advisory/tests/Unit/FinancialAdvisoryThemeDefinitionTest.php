<?php

declare(strict_types=1);

use Capell\ThemeStudio\FinancialAdvisory\FinancialAdvisoryThemeServiceProvider;
use Capell\ThemeStudio\FinancialAdvisory\Health\ThemeFinancialAdvisoryHealthCheck;

it('defines the financial-advisory renderer contract', function (): void {
    $definition = FinancialAdvisoryThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-financial-advisory')
        ->and($definition->key)->toBe(FinancialAdvisoryThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Financial Advisory')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('financial-advisory')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFinancialAdvisoryHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
