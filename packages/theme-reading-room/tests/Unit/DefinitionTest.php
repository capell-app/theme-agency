<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ReadingRoom\ReadingRoomThemeServiceProvider;

it('defines the reading-room theme contract', function (): void {
    $definition = ReadingRoomThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-reading-room')
        ->and($definition->key)->toBe(ReadingRoomThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Reading Room')
        ->and($definition->extends)->toBe('default')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->presets)->toHaveCount(2);

    $presetKeys = array_map(static fn ($preset): string => $preset->key, $definition->presets);

    expect($presetKeys)->toBe(['reading-room', 'late-edition']);
});

it('registers reading-room as definition-only with no legacy renderer', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(ReadingRoomThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new ReadingRoomThemeServiceProvider(app());
    $provider->boot($registry);

    expect($registry->hasRenderer(ReadingRoomThemeServiceProvider::THEME_KEY))->toBeFalse();

    CapellCore::clearPackages();
});
