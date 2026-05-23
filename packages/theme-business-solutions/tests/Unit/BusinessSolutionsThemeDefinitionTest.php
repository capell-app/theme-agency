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
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\BusinessSolutions\BusinessSolutionsThemeServiceProvider;
use Capell\ThemeStudio\BusinessSolutions\Health\ThemeBusinessSolutionsHealthCheck;

uses(PackagesTestCase::class);

it('defines twelve distinct business solution themes from the Stitch board', function (): void {
    $definitions = BusinessSolutionsThemeServiceProvider::definitions();
    $profiles = BusinessSolutionsThemeServiceProvider::profiles();

    expect($definitions)->toHaveCount(12)
        ->and(collect($definitions)->pluck('key')->unique()->values()->all())->toHaveCount(12)
        ->and(collect($definitions)->pluck('runtime.value')->unique()->all())->toBe(['blade'])
        ->and(collect($profiles)->pluck('layout')->unique()->count())->toBeGreaterThanOrEqual(10)
        ->and(collect($profiles)->where('modern', true)->count())->toBeGreaterThanOrEqual(6)
        ->and(ThemeBusinessSolutionsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('registers all business solution themes only when installed', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    $provider = new BusinessSolutionsThemeServiceProvider($this->app);
    $provider->register();
    CapellCore::forcePackageInstalled(BusinessSolutionsThemeServiceProvider::PACKAGE_NAME, false);
    $provider->boot($registry);

    expect($registry->definitions())->toBe([]);

    CapellCore::forcePackageInstalled(BusinessSolutionsThemeServiceProvider::PACKAGE_NAME);
    $provider->boot($registry);

    expect($registry->definitions())->toHaveCount(12)
        ->and($registry->has('business-healthcare'))->toBeTrue()
        ->and($registry->has('business-commerce'))->toBeTrue()
        ->and($registry->definition('business-commerce')->frontend['modern'])->toBeTrue();
});

it('renders selected business themes with different layout systems and no package identifiers', function (string $themeKey, string $layout): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(BusinessSolutionsThemeServiceProvider::PACKAGE_NAME);

    $registry = new ThemeRegistry;
    $provider = new BusinessSolutionsThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    $definition = $registry->definition($themeKey);
    $brand = (new BrandProfileData)->merge($definition->presets[0]->values);

    $html = $registry->renderer($themeKey)->render(new ThemePageData(
        title: 'Business theme',
        brand: $brand,
        sections: [
            new HeroSectionData(
                heading: 'Business growth with a clear operating system',
                eyebrow: 'Solutions',
                summary: 'A focused public site built for a real business workflow.',
                actions: [
                    ['label' => 'Book a consultation', 'url' => '/contact'],
                    ['label' => 'View services', 'url' => '/services', 'style' => 'secondary'],
                ],
            ),
            new FeatureSectionData(
                heading: 'Built around the work',
                summary: 'The layout changes to match the vertical.',
                features: [
                    ['title' => 'Service discovery', 'description' => 'Clear paths for visitors.'],
                    ['title' => 'Trust proof', 'description' => 'Evidence stays close to the decision.'],
                ],
            ),
            new ProofSectionData(
                heading: 'Trusted delivery',
                items: [
                    ['metric' => '98%', 'name' => 'Client confidence'],
                    ['metric' => '24h', 'name' => 'Response window'],
                    ['metric' => '4.9', 'name' => 'Average rating'],
                ],
            ),
            new ContentListingSectionData(
                heading: 'Featured resources',
                items: [
                    ['title' => 'Service guide', 'summary' => 'A practical overview.', 'url' => '/guide'],
                    ['title' => 'Case study', 'summary' => 'Proof from a similar team.', 'url' => '/case-study'],
                    ['title' => 'Planning checklist', 'summary' => 'A useful next step.', 'url' => '/checklist'],
                ],
            ),
            new CtaSectionData(
                heading: 'Plan the next move',
                actions: [['label' => 'Start now', 'url' => '/contact']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Northstar Group',
            items: [
                ['label' => 'Services', 'url' => '/services'],
                ['label' => 'Proof', 'url' => '/proof'],
            ],
            ctaLabel: 'Contact',
            ctaUrl: '/contact',
        ),
        footer: new FooterData(
            brandName: 'Northstar Group',
            columns: [
                ['heading' => 'Company', 'links' => [['label' => 'Contact', 'url' => '/contact']]],
            ],
        ),
    ));

    expect($html)
        ->toContain('data-business-layout="' . $layout . '"')
        ->toContain('Northstar Group')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme-business-solutions')
        ->not->toContain('capell-app/theme-business-solutions')
        ->not->toContain('signature=')
        ->not->toContain('signed-url')
        ->not->toContain('filament')
        ->not->toContain('editor');
})->with([
    'healthcare appointment layout' => ['business-healthcare', 'appointment-hero'],
    'real estate search layout' => ['business-real-estate', 'search-hero'],
    'modern commerce catalog layout' => ['business-commerce', 'catalog-grid'],
]);

it('renders every business theme through the richer visual system', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(BusinessSolutionsThemeServiceProvider::PACKAGE_NAME);

    $registry = new ThemeRegistry;
    $provider = new BusinessSolutionsThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    foreach (BusinessSolutionsThemeServiceProvider::definitions() as $definition) {
        $brand = (new BrandProfileData)->merge($definition->presets[0]->values);
        $html = $registry->renderer($definition->key)->render(new ThemePageData(
            title: $definition->name,
            brand: $brand,
            sections: [
                new HeroSectionData(
                    heading: $definition->description,
                    eyebrow: $definition->tags[0] ?? 'Business',
                    summary: 'Purpose-built layout for a specific business vertical.',
                    actions: [
                        ['label' => 'Start', 'url' => '/start'],
                        ['label' => 'Learn more', 'url' => '/learn', 'style' => 'secondary'],
                    ],
                ),
                new FeatureSectionData(
                    heading: 'Business-ready sections',
                    features: [
                        ['title' => 'Primary workflow', 'description' => 'The first viewport is shaped around the visitor task.'],
                        ['title' => 'Decision proof', 'description' => 'Trust signals stay close to conversion moments.'],
                    ],
                ),
                new ContentListingSectionData(
                    heading: 'Featured content',
                    items: [
                        ['title' => 'Overview', 'summary' => 'A short resource.', 'url' => '/overview'],
                        ['title' => 'Guide', 'summary' => 'A practical next step.', 'url' => '/guide'],
                    ],
                ),
            ],
            navigation: new NavigationData(
                brandName: $definition->name,
                items: [['label' => 'Services', 'url' => '/services']],
                ctaLabel: 'Contact',
                ctaUrl: '/contact',
            ),
            footer: new FooterData(brandName: $definition->name),
        ));

        expect($html)
            ->toContain('data-business-layout="' . $definition->frontend['layout'] . '"')
            ->toContain('business-theme-visual-panel')
            ->not->toContain('capell-theme-business-solutions')
            ->not->toContain('signature=')
            ->not->toContain('filament')
            ->not->toContain('data-editor')
            ->not->toContain('editor-url');
    }
});

it('documents screenshot generation for every business theme and editable content blocks', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(
        (string) file_get_contents($packagePath . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $overview = (string) file_get_contents($packagePath . '/docs/overview.md');
    /** @var list<array{id?: string, surface?: string, target?: string}> $entriesData */
    $entriesData = is_array($manifest['entries'] ?? null) ? $manifest['entries'] : [];
    $entries = collect($entriesData);
    $frontendTargets = $entries
        ->where('surface', 'frontend')
        ->pluck('target')
        ->values()
        ->all();

    expect($manifest['outputDirectory'])->toBe('public/docs/screenshots/packages/theme-business-solutions')
        ->and($manifest['requiresInstalledPackage'])->toBeTrue()
        ->and($entries->where('id', 'admin-content-blocks-edit-unique-business-content'))->toHaveCount(1)
        ->and($overview)->toContain('capell-app/demo-kit')
        ->and($overview)->toContain('content blocks')
        ->and($overview)->toContain('Layout Builder content editor')
        ->and($overview)->toContain('admin-content-blocks-edit-unique-business-content.png');

    foreach (BusinessSolutionsThemeServiceProvider::definitions() as $definition) {
        expect($frontendTargets)
            ->toContain('/theme-business-solutions-demo/' . $definition->key)
            ->and($overview)
            ->toContain('`' . $definition->key . '`')
            ->toContain('frontend-' . $definition->key . '-theme.png');
    }
});
