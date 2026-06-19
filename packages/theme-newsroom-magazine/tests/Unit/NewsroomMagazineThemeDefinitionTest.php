<?php

declare(strict_types=1);

use Capell\ThemeStudio\NewsroomMagazine\Health\ThemeNewsroomMagazineHealthCheck;
use Capell\ThemeStudio\NewsroomMagazine\NewsroomMagazineThemeServiceProvider;

it('defines the newsroom-magazine renderer contract', function (): void {
    $definition = NewsroomMagazineThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-newsroom-magazine')
        ->and($definition->key)->toBe(NewsroomMagazineThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Newsroom Magazine')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('newsroom-magazine')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeNewsroomMagazineHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
