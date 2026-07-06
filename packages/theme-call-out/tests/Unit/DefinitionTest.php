<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\CallOut\CallOutThemeServiceProvider;

it('defines the call-out theme contract', function (): void {
    $definition = CallOutThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-call-out')
        ->and($definition->key)->toBe(CallOutThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Call Out')
        ->and($definition->extends)->toBe('default')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->presets)->toHaveCount(2);
});

it('declares the call-out and after-hours presets with distinct surface colours', function (): void {
    $definition = CallOutThemeServiceProvider::definition();

    $presetKeys = collect($definition->presets)->map(fn ($preset) => $preset->key)->all();

    expect($presetKeys)->toBe(['call-out', 'after-hours']);

    $callOutPreset = collect($definition->presets)->firstWhere('key', 'call-out');
    $afterHoursPreset = collect($definition->presets)->firstWhere('key', 'after-hours');

    throw_unless($callOutPreset instanceof ThemePresetData, RuntimeException::class, 'Expected a "call-out" preset.');
    throw_unless($afterHoursPreset instanceof ThemePresetData, RuntimeException::class, 'Expected an "after-hours" preset.');

    expect($callOutPreset->values['surfaceColor'])->toBe('#ffffff')
        ->and($afterHoursPreset->values['surfaceColor'])->toBe('#0d1117')
        ->and($callOutPreset->values['surfaceColor'])->not->toBe($afterHoursPreset->values['surfaceColor']);
});

it('registers call-out as definition-only with no legacy renderer', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CallOutThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CallOutThemeServiceProvider(app());
    $provider->boot($registry);

    expect($registry->hasRenderer(CallOutThemeServiceProvider::THEME_KEY))->toBeFalse();

    CapellCore::clearPackages();
});
