<?php

declare(strict_types=1);

use Capell\ThemeStudio\FirstLight\FirstLightThemeServiceProvider;
use Capell\ThemeStudio\FirstLight\Health\ThemeFirstLightHealthCheck;

it('defines the first-light renderer contract', function (): void {
    $definition = FirstLightThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-first-light')
        ->and($definition->key)->toBe(FirstLightThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('First Light')
        ->and($definition->description)->toContain('calm daily feed')
        ->and($definition->tags)->toContain('Curation Feed', 'Minimal', 'Screenshots', 'Apps', 'References')
        ->and($definition->bestFit)->toContain('Daily inspiration feeds', 'Product reference libraries', 'Icon inspiration archives')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'feed-hero',
            'category-tabs',
            'curation-feed',
            'best-of-views',
            'app-website-icons',
            'source-metadata',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('first-light')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFirstLightHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
