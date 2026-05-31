<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Livewire\Block;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\PageOrderEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\Loader\PageLoader;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Livewire\WithPagination;
use Override;

class Pages extends AbstractBlock
{
    use WithPagination;

    protected static string $defaultView = 'capell-foundation-theme::components.block.asset.pages';

    /**
     * @var Collection<int, mixed>|LengthAwarePaginator<int, mixed>
     */
    protected Collection|LengthAwarePaginator $pages;

    #[Override]
    public function render(array $data = []): View|string|Closure
    {
        $data['pages'] = $this->pages;

        $view = parent::render($data);

        if ($view instanceof View) {
            return '<div class="contents">' . $view->render() . '</div>';
        }

        return $view;
    }

    protected function mountBlock(): void
    {
        $page = Frontend::page();
        $language = Frontend::language();
        $site = Frontend::site();
        $block = $this->block();

        if (! $page instanceof Pageable || ! $page instanceof Model || ! $language instanceof Language || ! $site instanceof Site) {
            $this->skipRender = true;

            return;
        }

        $limit = $block->meta['limit'] ?? config('capell-frontend.pagination_limit', 12);

        $paginationKey = $this->containerKey . ucfirst((string) $block->key) . $this->occurrence;
        $paginationPage = (int) $this->getPage($paginationKey);

        $selection = $block->assets->pluck('asset_id')->toArray();

        $morphModel = $block->getMeta('page_model');

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
            site: $site,
            page: $page,
            limit: $limit,
            paginationPage: $paginationPage,
            ordering: ($block->meta['order'] ?? '') === '' ? null : PageOrderEnum::from($block->meta['order']),
            pageGroup: $block->meta['page_group'] ?? null,
            withChildrenCount: $block->meta['with_children_count'] ?? false,
            withImage: $block->meta['with_image'] ?? false,
            withPagination: $block->meta['pagination'] ?? false,
            withParent: $block->meta['with_parent'] ?? false,
            withDate: $block->meta['with_date'] ?? false,
            paginationKey: $paginationKey,
            cacheKeyPrepend: sprintf('page-%d-block-%d-container-%s-%d', (int) $page->getKey(), $block->id, $this->containerKey, $this->occurrence),
            morphModel: $modelClass,
            modifyQuery: function (Builder $query) use ($selection): void {
                $query->whereIn('id', $selection);
            },
        );

        if ($this->pages->isEmpty() && config('capell-layout-builder.block.skip_render_empty', true) === true) {
            $this->skipRender = true;
        }
    }
}
