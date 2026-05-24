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
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Commerce\CommerceThemeServiceProvider;
use Capell\ThemeStudio\Commerce\Health\ThemeCommerceHealthCheck;
use Capell\ThemeStudio\Commerce\Rendering\BlogTeaserSectionRenderer;
use Capell\ThemeStudio\Commerce\Rendering\CatalogSectionRenderer;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

it('defines the commerce premium renderer contract', function (): void {
    $definition = CommerceThemeServiceProvider::definition();
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($definition->package)->toBe('capell-app/theme-commerce')
        ->and($definition->key)->toBe(CommerceThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Editorial Commerce')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/commerce.css'])
        ->and($definition->includedSections)->toBe([
            'navigation',
            'hero',
            'features',
            'content-listing',
            'product-finder',
            'collections',
            'product-grid',
            'comparison',
            'catalog',
            'proof',
            'blog-teaser',
            'cta',
            'footer',
        ])
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->tags)->toBe(['Commerce', 'Catalog', 'Conversion'])
        ->and($manifest['product']['tier'])->toBe('premium')
        ->and($manifest['commands']['demo'])->toBe('capell:theme-commerce-demo')
        ->and($manifest['healthChecks'][0]['class'])->toBe(ThemeCommerceHealthCheck::class)
        ->and(ThemeCommerceHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('declares renderers for every commerce section', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');

    $provider = new CommerceThemeServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'sectionRenderers');

    expect(array_keys($method->invoke($provider)))->toBe([
        'navigation',
        'hero',
        'features',
        'content-listing',
        'product-finder',
        'collections',
        'product-grid',
        'comparison',
        'catalog',
        'proof',
        'blog-teaser',
        'cta',
        'footer',
    ]);
});

it('registers commerce only when the theme package is installed', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);

    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName, false);
    $provider->boot($registry);

    expect($registry->has('commerce'))->toBeFalse();

    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $provider->boot($registry);

    expect($registry->has('commerce'))->toBeTrue()
        ->and($registry->definition('commerce')->package)->toBe(CommerceThemeServiceProvider::$packageName);
});

it('registers commerce tailwind imports and blade sources when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CommerceThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CommerceThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain('resources/css/theme-commerce.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');
});

it('renders public theme markup without forbidden package or authoring tokens', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $html = $registry->renderer('commerce')->render(new ThemePageData(
        title: 'Stone & Loom',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Outdoor goods selected by careful product notes',
                eyebrow: 'New season',
                summary: 'A warmer catalog page for browsing durable home and travel products.',
                actions: [['label' => 'Shop arrivals', 'url' => '/collections/new']],
            ),
            commerceThemeSection('product-finder', [
                'heading' => 'Find the right kit',
                'summary' => 'Filter by use, material, and season.',
                'items' => [
                    ['group' => 'Use', 'options' => ['Travel', 'Garden', 'Kitchen']],
                ],
            ]),
            commerceThemeSection('collections', [
                'heading' => 'Shop by collection',
                'summary' => 'Editorial merchandising for each buying mission.',
                'items' => [
                    ['title' => 'Weekend table', 'summary' => 'Serveware and linens.', 'url' => '/collections/table'],
                ],
            ]),
            commerceThemeSection('product-grid', [
                'heading' => 'Featured products',
                'features' => [
                    ['title' => 'Canvas tote', 'description' => 'Heavy cotton with brass hardware.', 'price' => '$84'],
                ],
            ]),
            commerceThemeSection('comparison', [
                'heading' => 'Compare materials',
                'items' => [
                    ['title' => 'Waxed canvas', 'summary' => 'Weather-ready and repairable.'],
                ],
            ]),
            commerceThemeSection('catalog', [
                'heading' => 'Catalog connection',
                'items' => [
                    ['title' => 'Live inventory'],
                ],
            ]),
            new ProofSectionData(
                heading: 'Trusted by buyers',
                items: [['quote' => 'Built for repeat buying', 'name' => 'Retail Review']],
            ),
            commerceThemeSection('blog-teaser', [
                'heading' => 'Buying guides',
                'items' => [
                    ['title' => 'How to choose canvas weight', 'summary' => 'A practical material guide.', 'url' => '/blog/canvas-weight'],
                ],
            ]),
            new CtaSectionData(
                heading: 'Build the next basket',
                actions: [['label' => 'Open catalog', 'url' => '/catalog']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Stone & Loom',
            items: [['label' => 'Catalog', 'url' => '/catalog']],
            ctaLabel: 'Shop',
            ctaUrl: '/catalog',
        ),
        footer: new FooterData(
            brandName: 'Stone & Loom',
            columns: [
                ['heading' => 'Shop', 'links' => [['label' => 'New arrivals', 'url' => '/collections/new']]],
            ],
        ),
    ));

    expect($html)
        ->toContain('Stone &amp; Loom')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme')
        ->not->toContain('capell-app/theme-commerce')
        ->not->toContain('theme-commerce')
        ->not->toContain('signed')
        ->not->toContain('filament')
        ->not->toContain('editor')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('keeps commerce typography defaults low specificity so section color utilities can win', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-commerce.css');

    expect($css)
        ->toContain(':where(.retail-shell h1, .retail-shell h2, .retail-shell h3)')
        ->toContain(':where(.retail-shell p)')
        ->not->toContain('.retail-shell :where(')
        ->not->toMatch('/(?:^|\n)\s*\.retail-shell\s+(?:h1|h2|h3|p)\b/');
});

it('renders standard feature and content listing sections through commerce registry fallbacks', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $html = $registry->renderer('commerce')->render(new ThemePageData(
        title: 'Commerce standard sections',
        brand: new BrandProfileData,
        sections: [
            new FeatureSectionData(
                heading: 'Model comparison without lock-in',
                summary: 'Standard feature data renders through the Commerce product grid.',
                features: [
                    ['title' => 'Fit finder', 'description' => 'Help shoppers choose confidently.', 'price' => '$48'],
                ],
            ),
            new ContentListingSectionData(
                heading: 'Buying guides',
                summary: 'Standard content listing data renders through the Commerce collections view.',
                items: [
                    ['title' => 'Canvas care guide', 'summary' => 'Keep products in rotation longer.', 'url' => '/guides/canvas-care'],
                ],
            ),
        ],
        navigation: new NavigationData(brandName: 'Stone & Loom'),
        footer: new FooterData(brandName: 'Stone & Loom'),
    ));

    expect($html)
        ->toContain('Model comparison without lock-in')
        ->toContain('Fit finder')
        ->toContain('Buying guides')
        ->toContain('Canvas care guide')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('passes optional Shopify availability through the registered catalog renderer', function (bool $shopifyInstalled, string $expectedMarkup, string $missingMarkup): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    app('translator')->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/shopify-commerce', $shopifyInstalled);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('commerce', 'catalog');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(commerceThemeSection('catalog', [
        'heading' => 'Connect the catalog',
        'summary' => 'Keep merchandising close to product data.',
        'items' => [['title' => 'Collections']],
    ]));

    expect($html)
        ->toContain($expectedMarkup)
        ->not->toContain($missingMarkup)
        ->not->toContain('Shopify')
        ->not->toContain('shopify-commerce')
        ->not->toContain('capell-app/');
})->with([
    'shopify installed' => [true, 'Connected catalog panel', 'Catalog panel'],
    'shopify not installed' => [false, 'Catalog panel', 'Connected catalog panel'],
]);

it('passes optional Blog availability through the registered blog teaser renderer', function (bool $blogInstalled, string $expectedMarkup, string $missingMarkup): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    app('translator')->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/blog', $blogInstalled);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('commerce', 'blog-teaser');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(commerceThemeSection('blog-teaser', [
        'heading' => 'Buying guides',
        'items' => [
            ['title' => 'Canvas weight guide', 'summary' => 'Choose materials with confidence.', 'url' => '/blog/canvas-weight'],
        ],
    ]));

    expect($html)
        ->toContain($expectedMarkup)
        ->not->toContain($missingMarkup);
})->with([
    'blog installed' => [true, 'href="/blog/canvas-weight"', '<article class="retail-resource-card'],
    'blog not installed' => [false, '<article class="retail-resource-card', 'href="/blog/canvas-weight"'],
]);

it('renders optional commerce section views without database queries', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    app('translator')->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $queries = [];

    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $catalogHtml = (new CatalogSectionRenderer(CommerceThemeServiceProvider::THEME_KEY, true, failLoudly: true))->render(
        commerceThemeSection('catalog', [
            'heading' => 'Connect the catalog',
            'items' => [['title' => 'Collections']],
        ]),
    );

    $blogHtml = (new BlogTeaserSectionRenderer(CommerceThemeServiceProvider::THEME_KEY, true, failLoudly: true))->render(
        commerceThemeSection('blog-teaser', [
            'heading' => 'Buying guides',
            'items' => [
                ['title' => 'Canvas weight guide', 'summary' => 'Choose materials with confidence.', 'url' => '/blog/canvas-weight'],
            ],
        ]),
    );

    expect($catalogHtml)->toContain('Connected catalog panel')
        ->and($blogHtml)->toContain('Canvas weight guide')
        ->and($queries)->toBe([]);
});

/**
 * @param  array<string, mixed>  $viewData
 */
function commerceThemeSection(string $key, array $viewData): ThemeSection
{
    return new class($key, $viewData) implements ThemeSection
    {
        /**
         * @param  array<string, mixed>  $viewData
         */
        public function __construct(
            private readonly string $sectionKey,
            private readonly array $viewData,
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
