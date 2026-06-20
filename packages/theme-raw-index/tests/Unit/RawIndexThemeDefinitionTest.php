<?php

declare(strict_types=1);

use Capell\ThemeStudio\RawIndex\Health\ThemeRawIndexHealthCheck;
use Capell\ThemeStudio\RawIndex\RawIndexThemeServiceProvider;

it('defines the raw-index renderer contract', function (): void {
    $definition = RawIndexThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-raw-index')
        ->and($definition->key)->toBe(RawIndexThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Raw Index')
        ->and($definition->description)->toContain('independent archives')
        ->and($definition->tags)->toContain('Raw Index', 'Archive', 'Experimental', 'Culture', 'Monospace')
        ->and($definition->bestFit)->toContain('Independent culture archives', 'Experimental publishing sites', 'Underground zines')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'archive-wall',
            'rough-links',
            'irregular-index',
            'submission-markers',
            'archive-dates',
            'zine-annotations',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('raw-index')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeRawIndexHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
