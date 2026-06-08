<?php

declare(strict_types=1);

namespace Capell\SeoSuite\View\Composers;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Frontend\Facades\Frontend;
use Capell\SeoSuite\Actions\BreadcrumbsSchemaAction;
use Capell\SeoSuite\Actions\BuildPageImageSchemaAction;
use Capell\SeoSuite\Actions\PageMetaSchemaAction;
use Capell\SeoSuite\Actions\SiteMetaSchemaAction;
use Illuminate\View\View;

final readonly class SchemaComponentComposer
{
    public function compose(View $view): void
    {
        $view->with('schemaJson', match ($view->name()) {
            'capell::components.schema.breadcrumb' => $this->breadcrumbSchema(),
            'capell::components.schema.image' => $this->imageSchema(),
            'capell::components.schema.organization' => $this->organizationSchema(),
            'capell::components.schema.webpage' => $this->webpageSchema(),
            default => [],
        });
    }

    /**
     * @return array<array-key, mixed>
     */
    private function breadcrumbSchema(): array
    {
        $page = Frontend::page();
        $site = Frontend::site();
        $language = Frontend::language();

        return $page instanceof Page && $site instanceof Site && $language instanceof Language
            ? BreadcrumbsSchemaAction::run($page, $site, $language)
            : [];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function imageSchema(): array
    {
        $page = Frontend::page();

        return $page instanceof Pageable ? BuildPageImageSchemaAction::run($page) : [];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function organizationSchema(): array
    {
        $site = Frontend::site();
        $language = Frontend::language();

        return $site instanceof Site && $language instanceof Language
            ? SiteMetaSchemaAction::run($site, $language)
            : [];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function webpageSchema(): array
    {
        $page = Frontend::page();
        $site = Frontend::site();
        $language = Frontend::language();

        return $page instanceof Page && $site instanceof Site && $language instanceof Language
            ? PageMetaSchemaAction::run($page, $site, $language)
            : [];
    }
}
