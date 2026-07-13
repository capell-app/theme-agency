<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeAgency\AgencyThemeServiceProvider;
use Capell\ThemeAgency\Health\ThemeAgencyHealthCheck;

it('defines the agency layout-native contract', function (): void {
    $definition = AgencyThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-agency')
        ->and($definition->key)->toBe(AgencyThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Agency')
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
        ->and($definition->presets[0]->key)->toBe('agency')
        ->and($definition->presets[2]->key)->toBe('hand-picked')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeAgencyHealthCheck::compatibleCapellApiVersion())->toBe('^1.0');
});

it('boots definition-only and registers only agency-owned layout widget keys', function (): void {
    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName);

    $themeRegistry = resolve(ThemeRegistry::class);
    $provider = new AgencyThemeServiceProvider(app());
    $provider->register();
    $provider->boot($themeRegistry);

    expect($themeRegistry->has(AgencyThemeServiceProvider::THEME_KEY))->toBeTrue()
        ->and($themeRegistry->has(AgencyThemeServiceProvider::THEME_KEY))->toBeTrue()
        ->and(resolve(RenderableRegistry::class)->get('layout-widget', 'capell.widget.agency.portfolio-grid')->blade)
        ->toBe('capell-theme-agency::widget.section');
});

it('renders looping hero video media with a large header image', function (): void {
    view()->addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-agency::sections.hero', [
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
    view()->addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-agency::sections.hero', [
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
