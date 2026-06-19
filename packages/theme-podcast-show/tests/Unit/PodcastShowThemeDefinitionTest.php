<?php

declare(strict_types=1);

use Capell\ThemeStudio\PodcastShow\Health\ThemePodcastShowHealthCheck;
use Capell\ThemeStudio\PodcastShow\PodcastShowThemeServiceProvider;

it('defines the podcast-show renderer contract', function (): void {
    $definition = PodcastShowThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-podcast-show')
        ->and($definition->key)->toBe(PodcastShowThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Podcast Show')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('podcast-show')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePodcastShowHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
