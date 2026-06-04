<?php

declare(strict_types=1);

namespace Capell\Inertia\Actions;

use Capell\Api\Actions\BuildPublicPagePayloadAction;
use Capell\Api\Data\PublicPagePayloadOptionsData;
use Capell\Core\Data\PublicPageFieldsData;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Translation;
use Capell\Frontend\Data\FrontendRenderContextData;
use Lorisleiva\Actions\Concerns\AsObject;

class BuildInertiaPagePropsAction
{
    use AsObject;

    /**
     * @return array<string, mixed>
     */
    public function handle(FrontendRenderContextData $context): array
    {
        $page = $context->page instanceof Page ? $context->page : null;
        $language = $context->language instanceof Language ? $context->language : null;
        $layout = $context->layout instanceof Layout ? $context->layout : null;

        return [
            'page' => BuildPublicPagePayloadAction::run(
                fields: $this->fields($page),
                options: new PublicPagePayloadOptionsData(
                    fields: ['url', 'title', 'content', 'meta'],
                    include: $layout instanceof Layout ? ['layout'] : [],
                    containers: ['all'],
                    includeWidgetComponents: true,
                ),
                layout: $layout,
                page: $page,
                language: $language,
            ),
            'language' => $this->language($language),
            'runtime' => [
                'adapter' => config('capell-inertia.adapter', 'vue'),
            ],
        ];
    }

    private function fields(?Page $page): PublicPageFieldsData
    {
        $translation = $page instanceof Page && $page->relationLoaded('translation')
            ? $page->translation
            : null;
        $pageUrl = $page instanceof Page && $page->relationLoaded('pageUrl')
            ? $page->pageUrl
            : null;

        return new PublicPageFieldsData(
            url: $pageUrl instanceof PageUrl ? $pageUrl->url : '/' . ltrim(request()->path(), '/'),
            title: $translation instanceof Translation ? $translation->title : null,
            content: $translation instanceof Translation ? $translation->content : null,
            meta: $translation instanceof Translation ? (array) $translation->meta : [],
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function language(?Language $language): array
    {
        return [
            'code' => is_string($language?->code) ? $language->code : null,
            'locale' => is_string($language?->locale) ? $language->locale : null,
        ];
    }
}
