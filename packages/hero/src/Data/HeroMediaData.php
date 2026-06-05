<?php

declare(strict_types=1);

namespace Capell\Hero\Data;

use Capell\Core\Enums\MediaConversionEnum;
use Capell\Core\Models\Media;

final readonly class HeroMediaData
{
    public const string FullBleedImageSizes = '100vw';

    public const string ModeInherit = 'inherit';

    public const string ModeOff = 'off';

    public const string ModeCustom = 'custom';

    public const string PreloadNone = 'none';

    public const string PreloadMetadata = 'metadata';

    public const string PreloadAuto = 'auto';

    public const string CollectionDesktopVideo = 'hero_video_desktop';

    public const string CollectionTabletVideo = 'hero_video_tablet';

    public const string CollectionMobileVideo = 'hero_video_mobile';

    public const string CollectionDesktopImage = 'hero_image_desktop';

    public const string CollectionTabletImage = 'hero_image_tablet';

    public const string CollectionMobileImage = 'hero_image_mobile';

    private const array SourceBreakpoints = [
        ['viewport' => 'mobile', 'media' => '(max-width: 639px)'],
        ['viewport' => 'tablet', 'media' => '(max-width: 1023px)'],
        ['viewport' => 'desktop', 'media' => '(min-width: 1024px)'],
    ];

    /**
     * @param  array<string, Media|null>  $videos
     * @param  array<string, Media|null>  $images
     */
    public function __construct(
        public bool $enabled,
        public bool $autoplay,
        public bool $loop,
        public bool $muted,
        public bool $pauseWhenOutOfView,
        public string $preload,
        public array $videos,
        public array $images,
    ) {}

    public static function disabled(): self
    {
        return new self(
            enabled: false,
            autoplay: true,
            loop: true,
            muted: true,
            pauseWhenOutOfView: true,
            preload: self::PreloadMetadata,
            videos: [],
            images: [],
        );
    }

    /**
     * @return array<string, string>
     */
    public static function videoCollections(): array
    {
        return [
            'desktop' => self::CollectionDesktopVideo,
            'tablet' => self::CollectionTabletVideo,
            'mobile' => self::CollectionMobileVideo,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function imageCollections(): array
    {
        return [
            'desktop' => self::CollectionDesktopImage,
            'tablet' => self::CollectionTabletImage,
            'mobile' => self::CollectionMobileImage,
        ];
    }

    public function hasVideo(): bool
    {
        return collect($this->videos)->contains(fn (?Media $media): bool => $media instanceof Media);
    }

    public function hasImage(): bool
    {
        return collect($this->images)->contains(fn (?Media $media): bool => $media instanceof Media);
    }

    public function poster(): ?Media
    {
        foreach (['desktop', 'tablet', 'mobile'] as $viewport) {
            $media = $this->images[$viewport] ?? null;

            if ($media instanceof Media) {
                return $media;
            }
        }

        return null;
    }

    /**
     * @return list<array{viewport: string, media: string, srcset: string, sizes: string}>
     */
    public function imageSources(): array
    {
        $sources = [];

        foreach (self::SourceBreakpoints as $source) {
            $media = $this->images[$source['viewport']] ?? null;

            if (! $media instanceof Media) {
                continue;
            }

            $sources[] = [
                'viewport' => $source['viewport'],
                'media' => $source['media'],
                'srcset' => $this->imageSrcset($media),
                'sizes' => self::FullBleedImageSizes,
            ];
        }

        return $sources;
    }

    /**
     * @return list<array{viewport: string, media: string, src: string, type: string}>
     */
    public function videoSources(): array
    {
        $sources = [];

        foreach (self::SourceBreakpoints as $source) {
            $media = $this->videos[$source['viewport']] ?? null;

            if (! $media instanceof Media) {
                continue;
            }

            $sources[] = [
                'viewport' => $source['viewport'],
                'media' => $source['media'],
                'src' => $media->getUrl(),
                'type' => $media->mime_type ?: 'video/mp4',
            ];
        }

        return $sources;
    }

    public function posterSrcset(): ?string
    {
        $poster = $this->poster();

        return $poster instanceof Media ? $this->imageSrcset($poster) : null;
    }

    private function imageSrcset(Media $media): string
    {
        if ($media->hasResponsiveImages()) {
            $responsiveSrcset = trim($media->getSrcset());

            if ($responsiveSrcset !== '') {
                return $responsiveSrcset;
            }
        }

        $conversionSrcset = $this->conversionSrcset($media);

        if ($conversionSrcset !== '') {
            return $conversionSrcset;
        }

        $width = $media->getWidth();

        if ($width !== null && $width > 0) {
            return sprintf('%s %dw', $media->getUrl(), $width);
        }

        return $media->getUrl() . ' 1x';
    }

    private function conversionSrcset(Media $media): string
    {
        $candidates = [];

        foreach (MediaConversionEnum::defaultDimensionsByConversionValue() as $conversionName => $dimensions) {
            if (! $media->hasConversion($conversionName)) {
                continue;
            }

            $candidates[] = sprintf(
                '%s %dw',
                $media->getFullUrl($conversionName),
                $dimensions['width'],
            );
        }

        return implode(', ', $candidates);
    }
}
