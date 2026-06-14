<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Filament\Resources\SocialFeedConnections;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\SocialFeeds\Actions\SyncSocialFeedConnectionAction;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\Pages\CreateSocialFeedConnection;
use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\Pages\EditSocialFeedConnection;
use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\Pages\ListSocialFeedConnections;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Providers\SocialFeedsServiceProvider;
use Capell\SocialFeeds\Support\SocialFeedProviderRegistry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class SocialFeedConnectionResource extends Resource
{
    protected static ?string $slug = 'social-feeds/connections';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-social-feeds::package.resources.connections'))
                ->schema([
                    TextInput::make('name')->label(__('capell-social-feeds::package.fields.name'))->required()->maxLength(255),
                    Select::make('provider')->label(__('capell-social-feeds::package.fields.provider'))->options(self::providerOptions())->required(),
                    Select::make('status')->label(__('capell-social-feeds::package.fields.status'))->options(self::statusOptions())->required()->default(SocialFeedConnectionStatus::Connected->value),
                    TextInput::make('site_id')->label(__('capell-social-feeds::package.fields.site_id'))->numeric(),
                    TextInput::make('credentials.feed_url')->label(__('capell-social-feeds::package.fields.feed_url'))->url()->maxLength(2048),
                    TextInput::make('credentials.handle')->label(__('capell-social-feeds::package.fields.handle'))->maxLength(255),
                    TextInput::make('credentials.api_key')->label(__('capell-social-feeds::package.fields.api_key'))->password()->revealable()->maxLength(2048),
                    KeyValue::make('meta')->label(__('capell-social-feeds::package.fields.meta'))->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-social-feeds::package.fields.name'))->searchable()->sortable(),
            TextColumn::make('provider')->label(__('capell-social-feeds::package.fields.provider'))->badge()->sortable(),
            TextColumn::make('status')->label(__('capell-social-feeds::package.fields.status'))->badge()->sortable(),
            TextColumn::make('sync_status')->label(__('capell-social-feeds::package.fields.sync_status'))->badge()->sortable(),
            TextColumn::make('items_count')->label(__('capell-social-feeds::package.fields.items'))->counts('items')->numeric()->sortable(),
            TextColumn::make('last_synced_at')->label(__('capell-social-feeds::package.fields.last_synced_at'))->dateTime()->sortable(),
            TextColumn::make('last_sync_error')->label(__('capell-social-feeds::package.fields.last_sync_error'))->limit(80)->toggleable(),
        ])->recordActions([
            EditAction::make(),
            self::syncNowAction(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return SocialFeedConnection::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('items');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-admin::navigation.group_growth');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-social-feeds::package.resources.connections');
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
            'index' => ListSocialFeedConnections::route('/'),
            'create' => CreateSocialFeedConnection::route('/create'),
            'edit' => EditSocialFeedConnection::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function providerOptions(): array
    {
        return collect(resolve(SocialFeedProviderRegistry::class)->all())
            ->mapWithKeys(static fn (mixed $provider, string $key): array => [$key => $provider->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return [
            SocialFeedConnectionStatus::Connected->value => __('capell-social-feeds::package.statuses.connected'),
            SocialFeedConnectionStatus::Disconnected->value => __('capell-social-feeds::package.statuses.disconnected'),
            SocialFeedConnectionStatus::Error->value => __('capell-social-feeds::package.statuses.error'),
            SocialFeedConnectionStatus::Pending->value => __('capell-social-feeds::package.statuses.pending'),
        ];
    }

    private static function syncNowAction(): Action
    {
        return Action::make('sync_now')
            ->label(__('capell-social-feeds::package.actions.sync_now'))
            ->icon('heroicon-o-arrow-path')
            ->action(function (SocialFeedConnection $record): void {
                $synced = SyncSocialFeedConnectionAction::run($record);

                Notification::make('capell_social_feeds_connection_synced')
                    ->title(trans_choice(
                        'capell-social-feeds::package.notifications.connection_synced',
                        $synced,
                        ['count' => $synced],
                    ))
                    ->success()
                    ->send();
            });
    }
}
