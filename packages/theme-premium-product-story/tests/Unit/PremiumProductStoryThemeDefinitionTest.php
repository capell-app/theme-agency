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

it('renders looping hero video media with a large header image', function (): void {
    view()->addNamespace('capell-theme-premium-product-story', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-premium-product-story::sections.hero', [
        'section' => [
            'preview_video_url' => '/media/product-demo.mp4',
            'preview_image_url' => '/media/product-poster.jpg',
            'preview_alt' => 'Product walkthrough',
            'header_image_url' => '/media/product-header.jpg',
            'header_image_alt' => 'Product range on a clean surface',
        ],
    ])->render();

    expect($html)
        ->toContain('<video')
        ->toContain('autoplay')
        ->toContain('loop')
        ->toContain('muted')
        ->toContain('playsinline')
        ->toContain('poster="/media/product-poster.jpg"')
        ->toContain('src="/media/product-demo.mp4"')
        ->toContain('aria-label="Product walkthrough"')
        ->toContain('src="/media/product-header.jpg"')
        ->toContain('alt="Product range on a clean surface"');
});

it('renders looping hero gif media without requiring a video source', function (): void {
    view()->addNamespace('capell-theme-premium-product-story', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-premium-product-story::sections.hero', [
        'section' => [
            'media' => [
                'preview_gif_url' => '/media/product-loop.gif',
                'preview_alt' => 'Animated product loop',
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('src="/media/product-loop.gif"')
        ->toContain('alt="Animated product loop"')
        ->not->toContain('<video');
});
