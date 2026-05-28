<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Support;

use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Translation;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;

final class EditableRegionRegistry
{
    /**
     * @return list<EditableRegionPayloadData>
     */
    public function regionsFor(PageUrl $pageUrl): array
    {
        $page = $pageUrl->pageable;

        if (! ($page?->translation instanceof Translation)) {
            return [];
        }

        $translation = $page->translation;
        $currentUrl = $pageUrl->full_url;

        $regions = [
            new EditableRegionPayloadData(
                model: $translation::class,
                recordKey: (int) $translation->getKey(),
                field: 'title',
                label: __('capell-frontend-authoring::authoring.page_title'),
                type: 'text',
                selector: config('capell-frontend-authoring.selectors.page_title', '#main h1:first-of-type'),
                currentUrl: $currentUrl,
                pageUrlId: (int) $pageUrl->getKey(),
                siteId: (int) $pageUrl->site_id,
                languageId: (int) $pageUrl->language_id,
                regionKey: 'page.title',
            ),
            new EditableRegionPayloadData(
                model: $translation::class,
                recordKey: (int) $translation->getKey(),
                field: 'meta.description',
                label: __('capell-frontend-authoring::authoring.meta_description'),
                type: 'textarea',
                selector: config('capell-frontend-authoring.selectors.page_title', '#main h1:first-of-type'),
                currentUrl: $currentUrl,
                pageUrlId: (int) $pageUrl->getKey(),
                siteId: (int) $pageUrl->site_id,
                languageId: (int) $pageUrl->language_id,
                regionKey: 'page.meta.description',
            ),
            new EditableRegionPayloadData(
                model: $translation::class,
                recordKey: (int) $translation->getKey(),
                field: 'content',
                label: __('capell-frontend-authoring::authoring.page_content'),
                type: 'html',
                selector: config('capell-frontend-authoring.selectors.page_content', '#main .content-component:first-of-type'),
                currentUrl: $currentUrl,
                pageUrlId: (int) $pageUrl->getKey(),
                siteId: (int) $pageUrl->site_id,
                languageId: (int) $pageUrl->language_id,
                regionKey: 'page.content',
            ),
        ];

        foreach (app()->tagged('capell-frontend-authoring:editable-regions') as $extender) {
            if (is_callable($extender)) {
                $regions = array_values([...$regions, ...array_filter(
                    (array) $extender($pageUrl),
                    fn (mixed $payload): bool => $payload instanceof EditableRegionPayloadData,
                )]);
            }
        }

        return $regions;
    }
}
