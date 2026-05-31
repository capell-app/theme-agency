<?php

declare(strict_types=1);

namespace Capell\PublicActions\Filament\Resources\Destinations;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Core\Facades\CapellCore;
use Capell\PublicActions\Enums\PublicActionDestinationStatus;
use Capell\PublicActions\Filament\Resources\Concerns\PublicActionFilamentOptions;
use Capell\PublicActions\Filament\Resources\Destinations\Pages\CreatePublicActionDestination;
use Capell\PublicActions\Filament\Resources\Destinations\Pages\EditPublicActionDestination;
use Capell\PublicActions\Filament\Resources\Destinations\Pages\ListPublicActionDestinations;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Providers\PublicActionsServiceProvider;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class PublicActionDestinationResource extends Resource
{
    use PublicActionFilamentOptions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpRight;

    protected static ?int $navigationSort = 42;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->columns(['default' => 1, 'lg' => 2])->schema([
            Select::make('public_action_id')
                ->label(__('capell-public-actions::filament.fields.action'))
                ->relationship(
                    'action',
                    'name',
                    modifyQueryUsing: fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query),
                )
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('name')
                ->label(__('capell-public-actions::filament.fields.name'))
                ->required(),
            Select::make('status')
                ->label(__('capell-public-actions::filament.fields.status'))
                ->options(self::enumOptions(PublicActionDestinationStatus::class))
                ->required(),
            TextInput::make('adapter')
                ->label(__('capell-public-actions::filament.fields.adapter'))
                ->default('http_webhook')
                ->required(),
            TextInput::make('endpoint_url')
                ->label(__('capell-public-actions::filament.fields.endpoint_url'))
                ->password()
                ->revealable(),
            TextInput::make('secret')
                ->label(__('capell-public-actions::filament.fields.secret'))
                ->password()
                ->revealable(),
            KeyValue::make('headers')
                ->label(__('capell-public-actions::filament.fields.headers'))
                ->columnSpanFull(),
            KeyValue::make('settings')
                ->label(__('capell-public-actions::filament.fields.settings'))
                ->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('action')->latest('updated_at'))
            ->columns([
                TextColumn::make('action.key')->label(__('capell-public-actions::filament.fields.action'))->searchable(),
                TextColumn::make('name')->label(__('capell-public-actions::filament.fields.name'))->searchable(),
                TextColumn::make('adapter')->label(__('capell-public-actions::filament.fields.adapter')),
                TextColumn::make('status')->label(__('capell-public-actions::filament.fields.status'))->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-public-actions::filament.fields.status'))
                    ->options(self::enumOptions(PublicActionDestinationStatus::class)),
            ])
            ->recordActions([EditAction::make()]);
    }

    /** @return class-string<PublicActionDestination> */
    #[Override]
    public static function getModel(): string
    {
        return PublicActionDestination::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('action', fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query));
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_websites');
    }

    #[Override]
    public static function getNavigationParentItem(): string
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
            'index' => ListPublicActionDestinations::route('/'),
            'create' => CreatePublicActionDestination::route('/create'),
            'edit' => EditPublicActionDestination::route('/{record}/edit'),
        ];
    }
}
