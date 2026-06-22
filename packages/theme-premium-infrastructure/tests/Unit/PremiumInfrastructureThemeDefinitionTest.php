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

it('renders looping hero video media with a large header image', function (): void {
    view()->addNamespace('capell-theme-premium-infrastructure', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-premium-infrastructure::sections.hero', [
        'section' => [
            'preview_video_url' => '/media/infrastructure-demo.mp4',
            'preview_image_url' => '/media/infrastructure-poster.jpg',
            'preview_alt' => 'Infrastructure product walkthrough',
            'header_image_url' => '/media/infrastructure-header.jpg',
            'header_image_alt' => 'Infrastructure dashboard header',
        ],
    ])->render();

    expect($html)
        ->toContain('<video')
        ->toContain('autoplay')
        ->toContain('loop')
        ->toContain('muted')
        ->toContain('playsinline')
        ->toContain('poster="/media/infrastructure-poster.jpg"')
        ->toContain('src="/media/infrastructure-demo.mp4"')
        ->toContain('aria-label="Infrastructure product walkthrough"')
        ->toContain('src="/media/infrastructure-header.jpg"')
        ->toContain('alt="Infrastructure dashboard header"');
});

it('renders looping hero gif media without requiring a video source', function (): void {
    view()->addNamespace('capell-theme-premium-infrastructure', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-premium-infrastructure::sections.hero', [
        'section' => [
            'media' => [
                'preview_gif_url' => '/media/infrastructure-loop.gif',
                'preview_alt' => 'Animated infrastructure loop',
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('src="/media/infrastructure-loop.gif"')
        ->toContain('alt="Animated infrastructure loop"')
        ->not->toContain('<video');
});
