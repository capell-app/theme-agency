<?php

declare(strict_types=1);

use Capell\ThemeStudio\CharacterPortfolioIndex\CharacterPortfolioIndexThemeServiceProvider;
use Capell\ThemeStudio\CharacterPortfolioIndex\Health\ThemeCharacterPortfolioIndexHealthCheck;

it('defines the character-portfolio-index renderer contract', function (): void {
    $definition = CharacterPortfolioIndexThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-character-portfolio-index')
        ->and($definition->key)->toBe(CharacterPortfolioIndexThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Character Portfolio Index')
        ->and($definition->description)->toContain('Characterful portfolio index theme')
        ->and($definition->tags)->toContain('Portfolio', 'Curation', 'Creators', 'Directory', 'Recommendations')
        ->and($definition->bestFit)->toContain('Portfolio indexes', 'Creator directories', 'Personal site galleries')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'showcase-headline',
            'category-tabs',
            'curated-grid',
            'standout-notes',
            'related-recommendations',
            'creator-summary',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('character-portfolio-index')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeCharacterPortfolioIndexHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
