<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\KnowledgeSources;

use BackedEnum;
use Capell\LiveChat\Enums\KnowledgeSourceStatus;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Capell\LiveChat\Filament\Resources\KnowledgeSources\Pages\CreateKnowledgeSource;
use Capell\LiveChat\Filament\Resources\KnowledgeSources\Pages\EditKnowledgeSource;
use Capell\LiveChat\Filament\Resources\KnowledgeSources\Pages\ListKnowledgeSources;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class KnowledgeSourceResource extends Resource
{
    protected static ?string $slug = 'live-chat/knowledge-sources';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-live-chat::generic.resources.knowledge_source'))
                ->schema([
                    TextInput::make('site_id')->label(__('capell-live-chat::generic.fields.site_id'))->numeric(),
                    Select::make('type')->label(__('capell-live-chat::generic.fields.type'))->options(self::typeOptions())->required()->default(KnowledgeSourceType::Website->value),
                    TextInput::make('source_key')->label(__('capell-live-chat::generic.fields.source_key'))->required()->maxLength(255),
                    TextInput::make('title')->label(__('capell-live-chat::generic.fields.title'))->required()->maxLength(255),
                    TextInput::make('url')->label(__('capell-live-chat::generic.fields.url'))->url()->maxLength(2048),
                    Select::make('status')->label(__('capell-live-chat::generic.fields.status'))->options(self::statusOptions())->required()->default(KnowledgeSourceStatus::Active->value),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label(__('capell-live-chat::generic.fields.title'))->searchable()->sortable(),
            TextColumn::make('type')->label(__('capell-live-chat::generic.fields.type'))->badge()->sortable(),
            TextColumn::make('source_key')->label(__('capell-live-chat::generic.fields.source_key'))->toggleable(),
            TextColumn::make('status')->label(__('capell-live-chat::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('last_synced_at')->label(__('capell-live-chat::generic.fields.last_synced_at'))->dateTime()->sortable()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return LiveChatKnowledgeSource::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-live-chat::generic.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-live-chat::generic.resources.knowledge_sources');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListKnowledgeSources::route('/'),
            'create' => CreateKnowledgeSource::route('/create'),
            'edit' => EditKnowledgeSource::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function typeOptions(): array
    {
        return collect(KnowledgeSourceType::cases())
            ->mapWithKeys(static fn (KnowledgeSourceType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(KnowledgeSourceStatus::cases())
            ->mapWithKeys(static fn (KnowledgeSourceStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
