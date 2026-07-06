<?php

declare(strict_types=1);

use Capell\ThemeStudio\FieldGuide\FieldGuideThemeServiceProvider;
use Capell\ThemeStudio\FieldGuide\Health\ThemeFieldGuideHealthCheck;

it('defines the field-guide renderer contract', function (): void {
    $definition = FieldGuideThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-field-guide')
        ->and($definition->key)->toBe(FieldGuideThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Field Guide')
        ->and($definition->description)->toContain('reference library you can actually navigate')
        ->and($definition->tags)->toContain('Inspiration Library', 'Filters', 'Gallery', 'Taxonomies', 'Search')
        ->and($definition->bestFit)->toContain('Large website inspiration libraries', 'Design reference archives', 'Multi-taxonomy galleries')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'filter-hero',
            'taxonomy-navigation',
            'taxonomy-grid-browser',
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
        ->and($definition->frontend['sectionVariants'] ?? [])->toBe([
            'taxonomy-grid-browser' => ['default', 'compact'],
            'latest-designs' => ['default', 'showcase-wide'],
            'editor-picks' => ['default', 'alternating'],
            'faq-archives' => ['default', 'two-column'],
            'cta' => ['default', 'browse'],
        ])
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('field-guide')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFieldGuideHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
