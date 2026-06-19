<?php

declare(strict_types=1);

use Capell\ThemeStudio\DevtoolOss\DevtoolOssThemeServiceProvider;
use Capell\ThemeStudio\DevtoolOss\Health\ThemeDevtoolOssHealthCheck;

it('defines the devtool-oss renderer contract', function (): void {
    $definition = DevtoolOssThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-devtool-oss')
        ->and($definition->key)->toBe(DevtoolOssThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Devtool OSS')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('devtool-oss')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeDevtoolOssHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
