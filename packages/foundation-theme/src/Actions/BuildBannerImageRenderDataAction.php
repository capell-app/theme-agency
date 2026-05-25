<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Actions;

use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Models\Media;
use Capell\FoundationTheme\Data\BannerImageRenderData;
use Capell\LayoutBuilder\Models\Widget;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

final class BuildBannerImageRenderDataAction
{
    use AsObject;

    public function handle(Widget $block, mixed $content, mixed $title, bool $rounded, mixed $reverseOrder): BannerImageRenderData
    {
        $backgroundImage = $this->firstLoadedBlockMedia($block, MediaCollectionEnum::BackgroundImage->value)
            ?? $this->firstLoadedBlockMedia($block, MediaCollectionEnum::Image->value)
            ?? $this->firstAssetMedia($block);

        $meta = is_array($block->meta) ? $block->meta : [];
        $actions = $meta['actions'] ?? null;
        $hasContent = filled($content) || filled($title) || filled($actions);

        return new BannerImageRenderData(
            backgroundImage: $backgroundImage,
            actions: $actions,
            hasContent: $hasContent,
            imageRoundedClass: $this->imageRoundedClass($rounded, $hasContent, (bool) $reverseOrder),
        );
    }

    private function firstLoadedBlockMedia(Widget $block, string $collectionName): ?Media
    {
        if (! $block->relationLoaded('media')) {
            return null;
        }

        $media = $block->getRelation('media');

        if (! $media instanceof Collection) {
            return null;
        }

        $match = $media->first(
            fn (mixed $media): bool => $media instanceof Media && $media->collection_name === $collectionName,
        );

        return $match instanceof Media ? $match : null;
    }

    private function firstAssetMedia(Widget $block): mixed
    {
        if (! $block->relationLoaded('assets')) {
            return null;
        }

        $assets = $block->getRelation('assets');

        if (! method_exists($assets, 'first')) {
            return null;
        }

        $firstAsset = $assets->first();

        if (! is_object($firstAsset) || ! method_exists($firstAsset, 'relationLoaded') || ! $firstAsset->relationLoaded('media')) {
            return null;
        }

        $media = $firstAsset->getRelation('media');

        return method_exists($media, 'first') ? $media->first() : null;
    }

    private function imageRoundedClass(bool $rounded, bool $hasContent, bool $reverseOrder): string
    {
        if (! $rounded) {
            return '';
        }

        if (! $hasContent) {
            return ' rounded-lg';
        }

        return $reverseOrder ? ' rounded-r-lg' : ' rounded-l-lg';
    }
}
