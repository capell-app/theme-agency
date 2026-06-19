<?php

declare(strict_types=1);

use Capell\ThemeStudio\PremiumProductStory\Health\ThemePremiumProductStoryHealthCheck;
use Capell\ThemeStudio\PremiumProductStory\PremiumProductStoryThemeServiceProvider;

it('defines the premium-product-story renderer contract', function (): void {
    $definition = PremiumProductStoryThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-premium-product-story')
        ->and($definition->key)->toBe(PremiumProductStoryThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Premium Product Story')
        ->and($definition->description)->toContain('consumer product storytelling')
        ->and($definition->tags)->toContain('Product', 'Consumer Brand', 'Storytelling', 'Comparison', 'Launch')
        ->and($definition->bestFit)->toContain('Consumer electronics brands', 'Hardware startups', 'Design-led ecommerce')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'product-families',
            'feature-highlights',
            'features',
            'ecosystem-story',
            'gallery-strip',
            'spec-comparison',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('premium-product-story')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePremiumProductStoryHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
