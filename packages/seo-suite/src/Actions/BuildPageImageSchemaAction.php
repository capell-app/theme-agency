<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\MediaConversionEnum;
use Capell\Core\Models\Media;
use Capell\Frontend\Contracts\RenderedModelTracker;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static array<int, array<string, mixed>> run(Pageable $page)
 */
final class BuildPageImageSchemaAction
{
    use AsObject;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(Pageable $page): array
    {
        $json = [];
        $primaryImage = $this->loadedRelation($page, 'image');

        if ($primaryImage instanceof Media) {
            $json[] = $this->imageObject($primaryImage);
        }

        $media = $this->loadedRelation($page, 'media');

        if (! $media instanceof Collection) {
            return $json;
        }

        $media
            ->reject(fn (Media $media): bool => $primaryImage instanceof Media && $media->id === $primaryImage->id)
            ->take(2)
            ->each(function (Media $media) use (&$json): void {
                $json[] = $this->imageObject($media);
            });

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    private function imageObject(Media $media): array
    {
        resolve(RenderedModelTracker::class)->track($media);

        $image = [
            '@context' => 'https://schema.org',
            '@type' => 'ImageObject',
            'contentUrl' => $media->getAvailableUrl([MediaConversionEnum::Large->value]),
            'name' => $media->name,
            'datePublished' => $media->created_at?->toDateString(),
        ];

        $caption = $this->customProperty($media, 'caption');
        if ($caption !== null) {
            $image['caption'] = $caption;
        }

        $description = $this->customProperty($media, 'description') ?? $this->customProperty($media, 'alt');
        if ($description !== null) {
            $image['description'] = $description;
        }

        return $image;
    }

    private function customProperty(Media $media, string $key): ?string
    {
        $value = $media->getCustomProperty($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function loadedRelation(Pageable $page, string $relation): mixed
    {
        if (! $page->relationLoaded($relation)) {
            return null;
        }

        return $page->getRelation($relation);
    }
}
