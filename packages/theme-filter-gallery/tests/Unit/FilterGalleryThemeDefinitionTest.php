<?php

declare(strict_types=1);

use Capell\ThemeStudio\FilterGallery\FilterGalleryThemeServiceProvider;
use Capell\ThemeStudio\FilterGallery\Health\ThemeFilterGalleryHealthCheck;

it('defines the filter-gallery renderer contract', function (): void {
    $definition = FilterGalleryThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-filter-gallery')
        ->and($definition->key)->toBe(FilterGalleryThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Filter Gallery')
        ->and($definition->description)->toContain('broad inspiration libraries')
        ->and($definition->tags)->toContain('Inspiration Library', 'Filters', 'Gallery', 'Taxonomies', 'Search')
        ->and($definition->bestFit)->toContain('Large website inspiration libraries', 'Design reference archives', 'Multi-taxonomy galleries')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'filter-hero',
            'taxonomy-navigation',
            'editor-picks',
            'latest-designs',
            'blog-mission',
            'faq-archives',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('filter-gallery')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFilterGalleryHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
