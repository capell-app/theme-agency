<?php

declare(strict_types=1);

use Capell\ThemeStudio\MinimalFashion\Health\ThemeMinimalFashionHealthCheck;
use Capell\ThemeStudio\MinimalFashion\MinimalFashionThemeServiceProvider;

it('defines the minimal-fashion renderer contract', function (): void {
    $definition = MinimalFashionThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-minimal-fashion')
        ->and($definition->key)->toBe(MinimalFashionThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Minimal Fashion')
        ->and($definition->description)->toContain('fashion retail')
        ->and($definition->tags)->toContain('Fashion', 'Retail', 'Lookbook', 'Product Care', 'Minimal')
        ->and($definition->bestFit)->toContain('Fashion retailers', 'Boutique clothing brands', 'Design-led product catalogues')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'seasonal-collections',
            'category-paths',
            'features',
            'lookbook-feature',
            'material-notes',
            'product-care',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('minimal-fashion')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeMinimalFashionHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
