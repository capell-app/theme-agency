<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data\SiteSpec;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * A page in the generated site. `pageType` references a page blueprint key
 * (see list_page_types); `url` is the public path (defaults to "/{slug}").
 * `contentStructure` is html|blocks (mirrors the blueprint's
 * meta.content_structure). `visibility` and `meta` are free-form passthroughs
 * onto the page's visibility window and Page.meta / translation meta.
 */
final class CapellSiteSpecPageData extends Data
{
    /**
     * @param  array<int, CapellSiteSpecSectionData>  $sections
     * @param  array{visible_from?: string|null, visible_until?: string|null}  $visibility
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $pageType,
        public readonly ?string $url = null,
        public readonly ?string $description = null,
        public readonly int $order = 0,
        public readonly string $contentStructure = 'html',
        #[DataCollectionOf(CapellSiteSpecSectionData::class)]
        public readonly array $sections = [],
        public readonly array $visibility = [],
        public readonly array $meta = [],
    ) {}

    /**
     * The resolved public path for this page, defaulting to "/{slug}".
     */
    public function resolvedUrl(): string
    {
        if ($this->url !== null && $this->url !== '') {
            return $this->url;
        }

        return '/' . ltrim($this->slug, '/');
    }
}
