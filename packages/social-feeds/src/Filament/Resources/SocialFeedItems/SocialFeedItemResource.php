<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Filament\Resources\SocialFeedItems;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\SocialFeeds\Filament\Resources\SocialFeedItems\Pages\ListSocialFeedItems;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Providers\SocialFeedsServiceProvider;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class SocialFeedItemResource extends Resource
{
    protected static ?string $slug = 'social-feeds/items';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $recordTitleAttribute = 'external_id';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-social-feeds::package.resources.items'))
                ->schema([]),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('connection.name')->label(__('capell-social-feeds::package.fields.connection'))->searchable()->sortable(),
            TextColumn::make('provider')->label(__('capell-social-feeds::package.fields.provider'))->badge()->sortable(),
            TextColumn::make('type')->label(__('capell-social-feeds::package.fields.type'))->badge()->sortable(),
            TextColumn::make('text')->label(__('capell-social-feeds::package.fields.text'))->limit(96)->searchable(),
            TextColumn::make('author_name')->label(__('capell-social-feeds::package.fields.author'))->searchable()->toggleable(),
            TextColumn::make('published_at')->label(__('capell-social-feeds::package.fields.published_at'))->dateTime()->sortable(),
            TextColumn::make('permalink')->label(__('capell-social-feeds::package.fields.permalink'))->limit(56)->copyable()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return SocialFeedItem::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('connection');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-admin::navigation.group_growth');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-social-feeds::package.resources.items');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(SocialFeedsServiceProvider::$packageName);
    }

    /**
     * @return array<string, PageRegistration>
     */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListSocialFeedItems::route('/'),
        ];
    }
}
