<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Filament\Resources\Documents;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\DocumentLifecycle\Actions\ArchiveDocumentAction;
use Capell\DocumentLifecycle\Actions\PublishDocumentAction;
use Capell\DocumentLifecycle\Actions\RecordDocumentAcceptanceAction;
use Capell\DocumentLifecycle\Actions\RestoreDocumentAction;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Filament\Resources\Documents\Pages\CreateDocument;
use Capell\DocumentLifecycle\Filament\Resources\Documents\Pages\EditDocument;
use Capell\DocumentLifecycle\Filament\Resources\Documents\Pages\ListDocuments;
use Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers\AcceptancesRelationManager;
use Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers\PublicationsRelationManager;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Providers\DocumentLifecycleServiceProvider;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Override;

final class DocumentResource extends Resource
{
    protected static ?string $slug = 'document-lifecycle/documents';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label(__('capell-document-lifecycle::navigation.fields.key'))
                    ->required()
                    ->maxLength(255)
                    ->disabledOn('edit'),
                TextInput::make('title')
                    ->label(__('capell-document-lifecycle::navigation.fields.title'))
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->label(__('capell-document-lifecycle::navigation.fields.status'))
                    ->options(DocumentStatusEnum::class)
                    ->required(),
                KeyValue::make('metadata')
                    ->label(__('capell-document-lifecycle::navigation.fields.metadata'))
                    ->columnSpanFull(),
                DateTimePicker::make('review_due_at')
                    ->label(__('capell-document-lifecycle::navigation.fields.review_due_at')),
                DateTimePicker::make('expires_at')
                    ->label(__('capell-document-lifecycle::navigation.fields.expires_at')),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('publications')->latest('updated_at'))
            ->columns([
                TextColumn::make('key')
                    ->label(__('capell-document-lifecycle::navigation.fields.key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('capell-document-lifecycle::navigation.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('capell-document-lifecycle::navigation.fields.status'))
                    ->badge()
                    ->color(fn (DocumentStatusEnum $state): string => $state->getColor())
                    ->sortable(),
                TextColumn::make('publications_count')
                    ->label(__('capell-document-lifecycle::navigation.fields.publications'))
                    ->sortable(),
                TextColumn::make('review_due_at')
                    ->label(__('capell-document-lifecycle::navigation.fields.review_due_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('capell-document-lifecycle::navigation.fields.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('capell-document-lifecycle::navigation.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                self::publishVersionRecordAction(),
                self::recordAcceptanceRecordAction(),
                Action::make('archive')
                    ->label(__('capell-document-lifecycle::navigation.actions.archive_document'))
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Document $record): bool => $record->status !== DocumentStatusEnum::Archived
                        && Gate::allows('update', $record))
                    ->action(function (Document $record): void {
                        Gate::authorize('update', $record);

                        ArchiveDocumentAction::run($record);

                        Notification::make('document-lifecycle-document-archived')
                            ->title(__('capell-document-lifecycle::navigation.messages.document_archived'))
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->send();
                    }),
                Action::make('restore')
                    ->label(__('capell-document-lifecycle::navigation.actions.restore_document'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Document $record): bool => $record->status === DocumentStatusEnum::Archived
                        && Gate::allows('update', $record))
                    ->action(function (Document $record): void {
                        Gate::authorize('update', $record);

                        RestoreDocumentAction::run($record);

                        Notification::make('document-lifecycle-document-restored')
                            ->title(__('capell-document-lifecycle::navigation.messages.document_restored'))
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->send();
                    }),
            ]);
    }

    /** @return class-string<Document> */
    #[Override]
    public static function getModel(): string
    {
        return Document::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_workflow');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-document-lifecycle::navigation.documents');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('capell-document-lifecycle::navigation.document');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('capell-document-lifecycle::navigation.documents');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(DocumentLifecycleServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            PublicationsRelationManager::class,
            AcceptancesRelationManager::class,
        ];
    }

    private static function publishVersionRecordAction(): Action
    {
        return Action::make('publish_version')
            ->label(__('capell-document-lifecycle::navigation.actions.publish_version'))
            ->icon('heroicon-o-cloud-arrow-up')
            ->color('success')
            ->visible(fn (Document $record): bool => $record->status !== DocumentStatusEnum::Archived
                && Gate::allows('update', $record))
            ->schema([
                TextInput::make('version_label')
                    ->label(__('capell-document-lifecycle::navigation.fields.version'))
                    ->maxLength(255),
                Textarea::make('content')
                    ->label(__('capell-document-lifecycle::navigation.fields.content'))
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),
                Textarea::make('metadata_note')
                    ->label(__('capell-document-lifecycle::navigation.fields.metadata_note'))
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->action(function (Document $record, array $data): void {
                Gate::authorize('update', $record);

                PublishDocumentAction::run(
                    document: $record,
                    content: (string) $data['content'],
                    versionLabel: self::nullableString($data['version_label'] ?? null),
                    publishedActor: self::authenticatedModel(),
                    metadata: self::metadataFromNote($data['metadata_note'] ?? null),
                );

                Notification::make('document-lifecycle-version-published')
                    ->title(__('capell-document-lifecycle::navigation.messages.version_published'))
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->send();
            });
    }

    private static function recordAcceptanceRecordAction(): Action
    {
        return Action::make('record_acceptance')
            ->label(__('capell-document-lifecycle::navigation.actions.record_acceptance'))
            ->icon('heroicon-o-check-badge')
            ->color('gray')
            ->requiresConfirmation()
            ->visible(fn (Document $record): bool => ((int) ($record->publications_count ?? $record->publications()->count())) > 0
                && Gate::allows('update', $record))
            ->schema([
                TextInput::make('context')
                    ->label(__('capell-document-lifecycle::navigation.fields.context'))
                    ->default('admin')
                    ->maxLength(255),
                Textarea::make('metadata_note')
                    ->label(__('capell-document-lifecycle::navigation.fields.metadata_note'))
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->action(function (Document $record, array $data): void {
                Gate::authorize('update', $record);

                RecordDocumentAcceptanceAction::run(
                    documentKey: $record->key,
                    acceptor: self::authenticatedModel(),
                    context: self::nullableString($data['context'] ?? null),
                    metadata: self::metadataFromNote($data['metadata_note'] ?? null),
                    ipAddress: request()->ip(),
                    userAgent: request()->userAgent(),
                );

                Notification::make('document-lifecycle-acceptance-recorded')
                    ->title(__('capell-document-lifecycle::navigation.messages.acceptance_recorded'))
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->send();
            });
    }

    private static function authenticatedModel(): ?Model
    {
        $user = Auth::user();

        return $user instanceof Model ? $user : null;
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array{admin_note: string}|array{}
     */
    private static function metadataFromNote(mixed $note): array
    {
        $note = self::nullableString($note);

        return $note === null ? [] : ['admin_note' => $note];
    }
}
