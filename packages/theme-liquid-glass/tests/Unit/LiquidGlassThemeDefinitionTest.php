<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\FooterData;
use Capell\Core\ThemeStudio\Data\GenericSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\LayoutBuilder\Models\Widget;
use Capell\ThemeStudio\LiquidGlass\Health\ThemeLiquidGlassHealthCheck;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;
use Capell\ThemeStudio\LiquidGlass\Support\Demo\LiquidGlassDemoContent;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

it('defines the Liquid Glass free renderer contract', function (): void {
    $definition = LiquidGlassThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-liquid-glass')
        ->and($definition->key)->toBe(LiquidGlassThemeServiceProvider::THEME_KEY)
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/liquid-glass.css'])
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

it('registers Liquid Glass as definition-only with no legacy renderer', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LiquidGlassThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new LiquidGlassThemeServiceProvider(app());
    $provider->boot($registry);

    expect($registry->hasRenderer(LiquidGlassThemeServiceProvider::THEME_KEY))->toBeFalse();

    CapellCore::clearPackages();
});

it('renders navigation from the Liquid Glass package views', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.navigation', [
        'section' => new NavigationData(
            brandName: 'Capell',
            items: [['label' => 'Home', 'url' => '/']],
            ctaLabel: 'Start',
            ctaUrl: '/start',
        ),
    ])->render();

    expect($html)
        ->toContain('Capell')
        ->toContain('Home')
        ->toContain('Start')
        ->toContain('liquid-glass-nav')
        ->toContain('Main navigation')
        ->not->toContain('capell-app/theme-liquid-glass');
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

it('renders Liquid Glass fallback action anchors to package-owned public targets', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $heroHtml = view('capell-theme-liquid-glass::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Launch with a glass interface',
            summary: 'Translucent sections for modern teams.',
        ),
    ])->render();

    $ctaHtml = view('capell-theme-liquid-glass::sections.cta', [
        'section' => new CtaSectionData(
            heading: 'Start with Liquid Glass',
            summary: 'Use a polished public theme without custom schema.',
        ),
    ])->render();

    expect($heroHtml)
        ->toContain('href="#main-content"')
        ->toContain('href="#proof"')
        ->not->toContain('href="#content"');

    expect($ctaHtml)
        ->toContain('href="#main-content"')
        ->toContain('href="#proof"')
        ->not->toContain('href="#content"');
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

it('renders the Liquid Glass features section from theme copy', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.features', [
        'section' => new FeatureSectionData(
            heading: 'Token-driven section rhythm',
            summary: 'Cards, buttons, and panels share one theme token contract.',
            features: [
                ['type' => 'Surface', 'title' => 'Readable panels', 'description' => 'Foreground, border, and panel colors are derived from the active preset values.'],
            ],
        ),
    ])->render();

    expect($html)
        ->toContain('Token-driven section rhythm')
        ->toContain('Readable panels')
        ->toContain('Surface')
        ->not->toContain('capell-app/theme-liquid-glass');
});

it('renders the Liquid Glass showcase section from generic section data', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.showcase', [
        'section' => new GenericSectionData('showcase', [
            'eyebrow' => 'Showcase',
            'heading' => 'Work that stays legible through glass',
            'summary' => 'A curated set of launch surfaces.',
            'items' => [
                ['discipline' => 'Product', 'title' => 'Marlow Studio', 'summary' => 'A launch page proof.', 'metric' => '3x', 'metricLabel' => 'Faster launches'],
            ],
        ]),
    ])->render();

    expect($html)
        ->toContain('Work that stays legible through glass')
        ->toContain('Marlow Studio')
        ->toContain('Product')
        ->not->toContain('capell-app/theme-liquid-glass');
});

it('renders the Liquid Glass presets section from generic section data', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.presets', [
        'section' => new GenericSectionData('presets', [
            'eyebrow' => 'Presets',
            'heading' => 'Three glass presets, one token contract',
            'summary' => 'Clarity, Prism, and Graphite cover bright, editorial, and dark surfaces.',
            'presets' => [
                ['name' => 'Clarity', 'title' => 'Bright glass launch', 'description' => 'Teal structure and warm accents.', 'surfaces' => ['Homepage', 'Contact']],
            ],
        ]),
    ])->render();

    expect($html)
        ->toContain('Three glass presets, one token contract')
        ->toContain('Clarity')
        ->toContain('Bright glass launch')
        ->toContain('Homepage')
        ->not->toContain('capell-app/theme-liquid-glass');
});

it('renders the Liquid Glass proof section from theme copy', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.proof', [
        'section' => new ProofSectionData(
            heading: 'Preset proof points',
            summary: 'Real rendering evidence for every preset.',
            items: [
                ['metric' => '3', 'quote' => 'Theme presets render through one Blade shell.', 'name' => 'Preset coverage', 'role' => 'Clarity, Prism, Graphite'],
            ],
        ),
    ])->render();

    expect($html)
        ->toContain('Preset proof points')
        ->toContain('Theme presets render through one Blade shell.')
        ->toContain('Preset coverage')
        ->not->toContain('capell-app/theme-liquid-glass');
});

it('renders the Liquid Glass footer section from theme copy', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-liquid-glass::sections.footer', [
        'section' => new FooterData(
            brandName: 'Liquid Glass',
            summary: 'A free modern theme of translucent panels.',
            columns: [
                ['heading' => 'Product', 'links' => [['label' => 'Features', 'url' => '#features']]],
            ],
        ),
    ])->render();

    expect($html)
        ->toContain('Liquid Glass')
        ->toContain('A free modern theme of translucent panels.')
        ->toContain('Product')
        ->toContain('href="#features"')
        ->not->toContain('capell-app/theme-liquid-glass');
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

it('seeds real Layout containers and a page-content Widget through ThemeDemoPageInstaller', function (): void {
    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Liquid Glass Definition Test'],
            languageCodes: ['en'],
            baseUrl: 'https://liquid-glass.definition-test.test',
        ),
        themeKey: LiquidGlassThemeServiceProvider::THEME_KEY,
        themeName: 'Liquid Glass',
        contentProvider: new LiquidGlassDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', LiquidGlassThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->first();

    expect($homepage)->toBeInstanceOf(Page::class);

    $layout = $homepage->layout;

    expect($layout)->toBeInstanceOf(Layout::class)
        ->and($layout->containers)->toBe([
            'main' => [
                'widgets' => [
                    ['widget_key' => 'page-content', 'occurrence' => 1],
                    ['widget_key' => 'liquid-glass-showcase-homepage-1', 'occurrence' => 1],
                    ['widget_key' => 'liquid-glass-presets-homepage-1', 'occurrence' => 1],
                    ['widget_key' => 'liquid-glass-cta-homepage-1', 'occurrence' => 1],
                ],
            ],
        ]);

    $widget = Widget::query()->firstWhere('key', 'page-content');

    expect($widget)->toBeInstanceOf(Widget::class);

    $ctaWidget = Widget::query()->firstWhere('key', 'liquid-glass-cta-homepage-1');

    expect($ctaWidget)->toBeInstanceOf(Widget::class)
        ->and($ctaWidget->component)->toBe('capell.widget.liquid-glass.cta')
        ->and($ctaWidget->meta['heading'] ?? null)->toBe('Bring your pages onto the glass');

    $showcaseWidget = Widget::query()->firstWhere('key', 'liquid-glass-showcase-homepage-1');

    expect($showcaseWidget)->toBeInstanceOf(Widget::class)
        ->and($showcaseWidget->component)->toBe('capell.widget.liquid-glass.showcase')
        ->and($showcaseWidget->meta['heading'] ?? null)->toBe('Teams that ship on the glass');

    $presetsWidget = Widget::query()->firstWhere('key', 'liquid-glass-presets-homepage-1');

    expect($presetsWidget)->toBeInstanceOf(Widget::class)
        ->and($presetsWidget->component)->toBe('capell.widget.liquid-glass.presets')
        ->and($presetsWidget->meta['heading'] ?? null)->toBe('One glass system, three token-driven presets');
});

it('preserves the real, previously-authored section copy per surface for later widget wiring', function (): void {
    $content = new LiquidGlassDemoContent;

    $homepageCopy = $content->sectionCopy('homepage');

    expect($homepageCopy)->toHaveCount(6);

    $types = array_column($homepageCopy, 'type');

    expect($types)->toBe(['features', 'showcase', 'presets', 'proof', 'content-listing', 'cta']);

    $features = $homepageCopy[0];
    $featureItems = is_array($features['features'] ?? null) ? $features['features'] : [];
    $firstFeature = is_array($featureItems[0] ?? null) ? $featureItems[0] : [];

    expect($features['heading'])->toBe('Surfaces that stay legible through the glass')
        ->and($firstFeature['title'])->toBe('Frosted panels with depth');

    $showcase = $homepageCopy[1];
    $showcaseItems = is_array($showcase['items'] ?? null) ? $showcase['items'] : [];
    $firstShowcaseItem = is_array($showcaseItems[0] ?? null) ? $showcaseItems[0] : [];

    expect($showcase['heading'])->toBe('Teams that ship on the glass')
        ->and($firstShowcaseItem['title'])->toBe('Marlow Studio launch');

    $presets = $homepageCopy[2];
    $presetItems = is_array($presets['presets'] ?? null) ? $presets['presets'] : [];
    $firstPreset = is_array($presetItems[0] ?? null) ? $presetItems[0] : [];

    expect($presets['heading'])->toBe('One glass system, three token-driven presets')
        ->and($firstPreset['name'])->toBe('Clarity');

    $proof = $homepageCopy[3];
    $proofItems = is_array($proof['items'] ?? null) ? $proof['items'] : [];
    $firstProofItem = is_array($proofItems[0] ?? null) ? $proofItems[0] : [];

    expect($firstProofItem['quote'])->toBe('The glass panels gave our launch page depth without losing legibility.');

    $ctaSurfaceCopy = $content->sectionCopy('cta');

    expect(array_column($ctaSurfaceCopy, 'type'))->toBe(['presets', 'proof', 'cta']);

    $notFoundCopy = $content->sectionCopy('not-found');

    expect(array_column($notFoundCopy, 'type'))->toBe(['cta'])
        ->and($notFoundCopy[0]['heading'])->toBe('Bring your pages onto the glass');

    expect($content->sectionCopy('unknown-surface'))->toBe([]);
});

it('preserves the real, previously-authored hero copy per surface for later widget wiring', function (): void {
    $content = new LiquidGlassDemoContent;

    $homepageHero = $content->heroCopy('homepage');

    expect($homepageHero['eyebrow'])->toBe('Liquid Glass')
        ->and($homepageHero['heading'])->toBe('A modern glass surface for launch and service pages')
        ->and($homepageHero['mediaAlt'])->toBe('Layered translucent glass panels');

    $directoryHero = $content->heroCopy('directory');

    expect($directoryHero['eyebrow'])->toBe('Listing')
        ->and($directoryHero['mediaAlt'])->toBe('Translucent content cards in a glass listing');

    $detailHero = $content->heroCopy('detail');

    expect($detailHero['eyebrow'])->toBe('Story')
        ->and($detailHero['mediaAlt'])->toBe('A launch page rebuilt on the glass section rhythm');

    $contactHero = $content->heroCopy('contact');

    expect($contactHero['eyebrow'])->toBe('Contact')
        ->and($contactHero['actions'][0])->toBe(['label' => 'Get in touch', 'url' => 'mailto:studio@liquidglass.example', 'style' => 'primary'])
        ->and($contactHero['mediaAlt'])->toBe('A polished glass lead journey');

    $emptyHero = $content->heroCopy('empty');

    expect($emptyHero['eyebrow'])->toBe('Listing')
        ->and($emptyHero['heading'])->toBe('No content cards match that filter yet')
        ->and($emptyHero['summary'])->toBe('Nothing matches the current filter. Clear it to see every card on the glass, or jump straight to the features.');

    $notFoundHero = $content->heroCopy('not-found');

    expect($notFoundHero['eyebrow'])->toBe('404')
        ->and($notFoundHero['heading'])->toBe('This page slipped through the glass')
        ->and($notFoundHero['summary'])->toBe('The link is broken or the page has moved. Head back to the features, or start a conversation.')
        ->and($notFoundHero['actions'][0])->toBe(['label' => 'Back to home', 'url' => '/', 'style' => 'primary']);

    $ctaHero = $content->heroCopy('cta');

    expect($ctaHero['eyebrow'])->toBe('Get started')
        ->and($ctaHero['summary'])->toBe('Move launch, listing, and lead pages onto translucent panels with a section rhythm that stays crisp from first view to conversion.')
        ->and($ctaHero['mediaAlt'])->toBe('Translucent glass panels for launch pages');

    expect($content->heroCopy('unknown-surface'))->toBe([]);
});

it('preserves the empty surface\'s real, previously-authored inline empty-state listing copy for later widget wiring', function (): void {
    $content = new LiquidGlassDemoContent;

    $emptyStateListing = $content->emptyStateListingCopy();

    expect($emptyStateListing)->toBe([
        'type' => 'content-listing',
        'heading' => 'Nothing to show on the glass here',
        'summary' => 'When content lands in this group it appears here as translucent cards, newest first.',
        'variant' => 'editorial',
        'items' => [],
    ]);
});
