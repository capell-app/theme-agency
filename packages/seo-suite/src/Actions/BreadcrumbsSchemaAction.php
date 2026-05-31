<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Frontend\Support\Loader\PageLoader;
use Capell\SeoSuite\Enums\SchemaEntityTypeEnum;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<array-key, mixed> run(Page $page, Site $site, Language $language)
 */
class BreadcrumbsSchemaAction
{
    use AsAction;

    /**
     * @return array<array-key, mixed>
     */
    public function handle(Page $page, Site $site, Language $language): array
    {
        $page->loadMissing('translations.language');

        $canonicalPages = PageLoader::getCanonicalPages($page, $language);
        $ancestors = PageLoader::getPageAncestors($page, $language, $site);

        if ($canonicalPages->isNotEmpty()) {
            $return = [];

            $canonicalPages->each(function (Page $canonicalPage) use ($language, $site, &$return): void {
                $pageUrl = $canonicalPage->pageUrl;
                $item = [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    '@id' => $pageUrl?->full_url !== null && $pageUrl->full_url !== ''
                        ? SchemaEntityTypeEnum::BreadcrumbList->toId($pageUrl->full_url)
                        : null,
                    'itemListElement' => [],
                ];

                $canonicalPageAncestors = PageLoader::getPageAncestors($canonicalPage, $language, $site);

                $canonicalPageAncestors?->each(function (Page $ancestorPage, int $index) use (&$item): void {
                    $translation = $ancestorPage->translation;
                    $pageUrl = $ancestorPage->pageUrl;

                    $item['itemListElement'][] = [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => strip_tags((string) ($translation->label ?? $ancestorPage->name)),
                        'item' => $pageUrl?->full_url,
                    ];
                });

                $return[] = $item;
            });

            return $return;
        }

        if ($ancestors?->isNotEmpty()) {
            $pageUrl = $page->pageUrl;
            $item = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                '@id' => $pageUrl?->full_url !== null && $pageUrl->full_url !== ''
                    ? SchemaEntityTypeEnum::BreadcrumbList->toId($pageUrl->full_url)
                    : null,
                'itemListElement' => [],
            ];

            $ancestors->each(function (Page $ancestorPage, int $index) use (&$item): void {
                $translation = $ancestorPage->translation;
                $pageUrl = $ancestorPage->pageUrl;

                $item['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => strip_tags((string) ($translation->label ?? $ancestorPage->name)),
                    'item' => $pageUrl?->full_url,
                ];
            });

            return [$item];
        }

        return [];
    }
}
