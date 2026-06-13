<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\LiquidGlass\Health\ThemeLiquidGlassHealthCheck;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

it('defines the Liquid Glass free renderer contract', function (): void {
    $definition = LiquidGlassThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-liquid-glass')
        ->and($definition->key)->toBe(LiquidGlassThemeServiceProvider::THEME_KEY)
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/liquid-glass.css'])
        ->and($definition->includedSections)->toBe(['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'])
        ->and($definition->presets)->toHaveCount(3)
        ->and($definition->presetOptions())->toBe([
            'clarity' => 'Clarity',
            'prism' => 'Prism',
            'graphite' => 'Graphite',
        ])
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and($definition->tags)->toContain('Glass')
        ->and(ThemeLiquidGlassHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('renders navigation from the Liquid Glass package views', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $provider = new LiquidGlassThemeServiceProvider($this->app);
    $renderer = liquidGlassThemeRenderer($provider, 'navigation');

    $html = $renderer->render(new NavigationData(
        brandName: 'Capell',
        items: [['label' => 'Home', 'url' => '/']],
        ctaLabel: 'Start',
        ctaUrl: '/start',
    ));

    expect($html)
        ->toContain('Capell')
        ->toContain('Home')
        ->toContain('Start')
        ->toContain('liquid-glass-nav')
        ->toContain('Main navigation')
        ->not->toContain('capell-app/theme-liquid-glass');
});

it('declares renderers for every included Liquid Glass section', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');

    $provider = new LiquidGlassThemeServiceProvider($this->app);
    $renderers = liquidGlassThemeSectionRenderers($provider);

    expect(array_keys($renderers))->toBe([
        'navigation',
        'hero',
        'features',
        'proof',
        'content-listing',
        'cta',
        'footer',
    ]);
});

it('renders Liquid Glass hero media with LCP image attributes', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Launch with a glass interface',
            summary: 'Translucent sections for modern teams.',
            mediaUrl: '/images/liquid-glass-hero.jpg',
            mediaAlt: 'Glass interface preview',
        ),
    ])->render();

    expect($html)
        ->toContain('Launch with a glass interface')
        ->toContain('src="/images/liquid-glass-hero.jpg"')
        ->toContain('alt="Glass interface preview"')
        ->toContain('width="1200"')
        ->toContain('height="900"')
        ->toContain('loading="eager"')
        ->toContain('decoding="async"')
        ->toContain('fetchpriority="high"')
        ->toContain('sizes="(min-width: 1024px) 48vw, 100vw"');
});

it('renders translated Liquid Glass hero fallback stats', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Launch with a glass interface',
            summary: 'Translucent sections for modern teams.',
        ),
    ])->render();

    expect($html)
        ->toContain('Liquid Glass')
        ->toContain('Surfaces')
        ->toContain('Runtime')
        ->toContain('Presets')
        ->toContain('Preview system')
        ->toContain('Layered site chrome')
        ->not->toContain('Pages')
        ->not->toContain('Assets')
        ->not->toContain('Layout')
        ->not->toContain('Widgets');
});

it('drives the Liquid Glass shell and card surfaces from theme tokens', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');

    $pageHtml = view('capell-theme-liquid-glass::page', [
        'brand' => new BrandProfileData(
            primaryColor: '#0f766e',
            accentColor: '#f97316',
            surfaceColor: '#f3fbfa',
            foregroundColor: '#10202a',
        ),
        'content' => '<main id="main-content">Preview</main>',
    ])->render();

    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-liquid-glass.css') ?: '';
    $views = implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [],
    ));

    expect($pageHtml)
        ->toContain('--theme-primary:#0f766e')
        ->toContain('--theme-accent:#f97316')
        ->toContain('--theme-surface:#f3fbfa')
        ->toContain('--theme-foreground:#10202a')
        ->toContain('class="site-theme-shell liquid-glass-shell min-h-screen antialiased"');

    expect($css)
        ->toContain('--liquid-glass-panel')
        ->toContain('backdrop-filter: blur(18px)')
        ->toContain('.liquid-glass-panel')
        ->toContain('.liquid-glass-card')
        ->toContain('.liquid-glass-cta');

    expect($views)
        ->toContain('liquid-glass-panel')
        ->toContain('liquid-glass-card')
        ->toContain('liquid-glass-button');
});

it('renders content listing items without package metadata', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.content-listing', (new ContentListingSectionData(
        heading: 'Latest signals',
        summary: 'Useful updates for teams.',
        items: [
            [
                'title' => 'Design system note',
                'summary' => 'A practical update.',
                'url' => '/notes/design-system',
                'type' => 'Note',
                'publishedDate' => 'June 2026',
            ],
        ],
        variant: 'media',
    ))->toViewData())->render();

    expect($html)
        ->toContain('Latest signals')
        ->toContain('Media')
        ->toContain('Design system note')
        ->toContain('href="/notes/design-system"')
        ->not->toContain('capell-app/theme-liquid-glass')
        ->not->toContain('authoring')
        ->not->toContain('wire:');
});

it('registers Liquid Glass theme assets when the package is installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LiquidGlassThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new LiquidGlassThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->where('packageName', LiquidGlassThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->where('packageName', LiquidGlassThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain('resources/css/theme-liquid-glass.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');

    CapellCore::clearPackages();
});

/**
 * @return array<string, ViewSectionRenderer>
 */
function liquidGlassThemeSectionRenderers(LiquidGlassThemeServiceProvider $provider): array
{
    $method = new ReflectionMethod($provider, 'sectionRenderers');
    $method->setAccessible(true);

    $renderers = $method->invoke($provider);

    expect($renderers)->toBeArray();

    return $renderers;
}

function liquidGlassThemeRenderer(LiquidGlassThemeServiceProvider $provider, string $sectionKey): ViewSectionRenderer
{
    $renderer = liquidGlassThemeSectionRenderers($provider)[$sectionKey] ?? null;

    expect($renderer)->toBeInstanceOf(ViewSectionRenderer::class);

    return $renderer;
}
