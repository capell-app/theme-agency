<?php

declare(strict_types=1);

use Capell\ThemeStudio\CryptoDefi\CryptoDefiThemeServiceProvider;
use Capell\ThemeStudio\CryptoDefi\Health\ThemeCryptoDefiHealthCheck;

it('defines the crypto-defi renderer contract', function (): void {
    $definition = CryptoDefiThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-crypto-defi')
        ->and($definition->key)->toBe(CryptoDefiThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Crypto DeFi')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('crypto-defi')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeCryptoDefiHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
