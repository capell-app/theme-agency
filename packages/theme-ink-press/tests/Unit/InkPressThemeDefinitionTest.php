<?php

declare(strict_types=1);

use Capell\ThemeStudio\InkPress\Health\ThemeInkPressHealthCheck;
use Capell\ThemeStudio\InkPress\InkPressThemeServiceProvider;

it('defines the ink-press renderer contract', function (): void {
    $definition = InkPressThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-ink-press')
        ->and($definition->key)->toBe(InkPressThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Ink Press')
        ->and($definition->description)->toContain('newsroom front page')
        ->and($definition->tags)->toContain('News', 'Analysis', 'Opinion', 'Video', 'Live')
        ->and($definition->bestFit)->toContain('News publishers', 'Policy journals', 'Analysis desks')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'top-stories',
            'live-brief',
            'topic-navigation',
            'opinion-analysis',
            'video-row',
            'missed-it',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('ink-press')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeInkPressHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
