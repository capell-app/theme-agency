<?php

declare(strict_types=1);

use Capell\ThemeStudio\QuietLuxuryRetail\Health\ThemeQuietLuxuryRetailHealthCheck;
use Capell\ThemeStudio\QuietLuxuryRetail\QuietLuxuryRetailThemeServiceProvider;

it('defines the quiet-luxury-retail renderer contract', function (): void {
    $definition = QuietLuxuryRetailThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-quiet-luxury-retail')
        ->and($definition->key)->toBe(QuietLuxuryRetailThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Quiet Luxury Retail')
        ->and($definition->description)->toContain('luxury retail')
        ->and($definition->tags)->toContain('Luxury Retail', 'Skincare', 'Fragrance', 'Hospitality', 'Consultation')
        ->and($definition->bestFit)->toContain('Skincare retailers', 'Fragrance houses', 'Apothecary brands')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'ritual-guide',
            'product-families',
            'features',
            'store-consultation',
            'ingredient-notes',
            'usage-guidance',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('quiet-luxury-retail')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeQuietLuxuryRetailHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
