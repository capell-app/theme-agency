<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Media;
use Capell\FoundationTheme\Data\AssetBannerItemData;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;
use Throwable;

final class BuildAssetBannerItemsAction
{
    use AsObject;

    /**
     * @return Collection<int, AssetBannerItemData>
     */
    public function handle(Widget $block): Collection
    {
        $blockAssets = $block->relationLoaded('assets') ? $block->getRelation('assets') : collect();

        if (! $blockAssets instanceof Collection) {
            return collect();
        }

        return $blockAssets
            ->filter(fn (mixed $blockAsset): bool => $blockAsset instanceof WidgetAsset)
            ->map(fn (WidgetAsset $blockAsset): AssetBannerItemData => $this->item($block, $blockAsset))
            ->values();
    }

    private function item(Widget $block, WidgetAsset $blockAsset): AssetBannerItemData
    {
        $asset = $blockAsset->relationLoaded('asset') ? $blockAsset->getRelation('asset') : null;
        $linkedPage = $this->linkedPage($blockAsset, $asset);
        $translation = $asset instanceof Model && $asset->relationLoaded('translation')
            ? $asset->getRelation('translation')
            : null;

        $assetDefinition = $this->assetDefinition($blockAsset);
        $hasTranslations = $asset instanceof Model
            && is_object($assetDefinition)
            && (bool) ($assetDefinition->hasTranslations ?? false);

        return new AssetBannerItemData(
            image: $this->image($block, $blockAsset, $asset),
            alt: (string) ($translation->label ?? $translation->title ?? ''),
            title: $hasTranslations ? $translation?->title : null,
            content: $hasTranslations ? $translation?->content : null,
            url: $this->pageUrl($linkedPage),
            linkText: $this->linkText($linkedPage),
        );
    }

    private function image(Widget $block, WidgetAsset $blockAsset, mixed $asset): mixed
    {
        return $this->firstLoadedMedia($blockAsset, MediaCollectionEnum::Image->value)
            ?? ($asset instanceof Model && $asset->relationLoaded('image') ? $asset->getRelation('image') : null)
            ?? $this->firstLoadedMedia($block, MediaCollectionEnum::BackgroundImage->value);
    }

    private function linkedPage(WidgetAsset $blockAsset, mixed $asset): mixed
    {
        if ($asset instanceof Pageable) {
            return $asset;
        }

        if ($asset instanceof Model && $asset->relationLoaded('linkedPage')) {
            return $asset->getRelation('linkedPage');
        }

        return $blockAsset->relationLoaded('linkedPage') ? $blockAsset->getRelation('linkedPage') : null;
    }

    private function pageUrl(mixed $linkedPage): ?string
    {
        if (! $linkedPage instanceof Model || ! $linkedPage->relationLoaded('pageUrl')) {
            return null;
        }

        $pageUrl = $linkedPage->getRelation('pageUrl');

        return is_string($pageUrl->full_url ?? null) ? $pageUrl->full_url : null;
    }

    private function linkText(mixed $linkedPage): ?string
    {
        if (! $linkedPage instanceof Model || ! $linkedPage->relationLoaded('translation')) {
            return null;
        }

        $translation = $linkedPage->getRelation('translation');

        return is_string($translation->link_text ?? null) ? $translation->link_text : null;
    }

    private function firstLoadedMedia(Model $model, string $collectionName): ?Media
    {
        if (! $model->relationLoaded('media')) {
            return null;
        }

        $media = $model->getRelation('media');

        if (! $media instanceof Collection) {
            return null;
        }

        $match = $media->first(
            fn (mixed $media): bool => $media instanceof Media && $media->collection_name === $collectionName,
        );

        return $match instanceof Media ? $match : null;
    }

    private function assetDefinition(WidgetAsset $blockAsset): mixed
    {
        if (! is_string($blockAsset->asset_type) || $blockAsset->asset_type === '') {
            return null;
        }

        try {
            return CapellCore::getAsset($blockAsset->asset_type);
        } catch (Throwable) {
            return null;
        }
    }
}
