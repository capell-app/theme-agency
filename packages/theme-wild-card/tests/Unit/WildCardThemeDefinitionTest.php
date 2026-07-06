<?php

declare(strict_types=1);

use Capell\ThemeStudio\WildCard\Health\ThemeWildCardHealthCheck;
use Capell\ThemeStudio\WildCard\WildCardThemeServiceProvider;

it('defines the wild-card renderer contract', function (): void {
    $definition = WildCardThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-wild-card')
        ->and($definition->key)->toBe(WildCardThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Wild Card')
        ->and($definition->description)->toContain('experimental streak')
        ->and($definition->tags)->toContain('Wild Card', 'Creative Awards', 'Portfolio', 'Collections', 'Submissions')
        ->and($definition->bestFit)->toContain('Creative industry directories', 'Agency and studio showcases', 'Portfolio-heavy CMS sites')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'featured-today-banner',
            'metadata-facet-wall',
            'card-shuffle-grid',
            'winners-ledger-table',
            'submission-pulse',
            'infinite-scroll-depth-pressure',
            'profiles-resources',
            'sponsor-modules',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->frontend['sectionVariants'])->toBe([
            'card-shuffle-grid' => ['default', 'compact'],
            'metadata-facet-wall' => ['default', 'dense'],
            'featured-today-banner' => ['default', 'split'],
            'winners-ledger-table' => ['default', 'collections'],
            'submission-pulse' => ['default', 'compact'],
            'infinite-scroll-depth-pressure' => ['default', 'rows'],
            'time-capsule-browser' => ['default', 'cabinet'],
        ])
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('wild-card')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeWildCardHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
