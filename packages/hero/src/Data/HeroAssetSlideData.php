<?php

declare(strict_types=1);

namespace Capell\Hero\Data;

use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final readonly class HeroAssetSlideData
{
    /**
     * @param  Collection<int, Media>|null  $images
     */
    public function __construct(
        public Model $asset,
        public string $color,
        public ?Page $linkedPage,
        public ?string $url,
        public ?Media $backgroundImage,
        public ?Collection $images,
    ) {}

    public static function fromBlockAsset(WidgetAsset $blockAsset, Widget $block, string $fallbackColor): self
    {
        $asset = $blockAsset->asset;
        $color = method_exists($asset, 'getMeta') ? $asset->getMeta('color', $fallbackColor) : $fallbackColor;
        $linkedPage = $asset instanceof Page ? $asset : $asset->linkedPage;
        $backgroundImage = self::resolveBackgroundImage($blockAsset);
        $images = self::resolveImages($blockAsset);

        return new self(
            asset: $asset,
            color: is_string($color) && $color !== '' ? $color : $fallbackColor,
            linkedPage: $linkedPage instanceof Page ? $linkedPage : null,
            url: $linkedPage instanceof Page ? $linkedPage->pageUrl?->full_url : null,
            backgroundImage: $backgroundImage,
            images: $images,
        );
    }

    private static function resolveBackgroundImage(WidgetAsset $blockAsset): ?Media
    {
        if ($blockAsset->asset instanceof Media) {
            return $blockAsset->asset;
        }

        $collection = MediaCollectionEnum::BackgroundImage->value;

        $media = $blockAsset->media?->firstWhere('collection_name', $collection)
            ?? $blockAsset->asset->media?->firstWhere('collection_name', $collection);

        return $media instanceof Media ? $media : null;
    }

    /**
     * @return Collection<int, Media>|null
     */
    private static function resolveImages(WidgetAsset $blockAsset): ?Collection
    {
        if ($blockAsset->asset instanceof Media) {
            return null;
        }

        $collection = MediaCollectionEnum::Image->value;

        $images = $blockAsset->media?->where('collection_name', $collection);

        if (! $images?->isNotEmpty()) {
            $images = $blockAsset->asset->media?->where('collection_name', $collection);
        }

        return $images?->isNotEmpty() ? $images->values() : null;
    }
}
