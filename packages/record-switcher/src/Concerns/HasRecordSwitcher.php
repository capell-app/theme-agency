<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Concerns;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Database\Query\Expression as QueryExpressionContract;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;

/**
 * @mixin EditRecord
 */
trait HasRecordSwitcher
{
    protected int $maxSelectOptions = 10;

    public function getHeading(): string|Htmlable
    {
        return new HtmlString(Blade::render('capell-record-switcher::components.record-switcher', [
            'value' => $this->getRecord()->getKey(),
            'label' => $this->getRecordSwitcherTitle(),
            'limit_results' => $this->maxSelectOptions,
        ]));
    }

    public function getRecordSwitcherTitle(): string|Htmlable
    {
        return $this->getRecordTitle();
    }

    /**
     * @return list<array{value: string, label: string|Htmlable, group?: string}>
     */
    #[Renderless]
    public function getRecordSwitcherOptions(?string $search = null): array
    {
        $query = $this->getRecordSwitcherQuery();

        if (filled($search)) {
            static::applyRecordSwitcherAttributeConstraints($query, $search);
        }

        $items = $this->modifyRecordSwitcherQuery($query, $search)
            ->get()
            ->map(fn (Model $model): array => $this->recordSwitcherItem($model))
            ->values()
            ->all();

        return array_values($items);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    protected static function applyRecordSwitcherAttributeConstraints(Builder $query, string $search): void
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = $query->getConnection();

        $isForcedCaseInsensitive = static::isRecordSwitcherForcedCaseInsensitive();
        $search = static::recordSwitcherSearchTermExpression($search, $isForcedCaseInsensitive);

        foreach (explode(' ', $search) as $searchWord) {
            /** @param Builder<TModel> $query */
            $query->where(function (Builder $query) use ($searchWord): void {
                $isFirst = true;

                foreach (static::getRecordSwitcherSearchColumns() as $attributes) {
                    static::applyRecordSwitcherAttributeConstraint(
                        query: $query,
                        search: $searchWord,
                        searchAttributes: Arr::wrap($attributes),
                        isFirst: $isFirst,
                    );
                }
            });
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<int, string>  $searchAttributes
     * @return Builder<TModel>
     */
    protected static function applyRecordSwitcherAttributeConstraint(
        Builder $query,
        string $search,
        array $searchAttributes,
        bool &$isFirst,
    ): Builder {
        $isForcedCaseInsensitive = static::isRecordSwitcherForcedCaseInsensitive();

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
                        static::recordSwitcherSearchColumnExpression(
                            $query,
                            (string) str($searchAttribute)->afterLast('.'),
                            $isForcedCaseInsensitive,
                            $databaseConnection,
                        ),
                        'like',
                        "%{$search}%",
                    ),
                ),
                fn (Builder $query): Builder => $query->{$whereClause}(
                    static::recordSwitcherSearchColumnExpression(
                        $query,
                        $searchAttribute,
                        $isForcedCaseInsensitive,
                        $databaseConnection,
                    ),
                    'like',
                    "%{$search}%",
                ),
            );

            $isFirst = false;
        }

        return $query;
    }

    protected static function recordSwitcherSearchTermExpression(string $search, bool $isSearchForcedCaseInsensitive): string
    {
        return $isSearchForcedCaseInsensitive ? Str::lower($search) : $search;
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    protected static function recordSwitcherSearchColumnExpression(
        Builder $query,
        string $column,
        bool $isSearchForcedCaseInsensitive,
        Connection $databaseConnection,
    ): QueryExpressionContract {
        $qualifiedColumn = str_contains($column, '`') ? $column : $query->qualifyColumn($column);
        $columnExpression = $databaseConnection->getQueryGrammar()->wrap($qualifiedColumn);

        if ($isSearchForcedCaseInsensitive) {
            $columnExpression = "lower({$columnExpression})";
        }

        $collation = $databaseConnection->getConfig('search_collation');

        if (filled($collation)) {
            $columnExpression = "{$columnExpression} collate {$collation}";
        }

        return new Expression($columnExpression);
    }

    /**
     * @return array<int, string|array<int, string>>
     */
    protected static function getRecordSwitcherSearchColumns(): array
    {
        return static::getResource()::getGloballySearchableAttributes();
    }

    protected static function isRecordSwitcherForcedCaseInsensitive(): bool
    {
        return true;
    }

    protected function afterSave(): void
    {
        $this->dispatch('record-switcher:refresh', label: $this->getRecordSwitcherTitle());
    }

    /**
     * @return array{value: string, label: string|Htmlable, group?: string}
     */
    protected function recordSwitcherItem(Model $model): array
    {
        $item = [
            'value' => static::getResource()::getUrl('edit', ['record' => $model->getRouteKey()]),
            'label' => $this->recordSwitcherItemLabel($model),
        ];

        $group = $this->recordSwitcherItemGroup($model);

        if (filled($group)) {
            $item['group'] = (string) $group;
        }

        return $item;
    }

    protected function recordSwitcherItemGroup(Model $model): ?string
    {
        return null;
    }

    /** @return Builder<Model> */
    protected function getRecordSwitcherQuery(): Builder
    {
        /** @var Builder<Model> $query */
        $query = static::getResource()::getEloquentQuery();

        return $query->limit($this->maxSelectOptions);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function modifyRecordSwitcherQuery(Builder $query, ?string $search): Builder
    {
        return $query;
    }

    protected function recordSwitcherItemLabel(Model $model): string|Htmlable
    {
        return static::getResource()::getRecordTitle($model) ?? '';
    }
}
