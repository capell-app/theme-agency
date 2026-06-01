<?php

declare(strict_types=1);

namespace Capell\Hero\Data;

use Capell\Core\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final readonly class HeroBlockRenderData
{
    /**
     * @param  array{
     *     align: string,
     *     arrows: bool,
     *     autoPlay: bool,
     *     autoDelay: int,
     *     buttonClass: string,
     *     disableOnInteraction: bool,
     *     drag: bool,
     *     effect: string,
     *     fade: bool,
     *     loop: bool,
     *     pagination: bool,
     *     pauseOnHover: bool,
     *     rewind: bool,
     *     speed: int,
     *     touch: bool|null,
     *     wheel: bool
     * } $carousel
     * @param  Collection<int, HeroAssetSlideData>  $slides
     */
    public function __construct(
        public ?string $backgroundColor,
        public string $color,
        public array $carousel,
        public string $containerClass,
        public string $contentAlignmentClass,
        public string $contentWidthClass,
        public ?string $height,
        public string $mediaPosition,
        public string $mediaSize,
        public ?Media $pageBackgroundImage,
        public ?string $pageBackgroundColor,
        public string $pageBackgroundSize,
        public string $pageBackgroundPosition,
        public string $pageBackgroundAttachment,
        public string $pageBackgroundRepeat,
        public ?HeroBackgroundData $pageHeroBackground,
        public ?HeroMediaData $pageHeroMedia,
        public ?string $pageHeroContentHtml,
        public ?string $pageHeroTitle,
        public ?string $blockFallbackTitle,
        public ?LengthAwarePaginator $paginationResults,
        public bool $hasPaginationSummary,
        public string $slideClass,
        public Collection $slides,
        public int $total,
        public bool $shouldRender,
    ) {}
}
