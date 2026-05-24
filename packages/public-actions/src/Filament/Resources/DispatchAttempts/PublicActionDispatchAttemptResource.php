<?php

declare(strict_types=1);

namespace Capell\PublicActions\Filament\Resources\DispatchAttempts;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Capell\PublicActions\Filament\Resources\Concerns\PublicActionFilamentOptions;
use Capell\PublicActions\Filament\Resources\DispatchAttempts\Pages\ListPublicActionDispatchAttempts;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Providers\PublicActionsServiceProvider;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class PublicActionDispatchAttemptResource extends Resource
{
    use PublicActionFilamentOptions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?int $navigationSort = 43;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['submission.action', 'destination'])->latest('dispatched_at'))
            ->columns([
                TextColumn::make('submission.action.key')->label(__('capell-public-actions::filament.fields.action')),
                TextColumn::make('destination.name')->label(__('capell-public-actions::filament.fields.destination')),
                TextColumn::make('adapter')->label(__('capell-public-actions::filament.fields.adapter')),
                TextColumn::make('status')->label(__('capell-public-actions::filament.fields.status'))->badge()->sortable(),
                TextColumn::make('response_status')->label(__('capell-public-actions::filament.fields.response_status'))->sortable(),
                TextColumn::make('dispatched_at')->label(__('capell-public-actions::filament.fields.dispatched_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-public-actions::filament.fields.status'))
                    ->options(self::enumOptions(PublicActionDispatchStatus::class)),
            ]);
    }

    /** @return class-string<PublicActionDispatchAttempt> */
    #[Override]
    public static function getModel(): string
    {
        return PublicActionDispatchAttempt::class;
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
        return ['index' => ListPublicActionDispatchAttempts::route('/')];
    }
}
