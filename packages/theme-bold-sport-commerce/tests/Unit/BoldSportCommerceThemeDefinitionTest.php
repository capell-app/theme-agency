<?php

declare(strict_types=1);

use Capell\ThemeStudio\BoldSportCommerce\BoldSportCommerceThemeServiceProvider;
use Capell\ThemeStudio\BoldSportCommerce\Health\ThemeBoldSportCommerceHealthCheck;

it('defines the bold-sport-commerce renderer contract', function (): void {
    $definition = BoldSportCommerceThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-bold-sport-commerce')
        ->and($definition->key)->toBe(BoldSportCommerceThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Bold Sport Commerce')
        ->and($definition->description)->toContain('sport commerce')
        ->and($definition->tags)->toContain('Sport', 'Commerce', 'Campaigns', 'Drops', 'Training')
        ->and($definition->bestFit)->toContain('Sport retailers', 'Team stores', 'Training communities')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'campaign-launch',
            'category-jumps',
            'features',
            'community-offer',
            'training-stories',
            'fit-specs',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('bold-sport-commerce')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeBoldSportCommerceHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
