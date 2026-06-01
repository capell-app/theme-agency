<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Collections;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\KnowledgeBase\Filament\Resources\Collections\Pages\CreateKnowledgeBaseCollection;
use Capell\KnowledgeBase\Filament\Resources\Collections\Pages\ListKnowledgeBaseCollections;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class KnowledgeBaseCollectionResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-knowledge-base::generic.admin.sections.collection'))
                ->schema([
                    TextInput::make('title')
                        ->label(__('capell-knowledge-base::generic.admin.fields.title'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('slug')
                        ->label(__('capell-knowledge-base::generic.admin.fields.slug'))
                        ->maxLength(255),
                    TextInput::make('key')
                        ->label(__('capell-knowledge-base::generic.admin.fields.key'))
                        ->maxLength(255),
                    Select::make('parent_id')
                        ->label(__('capell-knowledge-base::generic.admin.fields.parent_collection'))
                        ->options(static fn (): array => KnowledgeBaseCollection::query()
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->all())
                        ->searchable(),
                    Textarea::make('description')
                        ->label(__('capell-knowledge-base::generic.admin.fields.description'))
                        ->rows(3),
                    TextInput::make('sort_order')
                        ->label(__('capell-knowledge-base::generic.admin.fields.sort_order'))
                        ->integer()
                        ->default(0)
                        ->minValue(0),
                    Select::make('is_public')
                        ->label(__('capell-knowledge-base::generic.admin.fields.visibility'))
                        ->options([
                            '1' => __('capell-knowledge-base::generic.admin.visibility.public'),
                            '0' => __('capell-knowledge-base::generic.admin.visibility.private'),
                        ])
                        ->default('1')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('capell-knowledge-base::generic.admin.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('capell-knowledge-base::generic.admin.fields.slug'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label(__('capell-knowledge-base::generic.admin.fields.public'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('capell-knowledge-base::generic.admin.fields.sort_order'))
                    ->sortable(),
                TextColumn::make('articles_count')
                    ->label(__('capell-knowledge-base::generic.admin.fields.articles'))
                    ->counts('articles')
                    ->sortable(),
            ])
            ->defaultSort('sort_order');
    }

    #[Override]
    public static function getModel(): string
    {
        return KnowledgeBaseCollection::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-knowledge-base::generic.admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-knowledge-base::generic.admin.resources.collections');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(KnowledgeBaseServiceProvider::$packageName);
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('capell-knowledge-base::generic.admin.models.collection');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('capell-knowledge-base::generic.admin.models.collections');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListKnowledgeBaseCollections::route('/'),
            'create' => CreateKnowledgeBaseCollection::route('/create'),
        ];
    }
}
