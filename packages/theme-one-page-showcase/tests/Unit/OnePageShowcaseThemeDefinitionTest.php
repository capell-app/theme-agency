<?php

declare(strict_types=1);

use Capell\ThemeStudio\OnePageShowcase\Health\ThemeOnePageShowcaseHealthCheck;
use Capell\ThemeStudio\OnePageShowcase\OnePageShowcaseThemeServiceProvider;

it('defines the one-page-showcase renderer contract', function (): void {
    $definition = OnePageShowcaseThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-one-page-showcase')
        ->and($definition->key)->toBe(OnePageShowcaseThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('One Page Showcase')
        ->and($definition->description)->toContain('One Page Showcase theme')
        ->and($definition->tags)->toContain('One Page', 'Showcase', 'Gallery', 'Templates', 'Resources')
        ->and($definition->bestFit)->toContain('One-page website galleries', 'Landing page showcases', 'Template directories')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'showcase-hero',
            'category-tabs',
            'one-page-grid',
            'templates-sections',
            'tools-sponsors',
            'build-resources',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('one-page-showcase')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeOnePageShowcaseHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
