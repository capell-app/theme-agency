<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Frontend\Data\FrontendAssetContextData;
use Capell\Frontend\Data\FrontendAssetManifestData;
use Capell\Frontend\Data\FrontendAssetRequirementData;
use Capell\Frontend\Data\FrontendRuntimeManifestData;
use Capell\Frontend\Enums\RenderingStrategyEnum;
use Capell\FrontendOptimizer\Jobs\GenerateCriticalCssJob;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Capell\FrontendOptimizer\Support\CapellFrontendAssetManifestRenderer;
use Capell\FrontendOptimizer\Tests\Fixtures\HintedFrontendAssetRequirementData;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('converts a Capell manifest into a layout scoped optimizer profile', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('capell-frontend.asset_build_tool', 'public');
    config()->set('queue.default', 'database');

    $context = optimizerRendererContext();
    $manifest = optimizerRendererManifest();

    $html = resolve(CapellFrontendAssetManifestRenderer::class)->render($manifest, $context)->toHtml();

    $profile = FrontendRenderProfile::query()->sole();
    /** @var list<array{handle: string, critical_eligible?: bool, loading_strategy?: string, slot?: string}> $assets */
    $assets = $profile->signature['assets'];
    $runtimeAsset = collect($assets)->firstWhere('handle', 'theme-foundation:runtime');
    $firstAsset = $assets[0];
    $signature = $profile->signature;
    throw_unless(is_array($signature), RuntimeException::class, 'Expected render profile signature.');
    $signatureContext = $signature['context'] ?? null;
    throw_unless(is_array($signatureContext), RuntimeException::class, 'Expected render profile context signature.');
    $signatureLayout = $signatureContext['layout'] ?? null;
    $signatureTheme = $signatureContext['theme'] ?? null;
    throw_unless(is_array($signatureLayout), RuntimeException::class, 'Expected render profile layout signature.');
    throw_unless(is_array($signatureTheme), RuntimeException::class, 'Expected render profile theme signature.');

    expect($firstAsset)->toHaveKeys(['critical_eligible', 'loading_strategy', 'slot']);
    expect($runtimeAsset)->not->toBeNull();

    assert(isset($firstAsset['critical_eligible'], $firstAsset['loading_strategy'], $firstAsset['slot']));
    assert(is_array($runtimeAsset) && isset($runtimeAsset['loading_strategy']));

    expect($html)
        ->toContain('<link rel="stylesheet" href="http://localhost/build/resources/css/capell/frontend.css">')
        ->toContain('<link rel="stylesheet" href="http://localhost/vendor/capell/themes/static.css">')
        ->toContain('requestIdleCallback')
        ->toContain('capell-theme-foundation')
        ->toContain('capell-frontend.js')
        ->not->toContain('capell-app/theme-foundation')
        ->and($profile->scope)->toBe('layout')
        ->and($profile->label)->toBe($context->layout?->key . ' / ' . $context->theme?->key)
        ->and($signatureContext)->not->toHaveKey('page')
        ->and($signatureLayout['id'])->toBe($context->layout?->getKey())
        ->and($signatureTheme['id'])->toBe($context->theme?->getKey())
        ->and($firstAsset['handle'])->toBe('theme-foundation:css')
        ->and($firstAsset['critical_eligible'])->toBeTrue()
        ->and($firstAsset['loading_strategy'])->toBe('deferred')
        ->and($firstAsset['slot'])->toBe('above_fold')
        ->and($runtimeAsset['loading_strategy'])->toBe('idle');

    Bus::assertDispatched(GenerateCriticalCssJob::class);
});

it('inlines generated critical css and defers the full Foundation stylesheet', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('capell-frontend.asset_build_tool', 'public');

    $context = optimizerRendererContext();
    $manifest = optimizerRendererManifest();

    resolve(CapellFrontendAssetManifestRenderer::class)->render($manifest, $context);

    $profile = FrontendRenderProfile::query()->sole();
    $profile->forceFill([
        'critical_css_path' => 'capell/frontend-optimizer/critical-css/foundation.css',
    ])->save();

    Storage::disk('local')->put('capell/frontend-optimizer/critical-css/foundation.css', '.hero { display: grid; }');

    $html = resolve(CapellFrontendAssetManifestRenderer::class)->render($manifest, $context)->toHtml();

    expect($html)
        ->toContain('<style data-critical-css>body { margin: 0; }' . PHP_EOL . '.hero { display: grid; }</style>')
        ->toContain('<link rel="stylesheet" href="http://localhost/build/resources/css/capell/frontend.css" media="print" onload="this.media=\'all\'">')
        ->toContain('<noscript><link rel="stylesheet" href="http://localhost/build/resources/css/capell/frontend.css"></noscript>')
        ->not->toContain('editor')
        ->not->toContain('signed');
});

it('uses manifest optimizer hints for critical eligibility and javascript loading', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('capell-frontend.asset_build_tool', 'public');
    config()->set('queue.default', 'database');

    $context = optimizerRendererContext();
    $manifest = new FrontendAssetManifestData(
        css: [
            new HintedFrontendAssetRequirementData(
                handle: 'theme-liquid-glass:css',
                kind: FrontendAssetRequirementData::KIND_CSS,
                source: 'vendor/theme-liquid-glass/frontend.css',
                criticalEligible: true,
                frontendOptimizerLoadingStrategy: 'deferred',
                packageName: 'capell-app/theme-liquid-glass',
            ),
        ],
        js: [
            new HintedFrontendAssetRequirementData(
                handle: 'theme-liquid-glass:runtime',
                kind: FrontendAssetRequirementData::KIND_JS,
                source: 'vendor/theme-liquid-glass/runtime.js',
                frontendOptimizerLoadingStrategy: 'idle',
                packageName: 'capell-app/theme-liquid-glass',
            ),
        ],
        inline: [],
        preloads: [],
        runtime: FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly),
    );

    $html = resolve(CapellFrontendAssetManifestRenderer::class)->render($manifest, $context)->toHtml();

    $profile = FrontendRenderProfile::query()->sole();
    /** @var list<array{handle: string, critical_eligible?: bool, loading_strategy?: string, package_name?: string}> $assets */
    $assets = $profile->signature['assets'];
    $stylesheet = collect($assets)->firstWhere('handle', 'theme-liquid-glass:css');
    $runtime = collect($assets)->firstWhere('handle', 'theme-liquid-glass:runtime');

    expect($stylesheet)
        ->toBeArray()
        ->and($stylesheet['critical_eligible'] ?? null)->toBeTrue()
        ->and($stylesheet['loading_strategy'] ?? null)->toBe('deferred')
        ->and($stylesheet['package_name'] ?? null)->toBe('capell-app/theme-liquid-glass')
        ->and($runtime)->toBeArray()
        ->and($runtime['loading_strategy'] ?? null)->toBe('idle')
        ->and($runtime['package_name'] ?? null)->toBe('capell-app/theme-liquid-glass')
        ->and($html)->toContain('requestIdleCallback');

    Bus::assertDispatched(GenerateCriticalCssJob::class);
});

it('renders manifest preload resource hints through the optimizer profile', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('capell-frontend.asset_build_tool', 'public');
    config()->set('queue.default', 'database');

    $context = optimizerRendererContext();
    $manifest = new FrontendAssetManifestData(
        css: [
            new FrontendAssetRequirementData(
                handle: 'theme-foundation:css',
                kind: FrontendAssetRequirementData::KIND_CSS,
                source: 'resources/css/capell/frontend.css',
                buildPath: 'build',
            ),
        ],
        js: [],
        inline: [],
        preloads: [
            new HintedFrontendAssetRequirementData(
                handle: 'font:inter',
                kind: FrontendAssetRequirementData::KIND_PRELOAD,
                source: 'vendor/fonts/inter.woff2',
                resourceAs: 'font',
                resourceType: 'font/woff2',
                crossorigin: 'anonymous',
            ),
            new HintedFrontendAssetRequirementData(
                handle: 'hero:image',
                kind: FrontendAssetRequirementData::KIND_PRELOAD,
                source: 'images/hero.webp',
                fetchpriority: 'high',
            ),
        ],
        runtime: FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly),
    );

    $html = resolve(CapellFrontendAssetManifestRenderer::class)->render($manifest, $context)->toHtml();
    $profile = FrontendRenderProfile::query()->sole();

    expect($profile->signature['resource_hints'])->toBe([
        [
            'as' => 'font',
            'crossorigin' => 'anonymous',
            'href' => 'http://localhost/vendor/fonts/inter.woff2',
            'rel' => 'preload',
            'type' => 'font/woff2',
        ],
        [
            'as' => 'image',
            'fetchpriority' => 'high',
            'href' => 'http://localhost/images/hero.webp',
            'rel' => 'preload',
        ],
    ])
        ->and($html)->toContain('<link rel="preload" href="http://localhost/vendor/fonts/inter.woff2" as="font" type="font/woff2" crossorigin="anonymous">')
        ->and($html)->toContain('<link rel="preload" href="http://localhost/images/hero.webp" as="image" fetchpriority="high">');
});

it('reuses the profile hash for equivalent layout and theme asset graphs', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('capell-frontend.asset_build_tool', 'public');

    $context = optimizerRendererContext();
    $manifest = optimizerRendererManifest();
    $renderer = resolve(CapellFrontendAssetManifestRenderer::class);

    $renderer->render($manifest, $context);

    $firstHash = FrontendRenderProfile::query()->sole()->hash;

    $renderer->render($manifest, $context);
    $secondHash = FrontendRenderProfile::query()->sole()->hash;

    expect($secondHash)->toBe($firstHash)
        ->and(FrontendRenderProfile::query()->count())->toBe(1);
});

function optimizerRendererContext(): FrontendAssetContextData
{
    $language = Language::factory()->create();
    $theme = Theme::factory()->create(['key' => 'default']);
    $site = Site::factory()->language($language)->theme($theme)->create();
    $layout = Layout::factory()->site($site)->create(['key' => 'standard']);
    $page = Page::factory()->site($site)->layout($layout)->create();

    return new FrontendAssetContextData(
        page: $page,
        site: $site,
        language: $language,
        layout: $layout,
        theme: $theme,
        runtime: FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly),
    );
}

function optimizerRendererManifest(): FrontendAssetManifestData
{
    return new FrontendAssetManifestData(
        css: [
            new FrontendAssetRequirementData(
                handle: 'theme-foundation:css',
                kind: FrontendAssetRequirementData::KIND_CSS,
                source: 'resources/css/capell/frontend.css',
                buildPath: 'build',
            ),
            new FrontendAssetRequirementData(
                handle: 'theme-meta:static',
                kind: FrontendAssetRequirementData::KIND_CSS,
                source: 'vendor/capell/themes/static.css',
            ),
        ],
        js: [
            new FrontendAssetRequirementData(
                handle: 'theme-foundation:runtime',
                kind: FrontendAssetRequirementData::KIND_JS,
                source: 'resources/js/capell-frontend.js',
                buildPath: 'vendor/capell-theme-foundation',
            ),
        ],
        inline: [],
        preloads: [],
        runtime: FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly),
    );
}
