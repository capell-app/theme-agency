<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Actions\ResolveBrandProfileAction;
use Capell\Core\ThemeStudio\Assets\ThemeTokenRenderer;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\ThemeFrontendBuildAssetsData;
use Capell\Core\ThemeStudio\Data\ThemeOverrideData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\LayoutBuilder\Models\Widget;
use Capell\Tests\Support\RenderedHtml;
use Capell\ThemeAgency\AgencyThemeServiceProvider;
use Capell\ThemeAgency\Health\ThemeAgencyHealthCheck;
use Capell\ThemeAgency\Support\Demo\AgencyDemoContent;

it('defines the agency layout-native contract', function (): void {
    $definition = AgencyThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-agency')
        ->and($definition->key)->toBe(AgencyThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Agency')
        ->and($definition->description)->toContain('Awarded portfolios and design-education picks')
        ->and($definition->tags)->toContain('Portfolio', 'Directory', 'Awards', 'Creators', 'Gallery')
        ->and($definition->bestFit)->toContain('Portfolio directories', 'Creative award sites', 'Design education hubs')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'featured-portfolios',
            'filter-taxonomies',
            'portfolio-grid',
            'awarded-profiles',
            'creator-directory',
            'education-upsell',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(3)
        ->and($definition->presets[0]->key)->toBe('agency')
        ->and($definition->presets[2]->key)->toBe('hand-picked')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/agency.css'])
        ->and($definition->frontendBuildAssets())->toBeInstanceOf(ThemeFrontendBuildAssetsData::class)
        ->and($definition->frontendBuildAssets()?->cssSource)->toBe('resources/css/theme-agency.css')
        ->and($definition->frontendBuildAssets()?->cssBuildInput)->toBe('resources/css/capell/themes/agency.css')
        ->and($definition->frontendBuildAssets()?->condition)->toBe('theme-css:agency')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeAgencyHealthCheck::compatibleCapellApiVersion())->toBe('^1.0');
});

it('places owned navigation and hero before portable copy inside the agency shell', function (): void {
    $definition = (new AgencyDemoContent)->definitions('agency', 'Agency', 'https://agency.test')[0];
    $main = data_get($definition->containers, 'main');
    $widgets = data_get($main, 'widgets', []);
    throw_unless(is_array($widgets), RuntimeException::class, 'Expected agency widgets.');
    $widgetKeys = array_column($widgets, 'widget_key');

    expect(data_get($main, 'meta.html_class'))->toBe('ppc-shell')
        ->and($widgetKeys[0] ?? null)->toBe('agency-navigation-homepage-1')
        ->and($widgetKeys[1] ?? null)->toBe('agency-hero-homepage-1')
        ->and($widgetKeys[2] ?? null)->toBe('page-content')
        ->and(data_get($main, 'widgets.2.meta.page_content'))->toBe(['content']);
});

it('renders editor copy through the registered agency widget handlers', function (): void {
    CapellCore::forcePackageInstalled('capell-app/theme-foundation');
    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName);

    $themeRegistry = resolve(ThemeRegistry::class);
    $provider = new AgencyThemeServiceProvider(app());
    $provider->register();
    $provider->boot($themeRegistry);

    expect($themeRegistry->has(AgencyThemeServiceProvider::THEME_KEY))->toBeTrue();

    foreach (['portfolio-grid'] as $type) {
        $entry = resolve(RenderableRegistry::class)->get('layout-widget', 'capell.widget.agency.' . $type);
        $widget = new Widget(['meta' => [
            'type' => $type,
            'heading' => 'An editor-owned section',
            'summary' => 'Visible copy from the registered widget.',
            'items' => [],
        ]]);
        $viewName = $entry->blade;
        throw_unless(is_string($viewName) && view()->exists($viewName), RuntimeException::class, 'Expected a renderable widget view.');
        $document = new RenderedHtml(view($viewName, ['widget' => $widget])->render());

        expect($document->count('//*[self::h1 or self::h2][normalize-space(.)="An editor-owned section"]'))->toBe(1)
            ->and($document->count('//p[normalize-space(.)="Visible copy from the registered widget."]'))->toBe(1);
    }
});

it('renders looping hero video media with a large header image', function (): void {
    view()->addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-agency::sections.hero', [
        'section' => [
            'preview_video_url' => '/media/portfolio-demo.mp4',
            'preview_image_url' => '/media/portfolio-poster.jpg',
            'preview_alt' => 'Portfolio gallery walkthrough',
            'header_image_url' => '/media/portfolio-header.jpg',
            'header_image_alt' => 'Portfolio collection grid',
        ],
    ])->render();

    expect($html)
        ->toContain('<video')
        ->toContain('autoplay')
        ->toContain('loop')
        ->toContain('muted')
        ->toContain('playsinline')
        ->toContain('poster="/media/portfolio-poster.jpg"')
        ->toContain('src="/media/portfolio-demo.mp4"')
        ->toContain('aria-label="Portfolio gallery walkthrough"')
        ->toContain('src="/media/portfolio-header.jpg"')
        ->toContain('alt="Portfolio collection grid"');
});

it('renders looping hero gif media without requiring a video source', function (): void {
    view()->addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');

    $html = view('capell-theme-agency::sections.hero', [
        'section' => [
            'media' => [
                'preview_gif_url' => '/media/portfolio-loop.gif',
                'preview_alt' => 'Animated portfolio loop',
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('src="/media/portfolio-loop.gif"')
        ->toContain('alt="Animated portfolio loop"')
        ->not->toContain('<video');
});

require_once __DIR__ . '/../../../theme-foundation/tests/Support/ThemeNavigationAssertions.php';

it('exposes the current page through the portfolio navigation', function (): void {
    assertThemeNavigationOutcomes('agency');
});

it('renders each agency identity choice and rejects unsupported choices', function (): void {
    $definition = AgencyThemeServiceProvider::definition();
    $groups = $definition->frontendEditorGroups();
    $tokens = $definition->frontendEditorTokens();

    expect($groups['identity'])->toBe(['plateCrop'])
        ->and($tokens['plateCrop']['options'])->toBe(['contained', 'panoramic', 'cinematic']);

    foreach ($definition->presets as $preset) {
        expect($preset->values['plateCrop'] ?? null)->toBe('contained');
    }

    foreach ($tokens['plateCrop']['options'] as $option) {
        $profile = ResolveBrandProfileAction::run(
            new BrandProfileData,
            $definition,
            new ThemeOverrideData(themeKey: $definition->key, values: ['plateCrop' => $option]),
        );
        $rendered = (new ThemeTokenRenderer)->css($profile);
        preg_match('/--theme-plate-crop\s*:\s*([^;]+);/', $rendered, $declaration);

        expect($declaration[1] ?? null)->toBe($option);
    }

    $invalid = ResolveBrandProfileAction::run(
        new BrandProfileData,
        $definition,
        new ThemeOverrideData(themeKey: $definition->key, values: ['plateCrop' => 'unsupported-choice']),
    );

    expect((new ThemeTokenRenderer)->css($invalid))->not->toContain('--theme-plate-crop');
});

it('consumes the agency identity options in the theme stylesheet', function (): void {
    $css = (string) file_get_contents(dirname(__DIR__, 2) . '/resources/css/theme-agency.css');

    foreach (['contained', 'panoramic', 'cinematic'] as $option) {
        $block = 'style(--theme-plate-crop: ' . $option . ')';

        if ($option === 'contained') {
            expect($css)->not->toContain($block);

            continue;
        }

        expect($css)->toContain($block);
    }
});
