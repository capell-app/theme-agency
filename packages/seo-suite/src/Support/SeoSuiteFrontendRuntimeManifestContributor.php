<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Frontend\Contracts\FrontendContextReader;
use Capell\Frontend\Contracts\FrontendRuntimeManifestContributor;
use Capell\Frontend\Data\FrontendRuntimeManifestData;
use Capell\SeoSuite\Actions\BreadcrumbsSchemaAction;
use Capell\SeoSuite\Actions\BuildPageImageSchemaAction;
use Capell\SeoSuite\Actions\PageMetaSchemaAction;
use Capell\SeoSuite\Actions\SchemaGraphAction;
use Capell\SeoSuite\Actions\SiteMetaSchemaAction;
use Capell\SeoSuite\Enums\MetaSchemaEnum;

final class SeoSuiteFrontendRuntimeManifestContributor implements FrontendRuntimeManifestContributor
{
    public function contribute(FrontendContextReader $context, FrontendRuntimeManifestData $manifest): void
    {
        $site = $context->site();
        $language = $context->language();

        if (! $site instanceof Site || ! $language instanceof Language) {
            return;
        }

        $searchPageUrl = Page::getFirstPageByTypeForSite('results', $site, $language)?->pageUrl?->full_url;

        if (is_string($searchPageUrl) && $searchPageUrl !== '') {
            $context->setFrontendData('seo.website_search_url', $searchPageUrl);
        }

        $page = $context->page();

        if ($page instanceof Page) {
            $context->setFrontendData('seo.schema.breadcrumb', BreadcrumbsSchemaAction::run($page, $site, $language));
            $context->setFrontendData('seo.schema.image', BuildPageImageSchemaAction::run($page));
            $context->setFrontendData('seo.schema.webpage', PageMetaSchemaAction::run($page, $site, $language));

            if ($this->shouldRenderSchemaGraph($site->meta ?? [])) {
                $context->setFrontendData('seo.schema.graph_script', SchemaGraphAction::run($page, $site, $language)->toJsonLdScript());
            }
        }

        $context->setFrontendData('seo.schema.organization', SiteMetaSchemaAction::run($site, $language));
    }

    /**
     * @param  array<string, mixed>  $siteMeta
     */
    private function shouldRenderSchemaGraph(array $siteMeta): bool
    {
        $metaSchema = $siteMeta['meta_schema'] ?? [];

        return is_array($metaSchema) && in_array(MetaSchemaEnum::Graph->getComponent(), $metaSchema, true);
    }
}
