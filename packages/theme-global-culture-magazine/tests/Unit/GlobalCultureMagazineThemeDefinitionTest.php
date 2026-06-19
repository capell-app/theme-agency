<?php

declare(strict_types=1);

use Capell\ThemeStudio\GlobalCultureMagazine\GlobalCultureMagazineThemeServiceProvider;
use Capell\ThemeStudio\GlobalCultureMagazine\Health\ThemeGlobalCultureMagazineHealthCheck;

it('defines the global-culture-magazine renderer contract', function (): void {
    $definition = GlobalCultureMagazineThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-global-culture-magazine')
        ->and($definition->key)->toBe(GlobalCultureMagazineThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Global Culture Magazine')
        ->and($definition->description)->toContain('Premium global culture magazine theme')
        ->and($definition->tags)->toContain('Magazine', 'Global Affairs', 'Travel', 'Culture', 'Audio')
        ->and($definition->bestFit)->toContain('Global magazines', 'Culture publishers', 'City guide brands')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'lead-dispatch',
            'radio-audio',
            'city-guides',
            'travel-culture',
            'shop-books',
            'columnists',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('global-culture-magazine')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeGlobalCultureMagazineHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
