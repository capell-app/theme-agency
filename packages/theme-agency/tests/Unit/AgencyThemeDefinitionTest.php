<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
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
        ->and($definition->includedSections)->toContain('hero', 'features', 'proof', 'project-showcase', 'case-study', 'cta')
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

    $packageBuildAssets = CapellCore::getVendorAssetsForType(VendorAssetEnum::BuildAsset)
        ->filter(static fn (mixed $asset): bool => $asset->packageName === AgencyThemeServiceProvider::$packageName)
        ->map(static fn (mixed $asset): string => $asset->path() . '/' . $asset->file())
        ->all();

    expect($packageImports)->toContain(AgencyThemeServiceProvider::TAILWIND_IMPORT)
        ->and($packageSources)->toContain(AgencyThemeServiceProvider::TAILWIND_SOURCE)
        ->and($packageBuildAssets)->toContain('vendor/capell-theme-agency/resources/js/theme-agency.js')
        ->and($packageImports)->not->toContain('vendor/capell/themes/agency.css')
        ->and(file_exists(__DIR__ . '/../../' . AgencyThemeServiceProvider::TAILWIND_IMPORT))->toBeTrue()
        ->and(file_exists(__DIR__ . '/../../resources/js/theme-agency.js'))->toBeTrue();
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

it('keeps agency preset shell contrast at WCAG AA levels', function (): void {
    $definition = AgencyThemeServiceProvider::definition();

    collect($definition->presets)
        ->each(function (ThemePresetData $preset): void {
            expect(agencyThemeContrastRatio(
                $preset->values['surfaceColor'],
                $preset->values['foregroundColor'],
            ))->toBeGreaterThanOrEqual(4.5, sprintf(
                'Preset [%s] surface/foreground contrast must be at least 4.5:1.',
                $preset->key,
            ));
        });
});

it('guards against low contrast agency public copy classes', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-agency.css') ?: '';
    $sectionViews = implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [],
    ));

    expect($css)
        ->not->toContain('line-height: 0.92')
        ->and($sectionViews)->not->toContain('text-white/45')
        ->and($sectionViews)->not->toContain('text-white/55')
        ->and($sectionViews)->not->toContain('text-white/60')
        ->and($sectionViews)->not->toContain('text-white/65')
        ->and($sectionViews)->not->toContain('text-white/70')
        ->and($sectionViews)->not->toContain('>08<')
        ->and($sectionViews)->not->toContain('>14d<')
        ->and($sectionViews)->not->toContain('>01<');
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
        'project-showcase',
        'case-study',
        'cta',
        'footer',
    ]);
});

it('renders proof headings readably inside the white proof panel', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-agency::sections.proof', [
        'section' => new ProofSectionData(
            heading: 'Proof that the theme can carry real pages',
            summary: 'Proof copy should remain visible.',
            items: [['quote' => 'Faster launches', 'name' => 'Studio team']],
        ),
    ])->render();

    expect($html)
        ->toContain('Proof that the theme can carry real pages')
        ->toContain('text-zinc-950')
        ->toContain('aria-live="polite"')
        ->toContain('data-carousel-status')
        ->toContain('Previous proof item')
        ->toContain('Next proof item')
        ->toContain('sr-only')
        ->not->toContain('<script>')
        ->not->toContain('‹')
        ->not->toContain('›');
});

it('ships reduced-motion proof carousel behavior outside public Blade', function (): void {
    $script = file_get_contents(__DIR__ . '/../../resources/js/theme-agency.js') ?: '';

    expect($script)
        ->toContain('prefers-reduced-motion: reduce')
        ->toContain('aria-disabled')
        ->toContain('data-carousel-status')
        ->toContain('textContent')
        ->toContain('scrollBy');
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

it('renders a dedicated agency project showcase section', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $provider = new AgencyThemeServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'sectionRenderers');
    $renderer = $method->invoke($provider)['project-showcase'] ?? null;

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(agencyThemeSection('project-showcase', [
        'heading' => 'Launch work that moved markets',
        'summary' => 'Selected campaign, brand, and product systems.',
        'filters' => ['Brand', 'Campaign'],
        'items' => [
            [
                'title' => 'Retail launch system',
                'summary' => 'A multi-channel launch for a national retail team.',
                'url' => '/work/retail-launch',
                'image' => '/images/retail-launch.jpg',
                'imageAlt' => 'Retail launch campaign wall',
                'discipline' => 'Campaign',
                'year' => '2026',
                'stage' => 'Launched',
            ],
            [
                'title' => 'B2B brand sprint',
                'summary' => 'A compact identity system for a SaaS team.',
                'type' => 'Brand',
            ],
        ],
    ]));

    expect($html)
        ->toContain('Project showcase')
        ->toContain('Launch work that moved markets')
        ->toContain('Selected campaign, brand, and product systems.')
        ->toContain('All work')
        ->toContain('Campaign')
        ->toContain('src="/images/retail-launch.jpg"')
        ->toContain('alt="Retail launch campaign wall"')
        ->toContain('loading="lazy"')
        ->toContain('Retail launch system')
        ->toContain('Year')
        ->toContain('2026')
        ->toContain('Launched')
        ->toContain('B2B brand sprint')
        ->toContain('site-brand-gradient')
        ->not->toContain('capell-app/theme-agency')
        ->not->toContain('capell-theme-agency');
});

it('renders the agency project showcase empty state', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-agency::sections.project-showcase', [
        'section' => (object) [
            'heading' => 'Selected work',
            'summary' => null,
            'items' => [],
            'filters' => [],
        ],
    ])->render();

    expect($html)
        ->toContain('Add project stories')
        ->toContain('Add selected work, campaign launches, or portfolio projects')
        ->not->toContain('data-field')
        ->not->toContain('model_id')
        ->not->toContain('capell-app/theme-agency');
});

it('renders a dedicated agency case study section', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $provider = new AgencyThemeServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'sectionRenderers');
    $renderer = $method->invoke($provider)['case-study'] ?? null;

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(agencyThemeSection('case-study', [
        'heading' => 'Repositioning a national retail launch',
        'summary' => 'A full-funnel campaign system for a new seasonal range.',
        'client' => 'Northline Retail',
        'discipline' => 'Campaign',
        'year' => '2026',
        'mediaUrl' => '/images/case-hero.jpg',
        'mediaAlt' => 'Retail launch case study hero',
        'challenge' => 'The team needed one launch idea across retail, social, and partner channels.',
        'approach' => 'We built a modular campaign system with channel-specific creative rules.',
        'result' => 'The launch exceeded paid media benchmarks and improved store team adoption.',
        'metrics' => [
            ['label' => 'Lift', 'value' => '+38%'],
            ['label' => 'Assets', 'value' => '72'],
        ],
        'gallery' => [
            ['url' => '/images/case-gallery-1.jpg', 'alt' => 'Campaign poster system'],
            ['url' => '/images/case-gallery-2.jpg', 'alt' => 'Social launch frames'],
        ],
    ]));

    expect($html)
        ->toContain('Case study')
        ->toContain('Repositioning a national retail launch')
        ->toContain('Northline Retail')
        ->toContain('Campaign')
        ->toContain('2026')
        ->toContain('src="/images/case-hero.jpg"')
        ->toContain('alt="Retail launch case study hero"')
        ->toContain('Challenge')
        ->toContain('Approach')
        ->toContain('Result')
        ->toContain('The team needed one launch idea')
        ->toContain('+38%')
        ->toContain('Project gallery')
        ->toContain('src="/images/case-gallery-1.jpg"')
        ->toContain('loading="lazy"')
        ->not->toContain('capell-app/theme-agency')
        ->not->toContain('capell-theme-agency');
});

it('renders the agency case study empty state', function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-agency::sections.case-study', [
        'section' => (object) [
            'heading' => 'Case study',
            'summary' => null,
            'metrics' => [],
            'gallery' => [],
        ],
    ])->render();

    expect($html)
        ->toContain('Add case study details')
        ->toContain('Add the challenge, approach, result, metrics, and media')
        ->not->toContain('data-field')
        ->not->toContain('model_id')
        ->not->toContain('capell-app/theme-agency');
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

/**
 * @param  array<string, mixed>  $viewData
 */
function agencyThemeSection(string $key, array $viewData): ThemeSection
{
    return new readonly class($key, $viewData) implements ThemeSection
    {
        /**
         * @param  array<string, mixed>  $viewData
         */
        public function __construct(
            private string $sectionKey,
            private array $viewData,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return ['section' => (object) $this->viewData];
        }
    };
}

function agencyThemeContrastRatio(string $backgroundHex, string $foregroundHex): float
{
    $background = agencyThemeRelativeLuminance($backgroundHex);
    $foreground = agencyThemeRelativeLuminance($foregroundHex);

    $lighter = max($background, $foreground);
    $darker = min($background, $foreground);

    return ($lighter + 0.05) / ($darker + 0.05);
}

function agencyThemeRelativeLuminance(string $hex): float
{
    $normalizedHex = ltrim($hex, '#');

    $channels = [
        hexdec(substr($normalizedHex, 0, 2)) / 255,
        hexdec(substr($normalizedHex, 2, 2)) / 255,
        hexdec(substr($normalizedHex, 4, 2)) / 255,
    ];

    [$red, $green, $blue] = array_map(
        static fn (float $channel): float => $channel <= 0.03928
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4,
        $channels,
    );

    return (0.2126 * $red) + (0.7152 * $green) + (0.0722 * $blue);
}

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
