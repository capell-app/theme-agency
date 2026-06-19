<?php

declare(strict_types=1);

use Capell\ThemeStudio\CaseStudyPlatform\CaseStudyPlatformThemeServiceProvider;
use Capell\ThemeStudio\CaseStudyPlatform\Health\ThemeCaseStudyPlatformHealthCheck;

it('defines the case-study-platform renderer contract', function (): void {
    $definition = CaseStudyPlatformThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-case-study-platform')
        ->and($definition->key)->toBe(CaseStudyPlatformThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Case Study Platform')
        ->and($definition->description)->toContain('Creative case study platform theme')
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
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('case-study-platform')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeCaseStudyPlatformHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
