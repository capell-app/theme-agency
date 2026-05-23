<?php

declare(strict_types=1);

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
use Capell\ThemeStudio\Saas\Health\ThemeSaasHealthCheck;
use Capell\ThemeStudio\Saas\SaasThemeServiceProvider;
use Illuminate\Support\Facades\View;

it('defines the saas premium renderer contract', function (): void {
    $definition = SaasThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-saas')
        ->and($definition->key)->toBe(SaasThemeServiceProvider::THEME_KEY)
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/saas.css'])
        ->and($definition->includedSections)->toContain('hero', 'features', 'proof', 'cta')
        ->and($definition->presets)->toHaveCount(8)
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
        'cta',
        'footer',
    ]);
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
        ->and($registry->definition('saas')->package)->toBe(SaasThemeServiceProvider::$packageName);
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
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme')
        ->not->toContain('capell-app/theme-saas')
        ->not->toContain('capell-theme-saas')
        ->not->toContain('signed')
        ->not->toContain('filament')
        ->not->toContain('editor');
});
