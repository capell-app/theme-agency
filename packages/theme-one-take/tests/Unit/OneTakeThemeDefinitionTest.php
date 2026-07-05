<?php

declare(strict_types=1);

use Capell\ThemeStudio\OneTake\Health\ThemeOneTakeHealthCheck;
use Capell\ThemeStudio\OneTake\OneTakeThemeServiceProvider;

it('defines the one-take renderer contract', function (): void {
    $definition = OneTakeThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-one-take')
        ->and($definition->key)->toBe(OneTakeThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('One Take')
        ->and($definition->description)->toContain('one continuous take')
        ->and($definition->tags)->toContain('One Page', 'Showcase', 'Gallery', 'Templates', 'Resources')
        ->and($definition->bestFit)->toContain('One-page website galleries', 'Landing page showcases', 'Template directories')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'showcase-hero',
            'category-tabs',
            'one-page-grid',
            'templates-sections',
            'tools-sponsors',
            'build-resources',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('one-take')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeOneTakeHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
