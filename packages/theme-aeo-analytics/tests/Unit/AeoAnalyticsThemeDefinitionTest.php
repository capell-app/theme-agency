<?php

declare(strict_types=1);

use Capell\ThemeStudio\AeoAnalytics\AeoAnalyticsThemeServiceProvider;
use Capell\ThemeStudio\AeoAnalytics\Health\ThemeAeoAnalyticsHealthCheck;

it('defines the aeo-analytics renderer contract', function (): void {
    $definition = AeoAnalyticsThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-aeo-analytics')
        ->and($definition->key)->toBe(AeoAnalyticsThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('AEO Analytics')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('aeo-analytics')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeAeoAnalyticsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
