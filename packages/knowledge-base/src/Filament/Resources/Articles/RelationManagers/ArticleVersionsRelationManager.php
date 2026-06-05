<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Articles\RelationManagers;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ArticleVersionsRelationManager extends RelationManager
{
    protected static ?string $recordTitleAttribute = 'version';

    protected static string $relationship = 'versions';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-knowledge-base::generic.admin.relations.versions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('version')
                    ->label(__('capell-knowledge-base::generic.admin.fields.version'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('capell-knowledge-base::generic.admin.fields.title'))
                    ->searchable()
                    ->wrap(),
                IconColumn::make('current_version')
                    ->label(__('capell-knowledge-base::generic.admin.fields.current_version'))
                    ->state(function (KnowledgeBaseArticleVersion $record): bool {
                        $recordKey = $record->getKey();
                        $currentVersionId = $this->getOwnerRecord()->getAttribute('current_version_id');

                        if ((! is_int($recordKey) && ! is_string($recordKey)) || (! is_int($currentVersionId) && ! is_string($currentVersionId))) {
                            return false;
                        }

                        return (int) $recordKey === (int) $currentVersionId;
                    })
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label(__('capell-knowledge-base::generic.admin.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('capell-knowledge-base::generic.admin.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    #[Override]
    protected function canCreate(): bool
    {
        return false;
    }

    #[Override]
    protected function canEdit(Model $record): bool
    {
        return false;
    }

    #[Override]
    protected function canDelete(Model $record): bool
    {
        return false;
    }
}
