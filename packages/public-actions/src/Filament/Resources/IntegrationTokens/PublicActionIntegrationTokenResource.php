<?php

declare(strict_types=1);

namespace Capell\PublicActions\Filament\Resources\IntegrationTokens;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Core\Facades\CapellCore;
use Capell\PublicActions\Actions\RevokePublicActionIntegrationTokenAction;
use Capell\PublicActions\Enums\PublicActionIntegrationProvider;
use Capell\PublicActions\Filament\Resources\Concerns\PublicActionFilamentOptions;
use Capell\PublicActions\Filament\Resources\IntegrationTokens\Pages\ListPublicActionIntegrationTokens;
use Capell\PublicActions\Models\PublicActionIntegrationToken;
use Capell\PublicActions\Providers\PublicActionsServiceProvider;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class PublicActionIntegrationTokenResource extends Resource
{
    use PublicActionFilamentOptions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 44;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('created_at'))
            ->columns([
                TextColumn::make('name')->label(__('capell-public-actions::filament.fields.name'))->searchable(),
                TextColumn::make('provider')->label(__('capell-public-actions::filament.fields.provider'))->badge(),
                TextColumn::make('last_used_at')->label(__('capell-public-actions::filament.fields.last_used_at'))->dateTime()->sortable(),
                TextColumn::make('revoked_at')->label(__('capell-public-actions::filament.fields.revoked_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->label(__('capell-public-actions::filament.fields.provider'))
                    ->options(self::enumOptions(PublicActionIntegrationProvider::class)),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label(__('capell-public-actions::filament.actions.revoke'))
                    ->requiresConfirmation()
                    ->authorize('update')
                    ->visible(fn (PublicActionIntegrationToken $record): bool => ! $record->isRevoked())
                    ->action(fn (PublicActionIntegrationToken $record): PublicActionIntegrationToken => RevokePublicActionIntegrationTokenAction::run($record)),
            ]);
    }

    /** @return class-string<PublicActionIntegrationToken> */
    #[Override]
    public static function getModel(): string
    {
        return PublicActionIntegrationToken::class;
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
        return ['index' => ListPublicActionIntegrationTokens::route('/')];
    }
}
