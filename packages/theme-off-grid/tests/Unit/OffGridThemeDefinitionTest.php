<?php

declare(strict_types=1);

use Capell\ThemeStudio\OffGrid\Health\ThemeOffGridHealthCheck;
use Capell\ThemeStudio\OffGrid\OffGridThemeServiceProvider;

it('defines the off-grid renderer contract', function (): void {
    $definition = OffGridThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-off-grid')
        ->and($definition->key)->toBe(OffGridThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Off Grid')
        ->and($definition->description)->toContain('zine-grade archive')
        ->and($definition->tags)->toContain('Off Grid', 'Archive', 'Experimental', 'Culture', 'Monospace')
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
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('off-grid')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeOffGridHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
