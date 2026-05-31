<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Block;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\FoundationTheme\Support\NavigationAvailability;
use Capell\Frontend\Facades\Frontend;
use Capell\Navigation\Actions\BuildNavigationRenderModelAction;
use Capell\Navigation\Data\NavigationRenderContextData;
use Capell\Navigation\Data\NavigationRenderData;
use Capell\Navigation\Models;
use Capell\Navigation\Support\Loader\NavigationLoader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Navigation extends AbstractBlock
{
    /**
     * @var Collection<array-key, mixed>
     */
    public ?Collection $items = null;

    public ?Models\Navigation $menu = null;

    public ?NavigationRenderData $navigationRenderData = null;

    public ?string $headingContent = null;

    public mixed $headingContentStructure = null;

    public ?string $headingTitle = null;

    public string $listComponent = 'capell::list';

    protected static string $defaultView = 'capell-foundation-theme::components.block.navigation.index';

    protected function mountBlock(): void
    {
        if (! NavigationAvailability::check()) {
            $this->skipRender = true;

            return;
        }

        $menu = $this->getBlockMenu();

        if (! $menu instanceof Models\Navigation) {
            if (config('capell-layout-builder.block.skip_render_empty', true) === true) {
                $this->skipRender = true;
            }

            return;
        }

        $this->menu = $menu;
        $page = Frontend::page();
        $site = Frontend::site();
        $language = Frontend::language();
        $siteDomain = $site instanceof Site ? $site->siteDomain : null;

        if (! $page instanceof Pageable || ! $page instanceof Model || ! $site instanceof Site || ! $language instanceof Language || ! $siteDomain instanceof SiteDomain) {
            $this->skipRender = true;

            return;
        }

        $this->navigationRenderData = BuildNavigationRenderModelAction::run(new NavigationRenderContextData(
            navigation: $this->menu,
            page: $page,
            site: $site,
            language: $language,
            siteDomain: $siteDomain,
        ));

        $this->items = $this->navigationRenderData->items;
        $this->listComponent = $this->navigationRenderData->listComponent;
        $this->prepareHeading($page);

        if ($this->items->isEmpty()) {
            if (config('capell-layout-builder.block.skip_render_empty', true) === true) {
                $this->skipRender = true;
            }

            return;
        }
    }

    private function getBlockMenu(): ?Models\Navigation
    {
        $site = Frontend::site();
        $language = Frontend::language();

        if (! $site instanceof Site || ! $language instanceof Language) {
            return null;
        }

        if (isset($this->block->meta['navigation_id']) && is_numeric($this->block->meta['navigation_id'])) {
            return NavigationLoader::getNavigationById((int) $this->block->meta['navigation_id'], $site, $language);
        }

        if (! isset($this->block->meta['navigation']) || ! is_string($this->block->meta['navigation'])) {
            return null;
        }

        return NavigationLoader::getNavigation(
            $this->block->meta['navigation'],
            $site,
            $language,
        );
    }

    /**
     * @param  Pageable<Model>  $page
     */
    private function prepareHeading(Pageable $page): void
    {
        $blockRelations = $this->block->getRelations();
        $blockTranslation = $blockRelations['translation'] ?? null;
        $blockType = $blockRelations['type'] ?? null;
        $pageRelations = $page->getRelations();
        $pageTranslation = $pageRelations['translation'] ?? null;
        $pageType = $pageRelations['type'] ?? null;
        $showPageTitle = (bool) data_get($this->blockData, 'meta.show_page_title', false);
        $showPageContent = (bool) data_get($this->blockData, 'meta.show_page_content', false);

        if ($this->block->getMeta(sprintf('container_options.%s.hide_title', $this->containerKey)) !== true) {
            $this->headingTitle = data_get($blockTranslation, 'title')
                ?: ($showPageTitle ? data_get($pageTranslation, 'title') : null);
        }

        if ($this->block->getMeta(sprintf('container_options.%s.hide_content', $this->containerKey)) !== true) {
            $blockContent = data_get($blockTranslation, 'content');
            $this->headingContent = $blockContent ?: ($showPageContent ? data_get($pageTranslation, 'content') : null);
            $this->headingContentStructure = $blockContent
                ? data_get($blockType, 'content_structure')
                : ($showPageContent ? data_get($pageType, 'content_structure') : null);
        }
    }
}
