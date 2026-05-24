<?php

declare(strict_types=1);

namespace Capell\Hero\View\Components\Block;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Frontend\Facades\Frontend;
use Capell\LayoutBuilder\Actions\HeroBlockHasPrimaryHeadingAction;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Model;

class Hero extends AbstractBlock
{
    protected static string $defaultView = 'capell-hero::components.block.hero';

    /**
     * @param  array<array-key, mixed>  $morphRelations
     */
    public static function loadBlockAssets(array &$morphRelations, ?Language $language = null): void
    {
        $morphRelations[Page::class]['related'] = fn (BuilderContract $query): BuilderContract => $query->with(Page::getMorphRelations($language))
            ->withWhereHas('translation', fn (BuilderContract $query): BuilderContract => $query->with('language'));

        foreach (array_keys($morphRelations) as $assetModel) {
            if ($assetModel === Page::class) {
                continue;
            }

            if (! is_a($assetModel, Model::class, true)) {
                continue;
            }

            if (! method_exists($assetModel, 'getMorphRelations')) {
                continue;
            }

            $morphRelations[$assetModel]['related'] = fn (BuilderContract $query): BuilderContract => $query
                ->with($assetModel::getMorphRelations($language))
                ->withWhereHas('translation', fn (BuilderContract $query): BuilderContract => $query->with('language'));
        }
    }

    protected function mountBlock(): void
    {
        $page = Frontend::page();

        $hasHero = isset($page->translation->meta['hero']) && filled($page->translation->meta['hero']);

        if (
            $hasHero === false &&
            blank($this->block->translation?->content) &&
            $this->block->assets->isEmpty()
        ) {
            $this->skipRender = true;

            return;
        }

        HeroBlockHasPrimaryHeadingAction::run($this->block, $page);
    }
}
