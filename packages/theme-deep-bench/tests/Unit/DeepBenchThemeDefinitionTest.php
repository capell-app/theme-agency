<?php

declare(strict_types=1);

use Capell\ThemeStudio\DeepBench\DeepBenchThemeServiceProvider;
use Capell\ThemeStudio\DeepBench\Health\ThemeDeepBenchHealthCheck;

it('defines the deep-bench renderer contract', function (): void {
    $definition = DeepBenchThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-deep-bench')
        ->and($definition->key)->toBe(DeepBenchThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Deep Bench')
        ->and($definition->description)->toContain('who\'s-who of designers and developers')
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
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('deep-bench')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeDeepBenchHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
