<?php

declare(strict_types=1);

use Capell\ThemeStudio\PortfolioDirectory\Health\ThemePortfolioDirectoryHealthCheck;
use Capell\ThemeStudio\PortfolioDirectory\PortfolioDirectoryThemeServiceProvider;

it('defines the portfolio-directory renderer contract', function (): void {
    $definition = PortfolioDirectoryThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-portfolio-directory')
        ->and($definition->key)->toBe(PortfolioDirectoryThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Portfolio Directory')
        ->and($definition->description)->toContain('Portfolio directory theme')
        ->and($definition->tags)->toContain('Portfolio', 'Directory', 'Designers', 'Developers', 'Resources')
        ->and($definition->bestFit)->toContain('Portfolio directories', 'Designer galleries', 'Developer portfolios')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'directory-hero',
            'role-filters',
            'portfolio-grid',
            'resume-resources',
            'curated-lists',
            'profile-detail',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('portfolio-directory')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePortfolioDirectoryHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
