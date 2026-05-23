<?php

declare(strict_types=1);

use Capell\FoundationTheme\View\Components\Actions;
use Capell\Frontend\Actions\Performance\RecordExtensionRenderContributionAction;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder;

test('default theme escapes site titles and plain footer text', function (): void {
    $themePath = dirname(__DIR__, 2);

    $header = file_get_contents($themePath . '/resources/views/components/header/index.blade.php');
    $footer = file_get_contents($themePath . '/resources/views/components/footer/index.blade.php');
    $relatedSites = file_get_contents($themePath . '/resources/views/components/footer/related-sites.blade.php');
    $siteInfo = file_get_contents($themePath . '/resources/views/components/footer/site-info.blade.php');

    expect($footer)->toContain('RenderHtmlContentAction::run(Lang::get($footerCopy');
    expect($header)->not->toContain('{!! $site->translation->title !!}');
    expect($siteInfo)->not->toContain('{!! $site->translation->title !!}');
    expect($relatedSites)->not->toContain('{!! $relatedSite->translation->title !!}');
    expect($relatedSites)->not->toContain('{!! $description !!}');
    expect($footer)->not->toContain('{!!' . PHP_EOL . '                Lang::get($footerCopy');
});

test('content component sanitizes cms html before rendering', function (): void {
    $themePath = dirname(__DIR__, 2);

    $content = file_get_contents($themePath . '/resources/views/components/content.blade.php');

    expect($content)->toContain('RenderHtmlContentAction::run($content, $pageVariables)')
        ->and($content)->not->toContain('{!! $content !!}')
        ->and($content)->not->toContain('{!! $page->translation->content !!}');
});

test('public image source component renders URL images without admin metadata', function (): void {
    $themePath = dirname(__DIR__, 2);
    $componentPath = dirname($themePath, 3) . '/capell-4/packages/frontend/resources/views/components/image-source.blade.php';
    $component = file_get_contents($componentPath);

    expect(is_file($componentPath))->toBeTrue()
        ->and($component)->toContain('ResolveImageSourceDataAction::run')
        ->and($component)->toContain('capell-image-source')
        ->and($component)->not->toContain('recordId')
        ->and($component)->not->toContain('fieldPath')
        ->and($component)->not->toContain('wire:');
});

test('default theme treats navigation as optional', function (): void {
    $themePath = dirname(__DIR__, 2);

    $header = file_get_contents($themePath . '/resources/views/components/header/index.blade.php');
    $footer = file_get_contents($themePath . '/resources/views/components/footer/index.blade.php');
    $footerComponent = file_get_contents($themePath . '/src/View/Components/Footer/Index.php');

    expect($header)->toContain("scenario: 'foundation-theme-primary-navigation'")
        ->and($header)->not->toContain('NavigationAvailability::check()')
        ->and($header)->not->toContain('if ($navigationAvailable)')
        ->and($footerComponent)->toContain('NavigationAvailability::check()')
        ->and($footerComponent)->toContain('$navigationAvailable')
        ->and($footer)->not->toContain('NavigationAvailability::check()');
});

test('public layout output does not include debug block comments', function (): void {
    $themePath = dirname(__DIR__, 2);

    $container = file_get_contents($themePath . '/resources/views/components/layout/container.blade.php');

    expect($container)
        ->not->toContain('<!-- {$block->key} Block')
        ->not->toContain("config('app.debug')");
});

test('public action buttons mark their csrf output as non-cacheable', function (): void {
    Route::post('/public-actions/{action}', static fn (): string => 'ok')
        ->name('capell-public-actions.submit');
    Route::getRoutes()->refreshNameLookups();

    resolve(RecordExtensionRenderContributionAction::class)->clear();

    (new Actions(actions: [
        [
            'type' => 'public_action',
            'public_action_key' => 'request-access',
            'label' => 'Request access',
        ],
    ]))->render();

    $contribution = collect(resolve(RecordExtensionRenderContributionAction::class)->recorded())
        ->first(fn (mixed $record): bool => $record?->contributionClass === Actions::class);

    expect($contribution?->cacheable)->toBeFalse()
        ->and($contribution?->sensitiveOutput)->toBeTrue();
});

test('public blade keeps data loading out of templates', function (): void {
    $themePath = dirname(__DIR__, 2);
    $violations = [];
    $forbiddenPatterns = [
        'DB::',
        '::query(',
        'loadMissing(',
        'relationLoaded(',
        'getMedia(',
    ];

    $files = (new Finder)
        ->files()
        ->in($themePath . '/resources/views')
        ->name('*.blade.php')
        ->notPath('components/filament')
        ->notPath('components/infolists');

    foreach ($files as $file) {
        $contents = $file->getContents();
        $relativePath = str_replace($themePath . '/', '', $file->getPathname());

        foreach ($forbiddenPatterns as $pattern) {
            if (str_contains($contents, $pattern)) {
                $violations[] = $relativePath . ' contains ' . $pattern;
            }
        }
    }

    expect($violations)->toBe(
        [],
        'Public Blade data-loading violations found:' . PHP_EOL .
        json_encode($violations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

test('reviewed public blade blocks do not read asset and page relations directly', function (): void {
    $themePath = dirname(__DIR__, 2);
    $files = [
        'resources/views/components/block/modern/hero-banner.blade.php',
        'resources/views/components/block/modern/image-gallery.blade.php',
        'resources/views/components/block/modern/card-grid.blade.php',
        'resources/views/components/block/asset/accordion.blade.php',
        'resources/views/components/block/asset/carousel.blade.php',
        'resources/views/components/block/asset/feature-item.blade.php',
        'resources/views/components/block/asset/media.blade.php',
    ];
    $forbiddenPatterns = [
        '$page?->assets',
        '$attachment->asset',
        '$heroItem->asset',
        '$asset->asset->media',
        '$asset->asset->translation',
        '$blockAsset->asset->translation',
        '$blockAsset->asset->getMeta(',
        '$linkedPage->pageUrl',
    ];
    $violations = [];

    foreach ($files as $file) {
        $contents = file_get_contents($themePath . '/' . $file);

        foreach ($forbiddenPatterns as $pattern) {
            if (str_contains($contents, $pattern)) {
                $violations[] = $file . ' contains ' . $pattern;
            }
        }
    }

    expect($violations)->toBe(
        [],
        'Reviewed public Blade relation access found:' . PHP_EOL .
        json_encode($violations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

test('ap hero and gallery public output avoid reviewed accessibility and editor copy regressions', function (): void {
    $themePath = dirname(__DIR__, 2);
    $hero = file_get_contents($themePath . '/resources/views/components/block/modern/hero-banner.blade.php');
    $gallery = file_get_contents($themePath . '/resources/views/components/block/modern/image-gallery.blade.php');
    $cardGrid = file_get_contents($themePath . '/resources/views/components/block/modern/card-grid.blade.php');
    $pageContent = file_get_contents($themePath . '/resources/views/components/block/page/content.blade.php');

    expect($hero)->toContain('MarkPrimaryHeadingRenderedAction::run()')
        ->and($pageContent)->toContain("\$headingTag = (\$hasPrimaryHeading ? 'h2' : 'h1');")
        ->and($hero)->not->toContain('ap-hero__slideshow-play')
        ->and($gallery)->not->toContain('No images configured')
        ->and($cardGrid)->not->toContain('No cards configured');
});

test('reviewed foundation chrome avoids accessibility regressions', function (): void {
    $themePath = dirname(__DIR__, 2);
    $footer = file_get_contents($themePath . '/resources/views/components/footer/index.blade.php');
    $socialLinks = file_get_contents($themePath . '/resources/views/components/footer/social-links.blade.php');
    $relatedSites = file_get_contents($themePath . '/resources/views/components/footer/related-sites.blade.php');
    $languages = file_get_contents($themePath . '/resources/views/components/languages.blade.php');

    expect($footer)->not->toContain('href="javascript:void(0)"')
        ->and($footer)->toContain('type="button"')
        ->and($footer)->toContain("__('capell-foundation-theme::generic.scroll_to_top')")
        ->and($socialLinks)->toContain('rel="nofollow noopener"')
        ->and($relatedSites)->not->toContain('role="menu"')
        ->and($relatedSites)->not->toContain('role="menuitem"')
        ->and($languages)->not->toContain('role="menuitem"')
        ->and($languages)->toContain('alt=""');
});
