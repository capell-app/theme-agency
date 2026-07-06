<?php

declare(strict_types=1);

use Capell\ThemeStudio\OpenStudio\Health\ThemeOpenStudioHealthCheck;
use Capell\ThemeStudio\OpenStudio\OpenStudioThemeServiceProvider;

it('defines the open-studio renderer contract', function (): void {
    $definition = OpenStudioThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-open-studio')
        ->and($definition->key)->toBe(OpenStudioThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Open Studio')
        ->and($definition->description)->toContain('Put the work centre-stage')
        ->and($definition->tags)->toContain('Case Studies', 'Portfolio', 'Creative Talent', 'Project Feed', 'Hiring')
        ->and($definition->bestFit)->toContain('Creative portfolio platforms', 'Case study archives', 'Talent discovery sites')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'creator-hero',
            'discipline-filters',
            'project-feed',
            'process-notes',
            'related-projects',
            'credits-tools',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
            'filmstrip-project-showcase',
            'discipline-carousel-browse',
            'process-notes-timeline',
            'credits-grid-roster',
            'next-project-cta',
        )
        ->and($definition->frontend['sectionVariants'])->toBe([
            'hero' => ['default', 'split'],
            'filmstrip-project-showcase' => ['default', 'compact'],
            'discipline-carousel-browse' => ['default', 'sticky'],
            'process-notes-timeline' => ['default', 'compact'],
            'credits-grid-roster' => ['default', 'rows'],
            'next-project-cta' => ['default', 'stacked'],
        ])
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('open-studio')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeOpenStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
