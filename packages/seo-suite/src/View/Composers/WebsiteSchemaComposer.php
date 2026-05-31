<?php

declare(strict_types=1);

namespace Capell\SeoSuite\View\Composers;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Frontend\Facades\Frontend;
use Capell\SeoSuite\Enums\SchemaEntityTypeEnum;
use Illuminate\View\View;

final readonly class WebsiteSchemaComposer
{
    public function compose(View $view): void
    {
        $site = Frontend::site();
        $language = Frontend::language();
        $siteDomain = $site instanceof Site ? $site->siteDomain : null;

        if (! $site instanceof Site || ! $language instanceof Language || ! $siteDomain instanceof SiteDomain) {
            $view->with('websiteSchema');

            return;
        }

        $siteUrl = $siteDomain->full_url;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => SchemaEntityTypeEnum::WebSite->toId($siteUrl),
            'name' => $site->getMeta('business_name', $site->translation?->title),
            'url' => $siteUrl,
        ];

        $searchPageUrl = Page::getFirstPageByTypeForSite('results', $site, $language)?->pageUrl?->full_url;

        if (is_string($searchPageUrl) && $searchPageUrl !== '') {
            $schema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $searchPageUrl . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        $view->with('websiteSchema', $schema);
    }
}
