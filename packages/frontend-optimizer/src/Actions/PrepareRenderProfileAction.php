<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Enums\OptimizationStatus;
use Capell\FrontendOptimizer\Jobs\GenerateCriticalCssJob;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Capell\FrontendOptimizer\Support\CriticalCssSettings;
use Capell\FrontendOptimizer\Support\FrontendAssetSet;
use Illuminate\Contracts\Filesystem\Factory;
use Lorisleiva\Actions\Concerns\AsAction;

class PrepareRenderProfileAction
{
    use AsAction;

    public function __construct(
        private readonly Factory $filesystems,
        private readonly CriticalCssSettings $criticalCssSettings,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, FrontendAssetSet>  $assetSets
     */
    public function handle(
        OptimizationScope $scope,
        array $context,
        array $assetSets,
        string $url,
        ?string $label = null,
    ): FrontendRenderProfile {
        $profileData = ResolveRenderProfileAction::run(
            scope: $scope,
            context: $context,
            assetSets: $assetSets,
            label: $label,
        );

        $profile = PersistRenderProfileAction::run($profileData);

        if ($this->shouldDispatchGeneration($profile) && $this->claimGeneration($profile)) {
            $manifestPath = StoreRenderProfileManifestAction::run($profileData);
            $profile->forceFill(['manifest' => ['path' => $manifestPath]])->save();

            dispatch(new GenerateCriticalCssJob((int) $profile->getKey(), $url));
        }

        return $profile;
    }

    private function shouldDispatchGeneration(FrontendRenderProfile $profile): bool
    {
        if (! $this->criticalCssSettings->automaticGenerationEnabled()) {
            return false;
        }

        if ($this->criticalCssSettings->profileDisablesCriticalCss($profile->signature)) {
            return false;
        }

        if (config('queue.default') === 'sync') {
            return false;
        }

        if (in_array($profile->status, [OptimizationStatus::Queued->value, OptimizationStatus::Running->value], true)) {
            return false;
        }

        if (! is_string($profile->critical_css_path) || $profile->critical_css_path === '') {
            return true;
        }

        if (! $this->filesystems->disk('local')->exists($profile->critical_css_path)) {
            return true;
        }

        return $profile->status === OptimizationStatus::Failed->value;
    }

    private function claimGeneration(FrontendRenderProfile $profile): bool
    {
        $claimed = FrontendRenderProfile::query()
            ->whereKey($profile->getKey())
            ->whereNotIn('status', [
                OptimizationStatus::Queued->value,
                OptimizationStatus::Running->value,
            ])
            ->update([
                'status' => OptimizationStatus::Queued->value,
                'updated_at' => now(),
            ]) === 1;

        if ($claimed) {
            $profile->forceFill(['status' => OptimizationStatus::Queued->value]);
        }

        return $claimed;
    }
}
