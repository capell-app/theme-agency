<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Enums\MediaConversionEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Data\SocialMetaData;
use Capell\SeoSuite\Enums\OpenGraphTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @method static SocialMetaData run(Pageable $page, Site $site, Language $language)
 */
class BuildSocialMetaAction
{
    use AsAction;

    public function handle(Pageable $page, Site $site, Language $language): SocialMetaData
    {
        $pageType = $this->loadedRelation($page, 'type');
        $pageUrl = $this->loadedRelation($page, 'pageUrl');
        $siteTranslation = $this->loadedTranslation($site);
        $creator = $this->loadedRelation($page, 'creator');

        $configuratorType = data_get($pageType, 'meta.schema.type');
        $ogType = OpenGraphTypeEnum::fromSchemaType($configuratorType);

        $socialTitle = $this->resolveSocialTitle($page, $site);
        $socialDescription = $this->resolveSocialDescription($page);

        $image = $this->resolveSocialImage($page, $site);
        $imageUrl = $image?->getAvailableUrl([MediaConversionEnum::Large->value]);
        $imageWidth = $image instanceof Media ? $this->getImageWidth($image) : null;
        $imageHeight = $image instanceof Media ? $this->getImageHeight($image) : null;
        $imageMimeType = $image?->mime_type;
        $imageAlt = $image?->getCustomProperty('alt') ?? $image?->getCustomProperty('caption') ?? $image?->name;

        return new SocialMetaData(
            title: $socialTitle,
            description: $socialDescription,
            imageUrl: $imageUrl,
            imageWidth: $imageWidth,
            imageHeight: $imageHeight,
            imageMimeType: $imageMimeType,
            imageAlt: $imageAlt,
            ogType: $ogType,
            url: $this->stringValue(data_get($pageUrl, 'full_url')) ?? '',
            locale: app()->getLocale(),
            siteName: $this->siteName($siteTranslation),
            twitterHandle: $site->getMeta('twitter'),
            articlePublishedTime: $ogType->isArticle() ? ($page->visible_from ?? $page->created_at)?->toIso8601String() : null,
            articleModifiedTime: $ogType->isArticle() ? $page->updated_at?->toIso8601String() : null,
            articleAuthor: $ogType->isArticle() ? data_get($creator, 'name') : null,
        );
    }

    private function resolveSocialTitle(Pageable $page, Site $site): string
    {
        $translation = $this->loadedTranslation($page);

        if ($translation === null) {
            return $page->name;
        }

        $socialTitle = $translation->getMeta('social_title');
        if (is_string($socialTitle) && $socialTitle !== '') {
            return $socialTitle;
        }

        if ($translation->meta_title !== null && $translation->meta_title !== '') {
            return $translation->meta_title;
        }

        $title = $translation->title ?? '';
        $siteTranslation = $this->loadedTranslation($site);
        $append = $siteTranslation?->getMeta('title_after_text', $siteTranslation->title);

        if (is_string($append) && $append !== '' && $append !== $title) {
            $title .= $this->metaTitleSeparator() . $append;
        }

        return $title;
    }

    private function resolveSocialDescription(Pageable $page): string
    {
        $translation = $this->loadedTranslation($page);

        if ($translation === null) {
            return '';
        }

        $socialDescription = $translation->getMeta('social_description');
        if (is_string($socialDescription) && $socialDescription !== '') {
            return strip_tags($socialDescription);
        }

        return strip_tags($translation->meta_description ?? '');
    }

    private function loadedTranslation(mixed $model): ?Translation
    {
        $translation = $this->loadedRelation($model, 'translation');

        return $translation instanceof Translation ? $translation : null;
    }

    private function siteName(?Translation $translation): ?string
    {
        if ($translation === null || $translation->title === null || $translation->title === '') {
            return null;
        }

        return strip_tags($translation->title);
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function metaTitleSeparator(): string
    {
        $separator = config('capell-frontend.meta_title_seperator', ' ');

        return is_string($separator) ? $separator : ' ';
    }

    private function resolveSocialImage(Pageable $page, Site $site): ?Media
    {
        $socialImage = $this->loadedRelation($page, 'socialImage')
            ?? $this->firstLoadedMedia($page, MediaCollectionEnum::SocialImage);

        if ($socialImage instanceof Media) {
            return $socialImage;
        }

        $image = $this->loadedRelation($page, 'image')
            ?? $this->firstLoadedMedia($page, MediaCollectionEnum::Image);

        if ($image instanceof Media) {
            return $image;
        }

        $siteImage = $this->loadedRelation($site, 'image')
            ?? $this->firstLoadedMedia($site, MediaCollectionEnum::Image);

        return $siteImage instanceof Media ? $siteImage : null;
    }

    private function loadedRelation(mixed $model, string $relation): mixed
    {
        if (! $model instanceof Model) {
            return null;
        }

        if (! $model->relationLoaded($relation)) {
            return null;
        }

        return $model->getRelation($relation);
    }

    private function firstLoadedMedia(mixed $model, MediaCollectionEnum $collection): ?Media
    {
        if (! $model instanceof Model) {
            return null;
        }

        if (! $model->relationLoaded('media')) {
            return null;
        }

        $media = $model->getRelation('media');

        if (! $media instanceof Collection) {
            return null;
        }

        $match = $media->first(
            static fn (mixed $media): bool => $media instanceof Media
                && $media->collection_name === $collection->value,
        );

        return $match instanceof Media ? $match : null;
    }

    private function getImageWidth(Media $media): ?int
    {
        $width = $media->getCustomProperty('width');

        return $width !== null ? (int) $width : null;
    }

    private function getImageHeight(Media $media): ?int
    {
        $height = $media->getCustomProperty('height');

        return $height !== null ? (int) $height : null;
    }
}
