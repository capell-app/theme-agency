<?php

declare(strict_types=1);

use Capell\ThemeStudio\LaunchPad\Health\ThemeLaunchPadHealthCheck;
use Capell\ThemeStudio\LaunchPad\LaunchPadThemeServiceProvider;

it('defines the launch-pad renderer contract', function (): void {
    $definition = LaunchPadThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-launch-pad')
        ->and($definition->key)->toBe(LaunchPadThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Launch Pad')
        ->and($definition->description)->toContain('showcase gallery for landing pages and templates')
        ->and($definition->tags)->toContain('Landing Pages', 'Gallery', 'Templates', 'SaaS', 'Marketplace')
        ->and($definition->bestFit)->toContain('Landing page galleries', 'SaaS inspiration libraries', 'Template marketplaces')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'utility-hero',
            'category-navigation',
            'website-examples',
            'paid-templates',
            'partner-blocks',
            'gallery-system',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('launch-pad')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeLaunchPadHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
