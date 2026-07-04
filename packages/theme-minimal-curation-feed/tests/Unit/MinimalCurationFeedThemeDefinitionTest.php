<?php

declare(strict_types=1);

use Capell\ThemeStudio\MinimalCurationFeed\Health\ThemeMinimalCurationFeedHealthCheck;
use Capell\ThemeStudio\MinimalCurationFeed\MinimalCurationFeedThemeServiceProvider;

it('defines the minimal-curation-feed renderer contract', function (): void {
    $definition = MinimalCurationFeedThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-minimal-curation-feed')
        ->and($definition->key)->toBe(MinimalCurationFeedThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Minimal Curation Feed')
        ->and($definition->description)->toContain('daily curated links')
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
        ->and($definition->presets[0]->key)->toBe('minimal-curation-feed')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeMinimalCurationFeedHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
