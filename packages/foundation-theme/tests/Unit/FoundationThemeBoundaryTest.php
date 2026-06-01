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

it('moves modern block interactions out of blade and into the frontend runtime', function (): void {
    $entrypoint = file_get_contents(dirname(__DIR__, 2) . '/resources/js/capell-frontend.js');
    $faq = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/faq-section.blade.php');
    $pricing = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/pricing-table.blade.php');
    $testimonials = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/modern/testimonials.blade.php');
    $blocksCss = file_get_contents(dirname(__DIR__, 2) . '/resources/css/blocks/foundation-blocks.css');

    expect($entrypoint)
        ->toContain('data-faq-category-tab')
        ->toContain('data-billing-toggle')
        ->toContain('data-carousel-direction')
        ->toContain('data-carousel-slide')
        ->toContain('data-theme-pathways')
        ->toContain('data-pathway-panel')
        ->toContain('data-theme-spotlight')
        ->toContain('data-spotlight-tab')
        ->and($blocksCss)->toContain('@keyframes faqFadeIn')
        ->and($faq)->not->toContain('<style')
        ->and($faq)->not->toContain('<script')
        ->and($faq)->not->toContain('onclick=')
        ->and($pricing)->not->toContain('<script')
        ->and($pricing)->not->toContain('onclick=')
        ->and($testimonials)->not->toContain('<script')
        ->and($testimonials)->not->toContain('onclick=');
});

it('keeps public asset block blade on preloaded relations', function (): void {
    $viewDirectory = dirname(__DIR__, 2) . '/resources/views/components/block';
    $viewFiles = [
        $viewDirectory . '/asset/pages.blade.php',
        $viewDirectory . '/modern/faq-section.blade.php',
        $viewDirectory . '/modern/feature-list.blade.php',
        $viewDirectory . '/modern/pricing-table.blade.php',
        $viewDirectory . '/modern/process-steps.blade.php',
        $viewDirectory . '/modern/stats-section.blade.php',
        $viewDirectory . '/modern/testimonials.blade.php',
    ];

    foreach ($viewFiles as $viewFile) {
        $view = file_get_contents($viewFile);

        throw_unless(is_string($view), RuntimeException::class, sprintf('Expected %s to be readable.', $viewFile));

        expect($view)
            ->not->toContain('loadParent(')
            ->not->toContain('$blockAsset->asset->translation')
            ->not->toContain('$blockAsset->asset->getMeta')
            ->not->toContain('$asset->translation?->');
    }
});

it('publishes the foundation frontend runtime build during setup', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');
    $action = file_get_contents(dirname(__DIR__, 2) . '/src/Actions/SetupFoundationThemePackageAction.php');

    expect($provider)->toContain('capell-foundation-theme-assets')
        ->and(file_exists(dirname(__DIR__, 2) . '/publishes/build/manifest.json'))->toBeTrue()
        ->and(file_exists(dirname(__DIR__, 2) . '/publishes/build/assets/capell-frontend-Bpa81WpI.js'))->toBeTrue()
        ->and($action)->toContain('vendor:publish')
        ->and($action)->toContain('capell-foundation-theme-assets');
});

it('owns the default body content and layout component files', function (): void {
    $layout = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/layout/index.blade.php');

    throw_unless(is_string($layout), RuntimeException::class, 'Expected foundation layout Blade file to be readable.');

    expect(file_exists(dirname(__DIR__, 2) . '/resources/views/components/app/body.blade.php'))->toBeTrue()
        ->and(file_exists(dirname(__DIR__, 2) . '/resources/views/components/content.blade.php'))->toBeTrue()
        ->and(file_exists(dirname(__DIR__, 2) . '/resources/views/components/layout/index.blade.php'))->toBeTrue()
        ->and($layout)->toContain('<x-capell::header.index />')
        ->and($layout)->toContain("\$theme['meta']['footer_file'] ?? 'capell::footer'");
});

it('keeps runtime asset registrations behind the installed package guard', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');

    throw_unless(is_string($provider), RuntimeException::class, 'Expected foundation theme service provider to be readable.');

    $guardPosition = strpos($provider, 'if (! $this->isPackageInstalled())');
    $assetRegistrationPosition = strpos($provider, '$this->registerVendorCssJsAssets();');

    throw_if(! is_int($guardPosition) || ! is_int($assetRegistrationPosition), RuntimeException::class, 'Expected foundation theme asset guard and registration calls to be present.');

    expect($guardPosition)->not->toBeFalse()
        ->and($assetRegistrationPosition)->not->toBeFalse()
        ->and($guardPosition)->toBeLessThan($assetRegistrationPosition);
});

it('registers foundation chrome components for admin selection', function (): void {
    $provider = file_get_contents(dirname(__DIR__, 2) . '/src/Providers/FoundationThemeServiceProvider.php');

    throw_unless(is_string($provider), RuntimeException::class, 'Expected foundation theme service provider to be readable.');

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
        ->and($tokens)->toContain('->merge($theme instanceof Theme && is_array($theme->colors) ? $theme->colors : [])');
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

it('delegates shared interactive listing variants from child themes to foundation', function (): void {
    $basePath = dirname(__DIR__, 3);

    $views = [
        $basePath . '/theme-agency/resources/views/sections/content-listing.blade.php',
        $basePath . '/theme-corporate/resources/views/sections/content-listing.blade.php',
        $basePath . '/theme-commerce/resources/views/sections/collections.blade.php',
        $basePath . '/theme-healthcare/resources/views/sections/blog-teaser.blade.php',
        $basePath . '/theme-saas/resources/views/sections/content-listing.blade.php',
    ];

    foreach ($views as $view) {
        expect(file_get_contents($view))
            ->toContain("'gallery', 'pathways', 'spotlight'")
            ->toContain('capell-foundation-theme::theme.sections.content-listing');
    }
});
