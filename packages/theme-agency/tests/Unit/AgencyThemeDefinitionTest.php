<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\FooterData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Agency\AgencyThemeServiceProvider;
use Capell\ThemeStudio\Agency\Health\ThemeAgencyHealthCheck;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

it('defines the agency free renderer contract', function (): void {
    $definition = AgencyThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-agency')
        ->and($definition->key)->toBe(AgencyThemeServiceProvider::THEME_KEY)
        ->and($definition->previewImage)->toBe(AgencyThemeServiceProvider::PUBLIC_PREVIEW_IMAGE)
        ->and($definition->assets)->toBe(['css' => AgencyThemeServiceProvider::GENERATED_FRONTEND_CSS])
        ->and($definition->includedSections)->toContain('hero', 'features', 'proof', 'cta')
        ->and($definition->presets)->toHaveCount(6)
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->tags)->toContain('Expressive')
        ->and(ThemeAgencyHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('publishes the declared preview image and registers css through the tailwind source contract', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new AgencyThemeServiceProvider($this->app);
    $provider->boot($registry);

    $publishPaths = ServiceProvider::pathsToPublish(AgencyThemeServiceProvider::class, 'capell-theme-agency-assets');
    $publishedSourcePath = array_key_first($publishPaths);

    expect($publishPaths)->toHaveCount(1)
        ->and(realpath((string) $publishedSourcePath))->toBe(realpath(__DIR__ . '/../../docs/assets/marketplace/extension-card.jpg'))
        ->and($publishPaths[$publishedSourcePath])->toBe(public_path(ltrim(AgencyThemeServiceProvider::PUBLIC_PREVIEW_IMAGE, '/')));

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(static fn (mixed $asset): bool => $asset->packageName === AgencyThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(static fn (mixed $asset): bool => $asset->packageName === AgencyThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain(AgencyThemeServiceProvider::TAILWIND_IMPORT)
        ->and($packageSources)->toContain(AgencyThemeServiceProvider::TAILWIND_SOURCE)
        ->and($packageImports)->not->toContain('vendor/capell/themes/agency.css')
        ->and(file_exists(__DIR__ . '/../../' . AgencyThemeServiceProvider::TAILWIND_IMPORT))->toBeTrue();
});

it('declares surface and foreground tokens for every agency preset', function (): void {
    $definition = AgencyThemeServiceProvider::definition();

    collect($definition->presets)
        ->each(function (ThemePresetData $preset): void {
            expect($preset->values)->toHaveKeys([
                'surfaceColor',
                'foregroundColor',
                'neutralColor',
            ]);
        });
});

it('renders the page shell from brand surface and foreground tokens', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-agency::page', [
        'brand' => new BrandProfileData(
            surfaceColor: '#fafaf5',
            foregroundColor: '#1a1c19',
        ),
        'content' => '<main id="main-content">Preview</main>',
    ])->render();

    expect($html)
        ->toContain('--theme-surface:#fafaf5')
        ->toContain('--theme-foreground:#1a1c19')
        ->toContain('class="agency-shell site-theme-shell min-h-screen antialiased"')
        ->not->toContain('bg-zinc-950 text-zinc-950');
});

it('uses token-driven site gradients instead of fixed color stops', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-agency.css') ?: '';
    $sectionViews = implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [],
    ));

    expect($css)
        ->toContain('--site-surface: var(--theme-surface, #09090b)')
        ->toContain('--site-foreground: var(--theme-foreground, #f8fafc)')
        ->toContain('.site-brand-gradient')
        ->and($sectionViews)->toContain('site-brand-gradient')
        ->and($sectionViews)->not->toContain('via-fuchsia-500')
        ->and($sectionViews)->not->toContain('to-orange-900');
});

it('renders navigation from the agency package views', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $provider = new AgencyThemeServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'sectionRenderers');

    $renderer = $method->invoke($provider)['navigation'] ?? null;

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(new NavigationData(
        brandName: 'Capell',
        items: [['label' => 'Home', 'url' => '/']],
    ));

    expect($html)
        ->toContain('Capell')
        ->toContain('Home');
});

it('declares renderers for every included agency section', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $provider = new AgencyThemeServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'sectionRenderers');

    $renderers = $method->invoke($provider);

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

it('renders proof headings readably inside the white proof panel', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-agency::sections.proof', [
        'section' => new ProofSectionData(
            heading: 'Proof that the theme can carry real pages',
            summary: 'Proof copy should remain visible.',
            items: [['quote' => 'Faster launches', 'name' => 'Studio team']],
        ),
    ])->render();

    expect($html)
        ->toContain('Proof that the theme can carry real pages')
        ->toContain('text-zinc-950');
});

it('renders the agency hero with a campaign launch board fallback', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-agency::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Focused launch systems',
            eyebrow: 'Studio',
            summary: 'Strategy, identity, and delivery for growing teams.',
            actions: [
                ['label' => 'View work', 'url' => '/work'],
                ['label' => 'Start project', 'url' => '/contact', 'style' => 'secondary'],
            ],
        ),
    ])->render();

    expect($html)
        ->toContain('Focused launch systems')
        ->toContain('Launch board')
        ->toContain('Campaign scene')
        ->toContain('Channels')
        ->toContain('Live sprint')
        ->toContain('Start project')
        ->not->toContain('capell-app/theme-agency');
});

it('renders agency hero media with LCP image attributes', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-agency::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Focused launch systems',
            summary: 'Strategy, identity, and delivery for growing teams.',
            mediaUrl: '/images/agency-hero.jpg',
            mediaAlt: 'Studio launch wall',
        ),
    ])->render();

    expect($html)
        ->toContain('src="/images/agency-hero.jpg"')
        ->toContain('alt="Studio launch wall"')
        ->toContain('width="1200"')
        ->toContain('height="900"')
        ->toContain('loading="eager"')
        ->toContain('decoding="async"')
        ->toContain('fetchpriority="high"');
});

it('renders agency content listing images with lazy loading attributes', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-agency::sections.content-listing', [
        'section' => new ContentListingSectionData(
            heading: 'Selected work',
            items: [
                [
                    'title' => 'Product launch',
                    'summary' => 'A focused campaign.',
                    'url' => '/work/product-launch',
                    'image' => '/images/project.jpg',
                ],
            ],
        ),
    ])->render();

    expect($html)
        ->toContain('src="/images/project.jpg"')
        ->toContain('width="800"')
        ->toContain('height="600"')
        ->toContain('loading="lazy"')
        ->toContain('decoding="async"');
});

it('registers agency only when the theme package is installed', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    $provider = new AgencyThemeServiceProvider($this->app);
    $provider->register();
    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName, false);
    $provider->boot($registry);

    expect($registry->has('agency'))->toBeFalse();

    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName);

    $provider->boot($registry);

    expect($registry->has('agency'))->toBeTrue()
        ->and($registry->definition('agency')->package)->toBe(AgencyThemeServiceProvider::$packageName);
});

it('registers agency tailwind imports and blade sources when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new AgencyThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(fn (mixed $asset): bool => $asset->packageName === AgencyThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(fn (mixed $asset): bool => $asset->packageName === AgencyThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain('resources/css/theme-agency.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');
});

it('renders public theme markup without package identifiers', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new AgencyThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    $html = $registry->renderer('agency')->render(new ThemePageData(
        title: 'Studio',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Focused launch systems',
                eyebrow: 'Studio',
                summary: 'Strategy, identity, and delivery for growing teams.',
                actions: [['label' => 'View work', 'url' => '/work']],
            ),
            new FeatureSectionData(
                heading: 'What we ship',
                features: [['title' => 'Positioning', 'description' => 'Clear market stories.']],
            ),
            new ProofSectionData(
                heading: 'Proof',
                items: [['quote' => 'Faster campaigns', 'name' => 'Launch team']],
            ),
            new ContentListingSectionData(
                heading: 'Selected work',
                items: [['title' => 'Product launch', 'summary' => 'A focused campaign.', 'url' => '/work/product-launch']],
            ),
            new CtaSectionData(
                heading: 'Plan the next launch',
                actions: [['label' => 'Contact', 'url' => '/contact']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Northstar Studio',
            items: [['label' => 'Work', 'url' => '/work']],
            ctaLabel: 'Start',
            ctaUrl: '/contact',
        ),
        footer: new FooterData(
            brandName: 'Northstar Studio',
            columns: [
                ['heading' => 'Company', 'links' => [['label' => 'Contact', 'url' => '/contact']]],
            ],
        ),
    ));

    expect($html)
        ->toContain('Northstar Studio')
        ->toContain('Campaign system')
        ->toContain('Proof wall')
        ->toContain('Work wall')
        ->toContain('Launch room')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme')
        ->not->toContain('capell-app/theme-agency')
        ->not->toContain('capell-theme-agency')
        ->not->toContain('signed')
        ->not->toContain('filament')
        ->not->toContain('editor');
});
