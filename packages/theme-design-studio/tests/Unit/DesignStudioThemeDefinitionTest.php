<?php

declare(strict_types=1);

use Capell\ThemeStudio\DesignStudio\DesignStudioThemeServiceProvider;
use Capell\ThemeStudio\DesignStudio\Health\ThemeDesignStudioHealthCheck;

it('defines the design-studio renderer contract', function (): void {
    $definition = DesignStudioThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-design-studio')
        ->and($definition->key)->toBe(DesignStudioThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Design Studio')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('design-studio')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeDesignStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
