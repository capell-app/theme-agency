<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Block\Page;

use Capell\Core\Enums\PageOrderEnum;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\Loader\PageLoader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Latest extends AbstractPagesBlock
{
    protected static string $defaultView = 'capell-foundation-theme::components.block.asset.pages';

    protected function mountBlock(): void
    {
        $morphModel = $this->block->getMeta('page_model');

        $modelClass = null;

        if ($morphModel !== null) {
            $modelClass = Relation::getMorphedModel($morphModel);
        }

        $this->pages = PageLoader::getPages(
            language: Frontend::language(),
            site: Frontend::site(),
            page: Frontend::page(),
            limit: $this->block->meta['limit'] ?? config('capell-frontend.pagination_limit', 12),
            ordering: PageOrderEnum::Latest,
            pageGroup: $this->block->meta['page_group'] ?? null,
            withChildrenCount: $this->block->meta['with_children_count'] ?? false,
            withImage: $this->block->meta['with_image'] ?? false,
            withParent: $this->block->meta['with_parent'] ?? false,
            withDate: $this->block->meta['with_date'] ?? false,
            cacheKeyPrepend: 'latest-block-' . $this->block->id,
            morphModel: $modelClass,
            useCache: false,
            modifyQuery: function (Builder $query): void {
                $query->whereKeyNot(Frontend::page()->id);
            },
        );

        if ($this->pages->isEmpty() && config('capell-layout-builder.block.skip_render_empty', true) === true) {
            $this->skipRender = true;
        }
    }
}
