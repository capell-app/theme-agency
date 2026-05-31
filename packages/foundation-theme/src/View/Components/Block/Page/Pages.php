<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Block\Page;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\PageOrderEnum;
use Capell\Core\Models\Language;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\Loader\PageLoader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Pages extends AbstractPagesBlock
{
    protected static string $defaultView = 'capell-foundation-theme::components.block.asset.pages';

    protected function mountBlock(): void
    {
        $page = Frontend::page();
        $language = Frontend::language();

        if (! $language instanceof Language) {
            $this->skipRender = true;

            return;
        }

        $selection = $this->block->assets()->pluck('asset_id')->all();

        $morphModel = $this->block->getMeta('page_model');

        $modelClass = null;

        if ($morphModel !== null) {
            $resolvedModelClass = Relation::getMorphedModel($morphModel);

            if (is_string($resolvedModelClass) && is_subclass_of($resolvedModelClass, Pageable::class)) {
                /** @var class-string<Pageable<Model>> $resolvedModelClass */
                $modelClass = $resolvedModelClass;
            }
        }

        $this->pages = PageLoader::getPages(
            language: $language,
            site: Frontend::site(),
            page: $page,
            limit: $this->block->meta['limit'] ?? config('capell-frontend.pagination_limit', 12),
            ordering: ($this->block->meta['order'] ?? '') === '' ? null : PageOrderEnum::from($this->block->meta['order']),
            pageGroup: $this->block->meta['page_group'] ?? null,
            withChildrenCount: $this->block->meta['with_children_count'] ?? false,
            withImage: $this->block->meta['with_image'] ?? false,
            withParent: $this->block->meta['with_parent'] ?? false,
            withDate: $this->block->meta['with_date'] ?? false,
            cacheKeyPrepend: 'pages-block-' . $this->block->id,
            morphModel: $modelClass,
            useCache: false,
            modifyQuery: function (Builder $query) use ($selection): void {
                $query->whereIn('id', $selection);
            },
        );

        if ($this->pages->isEmpty() && config('capell-layout-builder.block.skip_render_empty', true) === true) {
            $this->skipRender = true;
        }
    }
}
