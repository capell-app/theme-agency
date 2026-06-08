<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Articles\RelationManagers;

use BackedEnum;
use Capell\KnowledgeBase\Actions\RelateKnowledgeBaseArticlesAction;
use Capell\KnowledgeBase\Enums\KnowledgeBaseRelatedArticleType;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseRelatedArticle;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class RelatedArticlesRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = 'heroicon-o-link';

    protected static ?string $recordTitleAttribute = 'relatedArticle.title';

    protected static string $relationship = 'relatedArticleLinks';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-knowledge-base::generic.admin.relations.related_articles');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['relatedArticle.collection']))
            ->defaultSort('sort_order')
            ->headerActions([
                Action::make('relate_article')
                    ->label(__('capell-knowledge-base::generic.admin.actions.relate_article'))
                    ->icon('heroicon-o-plus')
                    ->schema($this->relationFormSchema())
                    ->action(fn (array $data): KnowledgeBaseRelatedArticle => $this->relateArticle($data)),
            ])
            ->recordActions([
                Action::make('update_relation')
                    ->label(__('capell-knowledge-base::generic.admin.actions.update_relation'))
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (KnowledgeBaseRelatedArticle $record): array => [
                        'related_article_id' => $record->related_article_id,
                        'relation_type' => $record->relation_type->value,
                        'sort_order' => $record->sort_order,
                    ])
                    ->schema($this->relationFormSchema())
                    ->action(fn (KnowledgeBaseRelatedArticle $record, array $data): KnowledgeBaseRelatedArticle => $this->relateArticle($data, $record)),
            ])
            ->columns([
                TextColumn::make('relatedArticle.title')
                    ->label(__('capell-knowledge-base::generic.admin.fields.related_article'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('relatedArticle.collection.title')
                    ->label(__('capell-knowledge-base::generic.admin.fields.collection'))
                    ->toggleable(),
                TextColumn::make('relation_type')
                    ->label(__('capell-knowledge-base::generic.admin.fields.relation_type'))
                    ->formatStateUsing(fn (KnowledgeBaseRelatedArticleType|string|null $state): string => $this->formatRelationType($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('capell-knowledge-base::generic.admin.fields.sort_order'))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('capell-knowledge-base::generic.admin.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    #[Override]
    protected function canCreate(): bool
    {
        return false;
    }

    /**
     * @return array<int, Select|TextInput>
     */
    private function relationFormSchema(): array
    {
        return [
            Select::make('related_article_id')
                ->label(__('capell-knowledge-base::generic.admin.fields.related_article'))
                ->options(fn (): array => $this->articleOptions())
                ->searchable()
                ->required(),
            Select::make('relation_type')
                ->label(__('capell-knowledge-base::generic.admin.fields.relation_type'))
                ->options($this->relationTypeOptions())
                ->default(KnowledgeBaseRelatedArticleType::Related->value)
                ->required(),
            TextInput::make('sort_order')
                ->label(__('capell-knowledge-base::generic.admin.fields.sort_order'))
                ->integer()
                ->minValue(0)
                ->default(0),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function articleOptions(): array
    {
        $ownerRecord = $this->ownerArticle();

        return KnowledgeBaseArticle::query()
            ->whereKeyNot($ownerRecord->getKey())
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function relationTypeOptions(): array
    {
        $options = [];

        foreach (KnowledgeBaseRelatedArticleType::cases() as $relationType) {
            $options[$relationType->value] = $relationType->getLabel();
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function relateArticle(array $data, ?KnowledgeBaseRelatedArticle $existingRelation = null): KnowledgeBaseRelatedArticle
    {
        $ownerRecord = $this->ownerArticle();
        $relatedArticle = KnowledgeBaseArticle::query()->findOrFail((int) $data['related_article_id']);
        $relationType = KnowledgeBaseRelatedArticleType::from((string) $data['relation_type']);
        $sortOrder = max(0, (int) ($data['sort_order'] ?? 0));

        if ($existingRelation instanceof KnowledgeBaseRelatedArticle && (int) $existingRelation->related_article_id !== (int) $relatedArticle->getKey()) {
            $existingRelation->delete();
        }

        return RelateKnowledgeBaseArticlesAction::run($ownerRecord, $relatedArticle, $relationType, $sortOrder);
    }

    private function ownerArticle(): KnowledgeBaseArticle
    {
        $ownerRecord = $this->getOwnerRecord();

        /** @var KnowledgeBaseArticle $ownerRecord */
        return $ownerRecord;
    }

    private function formatRelationType(KnowledgeBaseRelatedArticleType|string|null $state): string
    {
        if ($state instanceof KnowledgeBaseRelatedArticleType) {
            return $state->getLabel();
        }

        if (is_string($state) && ($relationType = KnowledgeBaseRelatedArticleType::tryFrom($state)) instanceof KnowledgeBaseRelatedArticleType) {
            return $relationType->getLabel();
        }

        return '';
    }
}
