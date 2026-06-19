<?php

declare(strict_types=1);

use Capell\ThemeStudio\PremiumPortfolioCollection\Health\ThemePremiumPortfolioCollectionHealthCheck;
use Capell\ThemeStudio\PremiumPortfolioCollection\PremiumPortfolioCollectionThemeServiceProvider;

it('defines the premium-portfolio-collection renderer contract', function (): void {
    $definition = PremiumPortfolioCollectionThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-premium-portfolio-collection')
        ->and($definition->key)->toBe(PremiumPortfolioCollectionThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Premium Portfolio Collection')
        ->and($definition->description)->toContain('Premium portfolio collection theme')
        ->and($definition->tags)->toContain('Portfolio', 'Directory', 'Awards', 'Creators', 'Gallery')
        ->and($definition->bestFit)->toContain('Portfolio directories', 'Creative award sites', 'Design education hubs')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'featured-portfolios',
            'filter-taxonomies',
            'portfolio-grid',
            'awarded-profiles',
            'creator-directory',
            'education-upsell',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('premium-portfolio-collection')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePremiumPortfolioCollectionHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
