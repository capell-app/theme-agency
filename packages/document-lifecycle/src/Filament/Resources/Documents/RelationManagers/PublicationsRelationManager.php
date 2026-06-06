<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers;

use BackedEnum;
use Capell\DocumentLifecycle\Actions\BuildDocumentPublicationDiffAction;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicationsRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = 'heroicon-o-document-check';

    protected static string $relationship = 'publications';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-document-lifecycle::navigation.relations.publications');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('published_at')->latest('id'))
            ->actions([
                Action::make('download_publication_diff')
                    ->label(__('capell-document-lifecycle::navigation.actions.download_publication_diff'))
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->action(fn (DocumentPublication $record): StreamedResponse => response()->streamDownload(
                        function () use ($record): void {
                            echo BuildDocumentPublicationDiffAction::run($record);
                        },
                        'document-publication-diff-' . $record->getKey() . '-' . now()->format('Y-m-d-His') . '.json',
                        ['Content-Type' => 'application/json'],
                    )),
            ])
            ->columns([
                TextColumn::make('version_label')
                    ->label(__('capell-document-lifecycle::navigation.fields.version'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content_hash')
                    ->label(__('capell-document-lifecycle::navigation.fields.hash'))
                    ->copyable()
                    ->limit(16)
                    ->searchable(),
                TextColumn::make('published_revision_id')
                    ->label(__('capell-document-lifecycle::navigation.fields.revision'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label(__('capell-document-lifecycle::navigation.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    #[Override]
    protected static function getPluralModelLabel(): string
    {
        return __('capell-document-lifecycle::navigation.relations.publications');
    }
}
