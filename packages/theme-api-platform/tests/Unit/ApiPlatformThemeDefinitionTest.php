<?php

declare(strict_types=1);

use Capell\ThemeStudio\ApiPlatform\ApiPlatformThemeServiceProvider;
use Capell\ThemeStudio\ApiPlatform\Health\ThemeApiPlatformHealthCheck;

it('defines the api-platform renderer contract', function (): void {
    $definition = ApiPlatformThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-api-platform')
        ->and($definition->key)->toBe(ApiPlatformThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('API Platform')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('api-platform')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeApiPlatformHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
