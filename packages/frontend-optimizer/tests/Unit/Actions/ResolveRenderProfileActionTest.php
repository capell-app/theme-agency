<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Actions\ResolveRenderProfileAction;
use Capell\FrontendOptimizer\Data\FrontendResourceHintData;
use Capell\FrontendOptimizer\Enums\AssetLoadingStrategy;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Support\FrontendAssetSet;

it('creates a deterministic render profile hash from normalized context and assets', function (): void {
    $first = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: [
            'theme' => ['updated_at' => '2026-05-07', 'key' => 'default'],
            'layout' => 'landing',
        ],
        assetSets: [
            FrontendAssetSet::make()->css('hero', 'hero.css', AssetLoadingStrategy::Preload),
        ],
        label: 'Landing',
    );

    $second = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: [
            'layout' => 'landing',
            'theme' => ['key' => 'default', 'updated_at' => '2026-05-07'],
        ],
        assetSets: [
            FrontendAssetSet::make()->css('hero', 'hero.css', AssetLoadingStrategy::Preload),
        ],
        label: 'Landing',
    );

    expect($first->hash)->toBe($second->hash)
        ->and($first->manifest()['scope'])->toBe('layout')
        ->and($first->manifest()['assets'])->toHaveCount(1);
});

it('changes the render profile hash when widget assets change', function (): void {
    $first = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', 'hero.css')],
    );

    $second = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('carousel', 'carousel.css')],
    );

    expect($first->hash)->not()->toBe($second->hash);
});

it('changes the render profile hash when critical css fold settings change', function (): void {
    config()->set('capell-frontend-optimizer.enabled', true);

    $first = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', 'hero.css')],
    );

    config()->set('capell-frontend-optimizer.enabled', false);

    $second = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', 'hero.css')],
    );

    expect($first->hash)->not()->toBe($second->hash)
        ->and($first->signature['critical_css'])->toHaveKey('viewports');
});

it('includes resource hints in the render profile signature', function (): void {
    $first = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', 'hero.css')],
        resourceHints: [
            new FrontendResourceHintData(
                rel: 'preload',
                href: '/fonts/inter.woff2',
                as: 'font',
                type: 'font/woff2',
                crossorigin: 'anonymous',
            ),
        ],
    );

    $second = ResolveRenderProfileAction::run(
        scope: OptimizationScope::Layout,
        context: ['layout' => 'landing'],
        assetSets: [FrontendAssetSet::make()->css('hero', 'hero.css')],
    );

    expect($first->hash)->not()->toBe($second->hash)
        ->and($first->signature['resource_hints'])->toBe([[
            'as' => 'font',
            'crossorigin' => 'anonymous',
            'href' => '/fonts/inter.woff2',
            'rel' => 'preload',
            'type' => 'font/woff2',
        ]])
        ->and($first->manifest()['resource_hints'])->toHaveCount(1);
});
