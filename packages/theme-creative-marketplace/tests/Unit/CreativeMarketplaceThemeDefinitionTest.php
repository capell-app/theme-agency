<?php

declare(strict_types=1);

use Capell\ThemeStudio\CreativeMarketplace\CreativeMarketplaceThemeServiceProvider;
use Capell\ThemeStudio\CreativeMarketplace\Health\ThemeCreativeMarketplaceHealthCheck;

it('defines the creative-marketplace renderer contract', function (): void {
    $definition = CreativeMarketplaceThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-creative-marketplace')
        ->and($definition->key)->toBe(CreativeMarketplaceThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Creative Marketplace')
        ->and($definition->description)->toContain('Creative marketplace theme')
        ->and($definition->tags)->toContain('Marketplace', 'Creative Talent', 'Designers', 'Hiring', 'Shots')
        ->and($definition->bestFit)->toContain('Creative marketplaces', 'Designer hiring platforms', 'Visual work galleries')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'service-hero',
            'category-chips',
            'shot-grid',
            'briefs-pricing',
            'agencies-services',
            'profile-availability',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('creative-marketplace')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeCreativeMarketplaceHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
