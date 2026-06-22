<?php

declare(strict_types=1);

use Capell\ThemeStudio\PremiumPortfolioCollection\Health\ThemePremiumPortfolioCollectionHealthCheck;
use Capell\ThemeStudio\PremiumPortfolioCollection\PremiumPortfolioCollectionThemeServiceProvider;

it('defines the premium-portfolio-collection renderer contract', function (): void {
    $definition = PremiumPortfolioCollectionThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-premium-portfolio-collection')
        ->and($definition->key)->toBe(PremiumPortfolioCollectionThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Premium Portfolio Collection')
        ->and($definition->description)->toContain('Premium portfolio collection theme')
        ->and($definition->tags)->toContain('Portfolio', 'Directory', 'Awards', 'Creators', 'Gallery')
        ->and($definition->bestFit)->toContain('Portfolio directories', 'Creative award sites', 'Design education hubs')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'featured-portfolios',
            'filter-taxonomies',
            'portfolio-grid',
            'awarded-profiles',
            'creator-directory',
            'education-upsell',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('premium-portfolio-collection')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemePremiumPortfolioCollectionHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('renders looping hero video media with a large header image', function (): void {
    view()->addNamespace('capell-theme-premium-portfolio-collection', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-premium-portfolio-collection::sections.hero', [
        'section' => [
            'preview_video_url' => '/media/portfolio-demo.mp4',
            'preview_image_url' => '/media/portfolio-poster.jpg',
            'preview_alt' => 'Portfolio gallery walkthrough',
            'header_image_url' => '/media/portfolio-header.jpg',
            'header_image_alt' => 'Portfolio collection grid',
        ],
    ])->render();

    expect($html)
        ->toContain('<video')
        ->toContain('autoplay')
        ->toContain('loop')
        ->toContain('muted')
        ->toContain('playsinline')
        ->toContain('poster="/media/portfolio-poster.jpg"')
        ->toContain('src="/media/portfolio-demo.mp4"')
        ->toContain('aria-label="Portfolio gallery walkthrough"')
        ->toContain('src="/media/portfolio-header.jpg"')
        ->toContain('alt="Portfolio collection grid"');
});

it('renders looping hero gif media without requiring a video source', function (): void {
    view()->addNamespace('capell-theme-premium-portfolio-collection', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-premium-portfolio-collection::sections.hero', [
        'section' => [
            'media' => [
                'preview_gif_url' => '/media/portfolio-loop.gif',
                'preview_alt' => 'Animated portfolio loop',
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('src="/media/portfolio-loop.gif"')
        ->toContain('alt="Animated portfolio loop"')
        ->not->toContain('<video');
});
