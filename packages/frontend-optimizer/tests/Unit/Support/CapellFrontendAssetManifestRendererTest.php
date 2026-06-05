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
    $runtimeAsset = collect($assets)->firstWhere('handle', 'foundation-theme:runtime');
    $firstAsset = $assets[0];
    $signature = $profile->signature;
    throw_unless(is_array($signature), RuntimeException::class, 'Expected render profile signature.');
    $signatureContext = $signature['context'] ?? null;
    throw_unless(is_array($signatureContext), RuntimeException::class, 'Expected render profile context signature.');
    $signaturePage = $signatureContext['page'] ?? null;
    throw_unless(is_array($signaturePage), RuntimeException::class, 'Expected render profile page signature.');

    expect($firstAsset)->toHaveKeys(['critical_eligible', 'loading_strategy', 'slot']);
    expect($runtimeAsset)->not->toBeNull();

    assert(isset($firstAsset['critical_eligible'], $firstAsset['loading_strategy'], $firstAsset['slot']));
    assert(is_array($runtimeAsset) && isset($runtimeAsset['loading_strategy']));

    expect($html)
        ->toContain('<link rel="stylesheet" href="http://localhost/build/resources/css/capell/frontend.css">')
        ->toContain('<link rel="stylesheet" href="http://localhost/vendor/capell/themes/static.css">')
        ->toContain('requestIdleCallback')
        ->toContain('capell-foundation-theme')
        ->toContain('capell-frontend.js')
        ->not->toContain('capell-app/foundation-theme')
        ->and($profile->scope)->toBe('layout')
        ->and($profile->label)->toBe($context->layout?->key . ' / ' . $context->theme?->key)
        ->and($signaturePage['id'])->toBe($context->page?->getKey())
        ->and($signaturePage['type'])->toBe($context->page?->getMorphClass())
        ->and($firstAsset['handle'])->toBe('foundation-theme:css')
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
                handle: 'foundation-theme:css',
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
                handle: 'foundation-theme:runtime',
                kind: FrontendAssetRequirementData::KIND_JS,
                source: 'resources/js/capell-frontend.js',
                buildPath: 'vendor/capell-foundation-theme',
            ),
        ],
        inline: [],
        preloads: [],
        runtime: FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly),
    );
}
