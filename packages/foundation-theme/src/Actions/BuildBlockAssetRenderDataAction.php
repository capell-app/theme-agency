<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Actions;

use Capell\Core\Actions\ResolveImageSourceDataAction;
use Capell\Core\Contracts\Media\MediaContract;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Data\ImageSourceData;
use Capell\Core\Enums\ContentStructure;
use Capell\Core\Enums\MediaCollectionEnum;
use Capell\FoundationTheme\Data\BlockAssetRenderData;
use Capell\LayoutBuilder\Models\BlockAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

final class BuildBlockAssetRenderDataAction
{
    use AsObject;

    public function handle(BlockAsset $blockAsset): BlockAssetRenderData
    {
        $asset = $this->loadedRelation($blockAsset, 'asset');
        $translation = $asset instanceof Model ? $this->loadedRelation($asset, 'translation') : null;
        $type = $asset instanceof Model ? $this->loadedRelation($asset, 'type') : null;
        $meta = is_array(data_get($asset, 'meta')) ? data_get($asset, 'meta') : [];
        $title = $this->stringValue($translation, 'title');
        $contentStructure = data_get($type, 'content_structure');

        return new BlockAssetRenderData(
            asset: $asset,
            image: $this->image($blockAsset, $asset),
            linkedPage: $this->linkedPage($blockAsset, $asset),
            translation: $translation,
            meta: $meta,
            alt: $this->stringValue($translation, 'label') ?? $this->stringValue($translation, 'title') ?? '',
            actions: $this->metaArray($asset, 'actions'),
            accent: $this->metaString($asset, 'accent'),
            caption: $this->metaString($asset, 'caption') ?? $title,
            content: $this->stringValue($translation, 'content'),
            contentStructure: $contentStructure instanceof ContentStructure ? $contentStructure : null,
            cropPreset: $this->metaString($asset, 'crop_preset'),
            headingSize: $this->metaString($asset, 'heading_size') ?? 'h3',
            headingWeight: $this->metaString($asset, 'heading_weight') ?? 'medium',
            icon: $this->metaString($asset, 'icon'),
            linkText: $this->stringValue($translation, 'link_text'),
            linkUrl: $this->linkedPageUrl($blockAsset, $asset),
            position: $this->metaString($asset, 'position'),
            role: $this->metaString($asset, 'role'),
            social: $this->metaArray($asset, 'social'),
            status: $this->metaString($asset, 'status'),
            tags: $this->metaArray($asset, 'tags'),
            textAlign: $this->metaString($asset, 'align') ?? $this->metaString($type, 'align'),
            title: $title,
        );
    }

    private function image(BlockAsset $blockAsset, mixed $asset): ?ImageSourceData
    {
        $media = $this->firstLoadedMedia($blockAsset)
            ?? ($asset instanceof Model ? $this->firstLoadedMedia($asset) : null)
            ?? ($asset instanceof Model ? $this->loadedImage($asset) : null);

        $source = $this->metaValue($blockAsset, 'image_source')
            ?? ($asset instanceof Model ? $this->metaValue($asset, 'image_source') : null)
            ?? $this->metaValue($blockAsset, 'image')
            ?? ($asset instanceof Model ? $this->metaValue($asset, 'image') : null);

        return ResolveImageSourceDataAction::run($source, $media);
    }

    private function linkedPage(BlockAsset $blockAsset, mixed $asset): mixed
    {
        if ($asset instanceof Pageable) {
            return $asset;
        }

        if ($asset instanceof Model) {
            return $this->loadedRelation($asset, 'linkedPage');
        }

        return $this->loadedRelation($blockAsset, 'linkedPage');
    }

    private function linkedPageUrl(BlockAsset $blockAsset, mixed $asset): ?string
    {
        $linkedPage = $this->linkedPage($blockAsset, $asset);

        if (! $linkedPage instanceof Model) {
            return null;
        }

        $pageUrl = $this->loadedRelation($linkedPage, 'pageUrl');
        $fullUrl = data_get($pageUrl, 'full_url');

        return is_string($fullUrl) && $fullUrl !== '' ? $fullUrl : null;
    }

    private function loadedImage(Model $model): ?MediaContract
    {
        $image = $this->loadedRelation($model, 'image');

        return $image instanceof MediaContract ? $image : null;
    }

    private function firstLoadedMedia(Model $model): ?MediaContract
    {
        $media = $this->loadedRelation($model, 'media');

        if (! $media instanceof Collection) {
            return null;
        }

        $match = $media->first(
            static fn (mixed $media): bool => $media instanceof MediaContract
                && in_array((string) data_get($media, 'collection_name'), [
                    MediaCollectionEnum::Image->value,
                    MediaCollectionEnum::BackgroundImage->value,
                ], true),
        );

        if ($match instanceof MediaContract) {
            return $match;
        }

        $fallback = $media->first(static fn (mixed $media): bool => $media instanceof MediaContract);

        return $fallback instanceof MediaContract ? $fallback : null;
    }

    private function loadedRelation(Model $model, string $relation): mixed
    {
        if (! $model->relationLoaded($relation)) {
            return null;
        }

        return $model->getRelation($relation);
    }

    private function metaString(mixed $asset, string $key): ?string
    {
        if (! is_object($asset) || ! method_exists($asset, 'getMeta')) {
            return null;
        }

        $value = $asset->getMeta($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function metaArray(mixed $asset, string $key): array
    {
        if (! is_object($asset) || ! method_exists($asset, 'getMeta')) {
            return [];
        }

        $value = $asset->getMeta($key, []);

        return is_array($value) ? $value : [];
    }

    private function metaValue(mixed $asset, string $key): mixed
    {
        if (! is_object($asset) || ! method_exists($asset, 'getMeta')) {
            return null;
        }

        return $asset->getMeta($key);
    }

    private function stringValue(mixed $object, string $key): ?string
    {
        $value = data_get($object, $key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
