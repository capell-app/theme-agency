<?php

declare(strict_types=1);

namespace Capell\Hero\Data;

use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Frontend\Actions\RenderHtmlContentAction;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

final readonly class HeroAssetSlideData
{
    /**
     * @param  Collection<int, Media>|null  $images
     * @param  Collection<int, array{url: string|null, title: string|null, summary: string|null, linkText: string|null}>  $related
     */
    public function __construct(
        public Model $asset,
        public string $color,
        public mixed $actions,
        public Collection $related,
        public ?HeroBackgroundData $heroBackground,
        public ?HeroMediaData $heroMedia,
        public ?string $backgroundAttachment,
        public ?string $backgroundColor,
        public ?string $backgroundPosition,
        public ?string $backgroundRepeat,
        public ?string $backgroundSize,
        public ?string $content,
        public ?string $contentHtml,
        public ?string $linkText,
        public ?string $title,
        public ?Page $linkedPage,
        public ?string $url,
        public ?Media $backgroundImage,
        public ?object $image,
        public ?Collection $images,
    ) {}

    public static function fromWidgetAsset(
        WidgetAsset $widgetAsset,
        Widget $widget,
        string $fallbackColor,
        ?Page $page = null,
        ?Site $site = null,
    ): self {
        $asset = self::loadedRelation($widgetAsset, 'asset');

        throw_unless($asset instanceof Model, RuntimeException::class, 'Hero widget asset must resolve to an Eloquent model.');

        $color = self::modelMeta($asset, 'color', $fallbackColor);
        $linkedPage = $asset instanceof Page ? $asset : self::loadedRelation($asset, 'linkedPage');
        $backgroundImage = self::resolveBackgroundImage($widgetAsset);
        $images = self::resolveImages($widgetAsset);
        $translation = self::loadedRelation($asset, 'translation');
        $backgroundAttachment = self::modelMeta($asset, 'background_attachment', self::modelMeta($widget, 'background_attachment', 'scroll'));
        $backgroundColor = self::modelMeta($asset, 'background_color', self::modelMeta($widget, 'background_color'));
        $backgroundPosition = self::modelMeta($asset, 'background_position', self::modelMeta($widget, 'background_position', 'center'));
        $backgroundRepeat = self::modelMeta($asset, 'background_repeat', self::modelMeta($widget, 'background_repeat', 'no-repeat'));
        $backgroundSize = self::modelMeta($asset, 'background_size', self::modelMeta($widget, 'background_size', 'cover'));
        $linkText = self::modelMeta($asset, 'link_text');
        $content = is_string(data_get($translation, 'content')) ? data_get($translation, 'content') : null;

        return new self(
            asset: $asset,
            color: is_string($color) && $color !== '' ? $color : $fallbackColor,
            actions: self::modelMeta($asset, 'actions'),
            related: self::resolveRelatedItems($asset),
            heroBackground: null,
            heroMedia: null,
            backgroundAttachment: is_string($backgroundAttachment) ? $backgroundAttachment : null,
            backgroundColor: is_string($backgroundColor) ? $backgroundColor : null,
            backgroundPosition: is_string($backgroundPosition) ? $backgroundPosition : null,
            backgroundRepeat: is_string($backgroundRepeat) ? $backgroundRepeat : null,
            backgroundSize: is_string($backgroundSize) ? $backgroundSize : null,
            content: $content,
            contentHtml: $content !== null ? RenderHtmlContentAction::run($content, array_filter(['page' => $page, 'site' => $site])) : null,
            linkText: is_string($linkText) && $linkText !== '' ? $linkText : null,
            title: is_string(data_get($translation, 'title')) ? data_get($translation, 'title') : null,
            linkedPage: $linkedPage instanceof Page ? $linkedPage : null,
            url: $linkedPage instanceof Page ? data_get(self::loadedRelation($linkedPage, 'pageUrl'), 'full_url') : null,
            backgroundImage: $backgroundImage,
            image: self::loadedRelation($asset, 'image'),
            images: $images,
        );
    }

    public function withResolvedLayers(
        ?HeroBackgroundData $heroBackground,
        ?HeroMediaData $heroMedia,
        ?string $backgroundAttachment = null,
        ?string $backgroundColor = null,
        ?string $backgroundPosition = null,
        ?string $backgroundRepeat = null,
        ?string $backgroundSize = null,
    ): self {
        return new self(
            asset: $this->asset,
            color: $this->color,
            actions: $this->actions,
            related: $this->related,
            heroBackground: $heroBackground,
            heroMedia: $heroMedia,
            backgroundAttachment: $this->backgroundAttachment ?? $backgroundAttachment,
            backgroundColor: $this->backgroundColor ?? $backgroundColor,
            backgroundPosition: $this->backgroundPosition ?? $backgroundPosition,
            backgroundRepeat: $this->backgroundRepeat ?? $backgroundRepeat,
            backgroundSize: $this->backgroundSize ?? $backgroundSize,
            content: $this->content,
            contentHtml: $this->contentHtml,
            linkText: $this->linkText,
            title: $this->title,
            linkedPage: $this->linkedPage,
            url: $this->url,
            backgroundImage: $this->backgroundImage,
            image: $this->image,
            images: $this->images,
        );
    }

    private static function resolveBackgroundImage(WidgetAsset $widgetAsset): ?Media
    {
        $asset = self::loadedRelation($widgetAsset, 'asset');

        if ($asset instanceof Media) {
            return $asset;
        }

        $collection = MediaCollectionEnum::BackgroundImage->value;
        $widgetAssetMedia = self::loadedRelation($widgetAsset, 'media');
        $assetMedia = $asset instanceof Model ? self::loadedRelation($asset, 'media') : null;

        $media = $widgetAssetMedia instanceof Collection ? $widgetAssetMedia->firstWhere('collection_name', $collection) : null;

        if (! $media instanceof Media && $assetMedia instanceof Collection) {
            $media = $assetMedia->firstWhere('collection_name', $collection);
        }

        return $media instanceof Media ? $media : null;
    }

    /**
     * @return Collection<int, Media>|null
     */
    private static function resolveImages(WidgetAsset $widgetAsset): ?Collection
    {
        $asset = self::loadedRelation($widgetAsset, 'asset');

        if ($asset instanceof Media) {
            return null;
        }

        $collection = MediaCollectionEnum::Image->value;
        $widgetAssetMedia = self::loadedRelation($widgetAsset, 'media');
        $assetMedia = $asset instanceof Model ? self::loadedRelation($asset, 'media') : null;

        $images = $widgetAssetMedia instanceof Collection ? $widgetAssetMedia->where('collection_name', $collection) : null;

        if (! $images?->isNotEmpty() && $assetMedia instanceof Collection) {
            $images = $assetMedia->where('collection_name', $collection);
        }

        return $images?->isNotEmpty() ? $images->values() : null;
    }

    /**
     * @return Collection<int, array{url: string|null, title: string|null, summary: string|null, linkText: string|null}>
     */
    private static function resolveRelatedItems(Model $asset): Collection
    {
        $related = self::loadedRelation($asset, 'related');

        if (! $related instanceof Collection) {
            return collect();
        }

        return $related
            ->filter(fn (mixed $feature): bool => $feature instanceof Model)
            ->map(fn (Model $feature): array => self::relatedItem($feature))
            ->filter(fn (array $feature): bool => filled($feature['title']) || filled($feature['summary']) || filled($feature['linkText']))
            ->values();
    }

    /**
     * @return array{url: string|null, title: string|null, summary: string|null, linkText: string|null}
     */
    private static function relatedItem(Model $feature): array
    {
        $featureRelations = $feature->getRelations();
        $linkedPage = $feature instanceof Page ? $feature : ($featureRelations['linkedPage'] ?? null);
        $linkedPageRelations = $linkedPage instanceof Model ? $linkedPage->getRelations() : [];
        $pageUrl = $linkedPageRelations['pageUrl'] ?? null;
        $featureTranslation = $featureRelations['translation'] ?? null;
        $linkText = self::modelMeta($feature, 'link_text');
        $url = data_get($pageUrl, 'full_url');
        $title = data_get($featureTranslation, 'title');
        $summary = data_get($featureTranslation, 'summary');

        return [
            'url' => is_string($url) && $url !== '' ? $url : null,
            'title' => is_string($title) && $title !== '' ? $title : null,
            'summary' => is_string($summary) && $summary !== '' ? $summary : null,
            'linkText' => is_string($linkText) && $linkText !== '' ? $linkText : null,
        ];
    }

    private static function loadedRelation(Model $model, string $relation): mixed
    {
        if (! $model->relationLoaded($relation)) {
            return null;
        }

        return $model->getRelation($relation);
    }

    private static function modelMeta(Model $model, string $key, mixed $fallback = null): mixed
    {
        $meta = $model->getAttribute('meta') ?? [];

        if (is_array($meta) && Arr::has($meta, $key)) {
            $value = data_get($meta, $key);

            if (filled($value)) {
                return $value;
            }
        }

        $blueprint = self::loadedRelation($model, 'blueprint');

        if (! $blueprint instanceof Blueprint) {
            $blueprint = self::loadedRelation($model, 'type');
        }

        if ($blueprint instanceof Blueprint) {
            $blueprintMeta = $blueprint->meta ?? [];

            if (is_array($blueprintMeta) && Arr::has($blueprintMeta, $key)) {
                $value = data_get($blueprintMeta, $key);

                if (filled($value)) {
                    return $value;
                }
            }
        }

        return $fallback;
    }
}
