<?php

declare(strict_types=1);

use Capell\ThemeStudio\FrontRow\FrontRowThemeServiceProvider;
use Capell\ThemeStudio\FrontRow\Health\ThemeFrontRowHealthCheck;

it('defines the front-row renderer contract', function (): void {
    $definition = FrontRowThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-front-row')
        ->and($definition->key)->toBe(FrontRowThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Front Row')
        ->and($definition->description)->toContain('Awarded portfolios and design-education picks')
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
        ->and($definition->presets)->toHaveCount(3)
        ->and($definition->presets[0]->key)->toBe('front-row')
        ->and($definition->presets[2]->key)->toBe('hand-picked')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFrontRowHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('renders looping hero video media with a large header image', function (): void {
    view()->addNamespace('capell-theme-front-row', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-front-row::sections.hero', [
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
    view()->addNamespace('capell-theme-front-row', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-front-row::sections.hero', [
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
