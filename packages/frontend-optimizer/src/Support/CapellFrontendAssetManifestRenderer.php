<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Support;

use Capell\Frontend\Contracts\FrontendAssetManifestRenderer;
use Capell\Frontend\Data\FrontendAssetContextData;
use Capell\Frontend\Data\FrontendAssetManifestData;
use Capell\Frontend\Data\FrontendAssetRequirementData;
use Capell\Frontend\Support\Assets\DefaultFrontendAssetManifestRenderer;
use Capell\FrontendOptimizer\Actions\PrepareRenderProfileAction;
use Capell\FrontendOptimizer\Actions\RenderProfileAssetsAction;
use Capell\FrontendOptimizer\Actions\ResolveOptimizationScopeAction;
use Capell\FrontendOptimizer\Enums\AssetLoadingStrategy;
use Capell\FrontendOptimizer\Enums\AssetSlot;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Arr;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

final class CapellFrontendAssetManifestRenderer implements FrontendAssetManifestRenderer
{
    public function __construct(
        private readonly DefaultFrontendAssetManifestRenderer $fallbackRenderer,
        private readonly CriticalCssSettings $criticalCssSettings,
        private readonly UrlGenerator $url,
        private readonly Vite $vite,
    ) {}

    public function render(FrontendAssetManifestData $manifest, ?FrontendAssetContextData $context = null): HtmlString
    {
        if (! $context instanceof FrontendAssetContextData) {
            return $this->fallbackRenderer->render($manifest, $context);
        }

        try {
            $scope = ResolveOptimizationScopeAction::run(siteScope: $this->criticalCssSettings->scope());
            $url = $this->url->current();

            $profile = PrepareRenderProfileAction::run(
                scope: $scope,
                context: $this->profileContext($context, $scope, $url),
                assetSets: [$this->assetSet($manifest)],
                url: $url,
                label: $this->profileLabel($context),
            );

            return RenderProfileAssetsAction::run($profile->hash);
        } catch (Throwable) {
            return $this->fallbackRenderer->render($manifest, $context);
        }
    }

    private function assetSet(FrontendAssetManifestData $manifest): FrontendAssetSet
    {
        $assetSet = FrontendAssetSet::make();

        foreach ($manifest->css as $assetRequirement) {
            if (! $assetRequirement instanceof FrontendAssetRequirementData) {
                continue;
            }

            $isCriticalEligible = $this->isCriticalEligible($assetRequirement);

            $assetSet->css(
                handle: $assetRequirement->handle,
                path: $this->assetUrl($assetRequirement),
                loadingStrategy: $isCriticalEligible ? AssetLoadingStrategy::Deferred : AssetLoadingStrategy::Blocking,
                slot: $isCriticalEligible ? AssetSlot::AboveFold : AssetSlot::Base,
                criticalEligible: $isCriticalEligible,
                packageName: $this->packageName($assetRequirement),
            );
        }

        foreach ($manifest->js as $assetRequirement) {
            if (! $assetRequirement instanceof FrontendAssetRequirementData) {
                continue;
            }

            $assetSet->js(
                handle: $assetRequirement->handle,
                path: $this->assetUrl($assetRequirement),
                loadingStrategy: $assetRequirement->handle === 'foundation-theme:runtime'
                    ? AssetLoadingStrategy::Idle
                    : AssetLoadingStrategy::Deferred,
                packageName: $this->packageName($assetRequirement),
            );
        }

        return $assetSet;
    }

    private function isCriticalEligible(FrontendAssetRequirementData $assetRequirement): bool
    {
        return $assetRequirement->handle === 'foundation-theme:css';
    }

    private function assetUrl(FrontendAssetRequirementData $assetRequirement): string
    {
        if ($assetRequirement->isBuildAsset() && config('capell-frontend.asset_build_tool') === 'vite') {
            try {
                return $this->vite->asset($assetRequirement->source, $assetRequirement->buildPath);
            } catch (Throwable) {
                // Build manifests may be absent before assets are published; fall back to the public path.
            }
        }

        $path = trim(($assetRequirement->buildPath !== null ? trim($assetRequirement->buildPath, '/') . '/' : '') . ltrim($assetRequirement->source, '/'), '/');

        return asset($path);
    }

    private function packageName(FrontendAssetRequirementData $assetRequirement): ?string
    {
        if (Str::startsWith($assetRequirement->handle, 'foundation-theme:')) {
            return 'capell-app/foundation-theme';
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function profileContext(FrontendAssetContextData $context, OptimizationScope $scope, string $url): array
    {
        $profileContext = [
            'layout' => [
                'id' => $context->layout?->getKey(),
                'key' => $context->layout?->key,
                'updated_at' => $context->layout?->updated_at?->toISOString(),
            ],
            'page' => [
                'id' => $context->page?->getKey(),
                'type' => $context->page?->getMorphClass(),
                'updated_at' => $context->page?->updated_at?->toISOString(),
            ],
            'theme' => [
                'id' => $context->theme?->getKey(),
                'key' => $context->theme?->key,
                'updated_at' => $context->theme?->updated_at?->toISOString(),
            ],
        ];

        if ($scope === OptimizationScope::Layout) {
            return Arr::only($profileContext, ['layout', 'theme']);
        }

        if ($scope === OptimizationScope::PageUrl) {
            $profileContext['url'] = $url;
        }

        return $profileContext;
    }

    private function profileLabel(FrontendAssetContextData $context): ?string
    {
        $layoutKey = $context->layout?->key;
        $themeKey = $context->theme?->key;

        if (! is_string($layoutKey) || $layoutKey === '') {
            return null;
        }

        if (! is_string($themeKey) || $themeKey === '') {
            return $layoutKey;
        }

        return $layoutKey . ' / ' . $themeKey;
    }
}
