<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Actions\PruneRenderProfilesAction;
use Capell\FrontendOptimizer\Enums\OptimizationStatus;
use Capell\FrontendOptimizer\Models\FrontendOptimizationRun;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Illuminate\Support\Facades\Storage;

it('reports stale render profiles without deleting rows or files during dry runs', function (): void {
    Storage::fake('local');
    $profile = frontendOptimizerPruneProfile('stale-dry-run', now()->subDays(45));
    Storage::disk('local')->put('capell/frontend-optimizer/manifests/stale-dry-run.json', '{}');
    Storage::disk('local')->put('capell/frontend-optimizer/critical-css/stale-dry-run.css', 'body{}');

    $result = PruneRenderProfilesAction::run(retentionDays: 30, dryRun: true);

    expect($result->matchedProfiles)->toBe(1)
        ->and($result->deletedProfiles)->toBe(0)
        ->and($result->deletedFiles)->toBe(0)
        ->and(FrontendRenderProfile::query()->whereKey($profile->getKey())->exists())->toBeTrue();

    Storage::disk('local')->assertExists('capell/frontend-optimizer/manifests/stale-dry-run.json');
    Storage::disk('local')->assertExists('capell/frontend-optimizer/critical-css/stale-dry-run.css');
});

it('deletes stale render profiles and generated files', function (): void {
    Storage::fake('local');
    $staleProfile = frontendOptimizerPruneProfile('stale-delete', now()->subDays(45));
    $freshProfile = frontendOptimizerPruneProfile('fresh-keep', now()->subDays(5));
    FrontendOptimizationRun::query()->create([
        'render_profile_id' => $staleProfile->getKey(),
        'status' => OptimizationStatus::Generated->value,
    ]);
    Storage::disk('local')->put('capell/frontend-optimizer/manifests/stale-delete.json', '{}');
    Storage::disk('local')->put('capell/frontend-optimizer/critical-css/stale-delete.css', 'body{}');
    Storage::disk('local')->put('capell/frontend-optimizer/manifests/fresh-keep.json', '{}');

    $result = PruneRenderProfilesAction::run(retentionDays: 30);

    expect($result->matchedProfiles)->toBe(1)
        ->and($result->deletedProfiles)->toBe(1)
        ->and($result->deletedFiles)->toBe(2)
        ->and(FrontendRenderProfile::query()->whereKey($staleProfile->getKey())->exists())->toBeFalse()
        ->and(FrontendRenderProfile::query()->whereKey($freshProfile->getKey())->exists())->toBeTrue()
        ->and(FrontendOptimizationRun::query()->where('render_profile_id', $staleProfile->getKey())->exists())->toBeFalse();

    Storage::disk('local')->assertMissing('capell/frontend-optimizer/manifests/stale-delete.json');
    Storage::disk('local')->assertMissing('capell/frontend-optimizer/critical-css/stale-delete.css');
    Storage::disk('local')->assertExists('capell/frontend-optimizer/manifests/fresh-keep.json');
});

it('limits stale render profile pruning', function (): void {
    frontendOptimizerPruneProfile('stale-limit-one', now()->subDays(45));
    frontendOptimizerPruneProfile('stale-limit-two', now()->subDays(45));

    $result = PruneRenderProfilesAction::run(retentionDays: 30, limit: 1);

    expect($result->matchedProfiles)->toBe(1)
        ->and($result->deletedProfiles)->toBe(1)
        ->and(FrontendRenderProfile::query()->count())->toBe(1);
});

function frontendOptimizerPruneProfile(string $hash, DateTimeInterface $updatedAt): FrontendRenderProfile
{
    $profile = FrontendRenderProfile::query()->create([
        'critical_css_path' => 'capell/frontend-optimizer/critical-css/' . $hash . '.css',
        'hash' => hash('sha256', $hash),
        'label' => $hash,
        'manifest' => ['path' => 'capell/frontend-optimizer/manifests/' . $hash . '.json'],
        'scope' => 'layout',
        'signature' => ['assets' => []],
        'status' => OptimizationStatus::Generated->value,
    ]);

    $profile->forceFill([
        'created_at' => $updatedAt,
        'updated_at' => $updatedAt,
    ])->save();

    return $profile;
}
