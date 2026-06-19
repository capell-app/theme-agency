<?php

declare(strict_types=1);

use Capell\ThemeStudio\AutomotiveDealer\AutomotiveDealerThemeServiceProvider;
use Capell\ThemeStudio\AutomotiveDealer\Health\ThemeAutomotiveDealerHealthCheck;

it('defines the automotive-dealer renderer contract', function (): void {
    $definition = AutomotiveDealerThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-automotive-dealer')
        ->and($definition->key)->toBe(AutomotiveDealerThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Automotive Dealer')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('automotive-dealer')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeAutomotiveDealerHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
