<?php

declare(strict_types=1);

it('owns the opinionated public body behavior', function (): void {
    $body = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/app/body.blade.php');

    expect($body)->toContain('showLightbox');
});

it('owns the opinionated content prose and divider behavior', function (): void {
    $content = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/content.blade.php');

    expect($content)->toContain('data-lightbox');
});

it('owns the foundation frontend javascript runtime', function (): void {
    $entrypoint = file_get_contents(dirname(__DIR__, 2) . '/resources/js/capell-frontend.js');
    $config = file_get_contents(dirname(__DIR__, 2) . '/config/capell-foundation-theme.php');

    expect($entrypoint)->toContain('@ryangjchandler/alpine-tooltip')
        ->and($entrypoint)->toContain('@awcodes/alpine-floating-ui')
        ->and($entrypoint)->toContain('./utilities/lightbox')
        ->and($config)->toContain('@ryangjchandler/alpine-tooltip')
        ->and($config)->toContain('@awcodes/alpine-floating-ui');
});

it('bundles layout builder javascript into the foundation frontend runtime', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');
    $entrypoint = file_get_contents(dirname(__DIR__, 2) . '/resources/js/capell-frontend.js');

    expect($entrypoint)->toContain('./blocks/block/carousel')
        ->and($provider)->toContain("path: 'vendor/capell-foundation-theme'")
        ->and($provider)->toContain('foundation-theme-runtime')
        ->and($provider)->toContain('VendorAssetConditionRegistry')
        ->and($provider)->not->toContain('LAYOUT_BUILDER_ASSETS_CONDITION');
});

it('publishes the foundation frontend runtime build during setup', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');
    $command = file_get_contents(dirname(__DIR__, 2) . '/src/Console/Commands/SetupCommand.php');

    expect($provider)->toContain('capell-foundation-theme-assets')
        ->and(file_exists(dirname(__DIR__, 2) . '/publishes/build/manifest.json'))->toBeTrue()
        ->and(file_exists(dirname(__DIR__, 2) . '/publishes/build/assets/capell-frontend-Bpa81WpI.js'))->toBeTrue()
        ->and($command)->toContain('vendor:publish')
        ->and($command)->toContain('capell-foundation-theme-assets');
});

it('owns the default body content and layout component files', function (): void {
    $layout = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/layout/index.blade.php');

    expect(file_exists(dirname(__DIR__, 2) . '/resources/views/components/app/body.blade.php'))->toBeTrue()
        ->and(file_exists(dirname(__DIR__, 2) . '/resources/views/components/content.blade.php'))->toBeTrue()
        ->and(file_exists(dirname(__DIR__, 2) . '/resources/views/components/layout/index.blade.php'))->toBeTrue()
        ->and($layout)->toContain('<x-capell::header.index />')
        ->and($layout)->toContain("\$theme['meta']['footer_file'] ?? 'capell::footer'");
});

it('keeps runtime asset registrations behind the installed package guard', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');

    $guardPosition = strpos($provider, 'if (! $this->isPackageInstalled())');
    $assetRegistrationPosition = strpos($provider, '$this->registerVendorCssJsAssets();');

    expect($guardPosition)->not->toBeFalse()
        ->and($assetRegistrationPosition)->not->toBeFalse()
        ->and($guardPosition)->toBeLessThan($assetRegistrationPosition);
});

it('registers foundation chrome components for admin selection', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');

    expect($provider)->toContain("registerHeader('capell::header.index'")
        ->and($provider)->toContain("registerFooter('capell::footer'");
});

it('does not rebuild tailwind assets for runtime theme color changes', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');
    $command = file_get_contents(dirname(__DIR__, 2) . '/src/Console/Commands/GenerateTailwindAssetsCommand.php');
    $generator = file_get_contents(dirname(__DIR__, 2) . '/src/Support/Tailwind/TailwindAssetsGenerator.php');
    $tokens = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/app/head/tokens.blade.php');

    expect($provider)->not->toContain('ThemeColorsUpdated')
        ->and($command)->not->toContain('--theme-key')
        ->and($generator)->toContain('DefaultColorEnum::getKeyValues()')
        ->and($tokens)->toContain('->merge($theme->colors)');
});

it('delegates primary header navigation to the navigation render hook', function (): void {
    $header = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/header/index.blade.php');
    $layoutArea = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/layout/area.blade.php');
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');

    expect($header)->toContain("scenario: 'foundation-theme-primary-navigation'")
        ->and($header)->toContain("target: 'capell::header.index'")
        ->and($header)->toContain('<x-capell::layout.area area="header" />')
        ->and($layoutArea)->toContain('capell-layout-builder::components.layout.area')
        ->and($provider)->toContain("->register('header'")
        ->and($header)->toContain('capell-navigation-menu-open-changed')
        ->and($header)->toContain('capell-product-header')
        ->and($header)->toContain('capell-product-nav-item')
        ->and($header)->not->toContain('x-ref="toggleMenu"')
        ->and($header)->not->toContain('toggleMenu()')
        ->and($header)->not->toContain('Capell\\Navigation');
});

it('delegates main layout container rendering to the shared frontend hook', function (): void {
    $main = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/layout/main.blade.php');

    expect($main)->toContain('RenderHookLocation::MainContent')
        ->and($main)->toContain("scenario: 'frontend-main-layout'")
        ->and($main)->toContain("target: 'capell::layout.main'")
        ->and($main)->toContain('$mainContentHookOutput !==')
        ->and($main)->toContain('<x-capell::content')
        ->and($main)->not->toContain('Capell\\LayoutBuilder')
        ->and($main)->not->toContain('LayoutBlockData')
        ->and($main)->not->toContain('CapellLayoutManager')
        ->and($main)->not->toContain('x-capell::layout.container');
});

it('owns the product showcase styling for modern homepage blocks', function (): void {
    $hero = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/hero-banner.blade.php');
    $cardGrid = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/card-grid.blade.php');
    $featureList = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/feature-list.blade.php');
    $cta = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/cta-section.blade.php');
    $gallery = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/image-gallery.blade.php');

    expect($hero)->toContain('hero_panel_title')
        ->and($hero)->toContain('hero_empty_title')
        ->and($cardGrid)->toContain('ap-card__link')
        ->and($featureList)->toContain('ap-feature-item__icon')
        ->and($cta)->toContain('Homepage content is block, media, and layout driven.')
        ->and($gallery)->toContain('ap-gallery-caption');
});

it('does not own premium demo kit homepage section styling', function (): void {
    $themeCss = file_get_contents(dirname(__DIR__, 2) . '/resources/css/theme/theme.css');

    expect($themeCss)->not->toContain('capell-block-homepage-section');
});
