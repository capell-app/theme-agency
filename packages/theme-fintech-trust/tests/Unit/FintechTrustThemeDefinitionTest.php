<?php

declare(strict_types=1);

use Capell\ThemeStudio\FintechTrust\FintechTrustThemeServiceProvider;
use Capell\ThemeStudio\FintechTrust\Health\ThemeFintechTrustHealthCheck;

it('defines the fintech-trust renderer contract', function (): void {
    $definition = FintechTrustThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-fintech-trust')
        ->and($definition->key)->toBe(FintechTrustThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Fintech Trust')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('fintech-trust')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFintechTrustHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
