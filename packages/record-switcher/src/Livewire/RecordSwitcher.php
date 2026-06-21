<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Livewire;

use Capell\Core\Exceptions\UrlMissingSiteDomainException;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Filament\Resources\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Contracts\Database\Query\Expression as QueryExpressionContract;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

final class RecordSwitcher extends Component
{
    /** @var class-string */
    public string $pageClass;

    /** @var class-string<resource> */
    public string $resourceClass;

    public string $recordKey;

    public string $label;

    public int $limitResults = 10;

    public function render(): View
    {
        return view('capell-record-switcher::components.record-switcher', [
            'label' => $this->label,
            'limit_results' => $this->limitResults,
            'value' => $this->recordKey,
        ]);
    }

    /**
     * @return list<array{value: string, label: string, group?: string}>
     */
    #[Renderless]
    public function getOptions(?string $search = null): array
    {
        $query = $this->baseQuery();

        if (filled($search)) {
            $this->applyAttributeConstraints($query, $search);
        }

        $items = $this->modifyQuery($query)
            ->get()
            ->map(fn (Model $model): array => $this->item($model))
            ->values()
            ->all();

        return array_values($items);
    }

    /** @return Builder<Model> */
    private function baseQuery(): Builder
    {
        /** @var Builder<Model> $query */
        $query = $this->resourceClass::getEloquentQuery();

        return $query->limit($this->limitResults);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function modifyQuery(Builder $query): Builder
    {
        $modelClass = $this->resourceClass::getModel();

        if ($modelClass !== Page::class && ! is_subclass_of($modelClass, Page::class)) {
            $query->whereKeyNot($this->recordKey);

            $updatedAtColumn = $query->getModel()->getUpdatedAtColumn();

            if ($query->getModel()->usesTimestamps() && is_string($updatedAtColumn)) {
                $query->orderByDesc($query->getModel()->qualifyColumn($updatedAtColumn));
            }

            return $query->orderBy($query->getModel()->getKeyName());
        }

        $hasPageHierarchy = method_exists($this->resourceClass, 'hasPageHierarchy')
            && (bool) $this->resourceClass::hasPageHierarchy();
        $currentPage = Page::query()
            ->select(['id', 'site_id', 'parent_id'])
            ->whereKey($this->recordKey)
            ->first();

        $query->select([
            'pages.id',
            'pages.name',
            'pages.blueprint_id',
            'pages.site_id',
            'pages.parent_id',
            'pages._lft',
            'pages._rgt',
        ])
            ->with([
                'site:id,name,default',
                'pageUrl:id,pageable_type,pageable_id,site_id,language_id,url',
                'pageUrl.siteDomain:id,site_id,language_id,domain,path,scheme',
                ...($hasPageHierarchy ? ['ancestors:pages.id,name,parent_id,_lft,_rgt'] : []),
            ])
            ->whereHas(
                'type',
                fn (BuilderContract $query): BuilderContract => $query->adminResource($this->resourceName()),
            )
            ->whereNot('id', $this->recordKey);

        $this->applyPagePriorityOrdering($query, $currentPage);

        return $query->orderBy('pages.name');
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function applyPagePriorityOrdering(Builder $query, ?Page $currentPage): Builder
    {
        if (! $currentPage instanceof Page) {
            return $query;
        }

        if ($currentPage->parent_id === null) {
            return $query->orderByRaw(
                'case when pages.parent_id is null then 0 when pages.site_id = ? then 1 else 2 end',
                [$currentPage->site_id],
            );
        }

        return $query->orderByRaw(
            'case when pages.parent_id = ? then 0 when pages.site_id = ? then 1 else 2 end',
            [$currentPage->parent_id, $currentPage->site_id],
        );
    }

    private function resourceName(): string
    {
        $resourceName = [$this->resourceClass, 'getResourceName'];

        if (! is_callable($resourceName)) {
            return class_basename($this->resourceClass);
        }

        return (string) $resourceName();
    }

    /** @param Builder<Model> $query */
    private function applyAttributeConstraints(Builder $query, string $search): void
    {
        $search = Str::lower($search);

        foreach (explode(' ', $search) as $searchWord) {
            $query->where(function (Builder $query) use ($searchWord): void {
                $isFirst = true;

                foreach ($this->searchColumns() as $attributes) {
                    $this->applyAttributeConstraint($query, $searchWord, Arr::wrap($attributes), $isFirst);
                }
            });
        }
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<int, string>  $searchAttributes
     */
    private function applyAttributeConstraint(
        Builder $query,
        string $search,
        array $searchAttributes,
        bool &$isFirst,
    ): void {
        /** @var Connection $databaseConnection */
        $databaseConnection = $query->getConnection();

        foreach ($searchAttributes as $searchAttribute) {
            $whereClause = $isFirst ? 'where' : 'orWhere';
            $whereHasClause = $isFirst ? 'whereHas' : 'orWhereHas';

            $query->when(
                str($searchAttribute)->contains('.') && ! str($searchAttribute)->contains('`'),
                fn (Builder $query): Builder => $query->{$whereHasClause}(
                    (string) str($searchAttribute)->beforeLast('.'),
                    fn (Builder $query): Builder => $query->where(
                        $this->searchColumnExpression($query, (string) str($searchAttribute)->afterLast('.'), $databaseConnection),
                        'like',
                        sprintf('%%%s%%', $search),
                    ),
                ),
                fn (Builder $query): Builder => $query->{$whereClause}(
                    $this->searchColumnExpression($query, $searchAttribute, $databaseConnection),
                    'like',
                    sprintf('%%%s%%', $search),
                ),
            );

            $isFirst = false;
        }
    }

    /** @return array<int, string|array<int, string>> */
    private function searchColumns(): array
    {
        $modelClass = $this->resourceClass::getModel();

        if ($modelClass === Page::class || is_subclass_of($modelClass, Page::class)) {
            return ['`pages`.`name`'];
        }

        return $this->resourceClass::getGloballySearchableAttributes();
    }

    /** @param Builder<Model> $query */
    private function searchColumnExpression(Builder $query, string $column, Connection $databaseConnection): QueryExpressionContract
    {
        $qualifiedColumn = str_contains($column, '`')
            ? $column
            : $databaseConnection->getQueryGrammar()->wrap($query->qualifyColumn($column));

        $columnExpression = sprintf('lower(%s)', $qualifiedColumn);
        $collation = $databaseConnection->getConfig('search_collation');

        if (filled($collation)) {
            $columnExpression = sprintf('%s collate %s', $columnExpression, $collation);
        }

        return new Expression($columnExpression);
    }

    /**
     * @return array{value: string, label: string, group?: string}
     */
    private function item(Model $model): array
    {
        $item = [
            'value' => $this->resourceClass::getUrl('edit', ['record' => $model]),
            'label' => $this->itemLabel($model),
        ];

        $group = $this->itemGroup($model);

        if (filled($group)) {
            $item['group'] = $group;
        }

        return $item;
    }

    private function itemLabel(Model $model): string
    {
        if (! $model instanceof Page) {
            $label = $this->resourceClass::getRecordTitle($model);

            return $label instanceof Htmlable ? $label->toHtml() : (string) $label;
        }

        $label = e($model->name);

        if ($model->ancestors->isNotEmpty()) {
            $label = $model->ancestors
                ->map(fn (Page $ancestor): string => e(Str::limit($ancestor->name, 30)))
                ->implode(' &raquo; ')
                . ' &raquo; ' . $label;
        }

        $url = $this->pageUrl($model);

        return $label . ($url !== '' ? sprintf("<br /><span class='text-xs tracking-wider text-gray-500 dark:text-gray-400'>%s</span>", e($url)) : '');
    }

    private function itemGroup(Model $model): ?string
    {
        if ($model instanceof Page) {
            return $model->site?->name;
        }

        return null;
    }

    private function pageUrl(Page $model): string
    {
        $pageUrl = $model->relationLoaded('pageUrl')
            ? $model->getRelation('pageUrl')
            : $model->pageUrl()->with('siteDomain')->first();

        if (! $pageUrl instanceof PageUrl || ! $pageUrl->exists) {
            return '';
        }

        $pageUrl->loadMissing('siteDomain');

        try {
            return $pageUrl->fullUrl();
        } catch (UrlMissingSiteDomainException) {
            return '';
        }
    }
}
