<?php

declare(strict_types=1);

use Capell\ThemeStudio\MotionArchive\Health\ThemeMotionArchiveHealthCheck;
use Capell\ThemeStudio\MotionArchive\MotionArchiveThemeServiceProvider;

it('defines the motion-archive renderer contract', function (): void {
    $definition = MotionArchiveThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-motion-archive')
        ->and($definition->key)->toBe(MotionArchiveThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Motion Archive')
        ->and($definition->description)->toContain('digital-awards archives')
        ->and($definition->tags)->toContain('Motion Archive', 'Awards', 'Digital Projects', 'Video', 'Credits')
        ->and($definition->bestFit)->toContain('Digital awards archives', 'Motion preview galleries', 'Historical winner archives')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'archive-hero',
            'date-filter-rail',
            'winner-list',
            'featured-project',
            'jury-score-explainer',
            'media-credits',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('motion-archive')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeMotionArchiveHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
