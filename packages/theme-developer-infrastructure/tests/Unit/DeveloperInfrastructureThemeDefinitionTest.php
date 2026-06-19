<?php

declare(strict_types=1);

use Capell\ThemeStudio\DeveloperInfrastructure\DeveloperInfrastructureThemeServiceProvider;
use Capell\ThemeStudio\DeveloperInfrastructure\Health\ThemeDeveloperInfrastructureHealthCheck;

it('defines the developer-infrastructure renderer contract', function (): void {
    $definition = DeveloperInfrastructureThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-developer-infrastructure')
        ->and($definition->key)->toBe(DeveloperInfrastructureThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Developer Infrastructure')
        ->and($definition->description)->toContain('Developer Infrastructure theme')
        ->and($definition->tags)->toContain('Developer Platform', 'Infrastructure', 'Docs', 'Deployments', 'Enterprise')
        ->and($definition->bestFit)->toContain('Developer platforms', 'Infrastructure products', 'API products')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'platform-pillars',
            'command-blocks',
            'deploy-timeline',
            'docs-changelog',
            'integrations',
            'architecture-enterprise',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('developer-infrastructure')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeDeveloperInfrastructureHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
