<?php

declare(strict_types=1);

use Capell\ThemeStudio\QuietWebGallery\Health\ThemeQuietWebGalleryHealthCheck;
use Capell\ThemeStudio\QuietWebGallery\QuietWebGalleryThemeServiceProvider;

it('defines the quiet-web-gallery renderer contract', function (): void {
    $definition = QuietWebGalleryThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-quiet-web-gallery')
        ->and($definition->key)->toBe(QuietWebGalleryThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Quiet Web Gallery')
        ->and($definition->description)->toContain('calm web-design galleries')
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
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('quiet-web-gallery')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeQuietWebGalleryHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
