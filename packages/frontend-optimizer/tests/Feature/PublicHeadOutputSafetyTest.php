<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Frontend\Data\FrontendAssetManifestData;
use Capell\Frontend\Data\FrontendAssetRequirementData;
use Capell\Frontend\Data\FrontendContext;
use Capell\Frontend\Data\FrontendRuntimeManifestData;
use Capell\Frontend\Enums\RenderingStrategyEnum;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\CapellFrontendContext;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('keeps optimized public head output safe for anonymous and non-admin visitors', function (bool $signedIn): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('capell-frontend.asset_build_tool', 'public');
    config()->set('queue.default', 'database');

    if ($signedIn) {
        $this->actingAs(User::factory()->create());
    }

    $runtimeManifest = FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly);
    $assetManifest = frontendOptimizerPublicHeadAssetManifest($runtimeManifest);

    frontendOptimizerSetPublicHeadContext($runtimeManifest, $assetManifest);

    Blade::render('<x-capell::app.head :livewire-enabled="false" />');

    $profile = FrontendRenderProfile::query()->sole();
    $profile->forceFill([
        'critical_css_path' => 'capell/frontend-optimizer/critical-css/public-head.css',
    ])->save();

    Storage::disk('local')->put('capell/frontend-optimizer/critical-css/public-head.css', '.hero { display: grid; }');

    $html = Blade::render('<x-capell::app.head :livewire-enabled="false" />');

    expect($html)
        ->toContain('theme.css')
        ->toContain('requestIdleCallback')
        ->not->toContain($profile->hash)
        ->not->toContain('frontend_render_profiles')
        ->not->toContain('profile_hash')
        ->not->toContain('render_profile_id')
        ->not->toContain('frontend_optimizer')
        ->not->toContain('capell-frontend-optimizer')
        ->not->toContain('capell-app/foundation-theme')
        ->not->toContain('/admin')
        ->not->toContain('signed')
        ->not->toContain('editor')
        ->not->toContain('wire:snapshot')
        ->not->toContain('documentable_id')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
})->with([
    'anonymous visitor' => [false],
    'signed in non-admin visitor' => [true],
]);

function frontendOptimizerPublicHeadAssetManifest(FrontendRuntimeManifestData $runtimeManifest): FrontendAssetManifestData
{
    return new FrontendAssetManifestData(
        css: [
            new FrontendAssetRequirementData(
                handle: 'foundation-theme:css',
                kind: FrontendAssetRequirementData::KIND_CSS,
                source: 'theme.css',
            ),
        ],
        js: [
            new FrontendAssetRequirementData(
                handle: 'foundation-theme:runtime',
                kind: FrontendAssetRequirementData::KIND_JS,
                source: 'runtime.js',
            ),
        ],
        inline: [],
        preloads: [],
        runtime: $runtimeManifest,
    );
}

function frontendOptimizerSetPublicHeadContext(
    FrontendRuntimeManifestData $runtimeManifest,
    FrontendAssetManifestData $assetManifest,
): void {
    $language = Language::factory()->createOne();
    $theme = Theme::factory()->create(['key' => 'default']);
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Public Site'])
        ->create();
    $layout = Layout::factory()->site($site)->create(['key' => 'public-layout']);
    $page = Page::factory()
        ->site($site)
        ->layout($layout)
        ->withTranslations($language, ['title' => 'Public Page'])
        ->create();

    $site->load(['language', 'siteDomain', 'siteDomains', 'translation']);
    $page->load(['pageUrl', 'pageUrls.language', 'pageUrls.siteDomain', 'translation']);

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: $site,
        language: $language,
        page: $page,
        layout: $layout,
        theme: $theme,
        params: [
            'assetManifest' => $assetManifest,
            'runtimeManifest' => $runtimeManifest,
        ],
        slug: null,
    )));
    Frontend::clearResolvedInstance(CapellFrontendContext::class);
}
