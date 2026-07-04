<?php

declare(strict_types=1);

use Capell\ThemeStudio\ExperimentalDirectory\ExperimentalDirectoryThemeServiceProvider;
use Capell\ThemeStudio\ExperimentalDirectory\Health\ThemeExperimentalDirectoryHealthCheck;

it('defines the experimental-directory renderer contract', function (): void {
    $definition = ExperimentalDirectoryThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-experimental-directory')
        ->and($definition->key)->toBe(ExperimentalDirectoryThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Experimental Directory')
        ->and($definition->description)->toContain('creative industry directories')
        ->and($definition->tags)->toContain('Experimental Directory', 'Creative Awards', 'Portfolio', 'Collections', 'Submissions')
        ->and($definition->bestFit)->toContain('Creative industry directories', 'Agency and studio showcases', 'Portfolio-heavy CMS sites')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'featured-today',
            'metadata-filters',
            'latest-submissions',
            'winners-collections',
            'profiles-resources',
            'sponsor-modules',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('experimental-directory')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeExperimentalDirectoryHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
