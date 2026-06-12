<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Widget;

use Capell\FoundationTheme\Actions\BuildWidgetAssetRenderDataAction;
use Capell\FoundationTheme\Data\WidgetAssetRenderData;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;

class Asset extends AbstractWidget
{
    protected static string $defaultView = 'capell-foundation-theme::components.widget.asset.index';

    protected function mountWidget(): void
    {
        if ($this->assets()->isEmpty() && config('capell-layout-builder.widget.skip_render_empty', true) === true) {
            $this->skipRender = true;
        }
    }

    /**
     * @return array{
     *     assets: EloquentCollection<int, mixed>,
     *     assetRenderDataItems: SupportCollection<int, array{widgetAsset: WidgetAsset, renderData: WidgetAssetRenderData}>,
     *     firstAssetRenderData: WidgetAssetRenderData|null,
     *     lastAssetRenderData: WidgetAssetRenderData|null,
     *     total: int
     * }
     */
    protected function viewData(): array
    {
        $assets = $this->assets();
        $assetRenderDataItems = $this->assetRenderDataItems($assets);

        return [
            'assets' => $assets,
            'assetRenderDataItems' => $assetRenderDataItems,
            'firstAssetRenderData' => $this->renderDataFromItem($assetRenderDataItems->first()),
            'lastAssetRenderData' => $this->renderDataFromItem($assetRenderDataItems->last()),
            'total' => $assets->count(),
        ];
    }

    /**
     * @return EloquentCollection<int, mixed>
     */
    private function assets(): EloquentCollection
    {
        if (! $this->widget->relationLoaded('assets')) {
            return new EloquentCollection;
        }

        $assets = $this->widget->getRelation('assets');

        if ($assets instanceof EloquentCollection) {
            return $assets;
        }

        if ($assets instanceof SupportCollection) {
            return new EloquentCollection($assets->all());
        }

        return new EloquentCollection;
    }

    /**
     * @param  EloquentCollection<int, mixed>  $assets
     * @return SupportCollection<int, array{widgetAsset: WidgetAsset, renderData: WidgetAssetRenderData}>
     */
    private function assetRenderDataItems(EloquentCollection $assets): SupportCollection
    {
        return $assets
            ->filter(static fn (mixed $widgetAsset): bool => $widgetAsset instanceof WidgetAsset)
            ->map(static fn (WidgetAsset $widgetAsset): array => [
                'widgetAsset' => $widgetAsset,
                'renderData' => BuildWidgetAssetRenderDataAction::run($widgetAsset),
            ])
            ->values();
    }

    private function renderDataFromItem(mixed $item): ?WidgetAssetRenderData
    {
        if (! is_array($item)) {
            return null;
        }

        $renderData = $item['renderData'] ?? null;

        return $renderData instanceof WidgetAssetRenderData ? $renderData : null;
    }
}
