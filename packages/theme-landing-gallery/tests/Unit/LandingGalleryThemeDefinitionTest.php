<?php

declare(strict_types=1);

use Capell\ThemeStudio\LandingGallery\Health\ThemeLandingGalleryHealthCheck;
use Capell\ThemeStudio\LandingGallery\LandingGalleryThemeServiceProvider;

it('defines the landing-gallery renderer contract', function (): void {
    $definition = LandingGalleryThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-landing-gallery')
        ->and($definition->key)->toBe(LandingGalleryThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Landing Gallery')
        ->and($definition->description)->toContain('polished SaaS, ecommerce, and startup landing-page galleries')
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
        ->and($definition->presets[0]->key)->toBe('landing-gallery')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeLandingGalleryHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
