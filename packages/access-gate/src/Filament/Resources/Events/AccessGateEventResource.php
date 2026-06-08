<?php

declare(strict_types=1);

namespace Capell\AccessGate\Filament\Resources\Events;

use BackedEnum;
use Capell\AccessGate\Enums\EventType;
use Capell\AccessGate\Filament\Resources\Concerns\AccessGateFilamentOptions;
use Capell\AccessGate\Filament\Resources\Events\Pages\ListAccessGateEvents;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\AccessGate\Support\AccessGateSiteScope;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class AccessGateEventResource extends Resource
{
    use AccessGateFilamentOptions;

    protected static ?string $slug = 'access-gate/events/access-gate-events';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Clock;

    protected static ?int $navigationSort = 35;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['area', 'registration', 'grant'])->latest('occurred_at'))
            ->columns([
                TextColumn::make('occurred_at')
                    ->label(__('capell-access-gate::filament.fields.occurred_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('capell-access-gate::filament.fields.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('area.key')
                    ->label(__('capell-access-gate::filament.fields.area'))
                    ->searchable(),
                TextColumn::make('registration.email')
                    ->label(__('capell-access-gate::filament.fields.email'))
                    ->searchable(),
                TextColumn::make('grant_id')
                    ->label(__('capell-access-gate::filament.fields.grant'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user_id')
                    ->label(__('capell-access-gate::filament.fields.user_id'))
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('access_area_id')
                    ->label(__('capell-access-gate::filament.fields.area'))
                    ->relationship('area', 'key', modifyQueryUsing: fn (Builder $query): Builder => AccessGateSiteScope::applyAreaOptionsScope($query)),
                SelectFilter::make('type')
                    ->label(__('capell-access-gate::filament.fields.type'))
                    ->options(self::enumOptions(EventType::class, 'capell-access-gate::filament.event_type')),
            ]);
    }

    /** @return class-string<Event> */
    #[Override]
    public static function getModel(): string
    {
        return Event::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return AccessGateSiteScope::applyAreaScope(parent::getEloquentQuery());
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_workflow');
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-access-gate::filament.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-access-gate::filament.resources.events');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::getPackage(AccessGateServiceProvider::$packageName)->isInstalled();
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAccessGateEvents::route('/'),
        ];
    }
}
