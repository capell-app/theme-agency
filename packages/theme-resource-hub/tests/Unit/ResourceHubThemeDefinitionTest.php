<?php

declare(strict_types=1);

use Capell\ThemeStudio\ResourceHub\Health\ThemeResourceHubHealthCheck;
use Capell\ThemeStudio\ResourceHub\ResourceHubThemeServiceProvider;

it('defines the resource-hub renderer contract', function (): void {
    $definition = ResourceHubThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-resource-hub')
        ->and($definition->key)->toBe(ResourceHubThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Resource Hub')
        ->and($definition->description)->toContain('landing-page inspiration and education hubs')
        ->and($definition->tags)->toContain('Resource Hub', 'Landing Pages', 'Education', 'Templates', 'Archives')
        ->and($definition->bestFit)->toContain('Landing page inspiration hubs', 'Website example libraries', 'Template and course directories')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'utility-hero',
            'category-navigation',
            'website-examples',
            'social-templates',
            'courses-books',
            'learning-resources',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('resource-hub')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeResourceHubHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
