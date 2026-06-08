<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Articles;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\CreateKnowledgeBaseArticle;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\EditKnowledgeBaseArticle;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\ListKnowledgeBaseArticles;
use Capell\KnowledgeBase\Filament\Resources\Articles\RelationManagers\ArticleVersionsRelationManager;
use Capell\KnowledgeBase\Filament\Resources\Articles\RelationManagers\RelatedArticlesRelationManager;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class KnowledgeBaseArticleResource extends Resource
{
    protected static ?string $slug = 'knowledge-base/articles/knowledge-base-articles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-knowledge-base::generic.admin.sections.article'))
                ->schema([
                    Select::make('collection_id')
                        ->label(__('capell-knowledge-base::generic.admin.fields.collection'))
                        ->options(static fn (): array => KnowledgeBaseCollection::query()
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    Select::make('status')
                        ->label(__('capell-knowledge-base::generic.admin.fields.status'))
                        ->options(self::statusOptions())
                        ->default(KnowledgeBaseArticleStatus::Draft->value)
                        ->required(),
                    TextInput::make('title')
                        ->label(__('capell-knowledge-base::generic.admin.fields.title'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('slug')
                        ->label(__('capell-knowledge-base::generic.admin.fields.slug'))
                        ->maxLength(255),
                    Textarea::make('summary')
                        ->label(__('capell-knowledge-base::generic.admin.fields.summary'))
                        ->rows(3),
                    Textarea::make('body')
                        ->label(__('capell-knowledge-base::generic.admin.fields.body'))
                        ->required()
                        ->rows(10),
                    TextInput::make('version')
                        ->label(__('capell-knowledge-base::generic.admin.fields.version'))
                        ->default('v1')
                        ->maxLength(32),
                    TextInput::make('search_weight')
                        ->label(__('capell-knowledge-base::generic.admin.fields.search_weight'))
                        ->integer()
                        ->minValue(0)
                        ->maxValue(1000)
                        ->default(50),
                    Select::make('is_ai_readable')
                        ->label(__('capell-knowledge-base::generic.admin.fields.ai_readable'))
                        ->options([
                            '1' => __('capell-knowledge-base::generic.admin.visibility.yes'),
                            '0' => __('capell-knowledge-base::generic.admin.visibility.no'),
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount([
                'feedback',
                'feedback as helpful_feedback_count' => fn (Builder $feedbackQuery): Builder => $feedbackQuery->where('helpful', true),
            ]))
            ->columns([
                TextColumn::make('title')
                    ->label(__('capell-knowledge-base::generic.admin.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('collection.title')
                    ->label(__('capell-knowledge-base::generic.admin.fields.collection'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('capell-knowledge-base::generic.admin.fields.status'))
                    ->formatStateUsing(fn (KnowledgeBaseArticleStatus|string|null $state): string => self::formatStatus($state))
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_ai_readable')
                    ->label(__('capell-knowledge-base::generic.admin.fields.ai_readable'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('search_weight')
                    ->label(__('capell-knowledge-base::generic.admin.fields.search_weight'))
                    ->sortable(),
                TextColumn::make('feedback_count')
                    ->label(__('capell-knowledge-base::generic.admin.fields.feedback_count'))
                    ->sortable(),
                TextColumn::make('helpful_feedback_rate')
                    ->label(__('capell-knowledge-base::generic.admin.fields.helpful_feedback_rate'))
                    ->state(fn (KnowledgeBaseArticle $record): string => self::helpfulFeedbackRate($record)),
                TextColumn::make('published_at')
                    ->label(__('capell-knowledge-base::generic.admin.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-knowledge-base::generic.admin.fields.status'))
                    ->options(self::statusOptions()),
                SelectFilter::make('collection_id')
                    ->label(__('capell-knowledge-base::generic.admin.fields.collection'))
                    ->options(static fn (): array => KnowledgeBaseCollection::query()
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all()),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    #[Override]
    public static function getModel(): string
    {
        return KnowledgeBaseArticle::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-knowledge-base::generic.admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-knowledge-base::generic.admin.resources.articles');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(KnowledgeBaseServiceProvider::$packageName);
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('capell-knowledge-base::generic.admin.models.article');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('capell-knowledge-base::generic.admin.models.articles');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListKnowledgeBaseArticles::route('/'),
            'create' => CreateKnowledgeBaseArticle::route('/create'),
            'edit' => EditKnowledgeBaseArticle::route('/{record}/edit'),
        ];
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            ArticleVersionsRelationManager::class,
            RelatedArticlesRelationManager::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        $options = [];

        foreach (KnowledgeBaseArticleStatus::cases() as $status) {
            $options[$status->value] = $status->getLabel();
        }

        return $options;
    }

    private static function formatStatus(KnowledgeBaseArticleStatus|string|null $state): string
    {
        if ($state instanceof KnowledgeBaseArticleStatus) {
            return $state->getLabel();
        }

        if (is_string($state) && ($status = KnowledgeBaseArticleStatus::tryFrom($state)) instanceof KnowledgeBaseArticleStatus) {
            return $status->getLabel();
        }

        return '';
    }

    private static function helpfulFeedbackRate(KnowledgeBaseArticle $record): string
    {
        $feedbackCount = (int) ($record->getAttribute('feedback_count') ?? 0);

        if ($feedbackCount === 0) {
            return __('capell-knowledge-base::generic.admin.feedback.no_votes');
        }

        $helpfulFeedbackCount = (int) ($record->getAttribute('helpful_feedback_count') ?? 0);
        $percentage = (int) round(($helpfulFeedbackCount / $feedbackCount) * 100);

        return __('capell-knowledge-base::generic.admin.feedback.helpful_rate', [
            'percentage' => $percentage,
            'count' => $feedbackCount,
        ]);
    }
}
