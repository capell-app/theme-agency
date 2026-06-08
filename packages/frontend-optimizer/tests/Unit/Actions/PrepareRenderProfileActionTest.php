<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Actions\PersistRenderProfileAction;
use Capell\FrontendOptimizer\Actions\PrepareRenderProfileAction;
use Capell\FrontendOptimizer\Actions\ResolveRenderProfileAction;
use Capell\FrontendOptimizer\Enums\AssetLoadingStrategy;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Enums\OptimizationStatus;
use Capell\FrontendOptimizer\Jobs\GenerateCriticalCssJob;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Capell\FrontendOptimizer\Support\FrontendAssetSet;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

function frontendOptimizerManifestPath(FrontendRenderProfile $profile): string
{
    $manifest = $profile->manifest;
    $path = is_array($manifest) ? ($manifest['path'] ?? null) : null;

    throw_unless(is_string($path) && $path !== '', RuntimeException::class, 'Expected frontend optimizer profile manifest path.');

    return $path;
}

it('prepares a render profile and dispatches critical css generation when missing', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('queue.default', 'database');

    $profile = PrepareRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [
            FrontendAssetSet::make()
                ->css('hero', '/build/hero.css', AssetLoadingStrategy::Critical, criticalEligible: true),
        ],
        url: 'https://example.test/landing',
        label: 'Landing',
    );

    Storage::disk('local')->assertExists(frontendOptimizerManifestPath($profile));
    Bus::assertDispatched(
        GenerateCriticalCssJob::class,
        fn (GenerateCriticalCssJob $job): bool => $job->renderProfileId === $profile->id
            && $job->url === 'https://example.test/landing',
    );
    expect($profile->status)->toBe(OptimizationStatus::Queued->value);
});

it('does not dispatch duplicate critical css jobs while a profile is already queued', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('queue.default', 'database');

    $arguments = [
        'scope' => OptimizationScope::Layout,
        'context' => ['layout' => 'landing'],
        'assetSets' => [
            FrontendAssetSet::make()
                ->css('hero', '/build/hero.css', AssetLoadingStrategy::Critical, criticalEligible: true),
        ],
        'url' => 'https://example.test/landing',
        'label' => 'Landing',
    ];

    $profile = PrepareRenderProfileAction::run(...$arguments);
    $manifestPath = frontendOptimizerManifestPath($profile);
    Storage::disk('local')->delete($manifestPath);

    PrepareRenderProfileAction::run(...$arguments);

    Bus::assertDispatchedTimes(GenerateCriticalCssJob::class, 1);
    Storage::disk('local')->assertMissing($manifestPath);
});

it('does not dispatch generation from public rendering when the queue is synchronous', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('queue.default', 'sync');

    $profile = PrepareRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [
            FrontendAssetSet::make()
                ->css('hero', '/build/hero.css', AssetLoadingStrategy::Critical, criticalEligible: true),
        ],
        url: 'https://example.test/landing',
        label: 'Landing',
    );

    expect($profile->manifest)->toBeNull();
    Storage::disk('local')->assertMissing('capell/frontend-optimizer/manifests/' . $profile->hash . '.json');
    Bus::assertNotDispatched(GenerateCriticalCssJob::class);
});

it('does not dispatch generation when the optimizer is disabled', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('capell-frontend-optimizer.enabled', false);

    PrepareRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', '/build/hero.css', AssetLoadingStrategy::Critical)],
        url: 'https://example.test/landing',
    );

    Bus::assertNotDispatched(GenerateCriticalCssJob::class);
});

it('does not dispatch generation when the page type disables critical css', function (): void {
    Storage::fake('local');
    Bus::fake();

    PrepareRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: [
            'layout' => 'landing',
            'page_type_meta' => [
                'frontend_optimizer' => ['disable_critical_css' => true],
            ],
        ],
        assetSets: [FrontendAssetSet::make()->css('hero', '/build/hero.css', AssetLoadingStrategy::Critical)],
        url: 'https://example.test/landing',
    );

    Bus::assertNotDispatched(GenerateCriticalCssJob::class);
});

it('dispatches generation again when the stored critical css file is missing', function (): void {
    Storage::fake('local');
    Bus::fake();
    config()->set('queue.default', 'database');

    $profileData = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', '/build/hero.css', AssetLoadingStrategy::Critical)],
    );
    $profile = PersistRenderProfileAction::run($profileData);
    $profile->forceFill([
        'critical_css_path' => 'capell/frontend-optimizer/critical-css/missing.css',
        'status' => OptimizationStatus::Generated->value,
    ])->save();

    PrepareRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', '/build/hero.css', AssetLoadingStrategy::Critical)],
        url: 'https://example.test/landing',
    );

    Bus::assertDispatched(GenerateCriticalCssJob::class);
});
