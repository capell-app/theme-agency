<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\ThemePageAdapter;
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
use Capell\Core\ThemeStudio\Theme\ThemePageAdapterRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Saas\Health\ThemeSaasHealthCheck;
use Capell\ThemeStudio\Saas\Rendering\BlogSectionRenderer;
use Capell\ThemeStudio\Saas\SaasThemeServiceProvider;
use Capell\ThemeStudio\Saas\ThemeStudio\Adapters\SaasThemePageAdapter;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

it('defines the saas premium renderer contract', function (): void {
    $definition = SaasThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-saas')
        ->and($definition->key)->toBe(SaasThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toContain('SaaS')
        ->and($definition->description)->toContain('SaaS')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/saas.css'])
        ->and($definition->includedSections)->toBe([
            'navigation',
            'hero',
            'features',
            'proof',
            'content-listing',
            'comparison',
            'calculator',
            'cta',
            'footer',
            'blog',
        ])
        ->and($definition->includedSections)->toContain('content-listing', 'comparison', 'calculator', 'blog')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('saas')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->tags)->toContain('Conversion')
        ->and(ThemeSaasHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('renders navigation from the saas package views', function (): void {
    View::addNamespace('capell-theme-saas', __DIR__ . '/../../resources/views');

    $provider = new SaasThemeServiceProvider($this->app);
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

it('declares renderers for every included saas section', function (): void {
    View::addNamespace('capell-theme-saas', __DIR__ . '/../../resources/views');

    $provider = new SaasThemeServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'sectionRenderers');

    $renderers = $method->invoke($provider);

    expect(array_keys($renderers))->toBe([
        'navigation',
        'hero',
        'features',
        'proof',
        'content-listing',
        'comparison',
        'calculator',
        'cta',
        'footer',
        'blog',
    ]);
});

it('registers premium landing page tailwind assets from the saas theme only when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(SaasThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new SaasThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(fn (mixed $asset): bool => $asset->packageName === SaasThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(fn (mixed $asset): bool => $asset->packageName === SaasThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain('resources/css/theme-saas.css')
        ->and($packageImports)->not->toContain('resources/css/saas-theme.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');
});

it('keeps the source stylesheet aligned with the public saas renderer selectors', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-saas.css');

    expect($css)
        ->toContain('.saas-shell')
        ->toContain('.saas-final-cta')
        ->toContain('.saas-cta')
        ->not->toContain('.layout-container > .capell-block-homepage-section');
});

it('registers saas only when the theme package is installed', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    $provider = new SaasThemeServiceProvider($this->app);
    $provider->register();
    CapellCore::forcePackageInstalled(SaasThemeServiceProvider::$packageName, false);
    $provider->boot($registry);

    expect($registry->has('saas'))->toBeFalse();

    CapellCore::forcePackageInstalled(SaasThemeServiceProvider::$packageName);

    $provider->boot($registry);

    expect($registry->has('saas'))->toBeTrue()
        ->and($registry->definition('saas')->package)->toBe(SaasThemeServiceProvider::$packageName)
        ->and($this->app->make(ThemePageAdapterRegistry::class)->has('saas'))->toBeTrue()
        ->and($this->app->make(ThemePageAdapter::class))->not->toBeInstanceOf(SaasThemePageAdapter::class);
});

it('renders public theme markup without package identifiers', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(SaasThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new SaasThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    $html = $registry->renderer('saas')->render(new ThemePageData(
        title: 'Launchdeck',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Turn onboarding into activation',
                eyebrow: 'Growth platform',
                summary: 'A product-led page for teams improving conversion.',
                actions: [['label' => 'Start trial', 'url' => '/signup']],
            ),
            new FeatureSectionData(
                heading: 'Move faster with less friction',
                features: [['title' => 'Activation paths', 'description' => 'Guide new users to the first valuable action.']],
            ),
            new ProofSectionData(
                heading: 'Proof',
                items: [['metric' => '31%', 'name' => 'Activation lift']],
            ),
            new ContentListingSectionData(
                heading: 'Resources',
                items: [['title' => 'Onboarding teardown', 'summary' => 'A practical checklist.', 'url' => '/resources/onboarding']],
            ),
            new class implements ThemeSection
            {
                public function key(): string
                {
                    return 'comparison';
                }

                public function fallbackKey(): ?string
                {
                    return null;
                }

                public function toViewData(): array
                {
                    return [
                        'section' => (object) [
                            'heading' => 'Compare paths',
                            'summary' => 'Choose the route that fits your growth motion.',
                            'items' => [
                                ['title' => 'Self serve', 'summary' => 'Fast activation for smaller teams.'],
                            ],
                        ],
                    ];
                }
            },
            new class implements ThemeSection
            {
                public function key(): string
                {
                    return 'calculator';
                }

                public function fallbackKey(): ?string
                {
                    return null;
                }

                public function toViewData(): array
                {
                    return [
                        'section' => (object) [
                            'heading' => 'Model the lift',
                            'summary' => 'Estimate the compounding effect of activation improvements.',
                            'items' => [
                                ['title' => 'Trial conversion', 'summary' => 'Turn more trials into qualified accounts.', 'metric' => '+18%'],
                            ],
                        ],
                    ];
                }
            },
            new CtaSectionData(
                heading: 'Launch the next test',
                actions: [['label' => 'Start trial', 'url' => '/signup']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Launchdeck',
            items: [['label' => 'Product', 'url' => '/product']],
            ctaLabel: 'Start trial',
            ctaUrl: '/signup',
        ),
        footer: new FooterData(
            brandName: 'Launchdeck',
            columns: [
                ['heading' => 'Company', 'links' => [['label' => 'Contact', 'url' => '/contact']]],
            ],
        ),
    ));

    expect($html)
        ->toContain('Launchdeck')
        ->toContain('Growth ledger')
        ->toContain('Resource pipeline')
        ->toContain('Conversion command')
        ->toContain('Product signal')
        ->toContain('Workflow signal')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme')
        ->not->toContain('capell-app/theme-saas')
        ->not->toContain('capell-theme-saas')
        ->not->toContain('signed')
        ->not->toContain('filament')
        ->not->toContain('editor');
});

it('renders SaaS blog views when Blog is installed', function (): void {
    View::addNamespace('capell-theme-saas', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-saas', __DIR__ . '/../../resources/lang');

    $indexHtml = view('capell-theme-saas::blog.index', [
        'blogAvailable' => true,
        'heading' => 'Growth calendar',
        'summary' => 'What product teams should test next.',
        'articles' => [
            ['title' => 'Activation forecast', 'summary' => 'A practical planning model.', 'url' => '/blog/activation-forecast'],
        ],
    ])->render();

    $articleHtml = view('capell-theme-saas::blog.article', [
        'blogAvailable' => true,
        'title' => 'Activation forecast',
        'summary' => 'A practical planning model.',
        'body' => 'Plan the next growth sprint.',
    ])->render();

    expect($indexHtml)
        ->toContain('saas-insights-index')
        ->toContain('/blog/activation-forecast')
        ->toContain('Activation forecast')
        ->and($articleHtml)
        ->toContain('saas-article')
        ->toContain('Plan the next growth sprint.')
        ->not->toContain('capell-app/theme-saas')
        ->not->toContain('capell-theme-saas')
        ->not->toContain('filament')
        ->not->toContain('editor');
});

it('escapes untrusted article body content', function (): void {
    View::addNamespace('capell-theme-saas', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-saas', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-saas::blog.article', [
        'blogAvailable' => true,
        'title' => 'Activation forecast',
        'body' => '<script>alert("unsafe")</script><p>Visible copy</p>',
    ])->render();

    expect($html)
        ->not->toContain('<script>')
        ->not->toContain('<p>Visible copy</p>')
        ->toContain('&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;&lt;p&gt;Visible copy&lt;/p&gt;');
});

it('renders marketing-safe blog fallbacks when Blog is not installed', function (): void {
    View::addNamespace('capell-theme-saas', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-saas', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-saas::blog.index', [
        'blogAvailable' => false,
        'heading' => 'Growth resources',
        'articles' => [
            ['title' => 'Activation forecast', 'summary' => 'A practical planning model.', 'url' => '/blog/activation-forecast'],
        ],
    ])->render();

    expect($html)
        ->toContain('saas-insights-index')
        ->toContain('Growth resources')
        ->toContain('Activation forecast')
        ->toContain('Resource brief')
        ->not->toContain('href="/blog/activation-forecast"')
        ->not->toContain('capell-app/theme-saas')
        ->not->toContain('capell-theme-saas')
        ->not->toContain('filament')
        ->not->toContain('editor');
});

it('passes Blog package availability through the registered section renderer', function (bool $blogInstalled, string $expectedMarkup, string $missingMarkup): void {
    View::addNamespace('capell-theme-saas', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-saas', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(SaasThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/blog', $blogInstalled);

    $registry = new ThemeRegistry;
    $provider = new SaasThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('saas', 'blog');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(new class implements ThemeSection
    {
        public function key(): string
        {
            return 'blog';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Growth calendar',
                    'items' => [
                        ['title' => 'Activation forecast', 'summary' => 'A practical planning model.', 'url' => '/blog/activation-forecast'],
                    ],
                ],
            ];
        }
    });

    expect($html)
        ->toContain('saas-insights')
        ->toContain($expectedMarkup)
        ->not->toContain($missingMarkup);
})->with([
    'blog installed' => [true, 'href="/blog/activation-forecast"', '<article'],
    'blog not installed' => [false, 'saas-insight-card', 'href="/blog/activation-forecast"'],
]);

it('renders public blog views without database queries', function (): void {
    View::addNamespace('capell-theme-saas', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-saas', __DIR__ . '/../../resources/lang');

    $queries = [];

    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $sectionHtml = (new BlogSectionRenderer(SaasThemeServiceProvider::THEME_KEY, true, failLoudly: true))->render(
        new class implements ThemeSection
        {
            public function key(): string
            {
                return 'blog';
            }

            public function fallbackKey(): ?string
            {
                return null;
            }

            public function toViewData(): array
            {
                return [
                    'section' => (object) [
                        'heading' => 'Growth calendar',
                        'summary' => 'What product teams should test next.',
                        'items' => [
                            ['title' => 'Activation forecast', 'summary' => 'A practical planning model.', 'url' => '/blog/activation-forecast'],
                        ],
                    ],
                ];
            }
        },
    );

    $indexHtml = view('capell-theme-saas::blog.index', [
        'blogAvailable' => true,
        'heading' => 'Growth calendar',
        'articles' => [
            ['title' => 'Activation forecast', 'summary' => 'A practical planning model.', 'url' => '/blog/activation-forecast'],
        ],
    ])->render();

    $articleHtml = view('capell-theme-saas::blog.article', [
        'blogAvailable' => true,
        'title' => 'Activation forecast',
        'summary' => 'A practical planning model.',
        'body' => 'Plan the next growth sprint.',
    ])->render();

    expect($sectionHtml)->toContain('saas-insights')
        ->and($indexHtml)->toContain('saas-insights-index')
        ->and($articleHtml)->toContain('saas-article')
        ->and($queries)->toBe([]);
});
