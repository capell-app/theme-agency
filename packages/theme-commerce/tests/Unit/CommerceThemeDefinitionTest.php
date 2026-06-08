<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
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
use Illuminate\Contracts\Translation\Translator;
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
            'product-detail',
            'mini-basket',
            'comparison',
            'catalog',
            'lookbook',
            'promotion',
            'campaign',
            'search',
            'store-event',
            'newsletter',
            'buying-guide',
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
        'product-detail',
        'mini-basket',
        'comparison',
        'catalog',
        'lookbook',
        'promotion',
        'campaign',
        'search',
        'store-event',
        'newsletter',
        'buying-guide',
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

    $packageBuildAssets = CapellCore::getVendorAssetsForType(VendorAssetEnum::BuildAsset)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CommerceThemeServiceProvider::$packageName)
        ->map(fn (mixed $asset): string => $asset->path() . '/' . $asset->file())
        ->all();

    expect($packageImports)->toContain('resources/css/theme-commerce.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php')
        ->and($packageBuildAssets)->toContain('vendor/capell-theme-commerce/resources/js/theme-commerce.js')
        ->and(file_exists(__DIR__ . '/../../resources/js/theme-commerce.js'))->toBeTrue();
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
            commerceThemeSection('product-detail', [
                'heading' => 'Waxed canvas field tote',
                'summary' => 'A repairable carry-all with weather-ready finish.',
                'price' => '$148',
                'variants' => [['label' => 'Forest'], ['label' => 'Clay']],
                'stockStatus' => 'Ships this week',
            ]),
            commerceThemeSection('mini-basket', [
                'heading' => 'Basket preview',
                'summary' => 'Checkout-ready basket state.',
                'items' => [
                    ['title' => 'Canvas tote', 'quantity' => 2, 'price' => '$296'],
                ],
                'subtotal' => '$296',
                'checkoutUrl' => '/checkout',
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
        ->toContain('Retail proof ledger')
        ->toContain('Order signal')
        ->toContain('Retail channel')
        ->toContain('Waxed canvas field tote')
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

it('routes commerce section palette utilities through retail tokens', function (): void {
    $viewPaths = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../../resources/views', FilesystemIterator::SKIP_DOTS),
    );

    $offendingViews = [];

    foreach ($viewPaths as $viewPath) {
        if (! $viewPath instanceof SplFileInfo) {
            continue;
        }

        if ($viewPath->getExtension() !== 'php') {
            continue;
        }

        $contents = (string) file_get_contents($viewPath->getPathname());

        if (preg_match('/(?:bg|text|border|shadow|hover:border|hover:text|group-hover:text)-\[#(?:[0-9a-fA-F]{3}){1,2}\]/', $contents) === 1) {
            $offendingViews[] = $viewPath->getFilename();
        }
    }

    expect($offendingViews)->toBe([]);
});

it('ships token-driven dark mode for commerce shell surfaces', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-commerce.css') ?: '';

    expect($css)
        ->toContain(':where(.dark .retail-shell, .retail-shell.dark)')
        ->toContain('--retail-surface: #101913')
        ->toContain('--retail-panel: #1c2a22')
        ->toContain('--retail-deep: #07100b')
        ->toContain(':is(.bg-white, .bg-zinc-50)')
        ->toContain(':is(.border-stone-200, .border-stone-300, .border-zinc-200)')
        ->toContain('.text-\\[var\\(--retail-ink\\)\\]')
        ->toContain('input, textarea, select');
});

it('renders translated fallback and data-driven hero trust badges', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $fallbackHtml = view('capell-theme-commerce::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Browse the new range',
            eyebrow: 'New season',
            summary: 'Durable retail storytelling.',
        ),
    ])->render();

    expect($fallbackHtml)
        ->toContain('Premium stock visuals')
        ->toContain('Fast checkout')
        ->toContain('Built for conversion');

    $customHtml = view('capell-theme-commerce::sections.hero', [
        'section' => (object) [
            'heading' => 'Browse the new range',
            'badges' => [
                ['label' => 'Limited run'],
                ['label' => 'Two-day dispatch'],
                'Repairable materials',
            ],
        ],
    ])->render();

    expect($customHtml)
        ->toContain('Limited run')
        ->toContain('Two-day dispatch')
        ->toContain('Repairable materials')
        ->not->toContain('Premium stock visuals');
});

it('renders commerce hero media with LCP image attributes', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-commerce::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Browse the new range',
            summary: 'Durable retail storytelling.',
            mediaUrl: '/images/commerce-hero.jpg',
            mediaAlt: 'Editorial product table',
        ),
    ])->render();

    expect($html)
        ->toContain('src="/images/commerce-hero.jpg"')
        ->toContain('alt="Editorial product table"')
        ->toContain('width="1200"')
        ->toContain('height="900"')
        ->toContain('loading="eager"')
        ->toContain('decoding="async"')
        ->toContain('fetchpriority="high"')
        ->toContain('sizes="(min-width: 1024px) 48vw, 100vw"');
});

it('renders commerce product grid images with lazy loading attributes', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-commerce::sections.product-grid', [
        'section' => (object) [
            'heading' => 'Featured products',
            'items' => [
                [
                    'title' => 'Canvas tote',
                    'summary' => 'Heavy cotton with brass hardware.',
                    'image' => '/images/canvas-tote.jpg',
                    'imageAlt' => 'Canvas tote on a table',
                ],
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('src="/images/canvas-tote.jpg"')
        ->toContain('alt="Canvas tote on a table"')
        ->toContain('width="800"')
        ->toContain('height="800"')
        ->toContain('loading="lazy"')
        ->toContain('decoding="async"')
        ->toContain('sizes="(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 80vw"');
});

it('renders a dedicated commerce product detail section', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('commerce', 'product-detail');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(commerceThemeSection('product-detail', [
        'heading' => 'Waxed canvas field tote',
        'summary' => 'A repairable carry-all with weather-ready finish.',
        'price' => '$148',
        'compareAtPrice' => '$182',
        'stockStatus' => 'Ships this week',
        'ctaLabel' => 'Add field tote',
        'ctaUrl' => '/products/field-tote',
        'gallery' => [
            ['url' => '/images/tote-main.jpg', 'alt' => 'Waxed canvas field tote on a product table'],
            ['url' => '/images/tote-detail.jpg', 'alt' => 'Close detail of waxed canvas stitching'],
        ],
        'variants' => [
            ['label' => 'Forest'],
            ['label' => 'Clay'],
        ],
        'trustItems' => [
            ['label' => 'Free shipping over $75'],
            ['label' => 'Repair-friendly materials'],
        ],
        'recommendations' => [
            ['title' => 'Brass key clip', 'price' => '$34', 'url' => '/products/key-clip'],
            ['title' => 'Canvas care wax', 'price' => '$18', 'url' => '/products/care-wax'],
        ],
    ]));

    expect($html)
        ->toContain('Product detail')
        ->toContain('Waxed canvas field tote')
        ->toContain('A repairable carry-all with weather-ready finish.')
        ->toContain('$148')
        ->toContain('$182')
        ->toContain('Ships this week')
        ->toContain('Add field tote')
        ->toContain('href="/products/field-tote"')
        ->toContain('src="/images/tote-main.jpg"')
        ->toContain('alt="Waxed canvas field tote on a product table"')
        ->toContain('width="1200"')
        ->toContain('height="1200"')
        ->toContain('loading="eager"')
        ->toContain('fetchpriority="high"')
        ->toContain('src="/images/tote-detail.jpg"')
        ->toContain('loading="lazy"')
        ->toContain('Forest')
        ->toContain('Clay')
        ->toContain('Free shipping over $75')
        ->toContain('Recommended next')
        ->toContain('Brass key clip')
        ->not->toContain('capell-app/theme-commerce')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('renders shopify-shaped product detail data without querying public blade', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('commerce', 'product-detail');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $queries = [];
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $html = $renderer->render(commerceThemeSection('product-detail', [
        'shopifyProduct' => [
            'handle' => 'linen-overshirt',
            'title' => 'Linen overshirt',
            'description' => 'A breathable overshirt for travel capsules.',
            'featuredImage' => ['url' => '/shopify/linen-overshirt.jpg', 'altText' => 'Linen overshirt on a rail'],
            'variants' => [
                [
                    'title' => 'Natural / M',
                    'priceAmount' => '128.00',
                    'priceCurrency' => 'GBP',
                    'availableForSale' => true,
                    'selectedOptions' => [
                        ['name' => 'Color', 'value' => 'Natural'],
                        ['name' => 'Size', 'value' => 'M'],
                    ],
                ],
            ],
        ],
    ]));

    expect($queries)->toBe([])
        ->and($html)
        ->toContain('Linen overshirt')
        ->toContain('A breathable overshirt for travel capsules.')
        ->toContain('GBP 128.00')
        ->toContain('In stock')
        ->toContain('href="/products/linen-overshirt"')
        ->toContain('src="/shopify/linen-overshirt.jpg"')
        ->toContain('alt="Linen overshirt on a rail"')
        ->toContain('Natural / M')
        ->not->toContain('capell-app/theme-commerce')
        ->not->toContain('shopify_product')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('renders shopify-shaped product cards and catalog sync summary', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/shopify-commerce');

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $productGridRenderer = $registry->sectionRenderer('commerce', 'product-grid');
    $catalogRenderer = $registry->sectionRenderer('commerce', 'catalog');

    expect($productGridRenderer)->not->toBeNull()
        ->and($catalogRenderer)->not->toBeNull();
    assert($productGridRenderer instanceof SectionRenderer);
    assert($catalogRenderer instanceof SectionRenderer);

    $productGridHtml = $productGridRenderer->render(commerceThemeSection('product-grid', [
        'heading' => 'Shopify picks',
        'items' => [
            [
                'title' => 'Travel wrap',
                'summary' => 'Warm layer for long-haul kits.',
                'featuredImage' => ['url' => '/shopify/travel-wrap.jpg', 'altText' => 'Travel wrap folded on a bench'],
                'variants' => [
                    [
                        'title' => 'Charcoal',
                        'price_amount' => '74.50',
                        'price_currency' => 'GBP',
                        'available_for_sale' => false,
                        'selected_options' => [['value' => 'Charcoal']],
                    ],
                ],
            ],
        ],
    ]));

    $catalogHtml = $catalogRenderer->render(commerceThemeSection('catalog', [
        'heading' => 'Connected catalog',
        'items' => [['title' => 'Travel essentials']],
        'shopifySummary' => [
            'productsSynced' => 124,
            'variantsSynced' => 482,
            'availableStock' => 413,
            'syncedAt' => '2026-06-07 10:15',
        ],
    ]));

    expect($productGridHtml)
        ->toContain('Shopify picks')
        ->toContain('Travel wrap')
        ->toContain('GBP 74.50')
        ->toContain('Sold out')
        ->toContain('Charcoal')
        ->toContain('src="/shopify/travel-wrap.jpg"')
        ->toContain('alt="Travel wrap folded on a bench"')
        ->and($catalogHtml)
        ->toContain('Connected catalog panel')
        ->toContain('Products')
        ->toContain('124')
        ->toContain('Variants')
        ->toContain('482')
        ->toContain('Available')
        ->toContain('413')
        ->toContain('Synced:')
        ->toContain('2026-06-07 10:15')
        ->not->toContain('capell-app/theme-commerce')
        ->not->toContain('shopify_product')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('renders commerce cart, promotion countdown, and review primitives', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $basketRenderer = $registry->sectionRenderer('commerce', 'mini-basket');
    $promotionRenderer = $registry->sectionRenderer('commerce', 'promotion');
    $proofRenderer = $registry->sectionRenderer('commerce', 'proof');

    assert($basketRenderer instanceof SectionRenderer);
    assert($promotionRenderer instanceof SectionRenderer);
    assert($proofRenderer instanceof SectionRenderer);

    $basketHtml = $basketRenderer->render(commerceThemeSection('mini-basket', [
        'heading' => 'Basket preview',
        'summary' => 'Checkout-ready basket state.',
        'items' => [
            [
                'title' => 'Canvas tote',
                'quantity' => 2,
                'price' => '$296',
                'image' => '/images/canvas-tote.jpg',
                'imageAlt' => 'Canvas tote on a table',
            ],
        ],
        'subtotal' => '$296',
        'delivery' => 'Free delivery',
        'checkoutUrl' => '/checkout',
    ]));

    $promotionHtml = $promotionRenderer->render(commerceThemeSection('promotion', [
        'heading' => 'Member preview',
        'summary' => 'Segmented offer for early access buyers.',
        'countdown' => [
            ['label' => 'Days', 'value' => '03'],
            ['label' => 'Hours', 'value' => '18'],
            ['label' => 'Minutes', 'value' => '42'],
        ],
        'items' => [
            ['title' => 'Early access', 'summary' => 'Save on the field kit.', 'code' => 'FIELD20'],
        ],
    ]));

    $proofHtml = $proofRenderer->render(commerceThemeSection('proof', [
        'heading' => 'Trusted by buyers',
        'items' => [
            [
                'metric' => '4.8',
                'name' => 'Field kit rating',
                'summary' => 'Buyers mention durable fabric and clear fulfilment.',
                'rating' => '4.8',
                'reviewCount' => '128',
            ],
        ],
    ]));

    $navigationHtml = view('capell-theme-commerce::sections.navigation', [
        'section' => (object) [
            'brandName' => 'Stone & Loom',
            'items' => [['label' => 'Catalog', 'url' => '/catalog']],
            'basketUrl' => '/cart',
            'basketCount' => 3,
        ],
    ])->render();

    expect($basketHtml)
        ->toContain('Basket preview')
        ->toContain('Canvas tote')
        ->toContain('$296')
        ->toContain('Free delivery')
        ->toContain('href="/checkout"')
        ->not->toContain('capell-app/theme-commerce')
        ->not->toContain('model_id')
        ->not->toContain('field_path');

    expect($promotionHtml)
        ->toContain('Offer ends in')
        ->toContain('03')
        ->toContain('FIELD20')
        ->not->toContain('capell-app/theme-commerce');

    expect($proofHtml)
        ->toContain('Trusted by buyers')
        ->toContain('4.8 / 5')
        ->toContain('128')
        ->toContain('Reviews')
        ->not->toContain('capell-app/theme-commerce');

    expect($navigationHtml)
        ->toContain('href="/cart"')
        ->toContain('Basket')
        ->toContain('3')
        ->not->toContain('capell-app/theme-commerce');
});

it('renders advertised commerce search event newsletter and campaign layouts', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $searchRenderer = $registry->sectionRenderer('commerce', 'search');
    $eventRenderer = $registry->sectionRenderer('commerce', 'store-event');
    $newsletterRenderer = $registry->sectionRenderer('commerce', 'newsletter');
    $campaignRenderer = $registry->sectionRenderer('commerce', 'campaign');

    assert($searchRenderer instanceof SectionRenderer);
    assert($eventRenderer instanceof SectionRenderer);
    assert($newsletterRenderer instanceof SectionRenderer);
    assert($campaignRenderer instanceof SectionRenderer);

    $searchHtml = $searchRenderer->render(commerceThemeSection('search', [
        'heading' => 'Search the range',
        'summary' => 'Curated search result cards.',
        'items' => [
            [
                'title' => 'Waxed canvas field tote',
                'summary' => 'Repairable carry-all.',
                'type' => 'Product',
                'price' => '$148',
                'image' => '/images/tote.jpg',
                'imageAlt' => 'Waxed canvas tote',
                'url' => '/products/tote',
            ],
        ],
    ]));

    $eventHtml = $eventRenderer->render(commerceThemeSection('store-event', [
        'heading' => 'Launch weekend',
        'summary' => 'In-store buying event.',
        'items' => [
            [
                'title' => 'Material care workshop',
                'date' => '12 Jun',
                'location' => 'London showroom',
                'summary' => 'Learn how to care for waxed canvas.',
                'url' => '/events/material-care',
            ],
        ],
    ]));

    $newsletterHtml = $newsletterRenderer->render(commerceThemeSection('newsletter', [
        'heading' => 'Get buying notes',
        'summary' => 'Range drops and material guides.',
        'formAction' => '/newsletter',
        'buttonLabel' => 'Subscribe',
    ]));

    $campaignHtml = $campaignRenderer->render(commerceThemeSection('campaign', [
        'heading' => 'Field kit launch',
        'summary' => 'Segmented launch campaign.',
        'items' => [
            [
                'phase' => 'Early access',
                'title' => 'Members first',
                'summary' => 'Early access to the full kit.',
                'code' => 'FIELD20',
            ],
        ],
    ]));

    expect($searchHtml)
        ->toContain('Search')
        ->toContain('Waxed canvas field tote')
        ->toContain('src="/images/tote.jpg"')
        ->toContain('loading="lazy"')
        ->not->toContain('capell-app/theme-commerce')
        ->and($eventHtml)->toContain('Store event')
        ->and($eventHtml)->toContain('Material care workshop')
        ->and($eventHtml)->toContain('London showroom')
        ->and($eventHtml)->toContain('href="/events/material-care"')
        ->and($newsletterHtml)->toContain('Newsletter')
        ->and($newsletterHtml)->toContain('action="/newsletter"')
        ->and($newsletterHtml)->toContain('Subscribe')
        ->and($campaignHtml)->toContain('Campaign')
        ->and($campaignHtml)->toContain('Members first')
        ->and($campaignHtml)->toContain('FIELD20')
        ->and($searchHtml . $eventHtml . $newsletterHtml . $campaignHtml)->not->toContain('model_id')
        ->and($searchHtml . $eventHtml . $newsletterHtml . $campaignHtml)->not->toContain('field_path');
});

it('renders empty states for advertised commerce layouts', function (string $view, string $expectedTitle): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $html = view(commerceThemeSectionView($view), [
        'section' => (object) [
            'heading' => 'Empty layout',
            'summary' => null,
            'items' => [],
        ],
    ])->render();

    expect($html)
        ->toContain($expectedTitle)
        ->not->toContain('capell-app/theme-commerce')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
})->with([
    'search' => ['search', 'Add search results'],
    'store-event' => ['store-event', 'Add store events'],
    'campaign' => ['campaign', 'Add campaign content'],
]);

it('renders core commerce sections directly', function (string $view, object $section, string $expectedMarkup): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $html = view(commerceThemeSectionView($view), [
        'section' => $section,
    ])->render();

    expect($html)
        ->toContain($expectedMarkup)
        ->not->toContain('capell-app/theme-commerce')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
})->with([
    'navigation' => [
        'navigation',
        new NavigationData(
            brandName: 'Stone & Loom',
            items: [['label' => 'Catalog', 'url' => '/catalog']],
            ctaLabel: 'Shop',
            ctaUrl: '/catalog',
        ),
        '[&::-webkit-details-marker]:hidden',
    ],
    'comparison' => [
        'comparison',
        (object) [
            'heading' => 'Compare materials',
            'summary' => 'Choose the right buying path.',
            'items' => [
                ['title' => 'Waxed canvas', 'summary' => 'Weather-ready and repairable.'],
            ],
        ],
        'Waxed canvas',
    ],
    'proof' => [
        'proof',
        new ProofSectionData(
            heading: 'Trusted by buyers',
            items: [['metric' => '94%', 'name' => 'Repeat buyer rate', 'role' => 'Built for confident repeat purchase.']],
        ),
        'Retail proof ledger',
    ],
    'cta' => [
        'cta',
        new CtaSectionData(
            heading: 'Build the next basket',
            summary: 'Move shoppers from browsing to checkout.',
            actions: [['label' => 'Open catalog', 'url' => '/catalog']],
        ),
        'Build the next basket',
    ],
    'footer' => [
        'footer',
        new FooterData(
            brandName: 'Stone & Loom',
            columns: [
                ['heading' => 'Shop', 'links' => [['label' => 'New arrivals', 'url' => '/collections/new']]],
            ],
        ),
        'New arrivals',
    ],
]);

it('renders a real commerce comparison table', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-commerce::sections.comparison', [
        'section' => (object) [
            'heading' => 'Compare kits',
            'summary' => 'Choose the right bundle.',
            'criteria' => [
                ['label' => 'Materials'],
                ['label' => 'Best for'],
            ],
            'items' => [
                [
                    'title' => 'Field kit',
                    'price' => '$148',
                    'specs' => [
                        ['label' => 'Materials', 'value' => 'Waxed canvas'],
                        ['label' => 'Best for', 'value' => 'Daily carry'],
                    ],
                ],
                [
                    'title' => 'Travel kit',
                    'price' => '$224',
                    'specs' => [
                        ['label' => 'Materials', 'value' => 'Ripstop nylon'],
                        ['label' => 'Best for', 'value' => 'Long trips'],
                    ],
                ],
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('<table')
        ->toContain('Retail product comparison')
        ->toContain('Feature')
        ->toContain('Field kit')
        ->toContain('$148')
        ->toContain('Travel kit')
        ->toContain('Materials')
        ->toContain('Waxed canvas')
        ->toContain('Long trips')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('renders catalog carousel controls as visible stateful controls', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-commerce::sections.catalog', [
        'section' => (object) [
            'heading' => 'Catalog',
            'summary' => 'Browse ranges.',
            'items' => [
                ['title' => 'Outdoor'],
                ['title' => 'Kitchen'],
                ['title' => 'Travel'],
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('aria-live="polite"')
        ->toContain('data-carousel-status')
        ->toContain('aria-disabled="true"')
        ->toContain('Previous items')
        ->toContain('Next items')
        ->toContain('→')
        ->not->toContain('carousel-prev absolute top-1/2 left-2 hidden')
        ->not->toContain('carousel-next absolute top-1/2 right-2 hidden')
        ->not->toContain('‹')
        ->not->toContain('›');
});

it('ships generic reduced-motion carousel behavior for commerce', function (): void {
    $script = file_get_contents(__DIR__ . '/../../resources/js/theme-commerce.js') ?: '';

    expect($script)
        ->toContain("querySelectorAll('[data-carousel]')")
        ->toContain('prefers-reduced-motion: reduce')
        ->toContain('aria-disabled')
        ->toContain('data-carousel-status')
        ->toContain('scrollBy');
});

it('renders catalog highlight copy through translations with generic public selectors', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-commerce::sections.catalog', [
        'section' => (object) [
            'heading' => 'Connect the catalog',
            'items' => [['title' => 'Collections']],
        ],
    ])->render();

    expect($html)
        ->toContain('Highlights')
        ->toContain('Conversion-ready merchandising')
        ->toContain('Discover products by behavior, seasonality, and intent signals for stronger margin.')
        ->toContain('data-carousel="catalog"')
        ->not->toContain('data-carousel="commerce-catalog"');
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
        ->toContain('Buying path')
        ->toContain('Merchandising note')
        ->toContain('Buying guides')
        ->toContain('Canvas care guide')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('renders a hydrated commerce page within the declared frontend render budget', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $page = new ThemePageData(
        title: 'Stone & Loom',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Outdoor goods selected by careful product notes',
                eyebrow: 'New season',
                summary: 'A warmer catalog page for browsing durable home and travel products.',
                actions: [['label' => 'Shop arrivals', 'url' => '/collections/new']],
                mediaUrl: '/images/commerce-hero.jpg',
                mediaAlt: 'Editorial product table',
            ),
            commerceThemeSection('product-finder', [
                'heading' => 'Find the right kit',
                'items' => [['group' => 'Use', 'options' => ['Travel', 'Garden', 'Kitchen']]],
            ]),
            commerceThemeSection('collections', [
                'heading' => 'Shop by collection',
                'items' => [['title' => 'Weekend table', 'summary' => 'Serveware and linens.', 'url' => '/collections/table']],
            ]),
            commerceThemeSection('product-grid', [
                'heading' => 'Featured products',
                'items' => [['title' => 'Canvas tote', 'summary' => 'Heavy cotton with brass hardware.', 'price' => '$84', 'image' => '/images/canvas-tote.jpg']],
            ]),
            commerceThemeSection('comparison', [
                'heading' => 'Compare materials',
                'items' => [['title' => 'Waxed canvas', 'summary' => 'Weather-ready and repairable.']],
            ]),
            commerceThemeSection('catalog', [
                'heading' => 'Catalog connection',
                'items' => [['title' => 'Live inventory']],
            ]),
            commerceThemeSection('lookbook', [
                'heading' => 'Autumn material stories',
                'items' => [['title' => 'Waxed cotton', 'summary' => 'Weather-ready product story.']],
            ]),
            commerceThemeSection('promotion', [
                'heading' => 'Spring offer board',
                'items' => [['title' => 'Member preview', 'summary' => 'Segmented offer for early access buyers.']],
            ]),
            commerceThemeSection('buying-guide', [
                'heading' => 'Choose the right field jacket',
                'items' => [['title' => 'Canvas weight guide', 'summary' => 'Advice content for purchase confidence.']],
            ]),
            new ProofSectionData(
                heading: 'Trusted by buyers',
                items: [['metric' => '94%', 'name' => 'Repeat buyer rate', 'role' => 'Built for confident repeat purchase.']],
            ),
            commerceThemeSection('blog-teaser', [
                'heading' => 'Buying guides',
                'items' => [['title' => 'Canvas care guide', 'summary' => 'Keep products in rotation longer.', 'url' => '/guides/canvas-care']],
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
    );

    $renderer = $registry->renderer('commerce');
    $renderer->render($page);

    $startedAt = hrtime(true);
    $html = $renderer->render($page);
    $durationMs = (hrtime(true) - $startedAt) / 1_000_000;

    expect($html)->toContain('Stone &amp; Loom')
        ->and($durationMs)->toBeLessThan(20.0);
});

it('renders new premium commerce layouts through the registry', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $lookbookRenderer = $registry->sectionRenderer('commerce', 'lookbook');
    $promotionRenderer = $registry->sectionRenderer('commerce', 'promotion');
    $buyingGuideRenderer = $registry->sectionRenderer('commerce', 'buying-guide');

    assert($lookbookRenderer instanceof SectionRenderer);
    assert($promotionRenderer instanceof SectionRenderer);
    assert($buyingGuideRenderer instanceof SectionRenderer);

    $lookbookHtml = $lookbookRenderer->render(commerceThemeSection('lookbook', [
        'heading' => 'Autumn material stories',
        'summary' => 'Editorial merchandising for collection discovery.',
        'items' => [
            ['title' => 'Waxed cotton', 'summary' => 'Weather-ready product story.'],
        ],
    ]));

    $promotionHtml = $promotionRenderer->render(commerceThemeSection('promotion', [
        'heading' => 'Spring offer board',
        'items' => [
            ['title' => 'Member preview', 'summary' => 'Segmented offer for early access buyers.'],
        ],
    ]));

    $buyingGuideHtml = $buyingGuideRenderer->render(commerceThemeSection('buying-guide', [
        'heading' => 'Choose the right field jacket',
        'items' => [
            ['title' => 'Canvas weight guide', 'summary' => 'Advice content for purchase confidence.'],
        ],
    ]));

    expect($lookbookHtml)
        ->toContain('Autumn material stories')
        ->toContain('Waxed cotton')
        ->not->toContain('capell-app/theme-commerce');

    expect($promotionHtml)
        ->toContain('Spring offer board')
        ->toContain('Member preview')
        ->not->toContain('capell-app/theme-commerce');

    expect($buyingGuideHtml)
        ->toContain('Choose the right field jacket')
        ->toContain('Canvas weight guide')
        ->not->toContain('capell-app/theme-commerce');
});

it('passes optional Shopify availability through the registered catalog renderer', function (bool $shopifyInstalled, string $expectedMarkup, string $missingMarkup): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/shopify-commerce', $shopifyInstalled);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('commerce', 'catalog');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

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
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/blog', $blogInstalled);

    $registry = new ThemeRegistry;
    $provider = new CommerceThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('commerce', 'blog-teaser');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

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
    'blog installed' => [true, 'href="/blog/canvas-weight"', 'retail-resource-card rounded-xl border border-stone-200 bg-white p-6"'],
    'blog not installed' => [false, 'retail-resource-card rounded-xl border border-stone-200 bg-white p-6"', 'href="/blog/canvas-weight"'],
]);

it('renders optional commerce section views without database queries', function (): void {
    View::addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-commerce', __DIR__ . '/../../resources/lang');

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

/**
 * @return view-string
 */
function commerceThemeSectionView(string $view): string
{
    return match ($view) {
        'campaign' => 'capell-theme-commerce::sections.campaign',
        'comparison' => 'capell-theme-commerce::sections.comparison',
        'cta' => 'capell-theme-commerce::sections.cta',
        'footer' => 'capell-theme-commerce::sections.footer',
        'navigation' => 'capell-theme-commerce::sections.navigation',
        'proof' => 'capell-theme-commerce::sections.proof',
        'search' => 'capell-theme-commerce::sections.search',
        'store-event' => 'capell-theme-commerce::sections.store-event',
        default => throw new InvalidArgumentException(sprintf('Unknown commerce theme section view [%s].', $view)),
    };
}
