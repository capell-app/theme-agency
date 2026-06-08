<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Capell\FrontendOptimizer\Data\FrontendAssetDefinitionData;
use Capell\FrontendOptimizer\Data\FrontendResourceHintData;
use Capell\FrontendOptimizer\Data\RenderProfileData;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Support\CriticalCssSettings;
use Capell\FrontendOptimizer\Support\FrontendAssetSet;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static RenderProfileData run(OptimizationScope $scope, array<string, mixed> $context, array<int, FrontendAssetSet> $assetSets, array<int, FrontendResourceHintData> $resourceHints = [], ?string $label = null)
 */
class ResolveRenderProfileAction
{
    use AsAction;

    public function __construct(private readonly CriticalCssSettings $criticalCssSettings) {}

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, FrontendAssetSet>  $assetSets
     * @param  array<int, FrontendResourceHintData>  $resourceHints
     */
    public function handle(
        OptimizationScope $scope,
        array $context,
        array $assetSets,
        array $resourceHints = [],
        ?string $label = null,
    ): RenderProfileData {
        $assets = collect($assetSets)
            ->reduce(
                static fn (FrontendAssetSet $carry, FrontendAssetSet $assetSet): FrontendAssetSet => $carry->merge($assetSet),
                FrontendAssetSet::make(),
            )
            ->all()
            ->all();

        $signature = [
            'assets' => array_map(static fn (FrontendAssetDefinitionData $asset): array => $asset->signature(), $assets),
            'context' => $this->normalize($context),
            'critical_css' => $this->criticalCssSettings->signature(),
            'resource_hints' => array_map(static fn (FrontendResourceHintData $hint): array => $hint->signature(), $resourceHints),
            'scope' => $scope->value,
        ];

        $encodedSignature = json_encode($signature, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return new RenderProfileData(
            hash: hash('sha256', $encodedSignature),
            scope: $scope,
            signature: $signature,
            assets: $assets,
            resourceHints: $resourceHints,
            label: $label,
        );
    }

    private function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $entry) {
                $normalized[$key] = $this->normalize($entry);
            }

            if (! array_is_list($normalized)) {
                ksort($normalized);
            }

            return $normalized;
        }

        return $value;
    }
}
