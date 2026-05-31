<?php

declare(strict_types=1);

namespace Capell\PublicActions\Filament\Resources\PublicActions;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Site;
use Capell\PublicActions\Enums\PublicActionStatus;
use Capell\PublicActions\Filament\Resources\Concerns\PublicActionFilamentOptions;
use Capell\PublicActions\Filament\Resources\PublicActions\Pages\CreatePublicAction;
use Capell\PublicActions\Filament\Resources\PublicActions\Pages\EditPublicAction;
use Capell\PublicActions\Filament\Resources\PublicActions\Pages\ListPublicActions;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Providers\PublicActionsServiceProvider;
use Capell\PublicActions\Support\PublicActionHandlerRegistry;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Override;

final class PublicActionResource extends Resource
{
    use PublicActionFilamentOptions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->columns(['default' => 1, 'lg' => 2])->schema([
            TextInput::make('key')
                ->label(__('capell-public-actions::filament.fields.key'))
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('name')
                ->label(__('capell-public-actions::filament.fields.name'))
                ->required(),
            Select::make('status')
                ->label(__('capell-public-actions::filament.fields.status'))
                ->options(self::enumOptions(PublicActionStatus::class))
                ->required(),
            Select::make('handler_key')
                ->label(__('capell-public-actions::filament.fields.handler'))
                ->options(fn (): array => self::handlerOptions())
                ->required(),
            Select::make('site_id')
                ->label(__('capell-public-actions::filament.fields.site'))
                ->options(fn (): array => self::canScopeToSites()
                    ? SiteScope::applyForCurrentActor(Site::query()->select(['name', 'id']), 'id')->ordered()->pluck('name', 'id')->all()
                    : [])
                ->searchable()
                ->preload()
                ->visible(fn (): bool => self::canScopeToSites())
                ->afterStateUpdated(fn (?int $state, callable $set): mixed => $set('site_scope_key', $state === null ? 'global' : 'site:' . $state)),
            TextInput::make('site_scope_key')
                ->label(__('capell-public-actions::filament.fields.site_scope_key'))
                ->default('global')
                ->disabled()
                ->dehydrated(false),
            TextInput::make('success_redirect_url')
                ->label(__('capell-public-actions::filament.fields.success_redirect_url'))
                ->url(),
            TextInput::make('failure_redirect_url')
                ->label(__('capell-public-actions::filament.fields.failure_redirect_url'))
                ->url(),
            TextInput::make('success_message')
                ->label(__('capell-public-actions::filament.fields.success_message')),
            TextInput::make('failure_message')
                ->label(__('capell-public-actions::filament.fields.failure_message')),
            Toggle::make('settings.zapier_enabled')
                ->label(__('capell-public-actions::filament.fields.zapier_enabled')),
            Toggle::make('settings.api_enabled')
                ->label(__('capell-public-actions::filament.fields.api_enabled')),
            KeyValue::make('payload_schema')
                ->label(__('capell-public-actions::filament.fields.payload_schema'))
                ->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label(__('capell-public-actions::filament.fields.key'))->searchable()->sortable(),
                TextColumn::make('name')->label(__('capell-public-actions::filament.fields.name'))->searchable()->sortable(),
                TextColumn::make('status')->label(__('capell-public-actions::filament.fields.status'))->badge()->sortable(),
                TextColumn::make('handler_key')->label(__('capell-public-actions::filament.fields.handler'))->toggleable(),
                TextColumn::make('submissions_count')->label(__('capell-public-actions::filament.fields.submissions'))->counts('submissions'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-public-actions::filament.fields.status'))
                    ->options(self::enumOptions(PublicActionStatus::class)),
            ])
            ->recordActions([EditAction::make()]);
    }

    /** @return class-string<PublicAction> */
    #[Override]
    public static function getModel(): string
    {
        return PublicAction::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return SiteScope::applyForCurrentActor(parent::getEloquentQuery());
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_websites');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-public-actions::filament.navigation_group');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::getPackage(PublicActionsServiceProvider::$packageName)->isInstalled();
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPublicActions::route('/'),
            'create' => CreatePublicAction::route('/create'),
            'edit' => EditPublicAction::route('/{record}/edit'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareFormDataForPersistence(array $data): array
    {
        if (! self::canScopeToSites()) {
            $data['site_id'] = null;
            $data['site_scope_key'] = 'global';

            return $data;
        }

        $siteId = self::siteIdFromData($data['site_id'] ?? null);
        $actor = auth()->user();

        if ($siteId === null) {
            throw_unless(
                $actor instanceof Authenticatable && SiteScope::isGlobalActor($actor),
                AuthorizationException::class,
            );

            $data['site_id'] = null;
            $data['site_scope_key'] = 'global';

            return $data;
        }

        $site = Site::query()->find($siteId);

        throw_unless(
            $site instanceof Site && SiteScope::actorCanUseSite($actor, $site),
            AuthorizationException::class,
        );

        $data['site_id'] = $siteId;
        $data['site_scope_key'] = 'site:' . $siteId;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private static function handlerOptions(): array
    {
        return collect(resolve(PublicActionHandlerRegistry::class)->all())
            ->mapWithKeys(fn (object $handler, string $key): array => [$key => $key])
            ->all();
    }

    private static function canScopeToSites(): bool
    {
        return DatabaseSchema::hasTable('sites');
    }

    private static function siteIdFromData(mixed $siteId): ?int
    {
        if ($siteId === null || $siteId === '') {
            return null;
        }

        throw_unless(is_int($siteId) || is_numeric($siteId), AuthorizationException::class);

        return (int) $siteId;
    }
}
