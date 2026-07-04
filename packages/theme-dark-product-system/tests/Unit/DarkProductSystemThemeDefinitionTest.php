<?php

declare(strict_types=1);

use Capell\ThemeStudio\DarkProductSystem\DarkProductSystemThemeServiceProvider;
use Capell\ThemeStudio\DarkProductSystem\Health\ThemeDarkProductSystemHealthCheck;

it('defines the dark-product-system renderer contract', function (): void {
    $definition = DarkProductSystemThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-dark-product-system')
        ->and($definition->key)->toBe(DarkProductSystemThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Dark Product System')
        ->and($definition->description)->toContain('Dark Product System theme')
        ->and($definition->tags)->toContain('Dark SaaS', 'Product System', 'Workflows', 'Automation', 'Security')
        ->and($definition->bestFit)->toContain('Product-led SaaS', 'Workflow platforms', 'Technical operations tools')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'system-hero',
            'workflow-rails',
            'agents-automation',
            'planning-roadmap',
            'changelog-integrations',
            'security-proof',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('dark-product-system')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeDarkProductSystemHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
