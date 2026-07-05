<?php

declare(strict_types=1);

use Capell\ThemeStudio\ReelRoom\Health\ThemeReelRoomHealthCheck;
use Capell\ThemeStudio\ReelRoom\ReelRoomThemeServiceProvider;

it('defines the reel-room renderer contract', function (): void {
    $definition = ReelRoomThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-reel-room')
        ->and($definition->key)->toBe(ReelRoomThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Reel Room')
        ->and($definition->description)->toContain('digital-awards archive')
        ->and($definition->tags)->toContain('Reel Room', 'Awards', 'Digital Projects', 'Video', 'Credits')
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
        ->and($definition->presets[0]->key)->toBe('reel-room')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeReelRoomHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
