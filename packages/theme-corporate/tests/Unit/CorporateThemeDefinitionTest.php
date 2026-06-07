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
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Corporate\CorporateThemeServiceProvider;
use Capell\ThemeStudio\Corporate\Health\ThemeCorporateHealthCheck;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

it('defines the corporate free renderer contract', function (): void {
    $definition = CorporateThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-corporate')
        ->and($definition->key)->toBe(CorporateThemeServiceProvider::THEME_KEY)
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/corporate.css'])
        ->and($definition->includedSections)->toContain('hero', 'features', 'proof', 'cta')
        ->and($definition->presets)->toHaveCount(6)
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->tags)->toContain('Trust')
        ->and(ThemeCorporateHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('renders navigation from the corporate package views', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');

    $provider = new CorporateThemeServiceProvider($this->app);
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

it('declares renderers for every included corporate section', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');

    $provider = new CorporateThemeServiceProvider($this->app);
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

it('renders the corporate hero with a board briefing fallback', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Governance for growing teams',
            eyebrow: 'Advisory',
            summary: 'Practical strategy, compliance, and delivery support.',
            actions: [['label' => 'Explore services', 'url' => '/services']],
        ),
    ])->render();

    expect($html)
        ->toContain('Governance for growing teams')
        ->toContain('Board briefing')
        ->toContain('Board pack')
        ->toContain('Pack ready')
        ->toContain('Decision log')
        ->toContain('Agenda')
        ->not->toContain('capell-app/theme-corporate');
});

it('renders translated fallback and custom corporate hero stats', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $fallbackHtml = view('capell-theme-corporate::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Governance for growing teams',
            summary: 'Practical strategy, compliance, and delivery support.',
        ),
    ])->render();

    expect($fallbackHtml)
        ->toContain('Confidence')
        ->toContain('Clear signals')
        ->toContain('Board support')
        ->toContain('Structured proof')
        ->not->toContain('Pages')
        ->not->toContain('Content')
        ->not->toContain('Media')
        ->not->toContain('Assets')
        ->not->toContain('Layout')
        ->not->toContain('Widgets');

    $customHtml = view('capell-theme-corporate::sections.hero', [
        'section' => (object) [
            'heading' => 'Governance for growing teams',
            'eyebrow' => null,
            'summary' => null,
            'actions' => [],
            'mediaAlt' => null,
            'mediaUrl' => null,
            'stats' => [
                ['label' => 'Offices', 'value' => '12'],
                ['label' => 'Clients', 'value' => '240'],
                ['label' => 'Markets', 'value' => '8'],
            ],
        ],
    ])->render();

    expect($customHtml)
        ->toContain('Offices')
        ->toContain('240')
        ->toContain('Markets')
        ->not->toContain('Clear signals');
});

it('drives the corporate shell and card surfaces from theme tokens', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');

    $pageHtml = view('capell-theme-corporate::page', [
        'brand' => new BrandProfileData(
            surfaceColor: '#fbfaf7',
            foregroundColor: '#18201f',
        ),
        'content' => '<main id="main-content">Preview</main>',
    ])->render();

    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-corporate.css') ?: '';
    $views = implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [],
    ));

    expect($pageHtml)
        ->toContain('--theme-surface:#fbfaf7')
        ->toContain('--theme-foreground:#18201f')
        ->toContain('class="site-theme-shell min-h-screen antialiased"')
        ->not->toContain('bg-[#f7f8f6]')
        ->not->toContain('text-slate-950 antialiased');

    expect($css)
        ->toContain('--corporate-surface: var(--theme-surface, #f7f8f6)')
        ->toContain('--corporate-card-radius: var(--theme-radius-value, 0.35rem)')
        ->toContain('--corporate-card-padding: var(--theme-card-padding, 1.25rem)')
        ->toContain('.corporate-card')
        ->toContain('.corporate-card-muted')
        ->toContain('.corporate-card-elevated');

    expect($views)
        ->toContain('corporate-surface')
        ->toContain('corporate-card')
        ->toContain('corporate-card-muted')
        ->toContain('corporate-card-elevated')
        ->not->toContain('bg-[#f7f8f6]');
});

it('renders translated corporate proof aria labels with generic public selectors', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.proof', [
        'section' => new ProofSectionData(
            heading: 'Evidence',
            items: [
                [
                    'image' => '/proof.jpg',
                    'name' => 'Assurance review',
                    'quote' => 'Approvals moved faster.',
                ],
            ],
        ),
    ])->render();

    expect($html)
        ->toContain('aria-label="Open image for Assurance review"')
        ->toContain('aria-label="Previous proof cards"')
        ->toContain('aria-label="Next proof cards"')
        ->toContain('aria-label="Close gallery"')
        ->toContain('data-carousel="proof"')
        ->not->toContain('data-carousel="corporate-proof"');
});

it('renders content listing variant labels from translations', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.content-listing', [
        'section' => new ContentListingSectionData(
            heading: 'Common questions',
            items: [
                [
                    'title' => 'How do we start?',
                    'summary' => 'Start with a readiness review.',
                    'url' => '/questions/start',
                ],
            ],
            variant: 'faq',
        ),
    ])->render();

    expect($html)
        ->toContain('FAQ')
        ->toContain('Question 1')
        ->not->toContain('{{ ucfirst($variant) }}')
        ->not->toContain('Question {{ $loop->iteration }}');
});

it('registers corporate only when the theme package is installed', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    $provider = new CorporateThemeServiceProvider($this->app);
    $provider->register();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName, false);
    $provider->boot($registry);

    expect($registry->has('corporate'))->toBeFalse();

    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $provider->boot($registry);

    expect($registry->has('corporate'))->toBeTrue()
        ->and($registry->definition('corporate')->package)->toBe(CorporateThemeServiceProvider::$packageName);
});

it('registers corporate tailwind imports and blade sources when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CorporateThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CorporateThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CorporateThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageBuildAssets = CapellCore::getVendorAssetsForType(VendorAssetEnum::BuildAsset)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CorporateThemeServiceProvider::$packageName)
        ->map(fn (mixed $asset): string => $asset->path() . '/' . $asset->file())
        ->all();

    expect($packageImports)->toContain('resources/css/theme-corporate.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php')
        ->and($packageBuildAssets)->toContain('vendor/capell-theme-corporate/resources/js/theme-corporate.js');
});

it('renders public theme markup without package identifiers', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CorporateThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    $html = $registry->renderer('corporate')->render(new ThemePageData(
        title: 'Advisory',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Governance for growing teams',
                eyebrow: 'Advisory',
                summary: 'Practical strategy, compliance, and delivery support.',
                actions: [['label' => 'Explore services', 'url' => '/services']],
            ),
            new FeatureSectionData(
                heading: 'Trusted operating support',
                features: [['title' => 'Risk reviews', 'description' => 'Structured reviews for critical decisions.']],
            ),
            new ProofSectionData(
                heading: 'Evidence',
                items: [['metric' => '24%', 'name' => 'Faster approvals']],
            ),
            new ContentListingSectionData(
                heading: 'Insights',
                items: [['title' => 'Board reporting', 'summary' => 'A clearer monthly reporting model.', 'url' => '/insights/board-reporting']],
            ),
            new CtaSectionData(
                heading: 'Talk to an advisor',
                actions: [['label' => 'Book a call', 'url' => '/contact']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Northbridge Advisory',
            items: [['label' => 'Services', 'url' => '/services']],
            ctaLabel: 'Contact',
            ctaUrl: '/contact',
        ),
        footer: new FooterData(
            brandName: 'Northbridge Advisory',
            columns: [
                ['heading' => 'Company', 'links' => [['label' => 'Contact', 'url' => '/contact']]],
            ],
        ),
    ));

    expect($html)
        ->toContain('Northbridge Advisory')
        ->toContain('Operating model')
        ->toContain('Assurance')
        ->toContain('Board action')
        ->toContain('Register')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme')
        ->not->toContain('capell-app/theme-corporate')
        ->not->toContain('capell-theme-corporate')
        ->not->toContain('signed')
        ->not->toContain('filament')
        ->not->toContain('editor');
});

it('renders the corporate content listing variant matrix', function (string $variant, string $expectedMarkup): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    View::addNamespace('capell-foundation-theme', __DIR__ . '/../../../foundation-theme/resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.content-listing', [
        'section' => new ContentListingSectionData(
            heading: 'Corporate resources',
            summary: 'Structured updates for stakeholders.',
            items: [
                [
                    'title' => 'Board reporting model',
                    'summary' => 'A clearer monthly reporting model.',
                    'url' => '/insights/board-reporting',
                    'image' => '/images/reporting.jpg',
                    'type' => 'Briefing',
                    'meta' => ['Governance'],
                ],
            ],
            variant: $variant,
        ),
    ])->render();

    expect($html)
        ->toContain('Corporate resources')
        ->toContain($expectedMarkup)
        ->not->toContain('capell-app/theme-corporate');
})->with([
    'editorial' => ['editorial', 'Register'],
    'media' => ['media', 'Media'],
    'faq' => ['faq', 'Question 1'],
    'people' => ['people', 'Governance'],
    'metrics' => ['metrics', 'Metrics'],
    'gallery fallback' => ['gallery', 'Board reporting model'],
    'pathways fallback' => ['pathways', 'Board reporting model'],
    'spotlight fallback' => ['spotlight', 'Board reporting model'],
]);
