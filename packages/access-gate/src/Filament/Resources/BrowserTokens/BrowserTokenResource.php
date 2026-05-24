<?php

declare(strict_types=1);

namespace Capell\AccessGate\Filament\Resources\BrowserTokens;

use BackedEnum;
use Capell\AccessGate\Actions\RevokeAccessGateBrowserTokenRecordAction;
use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Filament\Resources\BrowserTokens\Pages\ListBrowserTokens;
use Capell\AccessGate\Filament\Resources\Concerns\AccessGateFilamentOptions;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class BrowserTokenResource extends Resource
{
    use AccessGateFilamentOptions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ComputerDesktop;

    protected static ?int $navigationSort = 34;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['area', 'grant'])->latest('last_used_at'))
            ->columns([
                TextColumn::make('area.key')
                    ->label(__('capell-access-gate::filament.fields.area'))
                    ->searchable(),
                TextColumn::make('grant.email')
                    ->label(__('capell-access-gate::filament.fields.email'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('capell-access-gate::filament.fields.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('last_used_at')
                    ->label(__('capell-access-gate::filament.fields.last_used_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('capell-access-gate::filament.fields.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('revoked_at')
                    ->label(__('capell-access-gate::filament.fields.revoked_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user_agent')
                    ->label(__('capell-access-gate::filament.fields.user_agent'))
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('access_area_id')
                    ->label(__('capell-access-gate::filament.fields.area'))
                    ->relationship('area', 'key'),
                SelectFilter::make('status')
                    ->label(__('capell-access-gate::filament.fields.status'))
                    ->options(self::enumOptions(BrowserTokenStatus::class, 'capell-access-gate::filament.browser_token_status')),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label(__('capell-access-gate::filament.actions.revoke'))
                    ->color('danger')
                    ->authorize('update')
                    ->visible(fn (BrowserToken $record): bool => $record->status === BrowserTokenStatus::Active)
                    ->requiresConfirmation()
                    ->action(fn (BrowserToken $record): mixed => RevokeAccessGateBrowserTokenRecordAction::run($record)),
            ]);
    }

    /** @return class-string<BrowserToken> */
    #[Override]
    public static function getModel(): string
    {
        return BrowserToken::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_websites');
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-access-gate::filament.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-access-gate::filament.resources.browser_tokens');
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
            'index' => ListBrowserTokens::route('/'),
        ];
    }
}
