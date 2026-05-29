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

    expect($packageImports)->toContain('resources/css/theme-corporate.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');
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
