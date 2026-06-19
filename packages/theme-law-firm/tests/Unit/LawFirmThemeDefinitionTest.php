<?php

declare(strict_types=1);

use Capell\ThemeStudio\LawFirm\Health\ThemeLawFirmHealthCheck;
use Capell\ThemeStudio\LawFirm\LawFirmThemeServiceProvider;

it('defines the law-firm renderer contract', function (): void {
    $definition = LawFirmThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-law-firm')
        ->and($definition->key)->toBe(LawFirmThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Law Firm')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('law-firm')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeLawFirmHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
