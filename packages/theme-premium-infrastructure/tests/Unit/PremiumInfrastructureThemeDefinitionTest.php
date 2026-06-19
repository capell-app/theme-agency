<?php

declare(strict_types=1);

use Capell\ThemeStudio\PremiumInfrastructure\Health\ThemePremiumInfrastructureHealthCheck;
use Capell\ThemeStudio\PremiumInfrastructure\PremiumInfrastructureThemeServiceProvider;

it('defines the premium-infrastructure renderer contract', function (): void {
    $definition = PremiumInfrastructureThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-premium-infrastructure')
        ->and($definition->key)->toBe(PremiumInfrastructureThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Premium Infrastructure')
        ->and($definition->description)->toContain('Premium Infrastructure theme')
        ->and($definition->tags)->toContain('Infrastructure', 'B2B SaaS', 'Developer Tools', 'Global Scale', 'Trust')
        ->and($definition->bestFit)->toContain('Infrastructure platforms', 'Complex B2B SaaS', 'Developer tool companies')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'product-panels',
            'solutions',
            'global-scale',
            'developer-tools',
            'case-studies-news',
            'trust-compliance',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('premium-infrastructure')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePremiumInfrastructureHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
