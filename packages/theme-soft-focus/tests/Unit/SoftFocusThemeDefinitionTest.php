<?php

declare(strict_types=1);

use Capell\ThemeStudio\SoftFocus\Health\ThemeSoftFocusHealthCheck;
use Capell\ThemeStudio\SoftFocus\SoftFocusThemeServiceProvider;

it('defines the soft-focus renderer contract', function (): void {
    $definition = SoftFocusThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-soft-focus')
        ->and($definition->key)->toBe(SoftFocusThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Soft Focus')
        ->and($definition->description)->toContain('understated inspiration gallery')
        ->and($definition->tags)->toContain('Web Gallery', 'Quiet', 'Inspiration', 'Archives', 'Editorial')
        ->and($definition->bestFit)->toContain('Calm web design galleries', 'Style and category archives', 'Best-of collections')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'browse-panels',
            'style-type-categories',
            'latest-showcase',
            'sponsor-space',
            'random-best-of',
            'editorial-posts',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->frontend['sectionVariants'] ?? [])->toBe([
            'browse-panels' => ['default', 'scatter'],
            'style-type-categories' => ['default', 'scattered'],
            'latest-showcase' => ['default', 'organic'],
            'sponsor-space' => ['default', 'floating'],
            'random-best-of' => ['default', 'seeded-rotation'],
        ])
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('soft-focus')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeSoftFocusHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
